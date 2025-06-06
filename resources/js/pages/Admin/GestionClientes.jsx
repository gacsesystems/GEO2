import React, { useState, useEffect } from "react";
import { axios } from "../../bootstrap";
import Modal from "../../components/ui/Modal";
import "./GestionClientes.css";
import { PlusIcon, PencilIcon, TrashIcon } from "../../components/ui/Icons";

export default function GestionClientes() {
    // Estados para listado
    const [clientes, setClientes] = useState([]);
    const [clientesFiltrados, setClientesFiltrados] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [fetchError, setFetchError] = useState("");

    // Estados para modal (crear/editar)
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingCliente, setEditingCliente] = useState(null);

    // Estados del formulario dentro del modal
    const [razonSocial, setRazonSocial] = useState("");
    const [alias, setAlias] = useState("");
    const [limiteEncuestas, setLimiteEncuestas] = useState("");
    const [activo, setActivo] = useState(true);
    const [formMessage, setFormMessage] = useState("");
    const [formError, setFormError] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Al montar, traer clientes
    useEffect(() => {
        fetchClientes();
    }, []);

    const fetchClientes = async () => {
        setIsLoading(true);
        setFetchError("");
        try {
            const response = await axios.get("/api/clientes");
            const clientesData = response.data.data || response.data;
            const clientesArray = Array.isArray(clientesData)
                ? clientesData
                : [];
            setClientes(clientesArray);
            setClientesFiltrados(clientesArray); // Inicializar los clientes filtrados
        } catch (error) {
            setFetchError(error.response?.data?.message || error.message);
            setClientes([]);
            setClientesFiltrados([]);
        } finally {
            setIsLoading(false);
        }
    };

    // Función para filtrar clientes
    const handleSearch = (searchTerm) => {
        if (!searchTerm.trim()) {
            setClientesFiltrados(clientes); // Si el término está vacío, mostrar todos
            return;
        }

        const term = searchTerm.toLowerCase().trim();
        const filtrados = clientes.filter(
            (cliente) =>
                cliente.razon_social.toLowerCase().includes(term) ||
                (cliente.alias && cliente.alias.toLowerCase().includes(term))
        );
        setClientesFiltrados(filtrados);
    };

    // Reinicia formulario a estado inicial
    const resetForm = () => {
        setRazonSocial("");
        setAlias("");
        setLimiteEncuestas("");
        setActivo(true);
        setFormMessage("");
        setFormError(false);
        setEditingCliente(null);
    };

    // Abrir modal para crear
    const handleOpenModalForCreate = () => {
        resetForm();
        setIsModalOpen(true);
    };

    // Abrir modal para editar
    const handleOpenModalForEdit = (cliente) => {
        resetForm();
        setEditingCliente(cliente);
        setRazonSocial(cliente.razon_social);
        setAlias(cliente.alias || "");
        setLimiteEncuestas(String(cliente.limite_encuestas));
        setActivo(cliente.activo);
        setIsModalOpen(true);
    };

    // Enviar formulario de crear/editar
    const handleFormSubmit = async (e) => {
        e.preventDefault();
        setIsSubmitting(true);
        setFormMessage("");
        setFormError(false);

        const clienteData = {
            razon_social: razonSocial.trim(),
            alias: alias.trim(),
            limite_encuestas: parseInt(limiteEncuestas, 10) || 0,
            activo,
        };

        try {
            let response;
            if (editingCliente) {
                response = await axios.put(
                    `/api/clientes/${editingCliente.id_cliente}`,
                    clienteData
                );

                const clienteActualizado = {
                    ...(response.data.data || response.data),
                    id_cliente: editingCliente.id_cliente,
                };

                // Actualizar ambos estados
                setClientes((prevClientes) =>
                    prevClientes.map((cliente) =>
                        cliente.id_cliente === editingCliente.id_cliente
                            ? clienteActualizado
                            : cliente
                    )
                );
                setClientesFiltrados((prevClientes) =>
                    prevClientes.map((cliente) =>
                        cliente.id_cliente === editingCliente.id_cliente
                            ? clienteActualizado
                            : cliente
                    )
                );

                setFormMessage("Cliente actualizado con éxito.");
            } else {
                response = await axios.post("/api/clientes", clienteData);
                const nuevoCliente = response.data.data || response.data;

                // Actualizar ambos estados
                setClientes((prevClientes) => [...prevClientes, nuevoCliente]);
                setClientesFiltrados((prevClientes) => [
                    ...prevClientes,
                    nuevoCliente,
                ]);

                setFormMessage("Cliente agregado con éxito.");
            }
            resetForm();
            setIsModalOpen(false);
        } catch (error) {
            setFormMessage(
                error.response?.data?.message ||
                    `Error al guardar el cliente: ${error.message}`
            );
            setFormError(true);
        } finally {
            setIsSubmitting(false);
        }
    };

    // Eliminar cliente
    const handleDeleteCliente = async (clienteId) => {
        if (
            window.confirm(
                "¿Estás seguro de que quieres eliminar este cliente?"
            )
        ) {
            try {
                await axios.delete(`/api/clientes/${clienteId}`);
                // Actualizar ambos estados
                setClientes((prev) =>
                    prev.filter((c) => c.id_cliente !== clienteId)
                );
                setClientesFiltrados((prev) =>
                    prev.filter((c) => c.id_cliente !== clienteId)
                );
            } catch (error) {
                alert(
                    error.response?.data?.message ||
                        "Error al eliminar el cliente."
                );
            }
        }
    };

    // Renderizado condicional: loading, error o contenido
    if (isLoading) {
        return <p className="loading-message">Cargando clientes...</p>;
    }
    if (fetchError) {
        return (
            <p className="error-message">Error al cargar datos: {fetchError}</p>
        );
    }

    return (
        <div className="gcp-container">
            <header className="gcp-header">
                <h1 className="gcp-main-title">Clientes</h1>
                <div className="gcp-header-actions">
                    <input
                        type="search"
                        placeholder="Buscar cliente..."
                        className="gcp-search-input"
                        onChange={(e) => handleSearch(e.target.value)}
                    />
                    <button
                        onClick={handleOpenModalForCreate}
                        className="gcp-button gcp-button-primary"
                    >
                        <PlusIcon />
                        <span>Agregar</span>
                    </button>
                </div>
            </header>
            <Modal
                isOpen={isModalOpen}
                onClose={() => {
                    setIsModalOpen(false);
                    resetForm();
                }}
                title={editingCliente ? "Editar Cliente" : "Agregar Cliente"}
            >
                <form onSubmit={handleFormSubmit} className="modal-form">
                    <label className="modal-label">
                        Razón Social
                        <input
                            type="text"
                            name="razon_social"
                            placeholder="Razón social"
                            value={razonSocial}
                            onChange={(e) => setRazonSocial(e.target.value)}
                            required
                            className="modal-input"
                        />
                    </label>
                    <label className="modal-label">
                        Alias
                        <input
                            type="text"
                            name="alias"
                            placeholder="Alias"
                            value={alias}
                            onChange={(e) => setAlias(e.target.value)}
                            className="modal-input"
                        />
                    </label>
                    <div className="form-checkbox-container">
                        <input
                            type="checkbox"
                            id="form-activo-cliente"
                            checked={activo}
                            onChange={(e) => setActivo(e.target.checked)}
                        />
                        <label htmlFor="form-activo-cliente">Activo</label>
                    </div>
                    <label className="modal-label">
                        Límite de encuestas
                        <input
                            type="number"
                            name="limite_encuestas"
                            placeholder="Límite de encuestas"
                            value={limiteEncuestas}
                            onChange={(e) => setLimiteEncuestas(e.target.value)}
                            required
                            className="modal-input"
                        />
                    </label>
                    {formMessage && (
                        <p
                            className={`modal-form-message ${
                                formError ? "error" : "success"
                            }`}
                        >
                            {formMessage}
                        </p>
                    )}
                    <div className="modal-form-actions">
                        <button
                            type="button"
                            onClick={() => {
                                setIsModalOpen(false);
                                resetForm();
                            }}
                            className="modal-button modal-button-cancel"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={isSubmitting}
                            className="modal-button modal-button-accept"
                        >
                            {isSubmitting
                                ? "Guardando..."
                                : editingCliente
                                ? "Actualizar"
                                : "Aceptar"}
                        </button>
                    </div>
                </form>
            </Modal>
            <div className="gcp-clientes-grid">
                {clientesFiltrados.length === 0 && !isLoading && (
                    <p className="gcp-no-clientes">
                        {clientes.length === 0
                            ? "No hay clientes para mostrar."
                            : "No se encontraron clientes que coincidan con la búsqueda."}
                    </p>
                )}
                {clientesFiltrados.map((cliente) => (
                    <div
                        key={`cliente-${cliente.id_cliente}`}
                        className={`gcp-cliente-card ${
                            !cliente.activo ? "inactive" : ""
                        }`}
                    >
                        <div className="gcp-card-header">
                            <h3 className="gcp-card-title">
                                {cliente.razon_social}
                            </h3>
                            {cliente.ruta_logo && (
                                <img
                                    src={cliente.ruta_logo}
                                    alt=""
                                    className="gcp-card-logo"
                                />
                            )}
                        </div>
                        <div className="gcp-card-body">
                            <p>
                                <strong>Alias:</strong> {cliente.alias || "N/A"}
                            </p>
                            <p>
                                <strong>Registro:</strong>{" "}
                                {new Date(
                                    cliente.fecha_registro || cliente.created_at
                                ).toLocaleDateString()}
                            </p>
                            <p>
                                <strong>Status:</strong>{" "}
                                <span
                                    className={
                                        cliente.activo
                                            ? "status-active"
                                            : "status-inactive"
                                    }
                                >
                                    {cliente.activo ? "Activo" : "Inactivo"}
                                </span>
                            </p>
                            <p>
                                <strong>Límite encuestas:</strong>{" "}
                                {cliente.limite_encuestas}
                            </p>
                        </div>
                        <div className="gcp-card-actions">
                            <button
                                onClick={() => handleOpenModalForEdit(cliente)}
                                className="gcp-action-button"
                                title="Editar cliente"
                            >
                                <PencilIcon />
                            </button>
                            <button
                                onClick={() =>
                                    handleDeleteCliente(cliente.id_cliente)
                                }
                                className="gcp-action-button"
                                title="Eliminar cliente"
                            >
                                <TrashIcon />
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
