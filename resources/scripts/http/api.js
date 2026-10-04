/**
 * @file resources/scripts/http/api.js
 * @description HTTP client module; centralizes API requests, upload transport and browser-facing request behavior.
 */

import axios from "axios";
import config from "../config/app";

const api = axios.create({
    baseURL: config.appUrl,

    timeout: 60 * 1000,

    maxContentLength: 20 * 1024 * 1024,
    maxBodyLength: 20 * 1024 * 1024,

    withCredentials: true,

    xsrfCookieName: "XSRF-TOKEN",
    xsrfHeaderName: "X-XSRF-TOKEN",
    withXSRFToken: true,

    redact: [
        "authorization",
        "password",
        "token",
    ],

    transitional: {
        clarifyTimeoutError: true,
    },

    formSerializer: {
        dots: false,
        metaTokens: false,
        indexes: false,
    },

    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    }
});

export default api;
