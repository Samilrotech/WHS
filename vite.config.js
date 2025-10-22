import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import html from '@rollup/plugin-html';
import path from 'path';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/sensei-theme.css',
        'resources/js/app.js',
      ],
      refresh: true
    }),
    html()
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources')
    }
  },
  json: {
    stringify: true // Helps with JSON import compatibility
  },
  build: {
    commonjsOptions: {
      include: [/node_modules/] // Helps with importing CommonJS modules
    }
  }
});
