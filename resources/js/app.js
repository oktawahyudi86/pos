

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const markPageLoaded = () => {
    document.body?.classList.remove('page-loading');
    document.body?.classList.add('page-loaded');
};

if (document.readyState === 'complete') {
    markPageLoaded();
} else {
    window.addEventListener('load', markPageLoaded, { once: true });
}
