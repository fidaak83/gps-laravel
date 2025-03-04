// standard IIFE
(function () {
    
    if (typeof L === 'undefined') {
        console.error("Leaflet is not loaded.");
    } else {
        // Check if the map element exists
        const mapElement = document.getElementById('map');
        if (mapElement) {
            // Initialize the map globally using window.map
            window.map = L.map('map', {
                center: [23.8859, 45.0792],  // Set the center of the map
                zoom: 6,  // Set the zoom level
                zoomControl: false  // Disable zoom controls
            });
    
            const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; Tawjeh'
            }).addTo(window.map);
    
            // Object to hold markers by their IMEI
            window.vehicleMarkers = {};
    
            // Define a custom icon for the markers
            const customIcon = L.icon({
                iconUrl: '/assets/car.png', // Path to your custom PNG
                iconSize: [32, 40], // Size of the icon (width, height) in pixels
                iconAnchor: [16, 32], // Point of the icon that corresponds to the marker's location
                popupAnchor: [0, -32] // Point from which the popup should open relative to the iconAnchor
            });
    
            // Function to add a marker for a single vehicle
            const addVehicleMarker = (vehicle) => {
                const marker = L.marker([vehicle.latitude, vehicle.longitude], { icon: customIcon })
                    .addTo(window.map)
                    .bindPopup(`<b>Vehicle: ${vehicle.name}</b><br><b>Imei: ${vehicle.imei}</b><br>Lat: ${vehicle.latitude}, Lng: ${vehicle.longitude}<br><b>Speed: ${vehicle.speed}</b>`);
    
                // Store the marker in the vehicleMarkers object using the IMEI
                window.vehicleMarkers[vehicle.imei] = marker;
            };
    
            // Check if my_vehicles is defined and not null
            if (typeof my_vehicles !== 'undefined' && my_vehicles !== null) {
                // Check if my_vehicles is an array
                if (Array.isArray(my_vehicles)) {
                    // If it's an array, iterate over each vehicle and add markers
                    my_vehicles.forEach(vehicle => {
                        addVehicleMarker(vehicle);
                    });
                } else if (typeof my_vehicles === 'object') {
                    // If it's an object with numeric keys, iterate over its values
                    if (Object.keys(my_vehicles).every(key => !isNaN(key))) {
                        Object.values(my_vehicles).forEach(vehicle => {
                            addVehicleMarker(vehicle);
                        });
                    } else {
                        // If it's a single object, add a marker for it
                        addVehicleMarker(my_vehicles);
                    }
                } else {
                    console.error('Invalid my_vehicles format. Expected an array or object.');
                }
            } else {
                console.error('my_vehicles is not defined or is null.');
            }
        } else {
            console.error('Map element not found!');
        }
    }
    
    
    
    // Define flyToStore in the global scope
    window.flyToStore = function (imei) {
        // Ensure the map is initialized and is a Leaflet map instance
        if (!window.map || !window.map.flyTo) {
            console.error('Map is not initialized or is not a Leaflet map instance.');
            return;
        }
    
        // Find the marker for the given IMEI
        const marker = window.vehicleMarkers[imei];
        if (!marker) {
            console.error(`Marker for IMEI ${imei} not found.`);
            return;
        }
    
        // Get the marker's position
        const latLng = marker.getLatLng();
    
        // Fly to the marker's position
        window.map.flyTo(latLng, 15, {
            duration: 1 // Duration of the fly animation in seconds
        });
    
        // Open a popup on the marker after the fly animation completes
        setTimeout(() => {
            marker.openPopup();
        }, 3000); // Delay in milliseconds (3 seconds)
    };

  })();

  export function updateMarker(vehicle) {
    console.log('Updating marker for vehicle:', vehicle);

    // Ensure the map is initialized before proceeding
    if (!window.map) {
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
        marker.setPopupContent(`<b>Vehicle: ${vehicle.name}</b><br><b>Vehicle ${vehicle.imei}</b><br>Lat: ${vehicle.latitude}, Lng: ${vehicle.longitude} <br> <b>Speed: ${vehicle.speed}<b/>`);
    } else {
        // If the marker does not exist, create a new one
        const newMarker = L.marker([vehicle.latitude, vehicle.longitude], { icon: customIcon })
            .addTo(window.map)
            .bindPopup(`<b>Vehicle: ${vehicle.name}</b><br><b>Vehicle ${vehicle.imei}</b><br>Lat: ${vehicle.latitude}, Lng: ${vehicle.longitude} <br> <b>Speed: ${vehicle.speed}<b/>`);

        // Store the new marker in the vehicleMarkers object
        window.vehicleMarkers[vehicle.imei] = newMarker;
    }
}