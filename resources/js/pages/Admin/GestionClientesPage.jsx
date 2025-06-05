import React, { useState, useEffect } from "react";
import axios from "axios";
import Modal from "../../components/ui/Modal";
import "./GestionClientesPage.css"; // Importar CSS

// Iconos (puedes usar SVGs inline, componentes de iconos, o <img>)
const PlusIcon = () => (
    <svg viewBox="0 0 20 20" fill="currentColor" className="icon-svg">
        <path
            fillRule="evenodd"
            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
            clipRule="evenodd"
        />
    </svg>
);
const PencilIcon = () => (
    <svg viewBox="0 0 20 20" fill="currentColor" className="icon-svg">
        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
    </svg>
);
const TrashIcon = () => (
    <svg viewBox="0 0 20 20" fill="currentColor" className="icon-svg">
        <path
            fillRule="evenodd"
            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
            clipRule="evenodd"
        />
    </svg>
);

function GestionClientesPage() {
    const [clientes, setClientes] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [fetchError, setFetchError] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingCliente, setEditingCliente] = useState(null); // Para editar

    //Estado del formulario
    const [razonSocial, setRazonSocial] = useState("");
    const [alias, setAlias] = useState("");
    const [limiteEncuestas, setLimiteEncuestas] = useState("");
    const [activo, setActivo] = useState(true);
    const [formMessage, setFormMessage] = useState(null);
    const [formError, setFormError] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {
        fetchClientes();
    }, []);

    const fetchClientes = async () => {
        setIsLoading(true);
        setFetchError(null);
        try {
            const response = await axios.get("/api/clientes");
            setClientes(response.data);
        } catch (error) {
            setFetchError(error.message);
        } finally {
            setIsLoading(false);
        }
    };

    const resetForm = () => {
        setRazonSocial("");
        setAlias("");
        setLimiteEncuestas("");
        setActivo(true);
        setFormMessage(null);
        setFormError(false);
        setEditingCliente(null);
    };

    const handleOpenModalForCreate = () => {
        resetForm();
        setIsModalOpen(true);
    };

    const handleOpenModalForEdit = (cliente) => {
        resetForm();
        setEditingCliente(cliente);
        setRazonSocial(cliente.razon_social);
        setAlias(cliente.alias || "");
        setLimiteEncuestas(cliente.limite_encuestas.toString());
        setActivo(cliente.activo);
        setIsModalOpen(true);
    };

    const handleFormSubmit = async (e) => {
        e.preventDefault();
        setIsSubmitting(true);
        setFormMessage(null);
        setFormError(false);

        const clienteData = {
            razon_social: razonSocial,
            alias,
            limite_encuestas: parseInt(limiteEncuestas, 10) || 0,
            activo,
        };

        try {
            let response;
            if (editingCliente) {
                // response = await axios.put(`/api/clientes/${editingCliente.id_cliente}`, clienteData);
                // setClientes(clientes.map(c => c.id_cliente === editingCliente.id_cliente ? response.data : c));
                setFormMessage("Cliente actualizado (simulado)!"); // Simulación
            } else {
                response = await axios.post("/api/clientes", clienteData);
                setClientes((prev) => [...prev, response.data]);
                setFormMessage("Cliente agregado con éxito!");
            }
            resetForm();
            setIsModalOpen(false);
        } catch (error) {
            setFormMessage(
                error.response?.data?.message ||
                    `Error al guardar el cliente ${error.message}`
            );
            setFormError(true);
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleDeleteCliente = async (clienteId) => {
        if (
            window.confirm(
                "¿Estás seguro de que quieres eliminar este cliente?"
            )
        ) {
            try {
                await axios.delete(`/api/clientes/${clienteId}`);
                setClientes(clientes.filter((c) => c.id_cliente !== clienteId));
                // Mostrar mensaje de éxito
            } catch (error) {
                // Mostrar mensaje de error
            }
        }
    };

    if (isLoading)
        return <p className="loading-message">Cargando clientes...</p>;
    if (fetchError)
        return (
            <p className="error-message">
                Error al cargar datos: {fetchError.message}
            </p>
        );

    return (
        <div className="gcp-container">
            {/* gcp: Gestion Clientes Page */}
            <header className="gcp-header">
                <h1 className="gcp-main-title">Clientes</h1>
                <div className="gcp-header-actions">
                    <input
                        type="search"
                        placeholder="Buscar cliente..."
                        className="gcp-search-input"
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
                <form onSubmit={handleFormSubmit}>
                    <input
                        type="text"
                        name="razon_social"
                        placeholder="Razón social"
                        value={razonSocial}
                        onChange={(e) => setRazonSocial(e.target.value)}
                        required
                    />
                    <input
                        type="text"
                        name="alias"
                        placeholder="Alias"
                        value={alias}
                        onChange={(e) => setAlias(e.target.value)}
                    />
                    <div className="form-checkbox-container">
                        <input
                            type="checkbox"
                            name="activo"
                            id="form-activo-cliente"
                            checked={activo}
                            onChange={(e) => setActivo(e.target.checked)}
                        />
                        <label htmlFor="form-activo-cliente">Activo</label>
                    </div>
                    <input
                        type="number"
                        name="limite_encuestas"
                        placeholder="Límite de encuestas"
                        value={limiteEncuestas}
                        onChange={(e) => setLimiteEncuestas(e.target.value)}
                        required
                    />
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
                {clientes.length === 0 && !isLoading && (
                    <p className="gcp-no-clientes">
                        No hay clientes para mostrar.
                    </p>
                )}
                {clientes.map((cliente) => (
                    <div
                        key={cliente.id_cliente || cliente.razon_social}
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
                                <strong>Nombre corto:</strong>{" "}
                                {cliente.nombre_corto || cliente.alias || "N/A"}
                            </p>
                            <p>
                                <strong>Fecha de registro:</strong>{" "}
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
                                <strong>Número de encuestas:</strong>{" "}
                                {cliente.limite_encuestas}
                            </p>
                        </div>
                        <div className="gcp-card-actions">
                            <button
                                onClick={() => handleOpenModalForEdit(cliente)}
                                className="gcp-action-button"
                            >
                                {" "}
                                {/* Reemplazar con handleOpenModalForEdit(cliente) */}
                                <PencilIcon />
                            </button>
                            <button
                                onClick={() =>
                                    handleDeleteCliente(cliente.id_cliente)
                                }
                                className="gcp-action-button"
                            >
                                {" "}
                                {/* Reemplazar con handleDeleteCliente(cliente.id_cliente) */}
                                <TrashIcon />
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default GestionClientesPage;
