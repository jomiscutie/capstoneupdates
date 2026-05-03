/**
 * NORSU OJT DTR — Service worker (static shell + safe offline handling)
 * v6: cache-first static assets; do not cache authenticated HTML; offline fallback page.
 */
const CACHE_NAME = 'norsu-dtr-v14';
const STATIC_ASSETS = [
  '/',
  '/offline.html',
  '/login',
  '/css/norsu-dtr-advanced.css',
  '/css/auth-tokens.css',
  '/css/norsu-dtr-system.css',
  '/css/norsu-dtr-classic-ui.css',
  '/css/norsu-dtr-dialogs.css',
  '/css/norsu-dtr-modal-buttons.css',
  '/css/norsu-dtr-theme-toggle.css',
  '/js/norsu-dtr-dialogs.js',
  '/js/face-recognition.js',
  '/js/offline-queue.js',
  '/images/negrosorientalstateuniversity_cover.jpg',
  '/images/norsu-seal.png',
  '/images/app-icon.png',
  '/images/coordinator-icon.png',
  '/images/login-illustration.png',
  '/images/registration-illustration.png',
  '/favicon.ico',
  '/vendor/bootstrap/css/bootstrap.min.css',
  '/vendor/bootstrap/js/bootstrap.bundle.min.js',
  '/vendor/bootstrap-icons/bootstrap-icons.css',
  '/vendor/bootstrap-icons/fonts/bootstrap-icons.woff2',
  '/vendor/bootstrap-icons/fonts/bootstrap-icons.woff',
  '/vendor/face-api/face-api.min.js',
  '/vendor/face-api/model/tiny_face_detector_model-weights_manifest.json',
  '/vendor/face-api/model/tiny_face_detector_model.bin',
  '/vendor/face-api/model/face_landmark_68_model-weights_manifest.json',
  '/vendor/face-api/model/face_landmark_68_model.bin',
  '/vendor/face-api/model/face_recognition_model-weights_manifest.json',
  '/vendor/face-api/model/face_recognition_model.bin',
  '/vendor/face-api/model/face_expression_model-weights_manifest.json',
  '/vendor/face-api/model/face_expression_model.bin'
];

function isStaticAssetRequest(url) {
  const p = url.pathname;
  if (/\.(css|js|mjs|woff2?|png|jpg|jpeg|gif|svg|ico|webp|json|bin)(\?|$)/i.test(p)) return true;
  if (p.startsWith('/vendor/') || p.startsWith('/css/') || p.startsWith('/js/') || p.startsWith('/images/')) return true;
  return false;
}

/** HTML pages we may cache (public only — never coordinator/admin/student app shells). */
function shouldCacheNavigationPath(pathname) {
  if (pathname === '/' || pathname === '/login') return true;
  if (pathname === '/offline.html') return true;
  if (pathname.startsWith('/student/register')) return true;
  return false;
}

/** App areas that need the server; when offline, show offline.html instead of a stale cached session page. */
function needsServerForPath(pathname) {
  return /\/(coordinator|admin)(\/|$)/.test(pathname) || /\/student\//.test(pathname);
}

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS).catch((err) => {
        console.warn('SW: cache addAll failed for some assets', err);
      });
    }).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  if (event.request.method !== 'GET') return;
  if (url.pathname.startsWith('/sanctum') || url.pathname.startsWith('/api/')) return;

  const isNav = event.request.mode === 'navigate';

  if (isStaticAssetRequest(url)) {
    event.respondWith(
      caches.match(event.request).then((cached) => {
        if (cached) {
          fetch(event.request)
            .then((res) => {
              if (res.ok) {
                const clone = res.clone();
                caches.open(CACHE_NAME).then((c) => c.put(event.request, clone));
              }
            })
            .catch(() => {});
          return cached;
        }
        return fetch(event.request).then((res) => {
          if (res.ok) {
            const clone = res.clone();
            caches.open(CACHE_NAME).then((c) => c.put(event.request, clone));
          }
          return res;
        });
      })
    );
    return;
  }

  if (isNav) {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          if (response.ok && shouldCacheNavigationPath(url.pathname)) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then((c) => c.put(event.request, clone));
          }
          return response;
        })
        .catch(() => {
          return caches.match(event.request).then((cached) => {
            if (cached) return cached;
            if (needsServerForPath(url.pathname)) {
              return caches.match('/offline.html');
            }
            return caches.match('/');
          });
        })
    );
    return;
  }

  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});
