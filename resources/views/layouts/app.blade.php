<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Keijora POS') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .mobile-no-zoom input,
            .mobile-no-zoom textarea,
            .mobile-no-zoom select {
                font-size: 16px !important;
            }
        </style>
    </head>
    <body class="enable-page-skeleton page-loading mobile-no-zoom font-sans antialiased" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        <div class="customer-page-skeleton" aria-hidden="true">
            <div class="min-h-screen bg-[#f6faff]">
                <div class="border-b border-[#d7dde8] bg-white px-4 py-4">
                    <div class="mx-auto flex max-w-7xl items-center justify-between">
                        <div class="skeleton-shimmer h-10 w-36 rounded-2xl"></div>
                        <div class="flex gap-3">
                            <div class="skeleton-shimmer h-10 w-10 rounded-full"></div>
                            <div class="skeleton-shimmer h-10 w-10 rounded-full"></div>
                        </div>
                    </div>
                </div>
                <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                    <div class="skeleton-shimmer h-24 rounded-3xl"></div>
                    <div class="grid gap-6 lg:grid-cols-3">
                        <div class="skeleton-shimmer h-64 rounded-3xl lg:col-span-2"></div>
                        <div class="skeleton-shimmer h-64 rounded-3xl"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="min-h-screen bg-[#f6faff]">
            @include('layouts.navigation')

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
