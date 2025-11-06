// PERUBAHAN 1: Versi cache diubah ke v15
const CACHE_NAME = 'jadwal-pjr-cache-v15';
// Daftar file inti yang akan disimpan
const CORE_FILES = [
  '.',
  'index.html',
  'style.css',
  // 'app.js' DIHAPUS DARI SINI, karena akan kita ambil via Network First
  'manifest.json',
  'https://cdn.tailwindcss.com',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'
  // Ikon dan gambar PJR akan di-cache saat dibutuhkan
];

// 1. Saat Service Worker di-install
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('Service Worker: Caching core files (v15)...');
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

  // === PERUBAHAN DI SINI ===

  // Strategi 1: Abaikan semua permintaan ke Firebase/Google APIs
  if (requestUrl.hostname.includes('firebase') || requestUrl.hostname.includes('googleapis.com') || requestUrl.hostname.includes('gstatic.com')) {
    event.respondWith(fetch(event.request));
    return;
  }
  
  // PERUBAHAN 2: Terapkan "Network First" untuk 'data.json' DAN 'app.js'
  if (requestUrl.pathname.endsWith('data.json') || requestUrl.pathname.endsWith('app.js')) {
    event.respondWith(
      caches.open(CACHE_NAME).then((cache) => {
        return fetch(event.request, { cache: 'no-store' }) 
          .then((networkResponse) => {
            console.log(`Service Worker: Mengambil ${requestUrl.pathname} baru dari network.`);
            // Simpan file baru ke cache untuk mode offline
            cache.put(event.request, networkResponse.clone());
            return networkResponse;
          })
          .catch(() => {
            // Gagal ambil dari network (offline), baru ambil dari cache
            console.log(`Service Worker: Gagal ambil ${requestUrl.pathname} dari network, ambil dari cache.`);
            return cache.match(event.request);
          });
      })
    );
    return;
  }
  // === AKHIR PERUBAHAN ===


  // Strategi 3: Untuk semua file lain (Cache First, then Network)
  // Ini berlaku untuk: index.html, style.css, font, gambar, dll.
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
