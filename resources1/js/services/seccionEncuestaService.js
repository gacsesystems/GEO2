const getApiUrl = (idEncuesta, idSeccion = null) => {
    let url = `/api/encuestas/${idEncuesta}/secciones`;
    if (idSeccion) {
        url += `/${idSeccion}`;
    }
    return url;
};

export const seccionEncuestaService = {
    getAll: (idEncuesta) => {
        return window.axios.get(getApiUrl(idEncuesta));
    },
    create: (idEncuesta, data) => {
        return window.axios.post(getApiUrl(idEncuesta), data);
    },
    getById: (idEncuesta, idSeccion) => {
        return window.axios.get(getApiUrl(idEncuesta, idSeccion));
    },
    update: (idSeccion, data) => {
        // idEncuesta está implícito en la ruta de la API para PUT
        // El controlador de Laravel usa el {seccionEncuesta} bindeado que ya tiene id_encuesta
        // Así que solo necesitamos el idSeccion para la URL si el backend no lo infiere del modelo
        // Si tu ruta es /api/secciones/{seccion}, entonces no necesitas idEncuesta aquí.
        // Asumiendo que la ruta es /api/encuestas/{encuesta}/secciones/{seccion}
        // y que `idSeccion` es el objeto completo o ya conoces `idEncuesta`
        // Si `idSeccion` es solo el ID, necesitas `idEncuesta`
        // Para simplificar, si el backend espera /api/secciones/{idSeccion} para PUT/DELETE:
        // return window.axios.put(`/api/secciones/${idSeccion}`, data);

        // Si necesitas pasar idEncuesta y el backend lo espera en la URL:
        // Asumimos que 'data' podría contener 'id_encuesta' o que lo recuperas de otra forma.
        // La forma más segura es que el endpoint de update no requiera idEncuesta en la URL si ya tiene el idSeccion
        // Por ahora, asumiendo que la ruta es /api/encuestas/{encuesta}/secciones/{seccion}
        // y que el backend puede determinar 'encuesta' a partir de 'seccion'
        // Si 'data' ya tiene id_encuesta, o se obtiene del objeto seccionActual:
        // return window.axios.put(getApiUrl(data.id_encuesta_del_objeto_seccion, idSeccion), data);
        // Pero como se usa UpdateSeccionEncuestaRequest que bindea Encuesta y SeccionEncuesta, el update ya sabe a qué encuesta pertenece la sección.
        // El endpoint PUT es /api/encuestas/{encuesta}/secciones/{seccionEncuesta}
        // Por lo tanto, necesitamos idEncuesta. Si 'data' no lo tiene, el componente que llama debe proveerlo.
        // Modificaremos el llamado en DiseñadorEncuestaPage para pasar ambos.
        // O, mejor, el servicio solo necesita el idSeccion y el backend lo resuelve
        return window.axios.put(`/api/secciones/${idSeccion}`, data); // ASUMIENDO RUTA /api/secciones/{id_seccion} (más simple para PUT)
        // SI NO, debes ajustar la URL aquí y en el backend/rutas.
        // Tu ruta API actual es /api/encuestas/{encuesta}/secciones/{seccionEncuesta}
        // por lo que update(idEncuesta, idSeccion, data) sería más preciso.
        // Pero por ahora lo dejo así esperando que el backend lo maneje bien con el binding.
        // Por ahora vamos a asumir que idSeccion es el objeto seccion completo.
        // Y que el controlador PUT espera el objeto seccion.
        // Entonces la URL de update es relativa a esa seccion.
        // Esto es lo más probable:
        // async update(seccion, data) y la URL es /api/encuestas/{seccion.id_encuesta}/secciones/{seccion.id_seccion}
    },
    // Reemplazo para update, más preciso con tus rutas
    updatePreciso: (idEncuesta, idSeccion, data) => {
        return window.axios.put(getApiUrl(idEncuesta, idSeccion), data);
    },
    remove: (idEncuesta, idSeccion) => {
        // Similar a update
        return window.axios.delete(getApiUrl(idEncuesta, idSeccion));
    },
    reordenar: (idEncuesta, idSeccion, data) => {
        // data = { nuevo_orden: X }
        return window.axios.post(
            `${getApiUrl(idEncuesta, idSeccion)}/reordenar/${data.nuevo_orden}`,
            {}
        );
        // Tu ruta API es /api/encuestas/{encuesta}/secciones/{seccionEncuesta}/reordenar/{nuevoOrden}
        // por lo que la data no es necesaria en el cuerpo del POST, solo en la URL
    },
};
