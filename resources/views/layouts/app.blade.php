<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ trim(($title ?? '').' · '.config('app.name', 'Laravel')) }}</title>

        @stack('head')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <div
                x-data="{
                    show: false,
                    message: @js(session('toast')),
                    init() {
                        if (this.message) this.open(this.message);
                        window.addEventListener('toast', (e) => this.open(e.detail?.message));
                    },
                    open(msg) {
                        if (!msg) return;
                        this.message = msg;
                        this.show = true;
                        setTimeout(() => this.show = false, 3000);
                    },
                }"
                class="pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4"
            >
                <div
                    x-show="show"
                    x-cloak
                    x-transition
                    class="pointer-events-auto rounded-lg bg-gray-900 px-4 py-3 text-sm font-medium text-white shadow-lg"
                    x-text="message"
                ></div>
            </div>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
