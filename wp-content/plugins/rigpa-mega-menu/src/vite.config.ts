import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";
import path from "path";

export default defineConfig({
  plugins: [react(), tailwindcss()],
  define: {
    "process.env.NODE_ENV": JSON.stringify("production"),
  },
  build: {
    outDir: path.resolve(__dirname, "../assets"),
    emptyOutDir: false,
    lib: {
      entry: path.resolve(__dirname, "main.tsx"),
      name: "RigpaMegaMenu",
      formats: ["iife"],
      fileName: () => "js/rigpa-mega-menu.js",
    },
    rollupOptions: {
      output: {
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith(".css")) {
            return "css/rigpa-mega-menu.css";
          }
          return "assets/[name][extname]";
        },
      },
    },
  },
});
