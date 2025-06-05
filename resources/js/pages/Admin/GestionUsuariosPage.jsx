import React, { useState, useEffect, useCallback } from "react";
// import axios from 'axios'; // No es necesario si usas window.axios
import CardTable from "../../components/admin/CardTable";
import Modal from "../../components/ui/Modal";
import UsuarioForm from "../../components/admin/UsuarioForm";
import { PlusIcon, PencilIcon, TrashIcon } from "../../components/ui/Icons";
import { rolService } from "../../services/rolService"; // Para cargar roles para el form
import { clienteService } from "../../services/clienteService"; // Para cargar clientes para el form
import "./GestionClientesPage.css"; // Puedes crear un CSS específico o usar clases de gcp-container

// Servicio para usuarios (usando window.axios)
const usuarioApiService = {
    getAll: () => window.axios.get("/api/usuarios"),
    create: (data) => window.axios.post("/api/usuarios", data),
    update: (id, data) => window.axios.put(`/api/usuarios/${id}`, data),
    delete: (id) => window.axios.delete(`/api/usuarios/${id}`),
};

const GestionUsuariosPage = () => {
    const [usuarios, setUsuarios] = useState([]);
    const [roles, setRoles] = useState([]);
    const [clientes, setClientes] = useState([]);

    const [isLoadingTable, setIsLoadingTable] = useState(true);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingUsuario, setEditingUsuario] = useState(null);
    const [isFormSubmitting, setIsFormSubmitting] = useState(false);
    const [apiFormError, setApiFormError] = useState(""); // Errores de API para el formulario
    const [localFormError, setLocalFormError] = useState(""); // Errores de validación local para el form

    // --- Estado para los campos del UsuarioForm ---
    const [nombreCompleto, setNombreCompleto] = useState("");
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [passwordConfirmation, setPasswordConfirmation] = useState("");
    const [idRol, setIdRol] = useState("");
    const [idCliente, setIdCliente] = useState("");
    const [activo, setActivo] = useState(true);
    // --- Fin Estado para los campos del UsuarioForm ---

    const fetchUsuarios = useCallback(async () => {
        setIsLoadingTable(true);
        try {
            const response = await usuarioApiService.getAll();
            setUsuarios(response.data.data || response.data);
        } catch (error) {
            console.error("Error fetching usuarios:", error);
            // Manejar error de carga de tabla
        } finally {
            setIsLoadingTable(false);
        }
    }, []);

    // Cargar datos para los selects del formulario una sola vez
    useEffect(() => {
        fetchUsuarios();

        rolService
            .getAll()
            .then((response) => setRoles(response.data.data || response.data))
            .catch((err) =>
                console.error("Error cargando roles para el formulario", err)
            );

        clienteService
            .getAll() // Podrías querer parámetros para obtener todos sin paginar
            .then((response) =>
                setClientes(response.data.data || response.data)
            )
            .catch((err) =>
                console.error("Error cargando clientes para el formulario", err)
            );
    }, [fetchUsuarios]);

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
        resetFormFields();
        setIsModalOpen(true);
    };

    const handleOpenModalForEdit = (usuario) => {
        setEditingUsuario(usuario);
        setNombreCompleto(usuario.nombre_completo || "");
        setEmail(usuario.email || "");
        setPassword(""); // No precargar
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
        // resetFormFields(); // Opcional: resetear campos al cerrar o solo al abrir para crear/editar
    };

    const validateForm = () => {
        setLocalFormError(""); // Limpiar error local previo
        if (!nombreCompleto.trim() || !email.trim() || !idRol) {
            setLocalFormError("Nombre, Email y Rol son obligatorios.");
            return false;
        }
        if (!editingUsuario && !password) {
            // Password obligatoria solo al crear
            setLocalFormError(
                "La contraseña es obligatoria para nuevos usuarios."
            );
            return false;
        }
        if (password && password !== passwordConfirmation) {
            setLocalFormError("Las contraseñas no coinciden.");
            return false;
        }
        const rolSeleccionadoObj = roles.find(
            (r) => String(r.id_rol) === idRol
        );
        if (rolSeleccionadoObj?.nombre_rol === "Cliente" && !idCliente) {
            setLocalFormError(
                'Debe seleccionar un cliente si el rol es "Cliente".'
            );
            return false;
        }
        return true;
    };

    const handleFormSubmit = async (e) => {
        e.preventDefault();
        setApiFormError(""); // Limpiar error de API previo
        if (!validateForm()) return;

        setIsFormSubmitting(true);
        const rolSeleccionadoObj = roles.find(
            (r) => String(r.id_rol) === idRol
        );
        const usuarioData = {
            nombre_completo: nombreCompleto,
            email: email,
            id_rol: parseInt(idRol, 10),
            id_cliente:
                rolSeleccionadoObj?.nombre_rol === "Cliente" && idCliente
                    ? parseInt(idCliente, 10)
                    : null,
            activo: activo,
        };

        if (password) {
            // Solo enviar password si se ha ingresado
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
            const message =
                error.response?.data?.message ||
                "Ocurrió un error al guardar el usuario.";
            // Si hay errores de validación del backend, también podrían venir aquí
            if (error.response?.data?.errors) {
                // Podrías formatear los errores de validación para mostrarlos
                const validationErrors = Object.values(
                    error.response.data.errors
                )
                    .flat()
                    .join(" ");
                setApiFormError(`${message} ${validationErrors}`);
            } else {
                setApiFormError(message);
            }
        } finally {
            setIsFormSubmitting(false);
        }
    };

    const handleDeleteUsuario = async (idUsuario) => {
        if (
            window.confirm(
                "¿Estás seguro de que quieres eliminar este usuario?"
            )
        ) {
            try {
                await usuarioApiService.delete(idUsuario);
                await fetchUsuarios();
            } catch (error) {
                console.error(
                    "Error eliminando usuario:",
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
            // ... (tus definiciones de columnas, igual que antes) ...
            { Header: "ID", accessor: "id" },
            { Header: "Nombre", accessor: "nombre_completo" },
            { Header: "Email", accessor: "email" },
            { Header: "Rol", accessor: (row) => row.role?.nombre_rol || "N/A" },
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
                            onClick={() => handleOpenModalForEdit(row.original)}
                            className="action-btn edit"
                            title="Editar"
                        >
                            <PencilIcon />
                        </button>
                        <button
                            onClick={() => handleDeleteUsuario(row.original.id)}
                            className="action-btn delete"
                            title="Eliminar"
                        >
                            <TrashIcon />
                        </button>
                    </div>
                ),
            },
        ],
        []
    ); // Añadir dependencias si handleOpenModalForEdit o handleDeleteUsuario cambian

    if (isLoadingTable) {
        return <div className="loading-fullscreen">Cargando usuarios...</div>;
    }

    return (
        <div className="gcp-container">
            {" "}
            {/* Reutilizando clase para consistencia */}
            <header className="gcp-header">
                <h1 className="gcp-main-title">Gestión de Usuarios</h1>
                <button
                    onClick={handleOpenModalForCreate}
                    className="gcp-button gcp-button-primary"
                >
                    <PlusIcon />
                    <span>Agregar Usuario</span>
                </button>
            </header>
            <CardTable
                title="Lista de Usuarios"
                data={usuarios}
                columns={columns}
                color="light"
            />
            <Modal
                isOpen={isModalOpen}
                onClose={handleCloseModal}
                title={
                    editingUsuario ? "Editar Usuario" : "Agregar Nuevo Usuario"
                }
            >
                <form onSubmit={handleFormSubmit}>
                    {" "}
                    {/* El form tag está aquí */}
                    <UsuarioForm
                        nombreCompleto={nombreCompleto}
                        setNombreCompleto={setNombreCompleto}
                        email={email}
                        setEmail={setEmail}
                        password={password}
                        setPassword={setPassword}
                        passwordConfirmation={passwordConfirmation}
                        setPasswordConfirmation={setPasswordConfirmation}
                        idRol={idRol}
                        setIdRol={setIdRol}
                        idCliente={idCliente}
                        setIdCliente={setIdCliente}
                        activo={activo}
                        setActivo={setActivo}
                        roles={roles}
                        clientes={clientes}
                        esEdicion={Boolean(editingUsuario)}
                        isLoading={isFormSubmitting}
                        formErrorLocal={localFormError} // Mostrar errores de validación local
                        apiError={apiFormError} // Mostrar errores de API
                    />
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
};

export default GestionUsuariosPage;
