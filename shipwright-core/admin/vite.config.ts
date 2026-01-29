import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  base: '', // ÖNEMLİ: Boş bırakınca relative path çalışır
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      output: {
        // Hash kullanmıyoruz ki PHP bulabilsin (yine de PHP glob ile arıyor ama garanti olsun)
        entryFileNames: 'assets/index.js',
        chunkFileNames: 'assets/index.js',
        assetFileNames: 'assets/style.[ext]'
      }
    }
  }
})