/**
 * @file resources/scripts/components/ui/layout/down-bar.js
 * @description Reusable browser component module for ui/layout/down-bar.js; owns the client-side behavior of this UI primitive.
 * @exports init
 */

export function init() {
    const downBars = document.querySelectorAll('[data-ui-down-bar]');

    if (downBars.length > 1) {
        console.warn(
            `[UI] Multiple DownBar instances detected: ${downBars.length}. ` +
            'Expected no more than one DownBar per page.',
            downBars
        );
    }
}
