import React from "react"; // No necesitas useState, useEffect si es puramente presentacional
import "./EncuestaForm.css";

const EncuestaForm = ({
    // --- Estado y setters del padre ---
    nombre,
    setNombre,
    descripcion,
    setDescripcion,
    esCuestionario,
    setEsCuestionario, // NUEVO
    fechaInicio,
    setFechaInicio, // NUEVO
    fechaFin,
    setFechaFin, // NUEVO
    // --- UI y errores ---
    formErrorLocal,
    apiError,
    isLoading,
}) => {
    return (
        <>
            {(formErrorLocal || apiError) && (
                <p className="error-message form-error">
                    {formErrorLocal || apiError}
                </p>
            )}

            <div className="form-group">
                <label htmlFor="nombreEncuestaForm">
                    Nombre de la Encuesta/Cuestionario
                </label>
                <input
                    type="text"
                    id="nombreEncuestaForm"
                    name="nombre"
                    value={nombre}
                    onChange={(e) => setNombre(e.target.value)}
                    required
                    disabled={isLoading}
                    maxLength="100"
                />
            </div>
            <div className="form-group">
                <label htmlFor="descripcionEncuestaForm">
                    Descripción (Opcional)
                </label>
                <textarea
                    id="descripcionEncuestaForm"
                    name="descripcion"
                    value={descripcion}
                    onChange={(e) => setDescripcion(e.target.value)}
                    rows="3"
                    disabled={isLoading}
                    maxLength="2000"
                />
            </div>

            {/* NUEVOS CAMPOS PARA CUESTIONARIO */}
            <div className="form-group checkbox-group">
                <input
                    type="checkbox"
                    id="esCuestionarioForm"
                    name="es_cuestionario"
                    checked={esCuestionario}
                    onChange={(e) => setEsCuestionario(e.target.checked)}
                    disabled={isLoading}
                />
                <label htmlFor="esCuestionarioForm" className="checkbox-label">
                    Es un cuestionario de paciente (con fechas de vigencia)
                </label>
            </div>

            {esCuestionario && (
                <div className="form-grid-cuestionario">
                    {" "}
                    {/* Un mini-grid para las fechas */}
                    <div className="form-group">
                        <label htmlFor="fechaInicioEncuestaForm">
                            Fecha de Inicio (Opcional)
                        </label>
                        <input
                            type="date"
                            id="fechaInicioEncuestaForm"
                            name="fecha_inicio"
                            value={fechaInicio}
                            onChange={(e) => setFechaInicio(e.target.value)}
                            disabled={isLoading}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="fechaFinEncuestaForm">
                            Fecha de Fin (Opcional)
                        </label>
                        <input
                            type="date"
                            id="fechaFinEncuestaForm"
                            name="fecha_fin"
                            value={fechaFin}
                            onChange={(e) => setFechaFin(e.target.value)}
                            disabled={isLoading}
                            min={fechaInicio || undefined}
                        />
                    </div>
                </div>
            )}
        </>
    );
};

export default EncuestaForm;
