/**
 * Browser geolocation helpers for checkout delivery location.
 */

const DEFAULT_OPTIONS = {
    enableHighAccuracy: true,
    timeout: 12000,
    maximumAge: 0,
};

export function isGeolocationSupported() {
    return typeof navigator !== 'undefined' && Boolean(navigator.geolocation);
}

export function getCurrentPosition(options = {}) {
    return new Promise((resolve, reject) => {
        if (!isGeolocationSupported()) {
            reject(new Error('Browser ini belum mendukung deteksi lokasi.'));
            return;
        }

        navigator.geolocation.getCurrentPosition(resolve, reject, {
            ...DEFAULT_OPTIONS,
            ...options,
        });
    });
}

export function getGeolocationErrorMessage(error, { coverageRequired = false } = {}) {
    if (error?.code === 1) {
        return 'Izin lokasi ditolak. Aktifkan izin lokasi di pengaturan browser, lalu coba lagi.';
    }

    if (error?.code === 2) {
        return 'Lokasi tidak tersedia. Pastikan GPS aktif, lalu coba lagi.';
    }

    if (error?.code === 3) {
        return 'Waktu habis saat mengambil lokasi. Coba lagi di area dengan sinyal lebih baik.';
    }

    if (coverageRequired) {
        return 'Aktifkan lokasi HP untuk memastikan alamat pengantaran berada dalam jangkauan kami.';
    }

    return 'Lokasi belum bisa dideteksi. Pastikan izin lokasi aktif, lalu coba lagi.';
}
