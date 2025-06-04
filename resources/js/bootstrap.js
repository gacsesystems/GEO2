import axios from "axios";

window.axios = axios;

// window.axios.defaults.baseURL = "/"; // O tu URL base de API si es diferente, ej: 'http://tu-dominio.test/api'
// window.axios.defaults.baseURL = "http://localhost:8000/api";
window.axios.defaults.baseURL = "/";
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * Laravel Sanctum (SPA con Cookies) o Sesiones Web:
 * Si estás usando Laravel Sanctum para autenticación SPA (la opción recomendada para
 * React y Laravel en el mismo dominio) o sesiones web tradicionales,
 * 'withCredentials = true' es crucial para que el navegador envíe cookies
 * (como la cookie de sesión de Sanctum o la cookie de sesión de Laravel)
 * con cada solicitud.
 */
window.axios.defaults.withCredentials = true;

/**
 * Manejo del Token CSRF para Laravel:
 * Laravel protege contra ataques CSRF. Para las peticiones POST, PUT, DELETE, etc.,
 * necesitas incluir el token CSRF.
 * Esta es la forma estándar de obtenerlo desde la meta tag que Laravel Breeze/Jetstream
 * suelen añadir en tu layout principal de Blade.
 */
const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
if (csrfTokenElement) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] =
        csrfTokenElement.getAttribute("content");
} else {
    console.warn(
        'CSRF token not found. AJAX POST/PUT/DELETE requests may fail. Make sure you have <meta name="csrf-token" content="{{ csrf_token() }}"> in your Blade layout.'
    );
}

/**
 * Manejo de Token de Autorización (Bearer Token):
 * Esta sección es para autenticación basada en tokens (como JWT o tokens API de Sanctum).
 * Si estás usando Sanctum SPA con cookies, esta parte para 'Authorization' header
 * generalmente NO es necesaria para las peticiones a tu propia API, ya que la cookie de sesión
 * maneja la autenticación.
 *
 * Si SÍ usas tokens API (ej. para una app móvil o un frontend completamente separado
 * que no puede usar cookies de manera confiable, o si tu API también la consumen terceros):
 * - Debes obtener el token después del login y almacenarlo (localStorage es común).
 * - Necesitas una lógica para cargar este token al iniciar la app.
 */
const token = localStorage.getItem("authToken"); // Ejemplo: si guardas el token en localStorage
if (token) {
    window.axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}
// Si usas Sanctum SPA con cookies, puedes comentar o eliminar la sección del 'Authorization' header
// a menos que también estés consumiendo APIs externas que requieran un Bearer token diferente.

/**
 * Interceptores de Axios:
 * Muy útiles para manejar respuestas y errores globalmente.
 */

// Interceptor de Solicitud (Opcional, pero útil para añadir el token dinámicamente)
// Esto es más robusto si el token puede cambiar durante la sesión
// o si no quieres setearlo globalmente al inicio si no existe.
// window.axios.interceptors.request.use(config => {
//     const token = localStorage.getItem('authToken');
//     if (token && !config.headers.Authorization) { // Solo si no está ya seteado
//         config.headers.Authorization = `Bearer ${token}`;
//     }
//     return config;
// });

// Interceptor de Respuesta
window.axios.interceptors.response.use(
    (response) => response, // Simplemente retorna la respuesta si es exitosa
    (error) => {
        const { status } = error.response || {}; // Obtener el status de la respuesta de error

        if (status === 401) {
            // No autorizado (Token inválido, sesión expirada, etc.)
            console.error(
                "Axios interceptor: Unauthorized (401). Redirecting to login."
            );

            // Si usas tokens, es buena idea limpiar el token almacenado.
            localStorage.removeItem("authToken"); // Limpia el token si lo usas

            // Redirigir a la página de login.
            // Evitar bucles de redirección si ya estamos en /login.
            if (window.location.pathname !== "/login") {
                window.location.href = "/login"; // Redirección simple
            }
            // En una SPA más compleja, podrías usar el router de React para navegar
            // y un AuthContext para actualizar el estado de autenticación globalmente.
            // Sin embargo, window.location.href es efectivo para forzar una recarga
            // y asegurar que el estado de la app se reinicie correctamente.
        } else if (status === 419) {
            // CSRF Token Mismatch o Sesión Expirada (común en Laravel)
            console.error(
                "Axios interceptor: CSRF Token Mismatch or Page Expired (419). Consider refreshing or re-authenticating."
            );
            // Podrías intentar recargar la página o redirigir a login.
            // A veces, un simple refresh soluciona problemas de CSRF si el token de la página se desactualizó.
            // alert("Tu sesión ha expirado o el token de seguridad no es válido. Por favor, recarga la página e inténtalo de nuevo.");
            // window.location.reload();
            // O redirigir a login si el reload no es suficiente:
            if (window.location.pathname !== "/login") {
                window.location.href = "/login";
            }
        }
        // Es importante retornar Promise.reject(error) para que las llamadas
        // individuales a axios que tienen su propio .catch() puedan manejar el error también.
        return Promise.reject(error);
    }
);

// Puedes añadir otras configuraciones de Axios aquí si es necesario.

console.log("Bootstrap.js loaded and Axios configured.");

// En bootstrap.js, DESCOMENTA Y USA ESTO SI MANEJAS TOKENS DINÁMICAMENTE:
// window.axios.interceptors.request.use(config => {
//     const token = localStorage.getItem('authToken');
//     // Solo añade el header si existe un token y la config no tiene ya un Authorization header
//     // (para no sobreescribir si alguna petición específica lo setea manualmente)
//     if (token && !config.headers.Authorization) {
//         config.headers.Authorization = `Bearer ${token}`;
//     }
//     return config;
// }, error => {
//     return Promise.reject(error);
// });
