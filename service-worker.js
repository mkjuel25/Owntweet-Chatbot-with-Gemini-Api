const CACHE_NAME = 'owntweet-chatbot-cache-v1'; // Unique cache name for your app
const urlsToCache = [
  '/',                       // Cache the index page
  'index.php',
  'guest.php',
  'profile.php',
  'auth.php',
  'config.php',
  'Gemini.php',
  'api.php',
  'css/style.css',          // If you have a separate CSS file
  'js/script.js',            // If you have a separate JS file
  'https://cdn.tailwindcss.com', // Tailwind CSS CDN (if you're using it this way)
  'https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css', // Boxicons CDN
  'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css', // Highlight.js CSS
  'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js', // Highlight.js JS
  'https://cdnjs.cloudflare.com/ajax/libs/marked/6.0.0/marked.min.js' // Marked.js
  // Add paths to any images, fonts, or other static assets you use
];

// Install Service Worker - Caching static assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

// Fetch events - Serve cached content if available, otherwise fetch from network
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse; // Serve from cache if found
        }
        return fetch(event.request); // Otherwise, fetch from network
      })
  );
});

// Activate Service Worker - Clean up old caches (optional, but good practice)
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName); // Delete old caches
          }
        })
      );
    })
  );
});
