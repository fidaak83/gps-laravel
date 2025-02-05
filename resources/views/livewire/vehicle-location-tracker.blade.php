<div>
    <h3>Vehicle Location Tracker</h3>
    @if ($vehicle)
        <p>IMEI: {{ $vehicle['imei'] }}</p>
        <p>Latitude: {{ $vehicle['latitude'] }}</p>
        <p>Longitude: {{ $vehicle['longitude'] }}</p>
    @else
        <p>No location data available.</p>
    @endif
</div>