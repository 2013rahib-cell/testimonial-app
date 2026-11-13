<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') – {{ config('app.name', 'Wall of Love') }}</title>
        {{-- Plain inline styles: error pages must render even if the Vite build is missing. --}}
        <style>
            *{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
            font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;background:#fafafa;color:#18181b;padding:24px}
            .card{max-width:440px;text-align:center}.code{font-size:72px;font-weight:800;letter-spacing:-2px;
            background:linear-gradient(90deg,#f43f5e,#6366f1);-webkit-background-clip:text;background-clip:text;color:transparent;margin:0}
            h1{font-size:22px;margin:8px 0}p{color:#52525b;line-height:1.6;margin:0 0 24px}
            a{display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:10px 20px;border-radius:12px;font-weight:600;font-size:14px}
            a:hover{background:#6366f1}
            @media (prefers-color-scheme:dark){body{background:#09090b;color:#f4f4f5}p{color:#a1a1aa}}
        </style>
    </head>
    <body>
        <main class="card">
            <p class="code">@yield('code')</p>
            <h1>@yield('title')</h1>
            <p>@yield('message')</p>
            <a href="{{ auth()->check() ? url('/dashboard') : url('/') }}">{{ auth()->check() ? 'Back to dashboard' : 'Back to home' }}</a>
        </main>
    </body>
</html>
