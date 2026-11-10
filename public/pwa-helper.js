/**
 * SPL (Susi Parsel Lebaran) - PWA Companion & App Badging Helper
 * Fitur:
 * 1. PWA Service Worker Registration
 * 2. WhatsApp & In-App Browser Detection with Direct "Open in Chrome" Android Intent
 * 3. Native PWA Install Prompt (beforeinstallprompt) & Fallback Guidance
 * 4. App Badging API (Red Dot Notification on Mobile/Desktop Shortcut Icon)
 * 5. Dynamic HTML5 Canvas Favicon Red Dot Indicator
 */
(function () {
    'use strict';

    // 1. SERVICE WORKER REGISTRATION & TEMPORARY CACHE CLEANUP
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            const swUrl = (window.SPL_SW_URL || './sw.js');
            navigator.serviceWorker.register(swUrl).catch((err) => {
                console.debug('PWA ServiceWorker registration notice:', err);
            });
        });
    }

    // Bersihkan semua penyimpanan temporary cache browser agar data 100% dari database
    if ('caches' in window) {
        caches.keys().then((names) => {
            for (const name of names) {
                caches.delete(name);
            }
        });
    }

    // 2. WHATSAPP & IN-APP BROWSER DETECTION
    function detectAndHandleInAppBrowser() {
        const ua = navigator.userAgent || navigator.vendor || window.opera || '';
        const isWhatsApp = /WhatsApp/i.test(ua);
        const isInstagram = /Instagram/i.test(ua);
        const isFB = /FBAN|FBAV/i.test(ua);
        const isLine = /Line/i.test(ua);
        const isAndroid = /Android/i.test(ua);
        const isInApp = isWhatsApp || isInstagram || isFB || isLine;

        // If running in standalone PWA, no need to show in-app banner
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (isStandalone) return;

        if (isInApp) {
            if (document.getElementById('spl-inapp-banner')) return;

            const banner = document.createElement('div');
            banner.id = 'spl-inapp-banner';
            banner.className = 'fixed top-0 left-0 right-0 bg-slate-900/95 backdrop-blur text-white z-[99999] px-4 py-3 shadow-xl border-b border-pink-500 font-sans text-xs';

            const cleanHost = window.location.host;
            const fullPath = window.location.pathname + window.location.search + window.location.hash;
            const scheme = window.location.protocol.replace(':', '');
            // Android intent to open native Google Chrome app directly
            const chromeIntent = `intent://${cleanHost}${fullPath}#Intent;scheme=${scheme};package=com.android.chrome;end`;

            let actionHtml = '';
            if (isAndroid) {
                actionHtml = `
                    <a href="${chromeIntent}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-md transition shrink-0">
                        <i class="fa-brands fa-chrome mr-1.5 text-sm"></i> Buka di Google Chrome
                    </a>
                `;
            } else {
                actionHtml = `
                    <span class="text-[11px] text-pink-300 font-bold bg-white/10 px-2.5 py-1 rounded-md shrink-0">
                        Ketuk ⋮ di pojok atas &gt; Buka di Browser Luar
                    </span>
                `;
            }

            banner.innerHTML = `
                <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 text-lg shadow-sm">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <div>
                            <div class="font-extrabold text-sm text-pink-400">Terbuka di Browser WhatsApp</div>
                            <div class="text-[11px] text-slate-300 leading-tight">
                                WhatsApp memblokir instalasi otomatis. Agar ikon <strong>SPL</strong> bisa diinstal di layar utama HP Anda, buka halaman ini di <strong>Google Chrome</strong>.
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                        ${actionHtml}
                        <button type="button" onclick="document.getElementById('spl-inapp-banner').remove()" class="p-1.5 text-slate-400 hover:text-white" title="Tutup">
                            ✕
                        </button>
                    </div>
                </div>
            `;

            document.body.prepend(banner);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', detectAndHandleInAppBrowser);
    } else {
        detectAndHandleInAppBrowser();
    }

    // 3. PWA INSTALL PROMPT HANDLER & AGENT MOBILE INSTALLATION
    let deferredPwaPrompt = null;

    function checkStandaloneMode() {
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        
        if (isStandalone) {
            // Aplikasi sudah terpasang di HP (mode standalone) -> sembunyikan tombol pasang
            document.querySelectorAll('.agent-pwa-install-element, .pwa-install-trigger').forEach((el) => {
                el.style.display = 'none';
            });
            const card = document.getElementById('agent-pwa-install-card');
            if (card) card.style.display = 'none';
        } else {
            // Masih di browser -> tampilkan tombol dan kartu pasang aplikasi untuk agen
            document.querySelectorAll('.agent-pwa-install-element').forEach((el) => {
                if (el.tagName === 'BUTTON' || el.tagName === 'A') {
                    el.style.removeProperty('display');
                }
            });
            const card = document.getElementById('agent-pwa-install-card');
            if (card && sessionStorage.getItem('spl_hide_agent_pwa_card') !== '1') {
                card.style.display = 'block';
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkStandaloneMode);
    } else {
        checkStandaloneMode();
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPwaPrompt = e;
        checkStandaloneMode();
        document.querySelectorAll('.pwa-install-trigger').forEach((el) => {
            el.style.display = 'inline-flex';
        });
    });

    window.triggerPwaInstall = function () {
        if (deferredPwaPrompt) {
            deferredPwaPrompt.prompt();
            deferredPwaPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    document.querySelectorAll('.pwa-install-trigger, .agent-pwa-install-element').forEach((el) => {
                        el.style.display = 'none';
                    });
                    const card = document.getElementById('agent-pwa-install-card');
                    if (card) card.style.display = 'none';
                }
                deferredPwaPrompt = null;
            });
        } else {
            // Jika browser belum mengizinkan prompt otomatis (karena HTTP lokal, Safari, atau Chrome belum trigger):
            // Buka petunjuk visual dengan panah penunjuk menu browser
            window.showChromeMenuPointer();
        }
    };

    window.showChromeMenuPointer = function () {
        if (document.getElementById('chrome-pointer-overlay')) return;

        const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);
        const overlay = document.createElement('div');
        overlay.id = 'chrome-pointer-overlay';
        overlay.className = 'fixed inset-0 bg-slate-950/85 backdrop-blur-sm z-[999999] flex flex-col items-center justify-between p-5 text-white font-sans text-center animate-in fade-in duration-200';
        
        if (isIOS) {
            // Safari iOS: panah mengarah ke bawah ke bilah share
            overlay.innerHTML = `
                <div class="pt-8 max-w-sm">
                    <div class="w-16 h-16 rounded-2xl bg-pink-600 mx-auto flex items-center justify-center text-3xl shadow-lg mb-3">
                        <i class="fa-solid fa-mobile-screen"></i>
                    </div>
                    <h3 class="font-extrabold text-lg text-white mb-2">Pasang di iPhone / iPad</h3>
                    <p class="text-xs text-slate-200 leading-relaxed mb-4">
                        Ketuk tombol <strong>Bagikan / Share</strong> (ikon kotak dengan panah ke atas di bilah bawah Safari), lalu pilih <strong>"Tambahkan ke Layar Utama"</strong> (Add to Home Screen).
                    </p>
                    <button type="button" onclick="document.getElementById('chrome-pointer-overlay').remove()" class="px-5 py-2.5 rounded-xl bg-white text-slate-900 font-extrabold text-xs shadow-md">
                        Saya Mengerti &bull; Tutup
                    </button>
                </div>
                <div class="flex flex-col items-center pb-4 animate-bounce">
                    <span class="text-xs font-bold text-pink-300 mb-1">Ketuk tombol Share di bawah</span>
                    <i class="fa-solid fa-arrow-down text-3xl text-pink-400"></i>
                </div>
            `;
        } else {
            // Android Chrome: panah mengarah ke pojok kanan atas ke menu titik tiga (⋮)
            overlay.innerHTML = `
                <div class="w-full flex justify-end pr-2 pt-1">
                    <div class="flex flex-col items-end animate-bounce">
                        <i class="fa-solid fa-arrow-up text-3xl text-pink-400 mr-2"></i>
                        <span class="bg-pink-600 text-white font-black text-xs px-3 py-1.5 rounded-xl shadow-lg mr-1 mt-1">
                            1. Ketuk Titik Tiga (⋮) di sini
                        </span>
                    </div>
                </div>
                <div class="max-w-sm my-auto">
                    <div class="w-16 h-16 rounded-2xl bg-pink-600 mx-auto flex items-center justify-center text-3xl shadow-lg mb-3">
                        <i class="fa-solid fa-mobile-screen"></i>
                    </div>
                    <h3 class="font-extrabold text-lg text-white mb-2">Cara Pasang SPL di Layar HP</h3>
                    <ol class="text-xs text-slate-200 text-left space-y-2.5 bg-white/10 p-4 rounded-2xl backdrop-blur-md mb-4">
                        <li class="flex items-start">
                            <span class="font-black text-pink-400 mr-2">1.</span>
                            <span>Ketuk tanda <strong>titik tiga (⋮)</strong> di pojok kanan atas browser Google Chrome.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="font-black text-pink-400 mr-2">2.</span>
                            <span>Pilih menu <strong>"Tambahkan ke Layar Utama"</strong> (Add to Home screen) atau <strong>"Instal Aplikasi"</strong>.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="font-black text-pink-400 mr-2">3.</span>
                            <span>Ikon <strong>SPL</strong> akan langsung terpasang di HP Anda layaknya aplikasi Play Store!</span>
                        </li>
                    </ol>
                    <button type="button" onclick="document.getElementById('chrome-pointer-overlay').remove()" class="w-full py-3 rounded-xl bg-white text-slate-900 hover:bg-slate-100 font-extrabold text-xs shadow-md transition active:scale-95">
                        Saya Mengerti, Tutup Panduan Ini
                    </button>
                </div>
                <div class="pb-2 text-[11px] text-slate-400">
                    Sistem SPL Musim Lebaran 2026
                </div>
            `;
        }

        document.body.appendChild(overlay);
    };

    window.openPwaHelpModal = function () {
        const modal = document.getElementById('pwa-help-modal');
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    window.closePwaHelpModal = function () {
        const modal = document.getElementById('pwa-help-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    window.dismissAgentPwaCard = function () {
        const card = document.getElementById('agent-pwa-install-card');
        if (card) card.style.display = 'none';
        sessionStorage.setItem('spl_hide_agent_pwa_card', '1');
    };

    window.addEventListener('appinstalled', () => {
        document.querySelectorAll('.pwa-install-trigger, .agent-pwa-install-element').forEach((el) => {
            el.style.display = 'none';
        });
        const card = document.getElementById('agent-pwa-install-card');
        if (card) card.style.display = 'none';
        deferredPwaPrompt = null;
    });

    // 4. APP BADGING API (ICON PINTASAN PWA) & FAVICON RED NOTIFICATION DOT
    let originalFaviconHref = null;

    function initFavicon() {
        const link = document.querySelector("link[rel*='icon']");
        if (link && !originalFaviconHref) {
            originalFaviconHref = link.href;
        }
    }

    function setFaviconBadge(count) {
        if (!originalFaviconHref) initFavicon();
        if (!originalFaviconHref) return;

        const link = document.querySelector("link[rel*='icon']");
        if (!link) return;

        if (count <= 0) {
            link.href = originalFaviconHref;
            return;
        }

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function () {
            try {
                const canvas = document.createElement('canvas');
                canvas.width = 64;
                canvas.height = 64;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, 64, 64);

                // Draw solid red notification dot at top right
                ctx.beginPath();
                ctx.arc(48, 16, 13, 0, 2 * Math.PI, false);
                ctx.fillStyle = '#e11d48'; // red-600
                ctx.fill();
                ctx.lineWidth = 2.5;
                ctx.strokeStyle = '#ffffff';
                ctx.stroke();

                // Draw text inside red dot if count > 0
                ctx.font = 'bold 13px sans-serif';
                ctx.fillStyle = '#ffffff';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                const label = count > 9 ? '9+' : String(count);
                ctx.fillText(label, 48, 16);

                link.href = canvas.toDataURL('image/png');
            } catch (e) {
                // Ignore canvas security errors
            }
        };
        img.src = originalFaviconHref;
    }

    window.updateAppBadge = async function () {
        const apiUrl = window.SPL_BADGE_API_URL || './api/notifications/badge-count';
        try {
            const res = await fetch(apiUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (res.ok) {
                const data = await res.json();
                const count = parseInt(data.count, 10) || 0;

                // 1. NATIVE PWA APP BADGE ON DESKTOP/MOBILE SHORTCUT
                if ('setAppBadge' in navigator) {
                    if (count > 0) {
                        navigator.setAppBadge(count).catch(() => {});
                    } else {
                        navigator.clearAppBadge().catch(() => {});
                    }
                }

                // 2. DYNAMIC RED DOT FAVICON
                setFaviconBadge(count);

                // 3. IN-APP BADGE INDICATORS
                document.querySelectorAll('.pwa-badge-indicator').forEach((el) => {
                    if (count > 0) {
                        el.style.display = 'inline-flex';
                        el.textContent = count > 9 ? '9+' : count;
                    } else {
                        el.style.display = 'none';
                    }
                });

                const feedUnseen = parseInt(data.feed_unseen, 10) || 0;
                document.querySelectorAll('.feed-badge-indicator').forEach((el) => {
                    if (feedUnseen > 0) {
                        el.style.display = 'inline-flex';
                        if (!el.classList.contains('w-2')) {
                            el.textContent = feedUnseen > 9 ? '9+' : feedUnseen;
                        }
                    } else {
                        el.style.display = 'none';
                    }
                });
            }
        } catch (e) {
            // silent fail
        }
    };

    // Auto update badge on window load & every 60s
    window.addEventListener('load', () => {
        initFavicon();
        updateAppBadge();
        setInterval(updateAppBadge, 60000);
    });

    // Update whenever app is brought to foreground
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            updateAppBadge();
        }
    });

})();

