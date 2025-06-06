import { axios } from "../bootstrap";

/**
 * Obtiene la lista de encuestas del usuario (admin o cliente).
 * @returns {Promise<Array>}
 */
export async function fetchEncuestas() {
    const { data } = await axios.get("/encuestas");
    return data.data; // Asumiendo que la API devuelve { data: [...] }
}

/**
 * Obtiene el detalle completo de una encuesta por ID.
 * @param {number} idEnc
 * @returns {Promise<Object>}
 */
export async function fetchEncuestaDetalle(idEnc) {
    const { data } = await axios.get(`/encuestas/${idEnc}/detalle-completo`);
    return data.data;
}

// Otros métodos: crearEncuesta, actualizarEncuesta, eliminarEncuesta...
