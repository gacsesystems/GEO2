import { axios } from "../bootstrap";

/**
 * Obtiene el detalle de respuestas para la cabecera 'respondidaId',
 * incluyendo entidad_externa y campo_externo para que Delphi actualice.
 * @param {number} encuestaId
 * @param {number} respondidaId
 * @returns {Promise<Object>}
 */
export async function fetchDetalleRespuestas(encuestaId, respondidaId) {
    const { data } = await axios.get(
        `/encuestas/${encuestaId}/respuestas/${respondidaId}`
    );
    return data; // Aquí vendrá { encuesta_id, encuesta_respondida_id, paciente_id, respuestas: [...] }
}
