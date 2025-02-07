if (typeof L === 'undefined') {
    console.error("Leaflet is not loaded.");
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
            imei: '864636067510004',
            latitude: 23.8859,
            longitude: 45.0792
        };

        // Create and add the initial marker to the map
        const initialMarker = L.marker([initialVehicleData.latitude, initialVehicleData.longitude])
            .addTo(map)
            .bindPopup(`<b>Vehicle ${initialVehicleData.imei}</b><br>Lat: ${initialVehicleData.latitude}, Lng: ${initialVehicleData.longitude}`);

        // Store the marker in the vehicleMarkers object using the IMEI
        window.vehicleMarkers[initialVehicleData.imei] = initialMarker;
    } else {
        console.error('Map element not found!');
    }
}

export function updateMarker(vehicle) {
    console.log('Updating marker for vehicle:', vehicle);

    // Ensure the map is initialized before proceeding
    if (!map) {
        console.error('Map is not initialized.');
        return;
    }

    // Check if the marker for the given vehicle's IMEI exists
    if (window.vehicleMarkers[vehicle.imei]) {
        // Get the marker using the IMEI
        const marker = window.vehicleMarkers[vehicle.imei];

        // Update the marker's position
        marker.setLatLng([vehicle.latitude, vehicle.longitude]);

        // Update the marker's popup content
        marker.setPopupContent(`<b>Vehicle ${vehicle.imei}</b><br>Lat: ${vehicle.latitude}, Lng: ${vehicle.longitude} <br> <b>Speed: ${vehicle.speed}<b/>`);
    } else {
        // If the marker does not exist, create a new one
        const newMarker = L.marker([vehicle.latitude, vehicle.longitude])
            .addTo(map)
            .bindPopup(`<b>Vehicle ${vehicle.imei}</b><br>Lat: ${vehicle.latitude}, Lng: ${vehicle.longitude} <br> <b>Speed: ${vehicle.speed}<b/>`);

        // Store the new marker in the vehicleMarkers object
        window.vehicleMarkers[vehicle.imei] = newMarker;
    }

    // // Optionally, you can pan to the new position of the marker
    // if (map) {
    //     map.panTo([vehicle.latitude, vehicle.longitude]);
    // } else {
    //     console.error("Map is not defined at the time of panTo call.");
    // }
}
