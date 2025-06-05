import React from "react";
import "./SeccionForm.css"; // Asegúrate que la ruta y el archivo existan

const SeccionForm = ({
    // Props para manejar los campos desde el padre (DiseñadorEncuestaPage)
    nombre,
    setNombre,
    descripcion,
    setDescripcion,
    formErrorLocal,
    apiError,
    isLoading,
}) => {
    return (
        <>
            {" "}
            {/* No hay <form> tag aquí */}
            {(formErrorLocal || apiError) && (
                <p className="error-message form-error">
                    {formErrorLocal || apiError}
                </p>
            )}
            <div className="form-group">
                <label htmlFor="nombreSeccionForm">Nombre de la Sección</label>
                <input
                    type="text"
                    id="nombreSeccionForm" // ID único
                    name="nombre"
                    value={nombre}
                    onChange={(e) => setNombre(e.target.value)}
                    required
                    disabled={isLoading}
                    maxLength="100"
                />
            </div>
            <div className="form-group">
                <label htmlFor="descripcionSeccionForm">
                    Descripción (Opcional)
                </label>
                <textarea
                    id="descripcionSeccionForm" // ID único
                    name="descripcion"
                    value={descripcion}
                    onChange={(e) => setDescripcion(e.target.value)}
                    rows="3"
                    disabled={isLoading}
                    maxLength="1000"
                />
            </div>
        </>
    );
};

export default SeccionForm;
