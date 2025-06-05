const API_BASE_URL = "/api/reportes/encuestas";

export const reporteService = {
    getRespuestasDetalladas: (idEncuesta, params = {}) => {
        return window.axios.get(
            `${API_BASE_URL}/${idEncuesta}/respuestas-detalladas`,
            { params }
        );
    },
    getResumenPorPregunta: (idEncuesta, params = {}) => {
        return window.axios.get(
            `${API_BASE_URL}/${idEncuesta}/resumen-por-pregunta`,
            { params }
        );
    },
    // Las funciones de exportación no necesitan un método de servicio aquí
    // porque se acceden directamente por URL para que el navegador maneje la descarga.
};
