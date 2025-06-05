const getApiUrl = (idEncuesta, idSeccion, idPregunta = null) => {
    let url = `/api/encuestas/${idEncuesta}/secciones/${idSeccion}/preguntas`;
    if (idPregunta) {
        url += `/${idPregunta}`;
    }
    return url;
};

export const preguntaService = {
    getAll: (idEncuesta, idSeccion) => {
        return window.axios.get(getApiUrl(idEncuesta, idSeccion));
    },
    create: (idEncuesta, idSeccion, data) => {
        return window.axios.post(getApiUrl(idEncuesta, idSeccion), data);
    },
    // getById: (idEncuesta, idSeccion, idPregunta) => { // Ya lo tienes en tu EncuestaController@show
    //     return window.axios.get(getApiUrl(idEncuesta, idSeccion, idPregunta));
    // },
    update: (idEncuesta, idSeccion, idPregunta, data) => {
        return window.axios.put(
            getApiUrl(idEncuesta, idSeccion, idPregunta),
            data
        );
    },
    remove: (idEncuesta, idSeccion, idPregunta) => {
        return window.axios.delete(
            getApiUrl(idEncuesta, idSeccion, idPregunta)
        );
    },
    reordenar: (idEncuesta, idSeccion, idPregunta, data) => {
        // data = { nuevo_orden: X }
        return window.axios.post(
            `${getApiUrl(idEncuesta, idSeccion, idPregunta)}/reordenar`,
            data
        );
        // Tu ruta API para reordenar pregunta es POST /api/encuestas/{encuesta}/secciones/{seccionEncuesta}/preguntas/{pregunta}/reordenar
        // y espera nuevo_orden en el cuerpo.
    },
};
