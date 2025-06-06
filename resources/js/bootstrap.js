import axios from "axios";

// Indicamos la URL base (ajústala a tu entorno):
axios.defaults.baseURL = "/";

// axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Para que envíe cookies CSRF (útil si usas Sanctum con cookies):
axios.defaults.withCredentials = true;

/**
 * Manejo del Token CSRF para Laravel:
 * Laravel protege contra ataques CSRF. Para las peticiones POST, PUT, DELETE, etc.,
 * necesitas incluir el token CSRF.
 * Esta es la forma estándar de obtenerlo desde la meta tag que Laravel Breeze/Jetstream
 * suelen añadir en tu layout principal de Blade.
 */
// const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
// if (csrfTokenElement) {
//     axios.defaults.headers.common["X-CSRF-TOKEN"] =
//         csrfTokenElement.getAttribute("content");
// } else {
//     console.warn(
//         'CSRF token not found. AJAX POST/PUT/DELETE requests may fail. Make sure you have <meta name="csrf-token" content="{{ csrf_token() }}"> in your Blade layout.'
//     );
// }

// Ejemplo de interceptor para adjuntar token si existe en localStorage o Context:
axios.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem("auth_token");
        if (token) {
            config.headers["Authorization"] = `Bearer ${token}`;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

export { axios };
