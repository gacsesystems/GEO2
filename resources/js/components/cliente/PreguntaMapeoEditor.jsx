import React, { useState, useEffect, useCallback } from "react";
import { entidadExternaService } from "../../services/entidadExternaService";
import { preguntaService } from "../../services/preguntaService"; // Para guardar el mapeo
import "./PreguntaMapeoEditor.css"; // Crearemos este CSS

const PreguntaMapeoEditor = ({
    pregunta, // La pregunta para la que se configura el mapeo
    onMapeoGuardado, // Callback opcional
    onCerrar, // Callback para cerrar el editor/modal
}) => {
    const [entidadesExternas, setEntidadesExternas] = useState([]);
    const [camposExternos, setCamposExternos] = useState([]);
    const [selectedEntidadId, setSelectedEntidadId] = useState("");
    const [selectedCampoId, setSelectedCampoId] = useState("");
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState("");
    const [currentMapeo, setCurrentMapeo] = useState(null); // {entidad_externa_id, campo_externo_id}

    // Cargar mapeo existente de la pregunta y todas las entidades externas
    useEffect(() => {
        setIsLoading(true);
        Promise.all([
            entidadExternaService.getAll(),
            // Cargar el mapeo actual si existe (podría venir con el objeto pregunta o cargarlo aquí)
            // Si pregunta.mapeo_externo ya existe:
            // Promise.resolve({ data: pregunta.mapeo_externo || null })
            // Si no, hacer una llamada API si es necesario (asumimos que no por ahora y viene en `pregunta`)
        ])
            .then(([entidadesRes]) => {
                setEntidadesExternas(
                    entidadesRes.data.data || entidadesRes.data
                );
                // Establecer valores iniciales si la pregunta ya tiene un mapeo
                if (pregunta.mapeo_externo) {
                    // Asumiendo que `pregunta` tiene `mapeo_externo: {entidad_externa_id, campo_externo_id}`
                    setCurrentMapeo(pregunta.mapeo_externo);
                    setSelectedEntidadId(
                        String(pregunta.mapeo_externo.entidad_externa_id)
                    );
                    //setSelectedCampoId(String(pregunta.mapeo_externo.campo_externo_id)); // Se cargará cuando se seleccione la entidad
                }
            })
            .catch((err) => setError("Error cargando datos para el mapeo."))
            .finally(() => setIsLoading(false));
    }, [pregunta]);

    // Cargar campos cuando se selecciona una entidad
    useEffect(() => {
        if (selectedEntidadId) {
            setIsLoading(true);
            entidadExternaService
                .getCamposByEntidad(selectedEntidadId)
                .then((res) => {
                    setCamposExternos(res.data.data || res.data);
                    // Si hay un mapeo actual y la entidad coincide, preseleccionar el campo
                    if (
                        currentMapeo &&
                        String(currentMapeo.entidad_externa_id) ===
                            selectedEntidadId
                    ) {
                        setSelectedCampoId(
                            String(currentMapeo.campo_externo_id)
                        );
                    } else {
                        setSelectedCampoId(""); // Resetear si la entidad cambió
                    }
                })
                .catch((err) => {
                    setError("Error cargando campos de la entidad.");
                    setCamposExternos([]);
                    setSelectedCampoId("");
                })
                .finally(() => setIsLoading(false));
        } else {
            setCamposExternos([]);
            setSelectedCampoId("");
        }
    }, [selectedEntidadId, currentMapeo]);

    const handleGuardarMapeo = async () => {
        if (!selectedEntidadId || !selectedCampoId) {
            setError("Debe seleccionar una entidad y un campo externo.");
            return;
        }
        setIsLoading(true);
        setError("");
        try {
            await preguntaService.guardarMapeo(pregunta.id_pregunta, {
                entidad_externa_id: parseInt(selectedEntidadId),
                campo_externo_id: parseInt(selectedCampoId),
            });
            if (onMapeoGuardado) onMapeoGuardado();
            if (onCerrar) onCerrar();
        } catch (err) {
            setError(
                err.response?.data?.message || "Error al guardar el mapeo."
            );
        } finally {
            setIsLoading(false);
        }
    };

    const handleEliminarMapeo = async () => {
        if (window.confirm("¿Está seguro de que desea eliminar este mapeo?")) {
            setIsLoading(true);
            setError("");
            try {
                await preguntaService.eliminarMapeo(pregunta.id_pregunta);
                setSelectedEntidadId(""); // Resetea los selects
                setSelectedCampoId("");
                setCurrentMapeo(null);
                if (onMapeoGuardado) onMapeoGuardado(); // Para que el padre recargue
                // No cerrar automáticamente, permitir al usuario ver que se eliminó
                setError("Mapeo eliminado. Puede asignar uno nuevo o cerrar."); // Mensaje informativo
            } catch (err) {
                setError(
                    err.response?.data?.message || "Error al eliminar el mapeo."
                );
            } finally {
                setIsLoading(false);
            }
        }
    };

    if (!pregunta) return null;

    return (
        <div className="pregunta-mapeo-editor data-form">
            {" "}
            {/* Reutiliza .data-form para estilos base */}
            <h4>Mapeo a Sistema Externo</h4>
            <p className="mapeo-info">
                Mapear la respuesta de la pregunta "
                <strong>{pregunta.texto_pregunta}</strong>" a un campo de una
                entidad externa.
            </p>
            {error && <p className="error-message form-error">{error}</p>}
            <div className="form-group">
                <label htmlFor="selectEntidadExterna">Entidad Externa</label>
                <select
                    id="selectEntidadExterna"
                    value={selectedEntidadId}
                    onChange={(e) => {
                        setSelectedEntidadId(e.target.value);
                        setSelectedCampoId(""); // Resetear campo al cambiar entidad
                    }}
                    disabled={isLoading}
                >
                    <option value="">Seleccione una entidad...</option>
                    {entidadesExternas.map((ent) => (
                        <option key={ent.id} value={String(ent.id)}>
                            {ent.descripcion || ent.clave}
                        </option>
                    ))}
                </select>
            </div>
            {selectedEntidadId && (
                <div className="form-group">
                    <label htmlFor="selectCampoExterno">Campo Externo</label>
                    <select
                        id="selectCampoExterno"
                        value={selectedCampoId}
                        onChange={(e) => setSelectedCampoId(e.target.value)}
                        disabled={isLoading || camposExternos.length === 0}
                    >
                        <option value="">Seleccione un campo...</option>
                        {camposExternos.map((campo) => (
                            <option key={campo.id} value={String(campo.id)}>
                                {campo.nombre} (
                                {campo.descripcion || campo.tipo})
                            </option>
                        ))}
                    </select>
                </div>
            )}
            <div className="modal-form-actions">
                {" "}
                {/* Reutiliza estilos de Modal.css */}
                {currentMapeo && ( // Mostrar botón de eliminar solo si hay un mapeo existente
                    <button
                        type="button"
                        onClick={handleEliminarMapeo}
                        className="modal-button modal-button-danger"
                        disabled={isLoading}
                    >
                        Eliminar Mapeo Actual
                    </button>
                )}
                <button
                    type="button"
                    onClick={onCerrar}
                    className="modal-button modal-button-cancel"
                    disabled={isLoading}
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    onClick={handleGuardarMapeo}
                    className="modal-button modal-button-accept"
                    disabled={isLoading || !selectedCampoId}
                >
                    {isLoading ? "Guardando..." : "Guardar Mapeo"}
                </button>
            </div>
        </div>
    );
};

export default PreguntaMapeoEditor;
