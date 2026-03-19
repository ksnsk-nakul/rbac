@php
    $appName = \App\Models\Setting::getValueSafe('system.app_name', config('app.name', 'Laravel'));
    $themeColor = \App\Models\Setting::getValueSafe('system.theme_color', '#f97316');
@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>403 · {{ $appName }}</title>
        <style>
            :root {
                color-scheme: light dark;
                --bg: #0f1216;
                --card: #151a21;
                --text: #e6e8ee;
                --muted: #9aa3b2;
                --accent: {{ $themeColor }};
            }
            @media (prefers-color-scheme: light) {
                :root {
                    --bg: #f6f7fb;
                    --card: #ffffff;
                    --text: #1d2433;
                    --muted: #5b6473;
                }
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif;
                background: var(--bg);
                color: var(--text);
                display: grid;
                place-items: center;
                min-height: 100vh;
                padding: 24px;
            }
            .card {
                width: 100%;
                max-width: 520px;
                background: var(--card);
                border-radius: 16px;
                padding: 32px;
                box-shadow: 0 16px 40px rgba(0,0,0,.25);
            }
            h1 { margin: 0 0 8px; font-size: 28px; }
            p { margin: 0 0 18px; color: var(--muted); }
            a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 10px 16px;
                border-radius: 10px;
                background: var(--accent);
                color: #fff;
                text-decoration: none;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Access denied</h1>
            <p>You don’t have permission to view this page.</p>
        </div>
    </body>
</html>
