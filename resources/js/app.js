/**
 * Sitrans global frontend error handling.
 *
 * - Error Boundary global: jika request Livewire gagal (server 5xx, jaringan
 *   putus, sesi habis), app tidak blank — overlay ramah bahasa Indonesia
 *   dengan tombol "Muat ulang" muncul, dan detail teknis tetap di console
 *   untuk developer.
 * - Banner "Tidak ada koneksi internet" otomatis berdasarkan navigator.onLine.
 * - Helper toast global (window.sitransToast) untuk feedback non-Livewire.
 */

const OVERLAY_ID = 'sitrans-error-overlay';
const OFFLINE_BANNER_ID = 'sitrans-offline-banner';

function ensureOverlay() {
    if (document.getElementById(OVERLAY_ID) !== null) {
        return document.getElementById(OVERLAY_ID);
    }

    const overlay = document.createElement('div');
    overlay.id = OVERLAY_ID;
    overlay.setAttribute('x-data', '');
    overlay.style.cssText = 'position:fixed;inset:0;z-index:2147483000;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);padding:16px;';

    overlay.innerHTML = `
        <div style="max-width:26rem;width:100%;background:#ffffff;border-radius:16px;padding:24px;box-shadow:0 20px 50px rgba(15,23,42,0.35);text-align:center;">
            <div style="margin:0 auto 12px;width:48px;height:48px;border-radius:9999px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:24px;">⚠️</div>
            <h2 style="font-size:1.05rem;font-weight:700;color:#0f172a;margin:0 0 6px;">Terjadi kesalahan</h2>
            <p id="${OVERLAY_ID}-message" style="font-size:0.875rem;color:#475569;margin:0 0 16px;line-height:1.5;">
                Aplikasi gagal memproses permintaan terakhir.
            </p>
            <div style="display:flex;gap:8px;justify-content:center;">
                <button id="${OVERLAY_ID}-reload" type="button" style="background:#0f172a;color:#fff;border:0;border-radius:10px;padding:10px 18px;font-weight:600;font-size:0.875rem;cursor:pointer;">
                    Muat ulang
                </button>
                <button id="${OVERLAY_ID}-dismiss" type="button" style="background:#f1f5f9;color:#0f172a;border:0;border-radius:10px;padding:10px 18px;font-weight:600;font-size:0.875rem;cursor:pointer;">
                    Tutup
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    document.getElementById(`${OVERLAY_ID}-reload`).addEventListener('click', () => {
        window.location.reload();
    });

    document.getElementById(`${OVERLAY_ID}-dismiss`).addEventListener('click', () => {
        hideOverlay();
    });

    return overlay;
}

function showOverlay(message) {
    const overlay = ensureOverlay();
    const messageEl = document.getElementById(`${OVERLAY_ID}-message`);
    if (messageEl !== null) {
        messageEl.textContent = message;
    }
    overlay.style.display = 'flex';
}

function hideOverlay() {
    const overlay = document.getElementById(OVERLAY_ID);
    if (overlay !== null) {
        overlay.style.display = 'none';
    }
}

function ensureOfflineBanner() {
    if (document.getElementById(OFFLINE_BANNER_ID) !== null) {
        return;
    }

    const banner = document.createElement('div');
    banner.id = OFFLINE_BANNER_ID;
    banner.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:2147483001;display:none;background:#b45309;color:#fff;padding:10px 16px;text-align:center;font-size:0.875rem;font-weight:600;';
    banner.textContent = '⚠ Tidak ada koneksi internet. Perubahan tidak akan tersimpan sampai koneksi kembali.';
    document.body.appendChild(banner);
}

function syncOfflineState() {
    const banner = document.getElementById(OFFLINE_BANNER_ID);
    if (banner === null) {
        return;
    }

    banner.style.display = navigator.onLine ? 'none' : 'block';
}

/**
 * Toast global sederhana. type: success | error | warning
 */
window.sitransToast = function (message, type = 'success', options = {}) {
    const colors = {
        success: { bg: '#15803d', icon: '✓' },
        error: { bg: '#b91c1c', icon: '✕' },
        warning: { bg: '#b45309', icon: '⚠' },
    };
    const style = colors[type] ?? colors.success;
    const sticky = options.sticky === true || type === 'error';

    const toast = document.createElement('div');
    toast.style.cssText = `position:fixed;top:16px;right:16px;z-index:2147483002;display:flex;align-items:flex-start;gap:8px;background:${style.bg};color:#fff;padding:12px 16px;border-radius:12px;box-shadow:0 10px 30px rgba(15,23,42,0.3);max-width:22rem;font-size:0.875rem;line-height:1.4;`;
    toast.innerHTML = `<span style="font-weight:700;">${style.icon}</span><span></span>`;

    const text = toast.lastElementChild;
    text.textContent = message;
    document.body.appendChild(toast);

    const close = document.createElement('button');
    close.type = 'button';
    close.textContent = '×';
    close.style.cssText = 'background:transparent;border:0;color:#fff;font-size:1rem;font-weight:700;cursor:pointer;margin-left:4px;padding:0;';
    close.addEventListener('click', () => toast.remove());
    toast.appendChild(close);

    if (!sticky) {
        window.setTimeout(() => toast.remove(), 4500);
    }
};

function bootErrorBoundary() {
    if (typeof window.Livewire === 'undefined' || typeof window.Livewire.interceptRequest !== 'function') {
        return;
    }

    let slowTimer = null;

    window.Livewire.interceptRequest(({ request, onSend, onError, onFailure, onFinish }) => {
        onSend(({ responsePromise }) => {
            // Watchdog: jika request Livewire masih berjalan > 15 detik,
            // beri tahu pengguna koneksi lambat (tanpa membatalkan request).
            slowTimer = window.setTimeout(() => {
                window.sitransToast?.('Proses memakan waktu lama. Periksa koneksi internet kamu.', 'warning');
            }, 15000);
        });

        onError(({ response, body, preventDefault }) => {
            preventDefault();

            if (response.status === 419) {
                showOverlay('Sesi berakhir, silakan login kembali. Halaman akan dimuat ulang, keranjang tersimpan di server.');
                window.setTimeout(() => window.location.reload(), 2500);

                return;
            }

            if (response.status >= 500) {
                showOverlay('Server sedang bermasalah. Coba beberapa saat lagi, atau muat ulang halaman ini.');

                return;
            }

            if (response.status === 403) {
                showOverlay('Kamu tidak punya izin untuk aksi ini. Hubungi pemilik kafe bila merasa ini keliru.');

                return;
            }

            if (response.status === 0) {
                showOverlay('Tidak ada koneksi internet. Cek koneksi kamu lalu coba lagi.');

                return;
            }

            showOverlay('Terjadi kesalahan saat memproses permintaan. Coba lagi atau muat ulang halaman.');
        });

        onFailure(() => {
            showOverlay('Tidak ada koneksi internet. Cek koneksi kamu lalu coba lagi.');
        });

        onFinish(() => {
            if (slowTimer !== null) {
                window.clearTimeout(slowTimer);
                slowTimer = null;
            }
        });
    });
}

function boot() {
    ensureOfflineBanner();
    syncOfflineState();

    window.addEventListener('online', syncOfflineState);
    window.addEventListener('offline', syncOfflineState);

    // Banner muncul juga saat tab kembali aktif (mis. setelah tidur).
    document.addEventListener('visibilitychange', syncOfflineState);

    if (document.readyState !== 'loading') {
        bootErrorBoundary();
    } else {
        document.addEventListener('DOMContentLoaded', bootErrorBoundary);
    }
}

boot();
