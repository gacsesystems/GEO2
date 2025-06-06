import React, { useState, useEffect } from "react";
import "./EncuestaForm.css"; // Asegúrate que la ruta y el archivo existan

const EncuestaForm = ({
    encuestaInicial,
    // onGuardar se pasa desde GestionEncuestasPage y maneja la llamada API
    // apiError y setApiError también vienen del padre
    // isLoading también viene del padre
    // --- Props para manejar los campos desde el padre ---
    nombre,
    setNombre,
    descripcion,
    setDescripcion,
    formErrorLocal, // Errores de validación local que el padre puede setear/mostrar
    apiError, // Errores de API que el padre puede setear/mostrar
    isLoading, // Para deshabilitar campos durante el submit
}) => {
    // El estado local de los campos ahora es manejado por GestionEncuestasPage
    // Este componente se vuelve más presentacional.

    // El useEffect para setear valores iniciales también se maneja en GestionEncuestasPage
    // cuando se abre el modal para editar.

    // El handleSubmitInterno y el tag <form> se manejarán en el modal/página padre.

    return (
        <>
            {" "}
            {/* No hay <form> tag aquí, se usa en el Modal */}
            {(formErrorLocal || apiError) && (
                <p className="error-message form-error">
                    {formErrorLocal || apiError}
                </p>
            )}
            <div className="form-group">
                <label htmlFor="nombreEncuestaForm">
                    Nombre de la Encuesta
                </label>
                <input
                    type="text"
                    id="nombreEncuestaForm" // ID único para el input
                    name="nombre" // Para FormData si se usara (y para accesibilidad)
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
                    id="descripcionEncuestaForm" // ID único para el textarea
                    name="descripcion"
                    value={descripcion}
                    onChange={(e) => setDescripcion(e.target.value)}
                    rows="4"
                    disabled={isLoading}
                    maxLength="2000"
                />
            </div>
            {/* Los botones de submit/cancelar estarán en el Modal */}
        </>
    );
};

export default EncuestaForm;
