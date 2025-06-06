import { axios } from "../bootstrap";

/**
 * Obtiene la lista de encuestas (Admin o Cliente)
 * @returns {Promise<Array>}
 */
export async function fetchEncuestas() {
    const { data } = await axios.get("/api/encuestas");
    return data.data || [];
}

/**
 * Obtiene detalle completo (para generar URL pública, etc.)
 * @param {number} encuestaId
 * @returns {Promise<Object>}
 */
export async function fetchEncuestaDetalle(encuestaId) {
    const { data } = await axios.get(
        `/api/encuestas/${encuestaId}/detalle-completo`
    );
    return data.data || {};
}

// Otros métodos: crearEncuesta, actualizarEncuesta, eliminarEncuesta...
export function fetchEstructuraEncuesta(encuestaId) {
    return axios
        .get(`/api/encuestas/${encuestaId}/detalle-completo`)
        .then((res) => res.data.data); // Asumimos que tu Resource devuelve { data: {...} }
}

/**
 * Crea una nueva encuesta
 * @param {Object} payload
 * @returns {Promise<Object>}
 */
export async function crearEncuesta(payload) {
    const { data } = await axios.post("/api/encuestas", payload);
    return data.data;
}

/**
 * Actualiza una encuesta existente
 * @param {number} encuestaId
 * @param {Object} payload
 * @returns {Promise<Object>}
 */
export async function actualizarEncuesta(encuestaId, payload) {
    const { data } = await axios.put(`/api/encuestas/${encuestaId}`, payload);
    return data.data;
}

/**
 * Elimina (soft delete) una encuesta
 * @param {number} encuestaId
 * @returns {Promise<void>}
 */
export async function eliminarEncuesta(encuestaId) {
    await axios.delete(`/api/encuestas/${encuestaId}`);
}

/**
 * (Opcional) si en algún momento quieres separar la generación de URL en su propio método:
 */
export async function generarUrlEncuesta(encuestaId) {
    const { data } = await axios.post(
        `/api/encuestas/${encuestaId}/generar-url`
    );
    return data;
}

// Secciones
export function crearSeccion(encuestaId, payload) {
    return axios
        .post(`/api/encuestas/${encuestaId}/secciones`, payload)
        .then((res) => res.data);
}
export function actualizarSeccion(encuestaId, seccionId, payload) {
    return axios
        .put(`/api/encuestas/${encuestaId}/secciones/${seccionId}`, payload)
        .then((res) => res.data);
}
export function eliminarSeccion(encuestaId, seccionId) {
    return axios.delete(`/api/encuestas/${encuestaId}/secciones/${seccionId}`);
}
export function reordenarSeccion(encuestaId, seccionId, nuevoOrden) {
    return axios.post(
        `/api/encuestas/${encuestaId}/secciones/${seccionId}/reordenar/${nuevoOrden}`
    );
}

// Preguntas
export function crearPregunta(encuestaId, seccionId, payload) {
    return axios
        .post(
            `/api/encuestas/${encuestaId}/secciones/${seccionId}/preguntas`,
            payload
        )
        .then((res) => res.data);
}
export function actualizarPregunta(encuestaId, seccionId, preguntaId, payload) {
    return axios
        .put(
            `/api/encuestas/${encuestaId}/secciones/${seccionId}/preguntas/${preguntaId}`,
            payload
        )
        .then((res) => res.data);
}
export function eliminarPregunta(encuestaId, seccionId, preguntaId) {
    return axios.delete(
        `/api/encuestas/${encuestaId}/secciones/${seccionId}/preguntas/${preguntaId}`
    );
}
export function reordenarPregunta(
    encuestaId,
    seccionId,
    preguntaId,
    nuevoOrden
) {
    return axios.post(
        `/api/encuestas/${encuestaId}/secciones/${seccionId}/preguntas/${preguntaId}/reordenar/${nuevoOrden}`
    );
}

// Opciones (para preguntas de tipo “opción única” o “selección múltiple”)
export function crearOpcion(encuestaId, seccionId, preguntaId, payload) {
    return axios
        .post(
            `/api/encuestas/${encuestaId}/secciones/${seccionId}/preguntas/${preguntaId}/opciones`,
            payload
        )
        .then((res) => res.data);
}
export function actualizarOpcion(
    encuestaId,
    seccionId,
    preguntaId,
    opcionId,
    payload
) {
    return axios
        .put(
            `/api/encuestas/${encuestaId}/secciones/${seccionId}/preguntas/${preguntaId}/opciones/${opcionId}`,
            payload
        )
        .then((res) => res.data);
}
export function eliminarOpcion(encuestaId, seccionId, preguntaId, opcionId) {
    return axios.delete(
        `/api/encuestas/${encuestaId}/secciones/${seccionId}/preguntas/${preguntaId}/opciones/${opcionId}`
    );
}
export function reordenarOpcion(
    encuestaId,
    seccionId,
    preguntaId,
    opcionId,
    nuevoOrden
) {
    return axios.post(
        `/api/encuestas/${encuestaId}/secciones/${seccionId}/preguntas/${preguntaId}/opciones/${opcionId}/reordenar/${nuevoOrden}`
    );
}

// Para obtener los “campos externos” desde tu ERP (solo un ejemplo):
export function fetchEntidadesExternas() {
    return axios.get("/api/entidades-externas").then((res) => res.data.data);
}
export function fetchCamposParaEntidad(entidadId) {
    return axios
        .get(`/api/entidades-externas/${entidadId}/campos`)
        .then((res) => res.data.data);
}
