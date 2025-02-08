<div class="w-full h-screen relative">
    <!-- Vehicles div - full height of the parent div -->
    <div id="vehicles" class="absolute top-0 left-0 p-4 z-20 w-48 h-full">
        <div id="card"
            class="bg-white  border p-2 max-h-full overflow-y-scroll   border-gray-200 rounded-lg shadow-xl bg-opacity-30 min-h-full backdrop-blur-sm">
            <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Vehicles:</h2>
            <ul class="text-xs font-medium text-gray-900 rounded-lg flex-col space-y-1">
                @foreach($data as $vehicle)
                    <li class="w-full text-green-500 border-b py-1 border-b-gray-300 truncate cursor-pointer"
                        onclick="flyToStore(`{{ $vehicle['imei'] }}`)">
                        <i class="bi bi-truck"></i> {{ $vehicle['imei'] }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Map content -->
    <div id="map" class="h-screen z-0">
        <!-- Your map content goes here -->
    </div>

    @push('scripts')
        <script>
            var my_vehicles = @json($data);
            console.log('my_vehicles', my_vehicles)

        </script>
    @endpush
</div>
