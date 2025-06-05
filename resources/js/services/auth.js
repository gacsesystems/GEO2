import axios from "axios";

export const getCurrentUser = async () => {
    try {
        const response = await axios.get("/api/me");
        return response.data;
    } catch (error) {
        if (error.response) {
            if (error.response.status === 401) {
                // No autenticado
                return { error: "unauthenticated" };
            }
            if (error.response.status === 403) {
                // No verificado
                return { error: "unverified" };
            }
        }
        // Otro error
        console.error("Error al obtener el usuario actual:", error);
        return { error: "unknown" };
    }
};

export const login = async (email, password) => {
    try {
        await axios.get("/sanctum/csrf-cookie"); // a) Obtener cookie XSRF

        const response = await axios.post("/api/login", { email, password });
        localStorage.setItem("authToken", response.data.token);
        // c) Guardar token para usarlo en futuras solicitudes
        axios.defaults.headers.common[
            "Authorization"
        ] = `Bearer ${response.data.token}`;
        return response.data;
    } catch (error) {
        console.error("Error al iniciar sesión:", error);
        throw error;
    }
};

export const logout = async () => {
    try {
        await axios.post("/api/logout");
        localStorage.removeItem("authToken");
        delete window.axios.defaults.headers.common["Authorization"];
        return true;
        // Si usas tokens JWT, elimínalo: localStorage.removeItem('authToken');
    } catch (error) {
        console.error("Error al cerrar sesión:", error);
        throw error;
    }
};

export const register = async (email, password) => {
    try {
        const response = await axios.post("/api/register", { email, password });
        return response.data;
    } catch (error) {
        console.error("Error al registrar usuario:", error);
        throw error;
    }
};

/**
 * Reenvía un enlace de verificación de email al usuario autenticado.
 */
export async function resendVerification() {
    // Llama al endpoint protegido que hace: user()->sendEmailVerificationNotification()
    await axios.post("/api/email/verification-notification");
    return true;
}
