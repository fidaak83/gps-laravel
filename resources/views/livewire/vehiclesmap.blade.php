<div class="w-full h-64 z-10">
    <livewire:map :customer_id="$customerid" />
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
    @if (request()->is('home/*')) <!-- Only include map.js for home route -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endif
@endpush
