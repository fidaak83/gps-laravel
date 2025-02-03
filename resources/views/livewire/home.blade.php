<!-- home.blade.php -->
<div class="w-full h-full relative">
    @livewire('map')
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
    @if(request()->is('home')) <!-- Only include map.js for home route -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="{{ asset('assets/js/map.js') }}"></script>
    @endif
@endpush
