// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['@nuxt/eslint'],
  devServer: {
    host: '100.72.41.21',
    port: 3000,
    cors: {
      origin: '*',
    }
  }
})
