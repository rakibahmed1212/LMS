import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
    ],
    // server: {
    //     // এই অংশটুকু যুক্ত করুন 👇
    //     cors: true,
    //     allowedHosts: ['.ngrok-free.dev'], // ngrok ডোমেনকে অনুমতি দেওয়ার জন্য
    //     hmr: {
    //         host: 'whooping-baking-backlands.ngrok-free.dev',
    //         protocol: 'wss', // HTTPS এর জন্য WebSocket Secure ব্যবহার করবে
    //     },
    // },
});
