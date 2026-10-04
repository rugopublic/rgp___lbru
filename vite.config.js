/**
 * @file vite.config.js
 * @description Vite build and development-server configuration; defines entry points, aliases, HMR behavior and production asset output.
 */

import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import path from 'path';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
    base: '/build/',
    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
            },
        },
    },
    server: {
        host: env.VITE_HOST || '127.0.0.1',
        port: Number(env.VITE_PORT || 5173),
        https: env.VITE_HTTPS === 'true' && fs.existsSync(path.resolve('local/ssl/server.key')) && fs.existsSync(path.resolve('local/ssl/server.crt'))
            ? {
                key: fs.readFileSync(path.resolve('local/ssl/server.key')),
                cert: fs.readFileSync(path.resolve('local/ssl/server.crt')),
            }
            : undefined,
        hmr: {
            host: env.VITE_HMR_HOST || env.VITE_HOST || '127.0.0.1',
            protocol: env.VITE_HMR_PROTOCOL || (env.VITE_HTTPS === 'true' ? 'wss' : 'ws'),
            port: Number(env.VITE_HMR_PORT || env.VITE_PORT || 5173),
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(import.meta.dirname, 'resources'),
        }
    },
    plugins: [
        laravel({
            input: [
                // Entry points for components.
                'resources/styles/components/ui/layout/widgets/action-bar.scss',

                'resources/scripts/components/http/data-uploader.js',
                'resources/styles/components/http/data-uploader.scss',

                'resources/scripts/components/ui/form/rich-textarea.js',
                'resources/styles/components/ui/form/rich-textarea.scss',

                'resources/styles/components/ui/form/change-tracker.scss',
                'resources/scripts/components/ui/form/change-tracker.js',

                'resources/styles/components/ui/form/typeahead.scss',
                'resources/scripts/components/ui/form/typeahead.js',

                'resources/styles/components/ui/form/sortable.scss',
                'resources/scripts/components/ui/form/sortable.js',

                'resources/scripts/components/ui/form/sender.js',

                'resources/styles/components/ui/form/container.scss',

                // Layout entry points.
                'resources/styles/admin/layout.scss',

                // General entry points.
                'resources/scripts/admin/app.js',
                'resources/styles/admin/app.scss',
                // Public sales surface.
                'resources/scripts/frontend/app.js',
                'resources/styles/frontend/app.scss'
            ],
            refresh: true,
        })
    ],
    };
});
