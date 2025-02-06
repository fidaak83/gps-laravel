<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    @endif

    @stack('styles')
</head>

<body class="font-sans antialiased dark:bg-black dark:text-white/50">

    <div class="w-full h-full relative z-1000">
        <!-- Card div placed on top of the map -->
        <div id="side-menu"
            class="absolute top-0 left-0 p-4 z-50 sm:w-24 h-screen bg-gradient-to-br from-yellow-400 to-yellow-600">
            <div id="menu" class="p-2 border-gray-200 rounded-lg flex flex-col h-full">
                <!-- Logo Image -->
                <img src="{{ asset('logo.png') }}" alt="Logo" class="w-40 h-auto mb-4">

                <!-- Links Container with space between each link and centered items -->
                <div class="flex flex-col flex-grow text-2xl mt-20 space-y-8 text-white">
                    <a href="/home" 
                        class="w-full h-12 rounded-full flex items-center justify-center">
                        <i class="bi bi-speedometer"></i>
                    </a>

                    <a href="/counter" wire:navigate class="w-full h-12 flex items-center justify-center">
                        <i class="bi bi-person-lines-fill"></i>
                    </a>
                    <a href="/vehicle" wire:navigate class="w-full h-12 flex items-center justify-center">
                        <i class="bi bi-clipboard2-data-fill"></i>
                    </a>
                    <a class="w-full h-12 flex items-center justify-center">
                        <i class="bi bi-gear-wide-connected"></i>
                    </a>
                </div>
            </div>


        </div>

        <!-- Page content below the side menu -->
        <div id="page-content" class="sm:ml-24 z-10 h-full">
            {{ $slot }}
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.1/dist/flowbite.min.js"></script>
    @stack('scripts')
</body>

</html>
