import path from "path"
import { defineConfig } from "vite"
import react from "@vitejs/plugin-react"
import tailwindcss from "@tailwindcss/vite"

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: { "@": path.resolve(import.meta.dirname, "./src") }, // enables "@/components/..." imports
  },
  server: { port: 5173, strictPort: true }, // fail loudly instead of drifting to 5174
})