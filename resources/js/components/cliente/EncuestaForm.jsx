import React from "react";
import "./EncuestaForm.css";

const EncuestaForm = ({
    nombre,
    setNombre,
    descripcion,
    setDescripcion,
    esCuestionario,
    setEsCuestionario,
    fechaInicio,
    setFechaInicio,
    fechaFin,
    setFechaFin,
    formErrorLocal,
    apiError,
    isLoading,
}) => {
    return (
        <div className="encuesta-form">
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

            {/* Checkbox “Es cuestionario” */}
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
                    Es un cuestionario de paciente (con fechas)
                </label>
            </div>

            {esCuestionario && (
                <div className="form-grid-cuestionario">
                    <div className="form-group">
                        <label htmlFor="fechaInicioEncuestaForm">
                            Fecha de Inicio
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
                            Fecha de Fin
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
        </div>
    );
};

export default EncuestaForm;
