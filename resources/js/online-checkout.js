import { initDeliveryLocation } from './online-checkout/delivery-location';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('delivery-location-root');
    if (!root) {
        return;
    }

    const config = window.onlineCheckoutDeliveryConfig;
    if (!config) {
        return;
    }

    initDeliveryLocation(config);
});
