const API_URL = "/api/clientes";

export const clienteService = {
    getAll: (params = {}) => {
        return window.axios.get(API_URL, { params }); // Usa la instancia global configurada
    },
    // ... otros métodos
};
