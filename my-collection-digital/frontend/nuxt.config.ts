import tailwindcss from "@tailwindcss/vite";

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr: true,
  compatibilityDate: '2024-11-01',
  devtools: { enabled: process.env.NODE_ENV !== 'production' },
  experimental: {
    appManifest: false,
  },
  image: {
    provider: 'ipx',
    ipx: {
      sizes: {
        xs: 320,
        sm: 640,
        md: 768,
        lg: 1024,
        xl: 1280,
        '2xl': 1536,
      }
    }
  },
  css: ['~/assets/css/main.css'],
  experimental: {
    viteNode: false
  },
  
  modules: [
    '@vite-pwa/nuxt',
    '@nuxtjs/i18n',
    '@vueuse/nuxt',
    '@nuxt/image',
    '@pinia/nuxt'
  ],
  nitro: {
    routeRules: {
      '/api/**': {
        headers: {
          'Content-Security-Policy': "default-src 'self'; script-src 'self' 'unsafe-inline' https://*.groq.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https: blob:; font-src 'self' data:; connect-src 'self' https://api.groq.com https://covers.openlibrary.org https://*.openlibrary.org; frame-src 'none'; object-src 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests",
          'X-Content-Type-Options': 'nosniff',
          'X-Frame-Options': 'DENY',
          'X-XSS-Protection': '1; mode=block',
          'Referrer-Policy': 'strict-origin-when-cross-origin',
          'Permissions-Policy': 'geolocation=(), microphone=(), camera=()',
        }
      }
    }
  },
  i18n: {
    locales: [
      { code: 'pt', iso: 'pt-BR', name: 'Português', file: 'pt-BR.json' },
      { code: 'en', iso: 'en-US', name: 'English', file: 'en-US.json' },
      { code: 'es', iso: 'es-ES', name: 'Español', file: 'es-ES.json' },
    ],
    defaultLocale: 'pt',
    lazy: true,
    langDir: 'locales/',
    strategy: 'no_prefix',
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: 'i18n_redirected',
      alwaysRedirect: true,
      fallbackLocale: 'pt',
    },
  },
  runtimeConfig: {
    public: {
      appEnv: process.env.NUXT_PUBLIC_APP_ENV || process.env.NODE_ENV || 'development',
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || '',
    },
  },
  pwa: {
    registerType: 'autoUpdate',
    manifest: {
      name: 'Meu Acervo Digital',
      short_name: 'Acervo',
      description: 'Leitura, estante e estatísticas com modo offline.',
      theme_color: '#0f172a',
      background_color: '#0b1220',
      display: 'standalone',
      scope: '/',
      start_url: '/',
      icons: [
        {
          src: '/icon.svg',
          sizes: 'any',
          type: 'image/svg+xml',
          purpose: 'any',
        },
        {
          src: '/maskable.svg',
          sizes: 'any',
          type: 'image/svg+xml',
          purpose: 'maskable',
        },
      ],
    },
    workbox: {
      navigateFallback: '/offline',
      runtimeCaching: [
        {
          urlPattern: ({ url }) => url.pathname.startsWith('/api/') && url.pathname.indexOf('/file') === -1,
          handler: 'StaleWhileRevalidate',
          options: {
            cacheName: 'api-cache',
            expiration: { maxEntries: 200, maxAgeSeconds: 60 * 60 * 24 * 7 },
            cacheableResponse: { statuses: [0, 200] },
          },
        },
        {
          urlPattern: ({ url }) => url.pathname.startsWith('/api/user-shelf/') && url.pathname.endsWith('/file'),
          handler: 'CacheFirst',
          options: {
            cacheName: 'reader-files',
            expiration: { maxEntries: 50, maxAgeSeconds: 60 * 60 * 24 * 30 },
            cacheableResponse: { statuses: [0, 200] },
          },
        },
        {
          urlPattern: ({ url }) => url.hostname.includes('covers.openlibrary.org') || url.hostname.includes('books.google'),
          handler: 'StaleWhileRevalidate',
          options: {
            cacheName: 'covers',
            expiration: { maxEntries: 200, maxAgeSeconds: 60 * 60 * 24 * 30 },
            cacheableResponse: { statuses: [0, 200] },
          },
        },
      ],
    },
    client: {
      installPrompt: true,
    },
    devOptions: {
      enabled: false,
    },
  },
  components: [
    {
      path: '~/components',
      pathPrefix: false,
      ignore: ['**/*.ts'],
    },
  ],
  vite: {
    plugins: [
      tailwindcss(),
    ],
  },
})

