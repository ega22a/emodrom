import './echo';
import Alpine from 'alpinejs';

/**
 * Thin JSON fetch wrapper that attaches the CSRF token Laravel expects on
 * every state-changing request, since these pages don't use axios.
 */
window.api = async function api(url, options = {}) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    const response = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            ...options.headers,
        },
    });

    const body = await response.json().catch(() => null);

    if (!response.ok) {
        throw new Error(body?.message ?? 'Что-то пошло не так.');
    }

    return body;
};

window.Alpine = Alpine;

/**
 * Page-specific scripts (host-screen.js, player-screen.js) register their
 * Alpine.data() components via an "alpine:init" listener. Those scripts
 * are separate deferred modules loaded after this one, so starting Alpine
 * here directly would run before they get a chance to register — wait for
 * DOMContentLoaded, by which point every deferred module has executed.
 */
document.addEventListener('DOMContentLoaded', () => Alpine.start());
