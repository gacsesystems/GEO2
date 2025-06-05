import React, { useState, useEffect, useCallback } from "react";
import { useNavigate } from "react-router-dom";
import { encuestaService } from "../../services/encuestaService";
import Modal from "../../components/common/Modal"; // Asumo que Modal está en common
import EncuestaForm from "../../components/cliente/EncuestaForm";
import EncuestasTable from "../../components/cliente/EncuestasTable";
import { PlusIcon } from "../../components/ui/Icons"; // O el que uses para "Agregar"
import "./GestionEncuestasPage.css"; // Asegúrate que exista y tenga los estilos

const GestionEncuestasPage = () => {
    const [encuestas, setEncuestas] = useState([]);
    const [isLoadingSubmit, setIsLoadingSubmit] = useState(false); // Para el submit del form
    const [isLoadingTable, setIsLoadingTable] = useState(true); // Para la carga de la tabla
    const [apiError, setApiError] = useState(""); // Errores de API para el form
    const [localFormError, setLocalFormError] = useState(""); // Errores locales del form

    const [showModal, setShowModal] = useState(false);
    const [encuestaActual, setEncuestaActual] = useState(null); // Para edición
    const [modalTitulo, setModalTitulo] = useState("");
    const [searchTerm, setSearchTerm] = useState("");

    // Estado para los campos de EncuestaForm
    const [nombreEncuesta, setNombreEncuesta] = useState("");
    const [descripcionEncuesta, setDescripcionEncuesta] = useState("");

    const navigate = useNavigate();

    const cargarEncuestas = useCallback(async () => {
        setIsLoadingTable(true); // Mostrar loading para la tabla
        setApiError(""); // Limpiar errores previos de la tabla
        try {
            const response = await encuestaService.getMisEncuestas();
            setEncuestas(response.data.data || response.data); // Ajusta según la estructura de tu API
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
        setLocalFormError("");
        setApiError(""); // También limpia el error de API al resetear/abrir form
    };

    const handleAbrirModalParaCrear = () => {
        setEncuestaActual(null);
        resetFormFields();
        setModalTitulo("Crear Nueva Encuesta");
        setShowModal(true);
    };

    const handleAbrirModalParaEditar = (encuesta) => {
        setEncuestaActual(encuesta);
        setNombreEncuesta(encuesta.nombre || "");
        setDescripcionEncuesta(encuesta.descripcion || "");
        setLocalFormError("");
        setApiError("");
        setModalTitulo(`Editar Encuesta: ${encuesta.nombre}`);
        setShowModal(true);
    };

    const handleCerrarModal = () => {
        setShowModal(false);
        setEncuestaActual(null);
        // resetFormFields(); // Opcional: resetear al cerrar
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
        const encuestaData = {
            nombre: nombreEncuesta,
            descripcion: descripcionEncuesta,
        };

        try {
            if (encuestaActual && encuestaActual.id_encuesta) {
                await encuestaService.update(
                    encuestaActual.id_encuesta,
                    encuestaData
                );
            } else {
                await encuestaService.create(encuestaData);
            }
            await cargarEncuestas(); // Recargar tabla
            handleCerrarModal();
        } catch (err) {
            console.error("Error al guardar encuesta:", err);
            const errorMsg =
                err.response?.data?.message ||
                (err.response?.data?.errors
                    ? Object.values(err.response.data.errors).flat().join(" ")
                    : "Error al guardar la encuesta.");
            setApiError(errorMsg);
        } finally {
            setIsLoadingSubmit(false);
        }
    };

    const handleEliminarEncuesta = async (idEncuesta) => {
        // ... (tu lógica de eliminar se mantiene igual) ...
        if (
            window.confirm(
                "¿Está seguro de que desea eliminar esta encuesta? Se eliminarán también todas sus secciones, preguntas y respuestas asociadas."
            )
        ) {
            // setIsLoadingTable(true); // Podrías tener un loader específico para la acción
            setApiError("");
            try {
                await encuestaService.remove(idEncuesta);
                // Actualizar estado local para reflejar eliminación sin recargar todo
                setEncuestas((prevEncuestas) =>
                    prevEncuestas.filter((e) => e.id_encuesta !== idEncuesta)
                );
            } catch (err) {
                console.error("Error al eliminar encuesta:", err);
                setApiError(
                    err.response?.data?.message ||
                        "Error al eliminar la encuesta."
                );
            } finally {
                // setIsLoadingTable(false);
            }
        }
    };

    const handleDisenarEncuesta = (idEncuesta) => {
        navigate(`/cliente/encuestas/${idEncuesta}/disenar`);
    };

    const handleObtenerUrl = async (idEncuesta) => {
        // ... (tu lógica de obtener URL se mantiene igual) ...
        // setIsLoadingSubmit(true); // Usar un loader específico si se desea
        try {
            const response = await encuestaService.generarUrl(idEncuesta);
            const urlPublica = `${window.location.origin}/survey/code/${response.data.codigo_url}`;
            navigator.clipboard.writeText(urlPublica).then(
                () => alert(`Enlace copiado al portapapeles:\n${urlPublica}`),
                (err) => {
                    console.error("Error al copiar enlace: ", err);
                    alert(`No se pudo copiar. Enlace:\n${urlPublica}`);
                }
            );
        } catch (err) {
            alert("Error al generar el enlace para la encuesta.");
        } finally {
            // setIsLoadingSubmit(false);
        }
    };

    const handleVerResultados = (idEncuesta) => {
        navigate(`/cliente/encuestas/${idEncuesta}/reportes`);
    };

    const encuestasFiltradas = encuestas.filter(
        (encuesta) =>
            encuesta.nombre &&
            encuesta.nombre.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="gestion-page-container gestion-encuestas-container">
            {" "}
            {/* Reutiliza clases de admin si son globales */}
            <div className="page-header">
                {" "}
                {/* Reutiliza clases de admin */}
                <h1>Mis Encuestas</h1>
                <button
                    onClick={handleAbrirModalParaCrear}
                    className="btn-accion-principal btn-agregar"
                >
                    <PlusIcon /> {/* Icono real */}
                    <span>CREAR ENCUESTA</span>
                </button>
            </div>
            <div className="filtros-y-busqueda">
                <input
                    type="text"
                    placeholder="Buscar por nombre de encuesta..."
                    className="search-input" // Estilo global para search inputs
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
            </div>
            {isLoadingTable && (
                <p className="loading-message">Cargando encuestas...</p>
            )}
            {!isLoadingTable && apiError && encuestas.length === 0 && (
                <p className="error-message full-width-error">{apiError}</p>
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
                <p className="info-message">
                    No has creado ninguna encuesta todavía. ¡Crea la primera!
                </p>
            )}
            <Modal
                isOpen={showModal} // Cambiado de 'show' a 'isOpen' para coincidir con tu Modal
                onClose={handleCerrarModal}
                title={modalTitulo}
            >
                <form onSubmit={handleGuardarEncuesta} id="gestionEncuestaForm">
                    {" "}
                    {/* Form tag aquí */}
                    <EncuestaForm
                        // Props para el estado de los campos, manejados por esta página
                        nombre={nombreEncuesta}
                        setNombre={setNombreEncuesta}
                        descripcion={descripcionEncuesta}
                        setDescripcion={setDescripcionEncuesta}
                        // encuestaInicial NO es necesario si manejamos los campos aquí
                        // onGuardar NO es necesario si el submit está aquí
                        formErrorLocal={localFormError} // Mostrar errores de validación local
                        apiError={apiError} // Mostrar errores de API
                        // setApiError={setApiError} // EncuestaForm ya no necesita setear el error de API
                        isLoading={isLoadingSubmit} // Para deshabilitar campos del form
                    />
                    {/* Los botones ahora están fuera de EncuestaForm, dentro del Modal */}
                    <div className="modal-form-actions">
                        <button
                            type="button"
                            onClick={handleCerrarModal}
                            className="modal-button modal-button-cancel"
                            disabled={isLoadingSubmit}
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit" // Este botón activa el onSubmit del <form> de arriba
                            className="modal-button modal-button-accept"
                            disabled={isLoadingSubmit}
                        >
                            {isLoadingSubmit
                                ? "Guardando..."
                                : encuestaActual
                                ? "Actualizar"
                                : "Crear Encuesta"}
                        </button>
                    </div>
                </form>
            </Modal>
        </div>
    );
};

export default GestionEncuestasPage;
