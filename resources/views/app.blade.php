<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        @php
            $appName = \App\Models\Setting::getValueSafe('system.app_name', config('app.name', 'Laravel'));
            $appFavicon = \App\Models\Setting::getValueSafe('system.app_favicon', null);
            $defaultTheme = \App\Models\Setting::getValueSafe('system.theme_default', 'system');
            $themeColor = \App\Models\Setting::getValueSafe('system.theme_color', '#f97316');
        @endphp

        <title inertia>{{ $appName }}</title>

        @if($appFavicon)
            <link rel="icon" href="{{ asset('storage/'.$appFavicon) }}" sizes="any">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        @endif
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <script>
            window.__APP_SETTINGS__ = {
                appName: @json($appName),
                themeDefault: @json($defaultTheme),
                themeColor: @json($themeColor),
            };
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.js', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead

        <style>
            :root {
                --brand: {{ $themeColor }};
                --color-primary: var(--brand);
                --color-primary-hover: color-mix(in srgb, var(--brand) 85%, black);
                --color-primary-light: color-mix(in srgb, var(--brand) 15%, white);
                --primary: var(--color-primary);
                --primary-foreground: #ffffff;
                --accent: color-mix(in srgb, var(--brand) 14%, white);
                --accent-foreground: hsl(0 0% 9%);
                --ring: var(--color-primary);
                --sidebar-primary: var(--color-primary);
                --sidebar-primary-foreground: #ffffff;
                --sidebar-accent: color-mix(in srgb, var(--brand) 12%, white);
                --sidebar-accent-foreground: hsl(0 0% 20%);
                --sidebar-ring: var(--color-primary);
            }
            .dark {
                --brand: {{ $themeColor }};
                --color-primary: var(--brand);
                --color-primary-hover: color-mix(in srgb, var(--brand) 80%, black);
                --color-primary-light: color-mix(in srgb, var(--brand) 20%, black);
                --primary: var(--color-primary);
                --primary-foreground: #ffffff;
                --accent: color-mix(in srgb, var(--brand) 18%, black);
                --accent-foreground: #ffffff;
                --ring: var(--color-primary);
                --sidebar-primary: var(--color-primary);
                --sidebar-primary-foreground: #ffffff;
                --sidebar-accent: color-mix(in srgb, var(--brand) 20%, black);
                --sidebar-accent-foreground: #ffffff;
                --sidebar-ring: var(--color-primary);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div id="app" data-page="{{ json_encode($page) }}"></div>
    </body>
</html>
