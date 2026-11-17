import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// Dev:   Vite on :5173 proxies /api and /uploads to the PHP server on :8000
// Build: outputs to ../backend/build, served by backend/index.php (SEO shell)
const PHP_SERVER = process.env.PHP_SERVER || 'http://localhost:8000';

export default defineConfig(({ command }) => ({
  plugins: [react()],
  base: command === 'build' ? '/build/' : '/',
  server: {
    port: 5173,
    proxy: {
      '/api': { target: PHP_SERVER, changeOrigin: true },
      '/uploads': { target: PHP_SERVER, changeOrigin: true },
      '/sitemap.xml': { target: PHP_SERVER, changeOrigin: true },
    },
  },
  build: {
    outDir: '../backend/build',
    emptyOutDir: true,
    sourcemap: false,
    cssCodeSplit: true,
    rollupOptions: {
      output: {
        manualChunks: {
          react: ['react', 'react-dom', 'react-router-dom', 'react-helmet-async'],
        },
      },
    },
  },
}));
