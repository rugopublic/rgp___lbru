/**
 * @file resources/scripts/config/app.js
 * @description Frontend runtime configuration module; exposes environment-backed URLs and client settings.
 */

const config = {
    appUrl: import.meta.env.VITE_APP_URL,
    devServerUrl: import.meta.env.VITE_DEV_SERVER_URL,

    upload: {
        chunkSize: Number(import.meta.env.VITE_UPLOAD_CHUNK_SIZE),

        timeout: {
            base: 60 * 1000,
            perGiB: 30 * 1000,
        },
    },
};

export default config;
