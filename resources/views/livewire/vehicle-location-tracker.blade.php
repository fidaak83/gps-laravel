<div>
    <h3>Vehicle Location Tracker</h3>
    
    @if ($vehicle)
        <p><strong>IMEI:</strong> {{ $vehicle['imei'] ?? 'N/A' }}</p>
        <p><strong>Latitude:</strong> {{ $vehicle['latitude'] ?? 'N/A' }}</p>
        <p><strong>Longitude:</strong> {{ $vehicle['longitude'] ?? 'N/A' }}</p>
        <p><strong>Speed:</strong> {{ $vehicle['speed'] ?? 'N/A' }}</p>
        <p><strong>Timestamp:</strong> {{ $vehicle['timestamp'] ?? 'N/A' }}</p>
    @else
        <p>No location data available.</p>
    @endif
</div>
