<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HRMS') }} — @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: true }">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        @include('layouts.partials.sidebar')

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col" :class="sidebarOpen ? 'ml-64' : 'ml-16'" style="transition: margin-left 0.3s">
            @include('layouts.partials.navbar')

            <main class="flex-1 p-6">
                @include('layouts.partials.alerts')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
