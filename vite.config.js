import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  resolve: {
    alias: {
      //img: resolve('resources/img'),
      fonts: resolve('resources/css/fonts'),
      forms: resolve('resources/js/forms'),
      '@': __dirname + '/resources/js/',
      vue: 'vue/dist/vue.esm-bundler.js'  // Add this line
    }
  },
  plugins: [
      laravel({
          input: [
              'resources/css/app.css',
              'resources/js/app.js',
              // Control Panel assets.
              // https://statamic.dev/extending/control-panel#adding-css-and-js-assets
              // 'resources/css/cp.css',
              // 'resources/js/cp.js',
          ],
          refresh: true,
      }),
      vue({
        template: {
            transformAssetUrls: {
                base: null,
                includeAbsolute: false,
            },
        },
      }),
  ],
  define: {
    __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: 'false',
  },
});
