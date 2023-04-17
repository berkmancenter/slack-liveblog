import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  build: {
    rollupOptions: {
      output: {
        assetFileNames: '[name][extname]',
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
      },
    },
    outDir: '../dist/front',
  },
})
