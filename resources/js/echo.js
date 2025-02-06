import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

import { updateMarker } from './map';

// Set up Pusher globally
window.Pusher = Pusher;
console.log('Echo script loaded')
// Initialize Echo with Reverb broadcaster (assuming 'reverb' is a valid broadcaster)
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    debug: true,  // Enable debugging
});


// Listen to the correct channel and event
console.log('Subscribing to vehicle-location channel...');
window.Echo.channel('gps')
    .listen('.location', (e) => {

        // console.log('Received vehicle location update:', e);
        updateMarker(e.location)
    })
    .error((error) => {
        console.error('Error subscribing to event:', error);
    });
