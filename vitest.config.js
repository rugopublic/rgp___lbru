/**
 * @file vitest.config.js
 * @description Vitest configuration for browser-oriented JavaScript unit and integration tests.
 */

import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        environment: 'jsdom',
        globals: true,
        include: ['resources/scripts/**/*.spec.js'],
    },
});
