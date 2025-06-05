import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/main.jsx"],
            refresh: true,
        }),
    ],
    server: {
        proxy: {
            "/api": "http://localhost:8000",
        },
    },
});
