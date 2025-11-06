// PERUBAHAN TUNGGAL ADA DI BARIS INI (v12 menjadi v13)
const CACHE_NAME = 'jadwal-pjr-cache-v13';
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
      console.log('Service Worker: Caching core files (v13)...');
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

  // Strategi 1: (BARU) Abaikan semua permintaan ke Firebase/Google APIs
  // Ini PENTING agar statistik Firebase berfungsi.
  if (requestUrl.hostname.includes('firebase') || requestUrl.hostname.includes('googleapis.com')) {
    event.respondWith(fetch(event.request));
    return;
  }

  // Strategi 2: Untuk data.json (Network First, then Cache, TAPI DIPAKSA)
  if (requestUrl.pathname.endsWith('data.json')) {
    event.respondWith(
      caches.open(CACHE_NAME).then((cache) => {
        return fetch(event.request, { cache: 'no-store' })
          .then((networkResponse) => {
            console.log('Service Worker: Mengambil data.json baru dari network.');
            cache.put(event.request, networkResponse.clone());
            return networkResponse;
          })
          .catch(() => {
            console.log('Service Worker: Gagal ambil data.json dari network, ambil dari cache.');
            return cache.match(event.request);
          });
      })
    );
    return;
  }
  // === AKHIR PERUBAHAN ===


  // Strategi 3: Untuk semua file lain (Cache First, then Network)
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
