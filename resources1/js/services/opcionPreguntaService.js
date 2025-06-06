// Tus rutas para opciones son 'shallow', lo que significa que para show, update, destroy,
// solo necesitas el ID de la opción. Para store, necesitas el ID de la pregunta.

const getApiUrlForQuestion = (idEncuesta, idSeccion, idPregunta) =>
    `/api/encuestas/${idEncuesta}/secciones/${idSeccion}/preguntas/${idPregunta}/opciones`;
const API_URL_OPTIONS_SHALLOW = "/api/opciones"; // Para GET {opcion}, PUT {opcion}, DELETE {opcion}

export const opcionPreguntaService = {
    // Obtener todas las opciones de una pregunta (el getDetalleCompleto de encuesta ya las trae, pero por si acaso)
    // getAllByPregunta: (idEncuesta, idSeccion, idPregunta) => {
    //     return window.axios.get(getApiUrlForQuestion(idEncuesta, idSeccion, idPregunta));
    // },
    create: (idEncuesta, idSeccion, idPregunta, data) => {
        // data = { texto_opcion: "...", valor_opcion: "..." }
        return window.axios.post(
            getApiUrlForQuestion(idEncuesta, idSeccion, idPregunta),
            data
        );
    },
    createBulk: (idEncuesta, idSeccion, idPregunta, opcionesArray) => {
        // opcionesArray = [{ texto_opcion: "...", valor_opcion: "..." }, ...]
        return window.axios.post(
            `${getApiUrlForQuestion(idEncuesta, idSeccion, idPregunta)}/bulk`,
            { opciones: opcionesArray }
        );
    },
    update: (idOpcion, data) => {
        // data = { texto_opcion: "...", valor_opcion: "..." }
        return window.axios.put(`${API_URL_OPTIONS_SHALLOW}/${idOpcion}`, data);
    },
    remove: (idOpcion) => {
        return window.axios.delete(`${API_URL_OPTIONS_SHALLOW}/${idOpcion}`);
    },
    reordenar: (idOpcion, data) => {
        // data = { nuevo_orden: X }
        // Tu ruta API es POST /api/opciones/{opcionPregunta}/reordenar
        // y espera nuevo_orden en el cuerpo.
        return window.axios.post(
            `${API_URL_OPTIONS_SHALLOW}/${idOpcion}/reordenar`,
            data
        );
    },
};
