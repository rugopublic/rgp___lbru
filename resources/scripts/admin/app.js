/**
 * @file resources/scripts/admin/app.js
 * @description Admin application entry point; bootstraps global UI behavior and page-specific modules through Vite.
 */

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import '@/scripts/components/ui/form/media-dropzone';
import { init as initDownBar } from '@/scripts/components/ui/layout/down-bar';
import { initTextareas } from '@/scripts/components/ui/form/textarea';
import '../components/ui/form/input';
import { initCreateModals } from './pages/create-modal';
import './delete-flow';
import './lesson-editor.js';
import '@/styles/admin/lesson-editor.scss';

initTextareas();

const pageModules = import.meta.glob('./pages/**/*.js');

document.addEventListener('DOMContentLoaded', async () => {
    initDownBar();
    initCreateModals();
    const page = document.body.dataset.adminPage || 'common';
    const candidates = [
        `./pages/${page}.js`,
        `./pages/${page}/index.js`,
        `./pages/${page}/edit.js`,
        `./pages/${page}/create.js`,
    ];
    const key = candidates.find(candidate => pageModules[candidate]);
    if (key) {
        const module = await pageModules[key]();
        module.init?.();
        initCreateModals();
    }
});
