const API_BASE_URL = "/api/entidades-externas";

export const entidadExternaService = {
    getAll: () => {
        return window.axios.get(API_BASE_URL);
    },
    getCamposByEntidad: (idEntidad) => {
        return window.axios.get(`${API_BASE_URL}/${idEntidad}/campos-externos`);
        // O si prefieres una ruta no anidada:
        // return window.axios.get(`/api/campos-externos?entidad_id=${idEntidad}`);
    },
};
