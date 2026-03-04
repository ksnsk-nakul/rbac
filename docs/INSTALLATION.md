# Installation and Deployment Guide

This document covers:
- Manual installation with Apache (without Docker)
- Docker installation using `docker-compose.yml`
- DNS/domain management for production deployment

## 1. Manual Installation (Apache + PHP + MySQL + Redis)

### 1.1 Server prerequisites
Use a Linux server (Ubuntu 22.04+ recommended) with:
- PHP 8.2+ (`8.5` is used in Docker image)
- Apache 2.4+
- MySQL 8.0+
- Redis 6+
- Composer 2+
- Node.js 20+ and npm (for Vue/Vite asset build)

Install packages (Ubuntu example):

```bash
sudo apt update
sudo apt install -y apache2 mysql-server redis-server git unzip curl \
  php php-cli php-fpm php-mysql php-mbstring php-xml php-bcmath php-zip php-curl php-intl
```

Install Composer:

```bash
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
```

Install Node.js (example with NodeSource):

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 1.2 Application setup

```bash
cd /var/www
sudo git clone <your-repository-url> rbac
cd rbac
cp .env.example .env
composer install
npm install
npm run build
php artisan key:generate
```

Update `.env` values for your server:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- DB credentials (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
- Redis settings (`REDIS_HOST`, etc.)

Create MySQL DB/user (example):

```sql
CREATE DATABASE rbac;
CREATE USER 'rbac'@'%' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON rbac.* TO 'rbac'@'%';
FLUSH PRIVILEGES;
```

Run migrations:

```bash
php artisan migrate --force
```

Set Laravel permissions:

```bash
sudo chown -R www-data:www-data /var/www/rbac
sudo chmod -R 775 storage bootstrap/cache
```

### 1.3 Apache virtual host configuration
Enable Apache modules:

```bash
sudo a2enmod rewrite headers expires proxy_fcgi setenvif
sudo a2enconf php8.2-fpm
```

Create vhost file `/etc/apache2/sites-available/rbac.conf`:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/rbac/public

    <Directory /var/www/rbac/public>
        AllowOverride All
        Require all granted
        Options FollowSymLinks
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/rbac_error.log
    CustomLog ${APACHE_LOG_DIR}/rbac_access.log combined

    # If using php-fpm:
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost/"
    </FilesMatch>
</VirtualHost>
```

Enable site:

```bash
sudo a2ensite rbac.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

### 1.4 Production background tasks
Queue worker (systemd example):

Create `/etc/systemd/system/rbac-queue.service`:

```ini
[Unit]
Description=RBAC Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/rbac/artisan queue:work --sleep=3 --tries=3 --timeout=90
WorkingDirectory=/var/www/rbac

[Install]
WantedBy=multi-user.target
```

Enable service:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now rbac-queue
```

Scheduler cron:

```bash
* * * * * cd /var/www/rbac && php artisan schedule:run >> /dev/null 2>&1
```

## 2. Docker Installation

### 2.1 Current Docker stack
The project includes:
- `app`: PHP-FPM Laravel container
- `nginx`: web server container
- `db`: MySQL container
- `redis`: Redis container
- `queue`: Laravel queue worker container

Start stack:

```bash
docker compose up -d --build
```

Install dependencies and run migrations:

```bash
docker compose exec app composer install
docker compose exec app php artisan migrate
```

### 2.2 Docker parameters and purpose
Set these in `.env`.

| Variable | Purpose | Example |
|---|---|---|
| `COMPOSE_PROJECT_NAME` | Prefix for container/network/volume names; isolates multiple projects on same host. | `rbac` |
| `APP_NAME` | Build-time app name passed as Docker build arg (metadata/customization use). | `Laravel` |
| `APP_ENV` | Build-time environment passed as Docker build arg. | `local` |
| `APP_IMAGE` | Image name used for `app` and `queue`. | `rbac-app` |
| `APP_PORT` | Host port mapped to nginx port 80. | `8080` |
| `DB_IMAGE` | MySQL image/tag. | `mysql:8.0` |
| `DB_ROOT_PASSWORD` | MySQL root password inside DB container. | `root` |
| `DB_DATABASE` | Database name created/used by app. | `rbac` |
| `DB_USERNAME` | MySQL app user. | `rbac` |
| `DB_PASSWORD` | MySQL app user password. | `secret` |
| `DB_VOLUME_NAME` | Override persistent DB volume name. | `rbac_db_data` |
| `REDIS_IMAGE` | Redis image/tag. | `redis:alpine` |
| `QUEUE_WORKER_COMMAND` | Queue process command in queue container. | `php artisan queue:work` |

Laravel app variables still come from `.env` (`APP_ENV`, `APP_KEY`, `APP_URL`, `REDIS_HOST`, etc.).

### 2.3 Multi-application example
To run another Laravel app on same machine using same Docker files:

```env
COMPOSE_PROJECT_NAME=crm
APP_IMAGE=crm-app
APP_PORT=8082
DB_DATABASE=crm
DB_USERNAME=crm
DB_PASSWORD=secret
DB_ROOT_PASSWORD=root
DB_VOLUME_NAME=crm_db_data
```

Then run:

```bash
docker compose up -d --build
```

## 3. DNS Management

### 3.1 Required DNS records
For a domain like `app.example.com`:
- Add `A` record: `app.example.com` -> your server public IPv4
- (Optional) Add `AAAA` record for IPv6
- Add `CNAME` for `www` if needed: `www` -> `app.example.com`

Use low TTL (e.g., 300 seconds) during migration/cutover.

### 3.2 Validation before SSL
Check DNS propagation:

```bash
dig +short app.example.com
nslookup app.example.com
```

When domain resolves correctly, configure web server SSL.

### 3.3 SSL certificate (Let’s Encrypt, Apache)

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d app.example.com -d www.app.example.com
```

Certbot updates Apache vhost for HTTPS and auto-renews certificates.

### 3.4 APP_URL alignment
After DNS/SSL is active, set in `.env`:

```env
APP_URL=https://app.example.com
APP_ENV=production
APP_DEBUG=false
```

Then clear/rebuild Laravel caches:

```bash
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 4. Post-deployment Checklist

- Site loads over HTTPS
- Login/authentication works
- Queue worker running (`queue:work`)
- Scheduler running (`schedule:run` via cron)
- Storage/log permissions are correct
- DB backups configured
- Monitoring and alerts enabled
