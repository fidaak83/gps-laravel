if (typeof L === 'undefined') {
} else {

    // Check if the map element exists
    const mapElement = document.getElementById('map');
    if (mapElement) {
        // Initialize the map
        const map = L.map('map', {
            center: [23.8859,45.0792],  // Set the center of the map
            zoom: 6,  // Set the zoom level
            zoomControl: false  // Disable zoom controls
        });

        const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; Tawjeh'
        }).addTo(map);
    } else {
    }
}

// Function to destroy the map when navigating away or reloading the page
function destroyMap() {
    if (typeof map !== 'undefined') {
        map.remove();  // Removes the map instance from the DOM
        console.log('Map destroyed');
    }
}

// Destroy map instance before the page is unloaded
window.addEventListener('beforeunload', function() {
    destroyMap();  // This will destroy the map instance when the page is being unloaded
});