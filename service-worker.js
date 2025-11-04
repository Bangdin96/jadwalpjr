const CACHE_NAME = 'jadwal-pjr-cache-v1';
// Daftar file inti yang akan disimpan
const CORE_FILES = [
  '.',
  'index.html',
  'style.css',
  'app.js',
  'manifest.json',
  'https://cdn.tailwindcss.com',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'
  // Ikon dan gambar PJR akan di-cache saat dibutuhkan
];

// 1. Saat Service Worker di-install
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('Service Worker: Caching core files...');
      return cache.addAll(CORE_FILES);
    })
  );
  self.skipWaiting();
});

// 2. Saat Service Worker aktif
self.addEventListener('activate', (event) => {
  // Hapus cache lama jika ada
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Service Worker: Menghapus cache lama:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// 3. Saat aplikasi mengambil file (event 'fetch')
self.addEventListener('fetch', (event) => {
  const requestUrl = new URL(event.request.url);

  // Strategi 1: Untuk data.json (Network First, then Cache)
  // Selalu coba ambil data terbaru. Jika gagal (offline), ambil dari cache.
  if (requestUrl.pathname.endsWith('data.json')) {
    event.respondWith(
      caches.open(CACHE_NAME).then((cache) => {
        return fetch(event.request)
          .then((networkResponse) => {
            // Berhasil dapat data baru, simpan ke cache
            cache.put(event.request, networkResponse.clone());
            return networkResponse;
          })
          .catch(() => {
            // Gagal ambil data (offline), ambil dari cache
            console.log('Service Worker: Gagal ambil data.json dari network, ambil dari cache.');
            return cache.match(event.request);
          });
      })
    );
    return;
  }

  // Strategi 2: Untuk semua file lain (Cache First, then Network)
  // Ambil dari cache dulu untuk kecepatan. Jika tidak ada, ambil dari network.
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse; // Langsung ambil dari cache
      }

      // Tidak ada di cache, ambil dari network
      return fetch(event.request).then((networkResponse) => {
        // Simpan respons baru ke cache untuk lain kali
        return caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, networkResponse.clone());
          return networkResponse;
        });
      });
    })
  );
});
