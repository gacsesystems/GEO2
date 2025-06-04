import axios from "axios";

export const getCurrentUser = async () => {
    try {
        const response = await axios.get("/api/user");
        return response.data;
    } catch (error) {
        if (error.response && error.response.status === 401) return null;

        console.error("Error al obtener el usuario actual:", error);
        throw error;
    }
};

export const login = async (email, password) => {
    try {
        // Ejemplo: await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post("/api/login", { email, password });
        // Si usas tokens JWT, guárdalo: localStorage.setItem('authToken', response.data.token);
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
