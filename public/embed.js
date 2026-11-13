/**
 * Wall of Love Embed Script
 * Usage:
 *   <div id="wall-of-love" data-space="your-space-slug" data-layout="masonry"
 *        data-theme="light" data-bg="#ffffff" data-animations="1"></div>
 *   <script src="https://your-app.com/embed.js" defer></script>
 *
 * Renders the approved testimonials of a space inside an auto-resizing iframe.
 */
(function () {
    'use strict';

    // Resolve our own origin NOW: document.currentScript is null inside later callbacks.
    var currentScript = document.currentScript || (function () {
        var scripts = document.querySelectorAll('script[src*="embed.js"]');
        return scripts[scripts.length - 1];
    })();

    var baseUrl = window.location.origin;
    try {
        if (currentScript && currentScript.src) {
            baseUrl = new URL(currentScript.src, window.location.href).origin;
        }
    } catch (e) { /* keep fallback */ }

    var frames = {}; // slug -> [iframe, ...]

    // One global listener; only trust height messages coming from our own origin.
    window.addEventListener('message', function (event) {
        if (event.origin !== baseUrl || !event.data || event.data.type !== 'wol-resize') return;
        var list = frames[event.data.space] || [];
        var height = parseInt(event.data.height, 10);
        if (!isFinite(height) || height <= 0) return;
        list.forEach(function (iframe) {
            if (iframe.contentWindow === event.source) {
                iframe.style.height = height + 'px';
            }
        });
    });

    function initWallOfLove() {
        var targets = document.querySelectorAll('#wall-of-love, [data-wall-of-love], div[data-space]');

        Array.prototype.forEach.call(targets, function (el) {
            if (el.getAttribute('data-wol-initialized') === 'true') return;

            var space = el.getAttribute('data-space');
            if (!space) return;

            var layout = el.getAttribute('data-layout') || 'masonry';
            var theme = el.getAttribute('data-theme') || 'light';
            var bg = el.getAttribute('data-bg') || (theme === 'dark' ? '#09090b' : '#ffffff');
            var animations = el.getAttribute('data-animations') || '1';

            var embedUrl = baseUrl + '/embed/wall-of-love/' + encodeURIComponent(space) +
                '?layout=' + encodeURIComponent(layout) +
                '&theme=' + encodeURIComponent(theme) +
                '&bg=' + encodeURIComponent(bg) +
                '&animations=' + encodeURIComponent(animations);

            var iframe = document.createElement('iframe');
            iframe.src = embedUrl;
            iframe.width = '100%';
            iframe.style.width = '100%';
            iframe.style.height = (layout === 'carousel' ? 360 : 650) + 'px';
            iframe.style.border = '0';
            iframe.style.overflow = 'hidden';
            iframe.style.display = 'block';
            iframe.style.transition = 'height 0.2s ease';
            iframe.setAttribute('scrolling', 'no');
            iframe.setAttribute('loading', 'lazy');
            iframe.setAttribute('title', 'Customer Testimonials - Wall of Love');

            (frames[space] = frames[space] || []).push(iframe);

            el.innerHTML = '';
            el.appendChild(iframe);
            el.setAttribute('data-wol-initialized', 'true');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWallOfLove);
    } else {
        initWallOfLove();
    }
})();
