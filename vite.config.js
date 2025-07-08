import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default defineConfig({
  plugins: [vue()],
  root: path.resolve(__dirname, "resources/js"),
  server: {
    port: 5173,
    origin: "http://localhost:5173",
    strictPort: true,
    watch: {
      usePolling: true,
      interval: 1000, // Adjust the polling interval as needed
    },
    fs: {
      // 👇 Allow serving files from this external path
      allow: [
        path.resolve(__dirname), // your package root
        path.resolve(__dirname, "resources/js"),
        path.resolve(__dirname, "../tests/Support/resources"), // 👈 your additional folder
        path.resolve(__dirname, "resources/sass"), // your SCSS
        path.resolve(__dirname, "../"), // parent if needed (e.g. shared components)
      ],
    },
    hmr: {
      host: "localhost",
    },
  },
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "resources/js"),
      vue: "vue/dist/vue.esm-bundler.js", // ✅ THIS IS THE FIX
    },
  },
});
