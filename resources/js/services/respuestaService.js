const API_BASE_URL = "/api/encuestas";

export const respuestaService = {
    submitRespuestas: (idEncuesta, data) => {
        // data = { respuestas: [...] }
        return window.axios.post(
            `${API_BASE_URL}/${idEncuesta}/respuestas`,
            data
        );
    },
    // Si necesitas obtener respuestas individuales (ya tienes un endpoint para show)
    // getRespuestaById: (idEncuesta, idRespuesta) => {
    //     return window.axios.get(`${API_BASE_URL}/${idEncuesta}/respuestas/${idRespuesta}`);
    // }
};
