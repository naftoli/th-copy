import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

// https://vite.dev/config/
export default defineConfig({
  base: '/vite/',
  plugins: [react()],
  esbuild: {
    loader: "jsx",
    include: /src\/.*\.jsx?$/,
    exclude: [],
  },
  optimizeDeps: {
    esbuildOptions: {
      loader: {
        '.js': 'jsx',
      },
    },
  },


  resolve: {

    alias: {
      api: path.resolve(__dirname, './src/api'),
      components: path.resolve(__dirname, './src/components'),
      data: path.resolve(__dirname, './src/data'),
      functions: path.resolve(__dirname, './src/functions'),
      img: path.resolve(__dirname, './src/img'),
      pages: path.resolve(__dirname, './src/pages'),
      store: path.resolve(__dirname, './src/store'),
      styles: path.resolve(__dirname, './src/styles'),
      tests: path.resolve(__dirname, './src/tests'),
      'react-select/lib/animated': 'react-select/animated',
      'react-select/lib/Creatable': 'react-select/creatable',
      heDatePicker: path.resolve(__dirname, './src/heDatePicker'),
    },
  },
})



