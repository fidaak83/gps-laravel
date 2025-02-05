import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Make sure Pusher is available globally
window.axios = axios;
window.Pusher = Pusher;  // Expose Pusher globally for Echo

// Initialize Echo with hardcoded values to test
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 's3cfpmpciyzglks9wbhs', // Your Reverb app key
    wsHost: 'localhost',         // WebSocket Host
    wsPort: 8080,                // WebSocket Port
    wssPort: 443,                // Use 443 for secure WebSocket (optional)
    forceTLS: false,             // Set to false for HTTP WebSocket
    enabledTransports: ['ws', 'wss'],  // WebSocket transports
});

// Listen for the event
window.Echo.channel('vehicle-location')
    .listen('VehicleLocationUpdated', (event) => {
        console.log('Received Vehicle Location:', event);
    });
