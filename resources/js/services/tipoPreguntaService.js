const API_URL = "/api/tipos-pregunta"; // DEBES CREAR ESTE ENDPOINT EN LARAVEL

export const tipoPreguntaService = {
    getAll: () => {
        // Este endpoint debe devolver todos los tipos de pregunta de tu tabla `tipos_pregunta`
        // Ejemplo: GET /api/tipos-pregunta -> [{id_tipo_pregunta: 1, nombre: 'Texto corto', ...}, ...]
        return window.axios.get(API_URL);
    },
};
