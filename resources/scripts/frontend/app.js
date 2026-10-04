/**
 * @file resources/scripts/frontend/app.js
 * @description Public frontend entry point; initializes Bootstrap, media UI and public interaction behavior.
 */

import * as bootstrap from 'bootstrap';
import '../components/ui/form/media-dropzone';
window.bootstrap = bootstrap;

import './interactions.js';
