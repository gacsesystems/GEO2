const API_URL = "/api/roles";

export const rolService = {
    getAll: () => {
        return window.axios.get(API_URL); // Usa la instancia global configurada
    },
};
