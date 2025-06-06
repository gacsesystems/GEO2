import React, { useState, useEffect, useCallback } from "react";
import { useParams, useNavigate, Link } from "react-router-dom";
import { encuestaService } from "../../services/encuestaService";
import { seccionEncuestaService } from "../../services/seccionEncuestaService";
import { preguntaService } from "../../services/preguntaService";
import { tipoPreguntaService } from "../../services/tipoPreguntaService"; // Para PreguntaForm
import { opcionPreguntaService } from "../../services/opcionPreguntaService"; // Para OpcionesPreguntaEditor
import Modal from "../../components/ui/Modal";
import SeccionForm from "../../components/cliente/SeccionForm";
import PreguntaForm from "../../components/cliente/PreguntaForm";
import PreguntasList from "../../components/cliente/PreguntasList";
import OpcionesPreguntaEditor from "../../components/cliente/OpcionesPreguntaEditor"; // Para las opciones
import {
    PlusIcon as IconoAgregar,
    PencilIcon as IconoEditar,
    TrashIcon as IconoEliminar,
    CogIcon as IconoOpciones,
    ArrowLeftIcon as IconoVolver,
    ArrowUpIcon,
    ArrowDownIcon,
} from "../../components/ui/Icons";
import "./DiseñadorEncuestaPage.css";

const DiseñadorEncuestaPage = () => {
    const { idEncuesta } = useParams();
    const navigate = useNavigate();

    const [encuesta, setEncuesta] = useState(null);
    const [secciones, setSecciones] = useState([]);
    const [tiposPregunta, setTiposPregunta] = useState([]);
    const [preguntasCandidatasPadre, setPreguntasCandidatasPadre] = useState(
        []
    );

    const [loadingPage, setLoadingPage] = useState(true);
    const [operationError, setOperationError] = useState(""); // Error general para operaciones

    // --- Estado para Modal de Sección ---
    const [showSeccionModal, setShowSeccionModal] = useState(false);
    const [seccionActual, setSeccionActual] = useState(null); // Para edición
    const [nombreSeccion, setNombreSeccion] = useState("");
    const [descripcionSeccion, setDescripcionSeccion] = useState("");
    const [isSavingSeccion, setIsSavingSeccion] = useState(false);
    const [seccionFormErrorLocal, setSeccionFormErrorLocal] = useState("");
    const [seccionApiError, setSeccionApiError] = useState("");

    // --- Estado para Modal de Pregunta ---
    const [showPreguntaModal, setShowPreguntaModal] = useState(false);
    const [preguntaActual, setPreguntaActual] = useState(null); // Para edición
    const [idSeccionContexto, setIdSeccionContexto] = useState(null); // Para nueva pregunta
    // Campos del PreguntaForm
    const [textoPregunta, setTextoPregunta] = useState("");
    const [idTipoPregunta, setIdTipoPregunta] = useState("");
    const [esObligatoria, setEsObligatoria] = useState(false);
    const [numeroMinimo, setNumeroMinimo] = useState("");
    const [numeroMaximo, setNumeroMaximo] = useState("");
    const [fechaMinima, setFechaMinima] = useState("");
    const [fechaMaxima, setFechaMaxima] = useState("");
    const [horaMinima, setHoraMinima] = useState("");
    const [horaMaxima, setHoraMaxima] = useState("");
    const [idPreguntaPadre, setIdPreguntaPadre] = useState("");
    const [valorCondicionPadre, setValorCondicionPadre] = useState("");
    const [idOpcionCondicionPadre, setIdOpcionCondicionPadre] = useState("");
    const [
        opcionesPreguntaPadreSeleccionada,
        setOpcionesPreguntaPadreSeleccionada,
    ] = useState([]);
    const [isSavingPregunta, setIsSavingPregunta] = useState(false);
    const [preguntaFormErrorLocal, setPreguntaFormErrorLocal] = useState("");
    const [preguntaApiError, setPreguntaApiError] = useState("");

    // --- Estado para Modal de Opciones de Pregunta ---
    const [showOpcionesModal, setShowOpcionesModal] = useState(false);
    const [preguntaParaOpciones, setPreguntaParaOpciones] = useState(null);

    const [showMapeoModal, setShowMapeoModal] = useState(false);
    const [preguntaParaMapeo, setPreguntaParaMapeo] = useState(null);

    const cargarDetalleEncuestaYTipos = useCallback(
        async (mostrarLoadingGeneral = true) => {
            if (mostrarLoadingGeneral) setLoadingPage(true);
            setOperationError("");
            try {
                const [encuestaResponse, tiposPreguntaResponse] =
                    await Promise.all([
                        encuestaService.getDetalleCompleto(idEncuesta),
                        tipoPreguntaService.getAll(), // Cargar tipos de pregunta
                    ]);

                const encuestaData = encuestaResponse.data.data;
                setEncuesta(encuestaData);
                setTiposPregunta(
                    tiposPreguntaResponse.data.data ||
                        tiposPreguntaResponse.data
                );

                const seccionesOrdenadas = (
                    encuestaData.secciones_encuesta || []
                ).sort((a, b) => a.orden - b.orden);
                seccionesOrdenadas.forEach((s) => {
                    if (s.preguntas)
                        s.preguntas.sort((a, b) => a.orden - b.orden);
                });
                setSecciones(seccionesOrdenadas);

                // Preparar preguntas candidatas para ser padre (de toda la encuesta)
                const todasLasPreguntas = seccionesOrdenadas.flatMap(
                    (s) => s.preguntas || []
                );
                setPreguntasCandidatasPadre(todasLasPreguntas);
            } catch (err) {
                console.error("Error al cargar datos del diseñador:", err);
                setOperationError(
                    err.response?.data?.message ||
                        "No se pudo cargar la información para el diseñador."
                );
                if (!encuesta && mostrarLoadingGeneral)
                    navigate("/cliente/encuestas");
            } finally {
                if (mostrarLoadingGeneral) setLoadingPage(false);
            }
        },
        [idEncuesta, navigate, encuesta]
    ); // `encuesta` en deps para la condición de navigate

    useEffect(() => {
        cargarDetalleEncuestaYTipos();
    }, [cargarDetalleEncuestaYTipos]); // Solo una vez o cuando idEncuesta cambie (manejado por useCallback)

    // --- Lógica para cargar opciones de la pregunta padre seleccionada en PreguntaForm ---
    useEffect(() => {
        if (idPreguntaPadre) {
            const padre = preguntasCandidatasPadre.find(
                (p) => String(p.id_pregunta) === idPreguntaPadre
            );
            if (
                padre &&
                (padre.tipo_pregunta?.requiere_opciones ||
                    padre.tipo_pregunta_info?.requiere_opciones)
            ) {
                // Las opciones ya deberían estar en padre.opciones_pregunta si getDetalleCompleto las trae
                setOpcionesPreguntaPadreSeleccionada(
                    padre.opciones_pregunta || []
                );
            } else {
                setOpcionesPreguntaPadreSeleccionada([]);
            }
        } else {
            setOpcionesPreguntaPadreSeleccionada([]);
        }
        setIdOpcionCondicionPadre(""); // Resetear al cambiar la pregunta padre
    }, [idPreguntaPadre, preguntasCandidatasPadre]);

    // --- MANEJO DE SECCIONES ---
    const resetSeccionForm = () => {
        setNombreSeccion("");
        setDescripcionSeccion("");
        setSeccionFormErrorLocal("");
        setSeccionApiError("");
    };
    const handleAbrirModalCrearSeccion = () => {
        setSeccionActual(null);
        resetSeccionForm();
        setShowSeccionModal(true);
    };
    const handleAbrirModalEditarSeccion = (seccion) => {
        setSeccionActual(seccion);
        setNombreSeccion(seccion.nombre || "");
        setDescripcionSeccion(seccion.descripcion || "");
        setSeccionFormErrorLocal("");
        setSeccionApiError("");
        setShowSeccionModal(true);
    };
    const handleCerrarSeccionModal = () => {
        setShowSeccionModal(false);
        setSeccionActual(null);
    };
    const validateSeccionForm = () => {
        setSeccionFormErrorLocal("");
        if (!nombreSeccion.trim()) {
            setSeccionFormErrorLocal("El nombre de la sección es obligatorio.");
            return false;
        }
        return true;
    };
    const handleGuardarSeccion = async (e) => {
        e.preventDefault();
        if (!validateSeccionForm()) return;
        setIsSavingSeccion(true);
        setSeccionApiError("");
        const seccionData = {
            nombre: nombreSeccion,
            descripcion: descripcionSeccion,
        };
        try {
            if (seccionActual && seccionActual.id_seccion) {
                await seccionEncuestaService.updatePreciso(
                    parseInt(idEncuesta),
                    seccionActual.id_seccion,
                    seccionData
                );
            } else {
                await seccionEncuestaService.create(
                    parseInt(idEncuesta),
                    seccionData
                );
            }
            await cargarDetalleEncuestaYTipos(false);
            handleCerrarSeccionModal();
        } catch (err) {
            setSeccionApiError(
                err.response?.data?.message || "Error al guardar sección."
            );
        } finally {
            setIsSavingSeccion(false);
        }
    };
    const handleEliminarSeccion = async (idSeccionAEliminar) => {
        // ... (lógica similar a la que tenías, usando seccionEncuestaService.remove(idEncuesta, idSeccionAEliminar))
        const seccion = secciones.find(
            (s) => s.id_seccion === idSeccionAEliminar
        );
        if (!seccion) return;
        if (
            window.confirm(
                `¿Eliminar sección "${seccion.nombre}" y todas sus preguntas?`
            )
        ) {
            setLoadingPage(true); // Podrías usar un loader específico para esta acción
            setOperationError("");
            try {
                await seccionEncuestaService.remove(
                    parseInt(idEncuesta),
                    idSeccionAEliminar
                );
                await cargarDetalleEncuestaYTipos(false); // Recargar sin spinner de página completa
            } catch (err) {
                setOperationError(
                    err.response?.data?.message || "Error al eliminar sección."
                );
            } finally {
                setLoadingPage(false);
            }
        }
    };
    const handleReordenarSeccion = async (idSeccion, direccion) => {
        // ... (lógica similar, usando seccionEncuestaService.reordenar(idEncuesta, idSeccion, { nuevo_orden: X }))
        const seccionIndex = secciones.findIndex(
            (s) => s.id_seccion === idSeccion
        );
        if (seccionIndex === -1) return;
        const seccion = secciones[seccionIndex];
        let nuevoOrdenCalculado = seccion.orden;

        if (direccion === "arriba" && seccion.orden > 1) nuevoOrdenCalculado--;
        else if (direccion === "abajo" && seccion.orden < secciones.length)
            nuevoOrdenCalculado++;
        else return;

        // setLoadingPage(true); // Loader específico para la acción
        setOperationError("");
        try {
            await seccionEncuestaService.reordenar(
                parseInt(idEncuesta),
                idSeccion,
                { nuevo_orden: nuevoOrdenCalculado }
            );
            await cargarDetalleEncuestaYTipos(false);
        } catch (err) {
            setOperationError(
                "Error al reordenar sección: " +
                    (err.response?.data?.message || err.message)
            );
        } finally {
            // setLoadingPage(false);
        }
    };

    // --- MANEJO DE PREGUNTAS ---
    const resetPreguntaForm = () => {
        setTextoPregunta("");
        setIdTipoPregunta("");
        setEsObligatoria(false);
        setNumeroMinimo("");
        setNumeroMaximo("");
        setFechaMinima("");
        setFechaMaxima("");
        setHoraMinima("");
        setHoraMaxima("");
        setIdPreguntaPadre("");
        setValorCondicionPadre("");
        setIdOpcionCondicionPadre("");
        setPreguntaFormErrorLocal("");
        setPreguntaApiError("");
    };
    const handleAbrirModalCrearPregunta = (idSeccionCtx) => {
        setPreguntaActual(null);
        resetPreguntaForm();
        setIdSeccionContexto(idSeccionCtx);
        setShowPreguntaModal(true);
    };
    const handleAbrirModalEditarPregunta = (pregunta) => {
        setPreguntaActual(pregunta);
        setTextoPregunta(pregunta.texto_pregunta || "");
        setIdTipoPregunta(
            pregunta.id_tipo_pregunta ? String(pregunta.id_tipo_pregunta) : ""
        );
        setEsObligatoria(pregunta.es_obligatoria || false);
        setNumeroMinimo(
            pregunta.numero_minimo !== null
                ? String(pregunta.numero_minimo)
                : ""
        );
        setNumeroMaximo(
            pregunta.numero_maximo !== null
                ? String(pregunta.numero_maximo)
                : ""
        );
        setFechaMinima(pregunta.fecha_minima?.substring(0, 10) || "");
        setFechaMaxima(pregunta.fecha_maxima?.substring(0, 10) || "");
        setHoraMinima(pregunta.hora_minima || "");
        setHoraMaxima(pregunta.hora_maxima || "");
        setIdPreguntaPadre(
            pregunta.id_pregunta_padre ? String(pregunta.id_pregunta_padre) : ""
        );
        setValorCondicionPadre(pregunta.valor_condicion_padre || "");
        setIdOpcionCondicionPadre(
            pregunta.id_opcion_condicion_padre
                ? String(pregunta.id_opcion_condicion_padre)
                : ""
        );
        setIdSeccionContexto(pregunta.id_seccion); // Guardar el contexto de la sección
        setPreguntaFormErrorLocal("");
        setPreguntaApiError("");
        setShowPreguntaModal(true);
    };
    const handleCerrarPreguntaModal = () => {
        setShowPreguntaModal(false);
        setPreguntaActual(null);
        setIdSeccionContexto(null);
    };
    const validatePreguntaForm = () => {
        setPreguntaFormErrorLocal("");
        if (!textoPregunta.trim() || !idTipoPregunta) {
            setPreguntaFormErrorLocal(
                "El texto y el tipo de pregunta son obligatorios."
            );
            return false;
        }
        // Más validaciones específicas...
        const padreSeleccionado = preguntasCandidatasPadre.find(
            (p) => String(p.id_pregunta) === idPreguntaPadre
        );
        if (
            idPreguntaPadre &&
            !idOpcionCondicionPadre &&
            !valorCondicionPadre.trim()
        ) {
            if (
                padreSeleccionado &&
                (padreSeleccionado.tipo_pregunta?.requiere_opciones ||
                    padreSeleccionado.tipo_pregunta_info?.requiere_opciones)
            ) {
                setPreguntaFormErrorLocal(
                    "Si la pregunta padre es de opciones, debe seleccionar una opción de condición."
                );
                return false;
            } else if (padreSeleccionado) {
                // Padre no es de opciones, pero no se dio valor
                setPreguntaFormErrorLocal(
                    "Si selecciona una pregunta padre, debe indicar un valor de condición o una opción de condición."
                );
                return false;
            }
        }
        return true;
    };

    const handleGuardarPregunta = async (e) => {
        e.preventDefault();
        if (!validatePreguntaForm()) return;
        setIsSavingPregunta(true);
        setPreguntaApiError("");

        const tipoSel = tiposPregunta.find(
            (tp) => String(tp.id_tipo_pregunta) === idTipoPregunta
        );
        const preguntaData = {
            texto_pregunta: textoPregunta,
            id_tipo_pregunta: parseInt(idTipoPregunta),
            es_obligatoria: esObligatoria,
            numero_minimo:
                tipoSel?.permite_min_max_numerico && numeroMinimo !== ""
                    ? parseInt(numeroMinimo)
                    : null,
            numero_maximo:
                tipoSel?.permite_min_max_numerico && numeroMaximo !== ""
                    ? parseInt(numeroMaximo)
                    : null,
            fecha_minima:
                tipoSel?.permite_min_max_fecha && fechaMinima
                    ? fechaMinima
                    : null,
            fecha_maxima:
                tipoSel?.permite_min_max_fecha && fechaMaxima
                    ? fechaMaxima
                    : null,
            hora_minima: horaMinima || null,
            hora_maxima: horaMaxima || null,
            id_pregunta_padre: idPreguntaPadre
                ? parseInt(idPreguntaPadre)
                : null,
            valor_condicion_padre:
                idPreguntaPadre && !idOpcionCondicionPadre
                    ? valorCondicionPadre
                    : null,
            id_opcion_condicion_padre:
                idPreguntaPadre && idOpcionCondicionPadre
                    ? parseInt(idOpcionCondicionPadre)
                    : null,
            id_seccion: preguntaActual
                ? preguntaActual.id_seccion
                : idSeccionContexto, // Importante para el backend
            // 'orden' lo maneja el backend al crear/actualizar a veces
        };
        if (preguntaActual) preguntaData.orden = preguntaActual.orden; // Enviar orden si se edita, el backend puede ajustarlo si es necesario

        try {
            if (preguntaActual && preguntaActual.id_pregunta) {
                await preguntaService.update(
                    parseInt(idEncuesta),
                    preguntaActual.id_seccion,
                    preguntaActual.id_pregunta,
                    preguntaData
                );
            } else {
                await preguntaService.create(
                    parseInt(idEncuesta),
                    idSeccionContexto,
                    preguntaData
                );
            }
            await cargarDetalleEncuestaYTipos(false);
            handleCerrarPreguntaModal();
        } catch (err) {
            setPreguntaApiError(
                err.response?.data?.message || "Error al guardar pregunta."
            );
        } finally {
            setIsSavingPregunta(false);
        }
    };
    const handleEliminarPregunta = async (idPreguntaAEliminar) => {
        // ... (lógica similar, usando preguntaService.remove(idEncuesta, idSeccionDeLaPregunta, idPreguntaAEliminar))
        let idSeccionDePregunta = null;
        for (const seccion of secciones) {
            if (
                seccion.preguntas?.find(
                    (p) => p.id_pregunta === idPreguntaAEliminar
                )
            ) {
                idSeccionDePregunta = seccion.id_seccion;
                break;
            }
        }
        if (!idSeccionDePregunta) {
            setOperationError(
                "No se pudo encontrar la sección de la pregunta a eliminar."
            );
            return;
        }

        if (window.confirm("¿Está seguro de eliminar esta pregunta?")) {
            // setLoadingPage(true); // O loader específico
            setOperationError("");
            try {
                await preguntaService.remove(
                    parseInt(idEncuesta),
                    idSeccionDePregunta,
                    idPreguntaAEliminar
                );
                await cargarDetalleEncuestaYTipos(false);
            } catch (err) {
                setOperationError(
                    err.response?.data?.message || "Error al eliminar pregunta."
                );
            } finally {
                // setLoadingPage(false);
            }
        }
    };
    const handleReordenarPregunta = async (
        idPregunta,
        idSeccionActual,
        direccion
    ) => {
        // ... (lógica similar, usando preguntaService.reordenar(idEncuesta, idSeccion, idPregunta, { nuevo_orden: X }))
        const seccion = secciones.find((s) => s.id_seccion === idSeccionActual);
        if (!seccion || !seccion.preguntas) return;
        const pregunta = seccion.preguntas.find(
            (p) => p.id_pregunta === idPregunta
        );
        if (!pregunta) return;

        let nuevoOrdenCalculado = pregunta.orden;
        if (direccion === "arriba" && pregunta.orden > 1) nuevoOrdenCalculado--;
        else if (
            direccion === "abajo" &&
            pregunta.orden < seccion.preguntas.length
        )
            nuevoOrdenCalculado++;
        else return;

        // setLoadingPage(true); // O loader específico
        setOperationError("");
        try {
            await preguntaService.reordenar(
                parseInt(idEncuesta),
                idSeccionActual,
                idPregunta,
                { nuevo_orden: nuevoOrdenCalculado }
            );
            await cargarDetalleEncuestaYTipos(false);
        } catch (err) {
            setOperationError(
                "Error al reordenar pregunta: " +
                    (err.response?.data?.message || err.message)
            );
        } finally {
            // setLoadingPage(false);
        }
    };

    // --- MANEJO DE OPCIONES DE PREGUNTA ---
    const handleAbrirModalOpciones = (pregunta) => {
        setPreguntaParaOpciones(pregunta); // Guardar la pregunta cuyas opciones se van a editar
        setShowOpcionesModal(true);
        setOperationError(""); // Limpiar errores generales
    };
    const handleCerrarOpcionesModal = () => {
        setShowOpcionesModal(false);
        setPreguntaParaOpciones(null);
    };
    const handleOpcionesActualizadasEnEditor = async () => {
        // El editor de opciones ya guarda en la API, aquí solo recargamos la data de la encuesta.
        handleCerrarOpcionesModal(); // Cierra el modal de opciones primero
        await cargarDetalleEncuestaYTipos(false); // Recarga todo el diseñador
    };

    const handleAbrirModalMapeo = (pregunta) => {
        setPreguntaParaMapeo(pregunta);
        setShowMapeoModal(true);
        setOperationError("");
    };
    const handleCerrarMapeoModal = () => {
        setShowMapeoModal(false);
        setPreguntaParaMapeo(null);
    };
    const handleMapeoGuardado = async () => {
        // El editor ya guardó, solo necesitamos recargar los datos para ver el mapeo actualizado si es necesario
        await cargarDetalleEncuestaYTipos(false); // Recarga para que `pregunta.mapeo_externo` se actualice
    };

    if (loadingPage && !encuesta)
        return <div className="loading-fullscreen">Cargando diseñador...</div>;
    if (operationError && !encuesta)
        return (
            <div className="error-message full-width-error">
                {operationError} <Link to="/cliente/encuestas">Volver</Link>
            </div>
        );
    if (!encuesta)
        return (
            <div className="info-message">
                Encuesta no encontrada.{" "}
                <Link to="/cliente/encuestas">Volver</Link>
            </div>
        );

    return (
        <div className="gestion-page-container disenador-encuesta-container">
            <div className="page-header">
                <div>
                    <Link to="/cliente/encuestas" className="btn-accion-link">
                        <IconoVolver /> Volver a Mis Encuestas
                    </Link>
                    <h1>Diseñador: {encuesta.nombre}</h1>
                    {encuesta.descripcion && (
                        <p className="descripcion-encuesta-principal">
                            {encuesta.descripcion}
                        </p>
                    )}
                </div>
                <button
                    onClick={handleAbrirModalCrearSeccion}
                    className="btn-accion-principal"
                >
                    <IconoAgregar /> AGREGAR SECCIÓN
                </button>
            </div>

            {loadingPage && secciones.length > 0 && (
                <p className="loading-message small">Actualizando...</p>
            )}
            {operationError &&
                !showSeccionModal &&
                !showPreguntaModal &&
                !showOpcionesModal && (
                    <p className="error-message full-width-error">
                        {operationError}
                    </p>
                )}

            <div className="secciones-listado">
                {secciones.length === 0 && !loadingPage && (
                    <p className="info-message">
                        Esta encuesta aún no tiene secciones.
                    </p>
                )}
                {secciones.map((seccion) => (
                    <div key={seccion.id_seccion} className="seccion-card">
                        <div className="seccion-header">
                            <h3>
                                {seccion.orden}. {seccion.nombre}
                            </h3>
                            <div className="seccion-acciones">
                                <button
                                    onClick={() =>
                                        handleReordenarSeccion(
                                            seccion.id_seccion,
                                            "arriba"
                                        )
                                    }
                                    className="btn-accion-secundario"
                                    disabled={
                                        seccion.orden === 1 || loadingPage
                                    }
                                >
                                    <IconoArriba />
                                </button>
                                <button
                                    onClick={() =>
                                        handleReordenarSeccion(
                                            seccion.id_seccion,
                                            "abajo"
                                        )
                                    }
                                    className="btn-accion-secundario"
                                    disabled={
                                        seccion.orden === secciones.length ||
                                        loadingPage
                                    }
                                >
                                    <IconoAbajo />
                                </button>
                                <button
                                    onClick={() =>
                                        handleAbrirModalEditarSeccion(seccion)
                                    }
                                    className="btn-accion-secundario"
                                    disabled={loadingPage}
                                >
                                    <IconoEditar /> Editar
                                </button>
                                <button
                                    onClick={() =>
                                        handleEliminarSeccion(
                                            seccion.id_seccion
                                        )
                                    }
                                    className="btn-accion-secundario btn-peligro"
                                    disabled={loadingPage}
                                >
                                    <IconoEliminar />
                                </button>
                            </div>
                        </div>
                        {seccion.descripcion && (
                            <p className="descripcion-seccion-item">
                                {seccion.descripcion}
                            </p>
                        )}
                        <div className="preguntas-de-seccion-container">
                            <h4>Preguntas:</h4>
                            <PreguntasList
                                preguntas={seccion.preguntas || []}
                                idSeccion={seccion.id_seccion}
                                onEditarPregunta={
                                    handleAbrirModalEditarPregunta
                                }
                                onEliminarPregunta={handleEliminarPregunta}
                                onGestionarOpciones={handleAbrirModalOpciones} // Abre el modal de opciones
                                onReordenarPregunta={handleReordenarPregunta}
                                isLoading={loadingPage} // Podría ser un loading más específico para la lista
                                onAbrirModalMapeo={handleAbrirModalMapeo}
                            />
                            <button
                                onClick={() =>
                                    handleAbrirModalCrearPregunta(
                                        seccion.id_seccion
                                    )
                                }
                                className="btn-agregar-pregunta"
                                disabled={loadingPage}
                            >
                                <IconoAgregar /> Añadir Pregunta
                            </button>
                        </div>
                    </div>
                ))}
            </div>

            {/* Modal para Sección */}
            <Modal
                isOpen={showSeccionModal}
                onClose={handleCerrarSeccionModal}
                title={
                    seccionActual
                        ? `Editar Sección: ${seccionActual.nombre}`
                        : "Agregar Nueva Sección"
                }
            >
                <form onSubmit={handleGuardarSeccion}>
                    <SeccionForm
                        nombre={nombreSeccion}
                        setNombre={setNombreSeccion}
                        descripcion={descripcionSeccion}
                        setDescripcion={setDescripcionSeccion}
                        formErrorLocal={seccionFormErrorLocal}
                        apiError={seccionApiError}
                        isLoading={isSavingSeccion}
                    />
                    <div className="modal-form-actions">
                        <button
                            type="button"
                            onClick={handleCerrarSeccionModal}
                            className="modal-button modal-button-cancel"
                            disabled={isSavingSeccion}
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            className="modal-button modal-button-accept"
                            disabled={isSavingSeccion}
                        >
                            {isSavingSeccion
                                ? "Guardando..."
                                : seccionActual
                                ? "Actualizar"
                                : "Crear Sección"}
                        </button>
                    </div>
                </form>
            </Modal>

            {/* Modal para Pregunta */}
            <Modal
                isOpen={showPreguntaModal}
                onClose={handleCerrarPreguntaModal}
                title={
                    preguntaActual
                        ? `Editar Pregunta #${preguntaActual.orden}`
                        : "Agregar Nueva Pregunta"
                }
            >
                <form onSubmit={handleGuardarPregunta}>
                    <PreguntaForm
                        // Estado de campos
                        textoPregunta={textoPregunta}
                        setTextoPregunta={setTextoPregunta}
                        idTipoPregunta={idTipoPregunta}
                        setIdTipoPregunta={setIdTipoPregunta}
                        esObligatoria={esObligatoria}
                        setEsObligatoria={setEsObligatoria}
                        numeroMinimo={numeroMinimo}
                        setNumeroMinimo={setNumeroMinimo}
                        numeroMaximo={numeroMaximo}
                        setNumeroMaximo={setNumeroMaximo}
                        fechaMinima={fechaMinima}
                        setFechaMinima={setFechaMinima}
                        fechaMaxima={fechaMaxima}
                        setFechaMaxima={setFechaMaxima}
                        horaMinima={horaMinima}
                        setHoraMinima={setHoraMinima}
                        horaMaxima={horaMaxima}
                        setHoraMaxima={setHoraMaxima}
                        idPreguntaPadre={idPreguntaPadre}
                        setIdPreguntaPadre={setIdPreguntaPadre}
                        valorCondicionPadre={valorCondicionPadre}
                        setValorCondicionPadre={setValorCondicionPadre}
                        idOpcionCondicionPadre={idOpcionCondicionPadre}
                        setIdOpcionCondicionPadre={setIdOpcionCondicionPadre}
                        // Datos para selects
                        tiposPregunta={tiposPregunta}
                        preguntasCandidatasPadre={preguntasCandidatasPadre.filter(
                            (p) =>
                                // Excluir la pregunta actual (si se está editando) de las candidatas a padre
                                !(
                                    preguntaActual &&
                                    p.id_pregunta === preguntaActual.id_pregunta
                                ) &&
                                // Excluir preguntas de la misma sección con orden mayor o igual si se edita,
                                // o cualquier pregunta de la misma sección si se crea (para evitar dependencia circular simple)
                                // Una lógica más robusta en backend sería mejor.
                                preguntaActual
                                    ? p.id_seccion !==
                                          preguntaActual.id_seccion ||
                                      p.orden < preguntaActual.orden
                                    : true
                        )}
                        opcionesPreguntaPadre={
                            opcionesPreguntaPadreSeleccionada
                        }
                        // UI y errores
                        esEdicion={Boolean(preguntaActual)}
                        isLoading={isSavingPregunta}
                        formErrorLocal={preguntaFormErrorLocal}
                        apiError={preguntaApiError}
                        preguntaInicial={preguntaActual} // Para lógica interna del form si es necesario
                    />
                    <div className="modal-form-actions">
                        <button
                            type="button"
                            onClick={handleCerrarPreguntaModal}
                            className="modal-button modal-button-cancel"
                            disabled={isSavingPregunta}
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            className="modal-button modal-button-accept"
                            disabled={isSavingPregunta}
                        >
                            {isSavingPregunta
                                ? "Guardando..."
                                : preguntaActual
                                ? "Actualizar"
                                : "Crear Pregunta"}
                        </button>
                    </div>
                </form>
            </Modal>

            {/* Modal para Opciones de Pregunta */}
            {preguntaParaOpciones && (
                <Modal
                    isOpen={showOpcionesModal}
                    onClose={handleCerrarOpcionesModal}
                    title={`Gestionar Opciones de: "${preguntaParaOpciones.texto_pregunta.substring(
                        0,
                        30
                    )}..."`}
                    // El footer podría estar dentro de OpcionesPreguntaEditor o aquí si se necesita más control
                >
                    <OpcionesPreguntaEditor
                        key={preguntaParaOpciones.id_pregunta} // Para resetear el estado del editor si cambia la pregunta
                        pregunta={preguntaParaOpciones}
                        onOpcionesActualizadas={
                            handleOpcionesActualizadasEnEditor
                        }
                        // disabled={isSavingAlgoGlobal} // Si alguna operación global está en curso
                    />
                    <div className="modal-form-actions">
                        <button
                            type="button"
                            onClick={handleCerrarOpcionesModal}
                            className="modal-button modal-button-cancel"
                        >
                            Cerrar
                        </button>
                    </div>
                </Modal>
            )}

            {preguntaParaMapeo && (
                <Modal
                    isOpen={showMapeoModal}
                    onClose={handleCerrarMapeoModal}
                    title={`Mapeo Externo para: "${preguntaParaMapeo.texto_pregunta.substring(
                        0,
                        30
                    )}..."`}
                >
                    <PreguntaMapeoEditor
                        pregunta={preguntaParaMapeo}
                        onMapeoGuardado={handleMapeoGuardado} // Para recargar la data
                        onCerrar={handleCerrarMapeoModal}
                    />
                </Modal>
            )}
        </div>
    );
};

export default DiseñadorEncuestaPage;
