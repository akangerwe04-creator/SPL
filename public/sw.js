// SPL (Susi Parsel Lebaran) Service Worker v4.0 - Permanent Storage Mode
// Tidak ada penyimpanan cache temporary. Seluruh data & tampilan wajib terhubung langsung ke database live.

self.addEventListener('install', (event) => {
  // Langsung aktifkan versi service worker terbaru tanpa menunggu
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  // Hapus dan bersihkan semua penyimpanan temporary cache lama di browser
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(keys.map((key) => caches.delete(key)));
    }).then(() => self.clients.claim())
  );
});

// Mode Langsung (No Temporary Cache Interception):
// Semua fetch request langsung dikirim ke server live & database aktif.
self.addEventListener('fetch', (event) => {
  // Biarkan browser mengeksekusi request langsung ke jaringan server
  return;
});
