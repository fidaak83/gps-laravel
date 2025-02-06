if (typeof L === 'undefined') {
} else {
    // Declare map globally
    let map;

    // Check if the map element exists
    const mapElement = document.getElementById('map');
    if (mapElement) {
        // Initialize the map globally
        map = L.map('map', {
            center: [23.8859, 45.0792],  // Set the center of the map
            zoom: 6,  // Set the zoom level
            zoomControl: false  // Disable zoom controls
        });

        const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; Tawjeh'
        }).addTo(map);

        // Object to hold markers by their IMEI
        window.vehicleMarkers = {};
        
        // Sample vehicle data with initial position
        const initialVehicleData = {
            imei: 864636067519849,
            latitude: 23.8859,
            longitude: 45.0792
        };

        // Create and add the initial marker to the map
        const initialMarker = L.marker([initialVehicleData.latitude, initialVehicleData.longitude])
            .addTo(map)
            .bindPopup(`<b>Vehicle ${initialVehicleData.imei}</b><br>Lat: ${initialVehicleData.latitude}, Lng: ${initialVehicleData.longitude}`);

        // Store the marker in the vehicleMarkers object using the IMEI
        window.vehicleMarkers[initialVehicleData.imei] = initialMarker;
    }
}


export function add(vehicle){
    console.log('from ', vehicle);
}
