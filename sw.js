const CACHE_NAME = 'vietsun-tms-v31';
const ASSETS = [
  './',
  './index.html',
  './manifest.json',
  './icon.svg',
  './logoVietsunColor.png',
  './logo-vietsunnew.png',
  './logoThna.png',
  './seal_vietsun.png',
  './vietsunwatermark2.png'
];

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS).catch(() => {}))
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;
  const url = new URL(event.request.url);

  // Không can thiệp vào cross-origin (VD: Google Fonts) hoặc các API PHP để trình duyệt dùng thẳng RAM/Disk cache gốc
  if (url.origin !== self.location.origin) return;
  if (url.pathname.includes('/api/') || url.search.includes('api=') || url.pathname.endsWith('.php')) {
    return;
  }

  // Cache-First cho ảnh tĩnh, icon, manifest (tải 0ms, không tốn request mạng)
  if (/\.(png|svg|jpg|jpeg|gif|webp|ico|json)$/i.test(url.pathname)) {
    event.respondWith(
      caches.match(event.request).then((cached) => {
        if (cached) return cached;
        return fetch(event.request).then((res) => {
          const copy = res.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy)).catch(() => {});
          return res;
        });
      })
    );
    return;
  }

  // Network-First cho trang HTML chính để luôn nhận bản cập nhật code mới nhất
  event.respondWith(
    fetch(event.request)
      .then((res) => {
        const copy = res.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy)).catch(() => {});
        return res;
      })
      .catch(() => caches.match(event.request))
  );
});
