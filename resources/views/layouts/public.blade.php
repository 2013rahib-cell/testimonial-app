<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-full antialiased font-sans">
        {{ $slot }}
        @fluxScripts
    </body>
</html>
