import React, { useState, useEffect, useCallback } from "react";
import { axios } from "../../bootstrap";
import Modal from "../../components/ui/Modal";
import { PlusIcon, PencilIcon, TrashIcon } from "../../components/ui/Icons";
import "./GestionUsuarios.css";
import CardTable from "../../components/layout/CardTable";

export default function GestionUsuarios() {
    // --- Estados generales ---
    const [usuarios, setUsuarios] = useState([]);
    const [roles, setRoles] = useState([]);
    const [clientes, setClientes] = useState([]);

    const [isLoadingTable, setIsLoadingTable] = useState(true);
    const [fetchError, setFetchError] = useState("");

    // Modal y formulario
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingUsuario, setEditingUsuario] = useState(null); // null = crear, objeto = editar
    const [isFormSubmitting, setIsFormSubmitting] = useState(false);
    const [localFormError, setLocalFormError] = useState("");
    const [apiFormError, setApiFormError] = useState("");

    // Campos del formulario
    const [nombreCompleto, setNombreCompleto] = useState("");
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [passwordConfirmation, setPasswordConfirmation] = useState("");
    const [idRol, setIdRol] = useState("");
    const [idCliente, setIdCliente] = useState("");
    const [activo, setActivo] = useState(true);

    //  --- Servicio interno para Usuarios ---
    const usuarioApiService = {
        getAll: () => axios.get("/api/usuarios"),
        create: (data) => axios.post("/api/usuarios", data),
        update: (id, data) => axios.put(`/api/usuarios/${id}`, data),
        delete: (id) => axios.delete(`/api/usuarios/${id}`),
    };

    // --- Carga inicial ---
    const fetchUsuarios = useCallback(async () => {
        setIsLoadingTable(true);
        setFetchError("");
        try {
            const response = await usuarioApiService.getAll();
            const lista = Array.isArray(response.data.data)
                ? response.data.data
                : [];
            setUsuarios(lista);
        } catch (error) {
            console.error("Error al cargar usuarios:", error);
            console.error("Detalles del error:", error.response?.data);
            setFetchError(
                error.response?.data?.message || "Error cargando usuarios."
            );
        } finally {
            setIsLoadingTable(false);
        }
    }, []);

    useEffect(() => {
        fetchUsuarios();

        //cargar roles
        axios
            .get("/api/roles")
            .then((res) => {
                const listaRoles = Array.isArray(res.data.data)
                    ? res.data.data
                    : [];
                setRoles(listaRoles);
            })
            .catch((error) => {
                console.error(
                    "Error al cargar roles:",
                    error.response || error
                );
                setRoles([]);
            });

        //cargar clientes
        axios
            .get("/api/clientes")
            .then((res) => {
                const listaClientes = Array.isArray(res.data.data)
                    ? res.data.data
                    : [];
                setClientes(listaClientes);
            })
            .catch((error) => {
                console.error(
                    "Error al cargar clientes:",
                    error.response || error
                );
                setClientes([]);
            });
    }, [fetchUsuarios]);

    // --- Helpers de formulario ---
    const resetFormFields = () => {
        setNombreCompleto("");
        setEmail("");
        setPassword("");
        setPasswordConfirmation("");
        setIdRol("");
        setIdCliente("");
        setActivo(true);
        setLocalFormError("");
        setApiFormError("");
    };

    const handleOpenModalForCreate = () => {
        setEditingUsuario(null);
        setIsModalOpen(true);
        resetFormFields();
    };

    const handleOpenModalForEdit = (usuario) => {
        setEditingUsuario(usuario);
        setNombreCompleto(usuario.nombre_completo || "");
        setEmail(usuario.email || "");
        setPassword("");
        setPasswordConfirmation("");
        setIdRol(usuario.id_rol ? String(usuario.id_rol) : "");
        setIdCliente(usuario.id_cliente ? String(usuario.id_cliente) : "");
        setActivo(usuario.activo !== undefined ? usuario.activo : true);
        setLocalFormError("");
        setApiFormError("");
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setEditingUsuario(null);
    };

    const validateForm = () => {
        setLocalFormError("");
        if (!nombreCompleto.trim() || !email.trim() || !idRol) {
            setLocalFormError("Nombre, Email y Rol son obligatorios.");
            return false;
        }
        if (!editingUsuario && !password) {
            setLocalFormError(
                "La contraseña es obligatoria para nuevos usuarios."
            );
            return false;
        }
        if (password && password !== passwordConfirmation) {
            setLocalFormError("Las contraseñas no coinciden.");
            return false;
        }
        const rolObj = roles.find((r) => String(r.id_rol) === idRol);
        if (rolObj?.nombre_rol === "Cliente" && !idCliente) {
            setLocalFormError(
                'Debe seleccionar un cliente si el rol es "Cliente".'
            );
            return false;
        }
        return true;
    };

    const handleFormSubmit = async (e) => {
        e.preventDefault();
        setApiFormError("");
        if (!validateForm()) return;

        setIsFormSubmitting(true);
        const rolObj = roles.find((r) => String(r.id_rol) === idRol);
        const usuarioData = {
            nombre_completo: nombreCompleto.trim(),
            email: email.trim(),
            id_rol: parseInt(idRol, 10),
            id_cliente:
                rolObj?.nombre_rol === "Cliente" && idCliente
                    ? parseInt(idCliente, 10)
                    : null,
            activo,
        };
        if (password) {
            usuarioData.password = password;
            usuarioData.password_confirmation = passwordConfirmation;
        }
        try {
            if (editingUsuario) {
                await usuarioApiService.update(editingUsuario.id, usuarioData);
            } else {
                await usuarioApiService.create(usuarioData);
            }
            await fetchUsuarios();
            handleCloseModal();
        } catch (error) {
            console.error(
                "Error guardando usuario:",
                error.response?.data || error
            );
            const mensaje =
                error.response?.data?.message || "Error al guardar usuario.";
            if (error.response?.data?.errors) {
                const validationErrors = Object.values(
                    error.response?.data?.errors
                )
                    .flat()
                    .join("\n");
                setApiFormError(`${mensaje}} ${validationErrors}`);
            } else {
                setApiFormError(mensaje);
            }
        } finally {
            setIsFormSubmitting(false);
        }
    };

    const handleDeleteUsuario = async (id) => {
        if (window.confirm("¿Estás seguro de querer eliminar este usuario?")) {
            try {
                await usuarioApiService.delete(id);
                await fetchUsuarios();
            } catch (error) {
                console.error(
                    "Error al eliminar usuario:",
                    error.response?.data || error
                );
                alert(
                    error.response?.data?.message ||
                        "Error al eliminar el usuario."
                );
            }
        }
    };

    const columns = React.useMemo(
        () => [
            {
                Header: "ID",
                accessor: "id",
                Cell: ({ value }) => value || "N/A",
            },
            {
                Header: "Nombre",
                accessor: "nombre_completo",
                Cell: ({ value }) => value || "N/A",
            },
            {
                Header: "Email",
                accessor: "email",
                Cell: ({ value }) => value || "N/A",
            },
            {
                Header: "Rol",
                accessor: (row) => row.rol || "N/A",
            },
            {
                Header: "Cliente",
                accessor: (row) =>
                    row.cliente?.alias || row.cliente?.razon_social || "N/A",
            },
            {
                Header: "Activo",
                accessor: "activo",
                Cell: ({ value }) => (
                    <span
                        className={`status-badge ${
                            value ? "active" : "inactive"
                        }`}
                    >
                        {value ? "Sí" : "No"}
                    </span>
                ),
            },
            {
                Header: "Acciones",
                accessor: "actions",
                Cell: ({ row }) => (
                    <div className="card-table-actions">
                        <button
                            onClick={() => handleOpenModalForEdit(row)}
                            className="action-btn edit"
                            title="Editar"
                        >
                            <PencilIcon />
                        </button>
                        <button
                            onClick={() => handleDeleteUsuario(row.id)}
                            className="action-btn delete"
                            title="Eliminar"
                        >
                            <TrashIcon />
                        </button>
                    </div>
                ),
            },
        ],
        [handleOpenModalForEdit, handleDeleteUsuario]
    );

    // Modificar el renderizado de la tabla
    const renderTable = () => {
        if (isLoadingTable) {
            return (
                <div className="loading-fullscreen">Cargando usuarios...</div>
            );
        }

        if (fetchError) {
            return (
                <p className="error-message loading-fullscreen">{fetchError}</p>
            );
        }

        if (!usuarios || usuarios.length === 0) {
            return (
                <p className="no-data-message">No hay usuarios para mostrar</p>
            );
        }

        return (
            <CardTable
                title="Lista de Usuarios"
                data={usuarios}
                columns={columns}
                color="light"
            />
        );
    };

    // --- Renderizado ---
    return (
        <div className="gup-container">
            <header className="gup-header">
                <h1 className="gup-main-title">Gestión de Usuarios</h1>
                <button
                    onClick={handleOpenModalForCreate}
                    className="gup-button gup-button-primary"
                >
                    <PlusIcon />
                    <span>Agregar Usuario</span>
                </button>
            </header>
            {renderTable()}
            <Modal
                isOpen={isModalOpen}
                onClose={handleCloseModal}
                title={
                    editingUsuario ? "Editar Usuario" : "Agregar Nuevo Usuario"
                }
            >
                <form onSubmit={handleFormSubmit} className="modal-form">
                    {(localFormError || apiFormError) && (
                        <p className="error-message form-error">
                            {localFormError || apiFormError}
                        </p>
                    )}
                    {/* Nombre Completo */}
                    <div className="form-group">
                        <label htmlFor="nombreCompleto">Nombre Completo</label>
                        <input
                            type="text"
                            id="nombreCompleto"
                            value={nombreCompleto}
                            onChange={(e) => setNombreCompleto(e.target.value)}
                            required
                            disabled={isFormSubmitting}
                        />
                    </div>
                    {/* Email */}
                    <div className="form-group">
                        <label htmlFor="emailUsuario">Correo Electrónico</label>
                        <input
                            type="email"
                            id="emailUsuario"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            required
                            disabled={isFormSubmitting}
                        />
                    </div>
                    {/* Password */}
                    <div className="form-group">
                        <label htmlFor="passwordUsuario">
                            {editingUsuario
                                ? "Nueva Contraseña (dejar vacío si no cambia)"
                                : "Contraseña"}
                        </label>
                        <input
                            type="password"
                            id="passwordUsuario"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            autoComplete="new-password"
                            disabled={isFormSubmitting}
                        />
                    </div>
                    {/* Confirmar Password */}
                    <div className="form-group">
                        <label htmlFor="passwordConfirm">
                            Confirmar Contraseña
                        </label>
                        <input
                            type="password"
                            id="passwordConfirm"
                            value={passwordConfirmation}
                            onChange={(e) =>
                                setPasswordConfirmation(e.target.value)
                            }
                            autoComplete="new-password"
                            disabled={isFormSubmitting || !password}
                        />
                    </div>
                    {/* Rol */}
                    <div className="form-group">
                        <label htmlFor="rolUsuario">Rol</label>
                        <select
                            id="rolUsuario"
                            value={idRol}
                            onChange={(e) => setIdRol(e.target.value)}
                            required
                            disabled={isFormSubmitting}
                        >
                            <option value="">Seleccione un rol...</option>
                            {roles.map((rol) => (
                                <option
                                    key={rol.id_rol}
                                    value={String(rol.id_rol)}
                                >
                                    {rol.nombre_rol}
                                </option>
                            ))}
                        </select>
                    </div>
                    {/* Cliente (solo si el rol es "Cliente") */}
                    {roles.find((r) => String(r.id_rol) === idRol)
                        ?.nombre_rol === "Cliente" && (
                        <div className="form-group">
                            <label htmlFor="clienteUsuario">
                                Cliente Asociado
                            </label>
                            <select
                                id="clienteUsuario"
                                value={idCliente}
                                onChange={(e) => setIdCliente(e.target.value)}
                                required
                                disabled={isFormSubmitting}
                            >
                                <option value="">
                                    Seleccione un cliente...
                                </option>
                                {clientes.map((c) => (
                                    <option
                                        key={c.id_cliente}
                                        value={String(c.id_cliente)}
                                    >
                                        {c.alias || c.razon_social}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}
                    {/* Checkbox Activo */}
                    <div className="form-group checkbox-group">
                        <input
                            type="checkbox"
                            id="activoUsuarioForm"
                            checked={activo}
                            onChange={(e) => setActivo(e.target.checked)}
                            disabled={isFormSubmitting}
                        />
                        <label htmlFor="activoUsuarioForm">Activo</label>
                    </div>

                    <div className="modal-form-actions">
                        <button
                            type="button"
                            onClick={handleCloseModal}
                            className="modal-button modal-button-cancel"
                            disabled={isFormSubmitting}
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            className="modal-button modal-button-accept"
                            disabled={isFormSubmitting}
                        >
                            {isFormSubmitting
                                ? "Guardando..."
                                : editingUsuario
                                ? "Actualizar Usuario"
                                : "Crear Usuario"}
                        </button>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
