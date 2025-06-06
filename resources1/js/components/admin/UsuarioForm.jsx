import React from "react";
import "./UsuarioForm.css"; // Asegúrate que este archivo exista y tenga estilos

const UsuarioForm = ({
    // --- Datos del formulario y sus setters (manejados por el padre) ---
    nombreCompleto,
    setNombreCompleto,
    email,
    setEmail,
    password,
    setPassword,
    passwordConfirmation,
    setPasswordConfirmation,
    idRol,
    setIdRol,
    idCliente,
    setIdCliente,
    activo,
    setActivo,
    // --- Datos para los selects ---
    roles,
    clientes,
    // --- Estado de la UI ---
    esEdicion,
    isLoading, // Para deshabilitar campos durante el submit
    // --- Errores ---
    formErrorLocal, // Errores de validación del formulario que el padre puede setear
    apiError, // Errores de la API que el padre puede setear
}) => {
    // Determinar si el select de cliente debe mostrarse basado en el rol seleccionado
    const rolSeleccionado = roles.find((r) => String(r.id_rol) === idRol);
    const mostrarSelectorCliente = rolSeleccionado?.nombre_rol === "Cliente";

    return (
        <>
            {" "}
            {/* No hay <form> tag aquí, se manejará en el modal/página padre */}
            {(formErrorLocal || apiError) && (
                <p className="error-message form-error">
                    {formErrorLocal || apiError}
                </p>
            )}
            <div className="form-group">
                <label htmlFor="nombreCompletoUsuarioForm">
                    Nombre Completo
                </label>
                <input
                    type="text"
                    id="nombreCompletoUsuarioForm"
                    name="nombre_completo"
                    value={nombreCompleto}
                    onChange={(e) => setNombreCompleto(e.target.value)}
                    required
                    disabled={isLoading}
                />
            </div>
            <div className="form-group">
                <label htmlFor="emailUsuarioForm">Correo Electrónico</label>
                <input
                    type="email"
                    id="emailUsuarioForm"
                    name="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                    disabled={isLoading}
                />
            </div>
            <div className="form-group">
                <label htmlFor="passwordUsuarioForm">
                    {esEdicion
                        ? "Nueva Contraseña (no cambiar si está vacío)"
                        : "Contraseña"}
                </label>
                <input
                    type="password"
                    id="passwordUsuarioForm"
                    name="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    autoComplete="new-password"
                    disabled={isLoading}
                />
            </div>
            <div className="form-group">
                <label htmlFor="passwordConfirmationUsuarioForm">
                    Confirmar Contraseña
                </label>
                <input
                    type="password"
                    id="passwordConfirmationUsuarioForm"
                    name="password_confirmation"
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                    autoComplete="new-password"
                    disabled={isLoading || !password}
                />
            </div>
            <div className="form-group">
                <label htmlFor="idRolUsuarioForm">Rol</label>
                <select
                    id="idRolUsuarioForm"
                    name="id_rol"
                    value={idRol}
                    onChange={(e) => setIdRol(e.target.value)} // El padre actualizará `idRol`
                    required
                    disabled={isLoading}
                >
                    <option value="">Seleccione un rol...</option>
                    {roles.map((rol) => (
                        <option key={rol.id_rol} value={String(rol.id_rol)}>
                            {rol.nombre_rol}
                        </option>
                    ))}
                </select>
            </div>
            {mostrarSelectorCliente && (
                <div className="form-group">
                    <label htmlFor="idClienteUsuarioForm">
                        Cliente Asociado
                    </label>
                    <select
                        id="idClienteUsuarioForm"
                        name="id_cliente"
                        value={idCliente}
                        onChange={(e) => setIdCliente(e.target.value)} // El padre actualizará `idCliente`
                        required={mostrarSelectorCliente}
                        disabled={isLoading}
                    >
                        <option value="">Seleccione un cliente...</option>
                        {clientes.map((cliente) => (
                            <option
                                key={cliente.id_cliente}
                                value={String(cliente.id_cliente)}
                            >
                                {cliente.alias || cliente.razon_social}{" "}
                                {/* Muestra alias o razón social */}
                            </option>
                        ))}
                    </select>
                </div>
            )}
            <div className="form-group checkbox-group">
                <input
                    type="checkbox"
                    id="activoUsuarioForm"
                    name="activo"
                    checked={activo}
                    onChange={(e) => setActivo(e.target.checked)} // El padre actualizará `activo`
                    disabled={isLoading}
                />
                <label htmlFor="activoUsuarioForm" className="checkbox-label">
                    Activo
                </label>
            </div>
        </>
    );
};

export default UsuarioForm;
