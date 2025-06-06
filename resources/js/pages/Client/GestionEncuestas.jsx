import React, { useState, useEffect, useCallback } from "react";
import { useNavigate } from "react-router-dom";
import * as encuestaService from "../../services/encuestaService";
import Modal from "../../components/ui/Modal"; // Asumo que Modal está en common
import EncuestaForm from "../../components/cliente/EncuestaForm";
import EncuestasTable from "../../components/cliente/EncuestasTable";
import { PlusIcon } from "../../components/ui/Icons"; // O el que uses para "Agregar"
import "./GestionEncuestas.css"; // Asegúrate que exista y tenga los estilos

export default function GestionEncuestas() {
    // -- Estados principales --
    const [encuestas, setEncuestas] = useState([]); // Lista de encuestas
    const [isLoadingTable, setIsLoadingTable] = useState(true); // Loading para la tabla
    const [isLoadingSubmit, setIsLoadingSubmit] = useState(false); // Loading para el submit
    const [apiError, setApiError] = useState(""); // Error de API
    const [localFormError, setLocalFormError] = useState(""); // Error local del form

    // -- Modal y formulario --
    const [showModal, setShowModal] = useState(false); // Estado para mostrar/ocultar el modal
    const [encuestaActual, setEncuestaActual] = useState(null); // Encuesta actual para edición
    const [modalTitulo, setModalTitulo] = useState(""); // Título del modal
    const [searchTerm, setSearchTerm] = useState(""); // Término de búsqueda

    // -- Campos del formulario --
    const [nombreEncuesta, setNombreEncuesta] = useState(""); // Nombre de la encuesta
    const [descripcionEncuesta, setDescripcionEncuesta] = useState(""); // Descripción de la encuesta
    const [esCuestionario, setEsCuestionario] = useState(false); // Si es cuestionario o encuesta
    const [fechaInicio, setFechaInicio] = useState(""); // Fecha de inicio
    const [fechaFin, setFechaFin] = useState(""); // Fecha de fin

    const navigate = useNavigate();

    const cargarEncuestas = useCallback(async () => {
        setIsLoadingTable(true); // Mostrar loading para la tabla
        setApiError(""); // Limpiar errores previos de la tabla
        try {
            const response = await encuestaService.fetchEncuestas();
            setEncuestas(response || []); // Ajustado para usar la respuesta directa
        } catch (err) {
            console.error("Error al cargar encuestas:", err);
            setApiError(
                err.response?.data?.message ||
                    "No se pudo cargar la lista de encuestas."
            );
        } finally {
            setIsLoadingTable(false);
        }
    }, []);

    useEffect(() => {
        cargarEncuestas();
    }, [cargarEncuestas]);

    const resetFormFields = () => {
        setNombreEncuesta("");
        setDescripcionEncuesta("");
        setEsCuestionario(false);
        setFechaInicio("");
        setFechaFin("");
        setLocalFormError("");
        setApiError(""); // También limpia el error de API al resetear/abrir form
    };

    const handleAbrirModalParaCrear = () => {
        setEncuestaActual(null);
        resetFormFields();
        setModalTitulo("Crear Nueva Encuesta");
        setShowModal(true);
    };

    const handleAbrirModalParaEditar = (enc) => {
        setEncuestaActual(enc);
        setNombreEncuesta(enc.nombre || "");
        setDescripcionEncuesta(enc.descripcion || "");
        setEsCuestionario(enc.es_cuestionario || false);
        setFechaInicio(enc.fecha_inicio?.substring(0, 10) || ""); // Formato YYYY-MM-DD para input date
        setFechaFin(enc.fecha_fin?.substring(0, 10) || "");
        setLocalFormError("");
        setApiError("");
        setModalTitulo(`Editar Encuesta: ${enc.nombre}`);
        setShowModal(true);
    };

    const handleCerrarModal = () => {
        setShowModal(false);
        setEncuestaActual(null);
    };

    const validateEncuestaForm = () => {
        setLocalFormError("");
        if (!nombreEncuesta.trim()) {
            setLocalFormError("El nombre de la encuesta es obligatorio.");
            return false;
        }
        return true;
    };

    const handleGuardarEncuesta = async (e) => {
        // Ahora este es el onSubmit del form en el Modal
        e.preventDefault();
        if (!validateEncuestaForm()) return;

        setIsLoadingSubmit(true);
        setApiError("");
        const payload = {
            nombre: nombreEncuesta.trim(),
            descripcion: descripcionEncuesta.trim() || null,
            es_cuestionario: esCuestionario,
            fecha_inicio: esCuestionario && fechaInicio ? fechaInicio : null,
            fecha_fin: esCuestionario && fechaFin ? fechaFin : null,
        };

        try {
            if (encuestaActual?.id_encuesta) {
                // actualizar
                await encuestaService.actualizarEncuesta(
                    encuestaActual.id_encuesta,
                    payload
                );
            } else {
                // crear
                await encuestaService.crearEncuesta(payload);
            }
            // recargar tabla y cerrar modal
            await cargarEncuestas();
            handleCerrarModal();
        } catch (err) {
            console.error("Error al guardar encuesta:", err);
            const mensaje =
                err.response?.data?.message ||
                (err.response?.data?.errors
                    ? Object.values(err.response.data.errors).flat().join(" ")
                    : "Error al guardar la encuesta.");
            setApiError(mensaje);
        } finally {
            setIsLoadingSubmit(false);
        }
    };

    // 9) Eliminar encuesta
    const handleEliminarEncuesta = async (idEncuesta) => {
        if (
            !window.confirm(
                "¿Está seguro de que desea eliminar esta encuesta? Se borrarán también sus secciones, preguntas y respuestas."
            )
        )
            return;
        setApiError("");
        try {
            await encuestaService.eliminarEncuesta(idEncuesta);
            setEncuestas((prev) =>
                prev.filter((e) => e.id_encuesta !== idEncuesta)
            );
        } catch (err) {
            console.error("Error al eliminar encuesta:", err);
            setApiError(
                err.response?.data?.message || "Error al eliminar la encuesta."
            );
        }
    };

    const handleDisenarEncuesta = (idEncuesta) => {
        navigate(`/cliente/encuestas/${idEncuesta}/disenar`);
    };

    // 11) Obtener URL pública y copiar al portapapeles
    const handleObtenerUrl = async (idEncuesta) => {
        try {
            const detalle = await encuestaService.fetchEncuestaDetalle(
                idEncuesta
            );
            const urlPublica = `${window.location.origin}/encuesta/codigo/${detalle.codigo_url}`;
            await navigator.clipboard.writeText(urlPublica);
            alert(`Enlace copiado:\n${urlPublica}`);
        } catch {
            alert("Error al generar el enlace de la encuesta.");
        }
    };

    const handleVerResultados = (idEncuesta) => {
        navigate(`/cliente/encuestas/${idEncuesta}/reportes`);
    };

    // 13) Filtrado local por nombre
    const encuestasFiltradas = encuestas.filter((enc) =>
        enc.nombre?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="gep-container">
            {/* Cabecera */}
            <header className="gep-header">
                <h1>Mis Encuestas</h1>
                {/* Buscador */}
                <div className="gep-search-container">
                    <input
                        type="text"
                        placeholder="Buscar por nombre…"
                        className="gep-search-input"
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
                <button
                    className="gep-btn-primary"
                    onClick={handleAbrirModalParaCrear}
                    disabled={isLoadingSubmit}
                >
                    <PlusIcon className="gep-icon" />
                    <span>Crear Encuesta</span>
                </button>
            </header>

            {/* Tabla o mensajería de estado */}
            {isLoadingTable && (
                <p className="gep-loading">Cargando encuestas…</p>
            )}

            {!isLoadingTable && apiError && encuestas.length === 0 && (
                <p className="gep-error-full">{apiError}</p>
            )}

            {!isLoadingTable && encuestas.length > 0 && (
                <EncuestasTable
                    encuestas={encuestasFiltradas}
                    onEditar={handleAbrirModalParaEditar}
                    onEliminar={handleEliminarEncuesta}
                    onDisenar={handleDisenarEncuesta}
                    onObtenerUrl={handleObtenerUrl}
                    onVerResultados={handleVerResultados}
                />
            )}

            {!isLoadingTable && encuestas.length === 0 && !apiError && (
                <p className="gep-info">
                    Aún no has creado ninguna encuesta. ¡Crea la primera!
                </p>
            )}

            {/* Modal para crear/editar */}
            <Modal
                isOpen={showModal}
                onClose={handleCerrarModal}
                title={modalTitulo}
            >
                <form onSubmit={handleGuardarEncuesta} className="gep-form">
                    <EncuestaForm
                        nombre={nombreEncuesta}
                        setNombre={setNombreEncuesta}
                        descripcion={descripcionEncuesta}
                        setDescripcion={setDescripcionEncuesta}
                        esCuestionario={esCuestionario}
                        setEsCuestionario={setEsCuestionario}
                        fechaInicio={fechaInicio}
                        setFechaInicio={setFechaInicio}
                        fechaFin={fechaFin}
                        setFechaFin={setFechaFin}
                        formErrorLocal={localFormError}
                        apiError={apiError}
                        isLoading={isLoadingSubmit}
                    />
                    <div className="gep-modal-actions">
                        <button
                            type="button"
                            onClick={handleCerrarModal}
                            className="modal-button modal-button-cancel"
                            disabled={isLoadingSubmit}
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            className="modal-button modal-button-accept"
                            disabled={isLoadingSubmit}
                        >
                            {isLoadingSubmit
                                ? "Guardando…"
                                : encuestaActual
                                ? "Actualizar"
                                : "Crear Encuesta"}
                        </button>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
