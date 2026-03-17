import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  base: '/attendance/app2/',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
  },
  server: {
    proxy: {
      '/chidonOld/attendance/api': {
        target: 'http://localhost',
        changeOrigin: true,
      },
    },
  },
});
