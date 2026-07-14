

import Alpine from 'alpinejs';
import { initHeaderLocation } from './online-header-location';
import { initAddressConfirmation } from './online-address-confirmation';

window.Alpine = Alpine;
window.initHeaderLocation = initHeaderLocation;
window.initAddressConfirmation = initAddressConfirmation;

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

// Auto-initialize header location detection if config is available
document.addEventListener('DOMContentLoaded', () => {
    if (window.onlineHeaderConfig && window.onlineHeaderConfig.geoapifyApiKey && window.onlineHeaderConfig.tenant) {
        initHeaderLocation(window.onlineHeaderConfig.geoapifyApiKey, window.onlineHeaderConfig.tenant);
    }
});
