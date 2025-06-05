const API_BASE_URL = "/api/encuestas";

export const encuestaService = {
    // Para el cliente, usualmente obtendrán sus propias encuestas.
    // El backend (EncuestaPolicy) debería filtrar esto automáticamente
    // si el usuario no es admin.
    getMisEncuestas: (params = {}) => {
        return window.axios.get(`${API_BASE_URL}`, { params }); // GET /api/encuestas
    },
    create: (data) => {
        return window.axios.post(`${API_BASE_URL}`, data); // POST /api/encuestas
    },
    getById: (idEncuesta) => {
        return window.axios.get(`${API_BASE_URL}/${idEncuesta}`); // GET /api/encuestas/{encuesta}
    },
    getDetalleCompleto: (idEncuesta) => {
        return window.axios.get(
            `${API_BASE_URL}/${idEncuesta}/detalle-completo`
        );
    },
    update: (idEncuesta, data) => {
        return window.axios.put(`${API_BASE_URL}/${idEncuesta}`, data);
    },
    remove: (idEncuesta) => {
        return window.axios.delete(`${API_BASE_URL}/${idEncuesta}`);
    },
    generarUrl: (idEncuesta) => {
        return window.axios.post(`${API_BASE_URL}/${idEncuesta}/generar-url`);
    },
    // Si necesitas un endpoint específico para encuestas de un cliente (usado por admin):
    // getPorCliente: (idCliente) => {
    //     return window.axios.get(`${API_BASE_URL}/por-cliente/${idCliente}`);
    // }
};
