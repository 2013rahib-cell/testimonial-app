/**
 * Wall of Love Embed Script
 * Automatically renders a responsive social proof widget inside any host container.
 */
(function() {
    'use strict';

    function initWallOfLove() {
        var targets = document.querySelectorAll('#wall-of-love, [data-space]');
        if (!targets || targets.length === 0) return;

        targets.forEach(function(el) {
            // Avoid duplicate initialization
            if (el.getAttribute('data-wol-initialized') === 'true') return;

            var space = el.getAttribute('data-space');
            if (!space) return;

            var layout = el.getAttribute('data-layout') || 'masonry';
            var theme = el.getAttribute('data-theme') || 'light';
            var bg = el.getAttribute('data-bg') || '#ffffff';
            var animations = el.getAttribute('data-animations') || '1';

            // Find current script tag to determine baseUrl
            var scriptTag = document.currentScript || (function() {
                var scripts = document.getElementsByTagName('script');
                return scripts[scripts.length - 1];
            })();

            var baseUrl = '';
            if (scriptTag && scriptTag.src) {
                var urlObj = new URL(scriptTag.src);
                baseUrl = urlObj.origin;
            } else {
                baseUrl = window.location.origin;
            }

            var embedUrl = baseUrl + '/embed/wall-of-love/' + encodeURIComponent(space) +
                '?layout=' + encodeURIComponent(layout) +
                '&theme=' + encodeURIComponent(theme) +
                '&bg=' + encodeURIComponent(bg) +
                '&animations=' + encodeURIComponent(animations);

            var iframe = document.createElement('iframe');
            iframe.src = embedUrl;
            iframe.width = '100%';
            iframe.height = layout === 'carousel' ? '360' : '650';
            iframe.style.border = '0';
            iframe.style.overflow = 'hidden';
            iframe.style.display = 'block';
            iframe.style.transition = 'height 0.2s ease';
            iframe.setAttribute('scrolling', 'no');
            iframe.setAttribute('title', 'Customer Testimonials - Wall of Love');

            // Responsive auto-resizing via postMessage if available
            window.addEventListener('message', function(event) {
                if (event.data && event.data.type === 'wol-resize' && event.data.space === space) {
                    iframe.style.height = (event.data.height + 20) + 'px';
                }
            });

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
