<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title') — {{ config('app.name', 'Laravue') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <script>
            (function () {
                const appearance = '{{ $appearance ?? 'system' }}';

                if (appearance === 'dark' || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <style>
            :root {
                --background: hsl(0 0% 100%);
                --foreground: hsl(0 0% 3.9%);
                --muted-foreground: hsl(0 0% 45.1%);
                --primary: hsl(0 0% 9%);
                --primary-foreground: hsl(0 0% 98%);
                --border: hsl(0 0% 92.8%);
                --radius: 0.5rem;
            }

            .dark {
                --background: hsl(0 0% 3.9%);
                --foreground: hsl(0 0% 98%);
                --muted-foreground: hsl(0 0% 63.9%);
                --primary: hsl(0 0% 98%);
                --primary-foreground: hsl(0 0% 9%);
                --border: hsl(0 0% 14.9%);
            }

            *,
            *::before,
            *::after {
                box-sizing: border-box;
            }

            html {
                background-color: var(--background);
            }

            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                background-color: var(--background);
                color: var(--foreground);
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            .wrap {
                width: 100%;
                max-width: 24rem;
                padding: 1.5rem;
                text-align: center;
            }

            .logo {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.25rem;
                height: 2.25rem;
                margin-bottom: 1.5rem;
                border-radius: var(--radius);
                background-color: var(--primary);
                color: var(--primary-foreground);
            }

            .logo svg {
                width: 1.25rem;
                height: 1.25rem;
                fill: currentColor;
            }

            .code {
                margin: 0 0 0.5rem;
                font-size: 0.875rem;
                font-weight: 500;
                letter-spacing: 0.05em;
                color: var(--muted-foreground);
            }

            .message {
                margin: 0 0 1.5rem;
                font-size: 1.25rem;
                font-weight: 500;
                line-height: 1.4;
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                height: 2.25rem;
                padding: 0 1rem;
                border-radius: var(--radius);
                font-size: 0.875rem;
                font-weight: 500;
                text-decoration: none;
                transition: opacity 0.15s ease;
            }

            .btn:hover {
                opacity: 0.9;
            }

            .btn-primary {
                background-color: var(--primary);
                color: var(--primary-foreground);
            }

            .btn-ghost {
                color: var(--muted-foreground);
                border: 1px solid var(--border);
            }
        </style>
    </head>
    <body>
        @php
            $canAccessDashboard = auth()->user()?->hasPermissionTo(
                \Database\Seeders\RolePermissionSeeder::PERMISSION_DASHBOARD_VIEW,
            );

            if ($canAccessDashboard) {
                $homeUrl = route('admin.dashboard');
                $homeLabel = __('Back to dashboard');
            } else {
                $homeUrl = route('home');
                $homeLabel = __('Back to home');
            }
        @endphp

        <main class="wrap" role="main">
            <a href="{{ $homeUrl }}" class="logo" aria-label="{{ config('app.name', 'Laravue') }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42" aria-hidden="true">
                    <path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z" />
                </svg>
            </a>

            <p class="code">@yield('code')</p>
            <h1 class="message">@yield('message')</h1>

            <div class="actions">
                @hasSection('action')
                    @yield('action')
                @else
                    <a class="btn btn-primary" href="{{ $homeUrl }}">{{ $homeLabel }}</a>
                @endif
            </div>
        </main>
    </body>
</html>
