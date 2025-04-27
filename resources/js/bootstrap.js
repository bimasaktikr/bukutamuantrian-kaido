import axios from 'axios';
window.axios = axios;
// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// window.Pusher = Pusher;
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_REVERB_APP_KEY,
//     wsHost: import.meta.env.VITE_REVERB_HOST,
//     wsPort: import.meta.env.VITE_REVERB_PORT,
//     wssPort: import.meta.env.VITE_REVERB_PORT,
//     forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
//     encrypted: import.meta.env.VITE_REVERB_SCHEME === 'https',
//     enabledTransports: ['ws'], // gunakan 'wss' kalau pakai HTTPS
// });

// import './echo';
