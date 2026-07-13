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
    <body class="enable-page-skeleton page-loading mobile-no-zoom text-gray-900 antialiased">
        <div class="customer-page-skeleton" aria-hidden="true">
            <div class="hidden min-h-screen md:flex">
                <div class="flex w-[60%] flex-col justify-between bg-[#eef2f8] p-8">
                    <div class="skeleton-shimmer h-14 w-44 rounded-2xl"></div>
                    <div class="space-y-4">
                        <div class="skeleton-shimmer h-8 w-32 rounded-full"></div>
                        <div class="skeleton-shimmer h-14 w-3/4 rounded-2xl"></div>
                        <div class="skeleton-shimmer h-5 w-2/3 rounded-full"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="skeleton-shimmer h-36 rounded-3xl"></div>
                        <div class="skeleton-shimmer h-36 rounded-3xl"></div>
                        <div class="skeleton-shimmer h-36 rounded-3xl"></div>
                    </div>
                </div>
                <div class="flex w-[40%] items-center justify-center bg-white p-8">
                    <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-[0_4px_12px_rgba(27,43,107,0.08)]">
                        <div class="skeleton-shimmer mb-8 h-8 w-28 rounded-full"></div>
                        <div class="space-y-5">
                            <div class="skeleton-shimmer h-14 rounded-xl"></div>
                            <div class="skeleton-shimmer h-14 rounded-xl"></div>
                            <div class="skeleton-shimmer h-12 rounded-xl"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="min-h-screen bg-white p-4 md:hidden">
                <div class="mx-auto max-w-md space-y-5 rounded-3xl bg-white pt-6">
                    <div class="skeleton-shimmer h-12 w-36 rounded-2xl"></div>
                    <div class="skeleton-shimmer h-8 w-28 rounded-full"></div>
                    <div class="space-y-4">
                        <div class="skeleton-shimmer h-14 rounded-xl"></div>
                        <div class="skeleton-shimmer h-14 rounded-xl"></div>
                        <div class="skeleton-shimmer h-12 rounded-xl"></div>
                    </div>
                </div>
            </div>
        </div>
        {{ $slot }}
    </body>
</html>
