import React, { useState, useEffect, startTransition } from "react";
import { createRoot } from "react-dom/client";
// Asumiendo que en React 19, estos hooks vendrán de 'react-dom' o 'react' directamente
// Por ahora, la documentación experimental los ubica en 'react-dom/client' o 'react-dom'
// para APIs relacionadas con el DOM como form actions.
// Ajusta la importación según la versión final de React 19.
import { useFormState, useFormStatus } from "react-dom"; // Importante: de 'react-dom'
import axios from "axios";
import "../css/app.css";

// Estado inicial para useFormState
const initialFormSubmitState = {
    message: null,
    error: false,
    newCliente: null,
    submitted: false, // Para saber si el formulario fue enviado
};
// --- La Acción del Formulario ---
// Esta función se ejecutará cuando el formulario sea enviado.
// Recibe el estado previo y los datos del formulario.
async function addClienteAction(prevState, formData) {
    const rawFormData = {
        razon_social: formData.get("razon_social"),
        alias: formData.get("alias"),
        limite_encuestas: parseInt(formData.get("limite_encuestas"), 10) || 0, // Asegurar que sea número
        activo: formData.get("activo") === "on", // 'on' si está marcado, null si no
    };

    try {
        const response = await axios.post("/api/clientes", rawFormData);
        // Devolvemos un nuevo estado para useFormState
        return {
            message: "Cliente agregado con éxito!",
            error: false,
            newCliente: response.data,
            submitted: true,
        };
    } catch (error) {
        console.error("Error al agregar cliente:", error);
        return {
            message: `Error al agregar cliente: ${
                error.response?.data?.message || error.message
            }`,
            error: true,
            newCliente: null,
            submitted: true,
        };
    }
}

// Componente para el botón de envío, para usar useFormStatus
function SubmitButton() {
    const { pending } = useFormStatus(); // Hook para obtener el estado de envío del form
    return (
        <button type="submit" disabled={pending} aria-disabled={pending}>
            {pending ? "Guardando..." : "Guardar"}
        </button>
    );
}

function App() {
    const [clientes, setClientes] = useState([]);
    const [initialLoading, setInitialLoading] = useState(true);
    const [fetchError, setFetchError] = useState(null);

    // Estado para los campos del formulario (siguen siendo controlados)
    const [razonSocial, setRazonSocial] = useState("");
    const [alias, setAlias] = useState("");
    const [limiteEncuestas, setLimiteEncuestas] = useState("");
    const [activo, setActivo] = useState(false);

    // Hook useFormState para manejar el resultado de la acción del formulario
    const [formSubmitState, formAction] = useFormState(
        addClienteAction,
        initialFormSubmitState
    );

    // Carga inicial de clientes
    useEffect(() => {
        const fetchClientes = async () => {
            setInitialLoading(true);
            setFetchError(null);
            try {
                const response = await axios.get("/api/clientes");
                setClientes(response.data);
            } catch (error) {
                console.error("Error al cargar clientes:", error);
                setFetchError(error);
            } finally {
                setInitialLoading(false);
            }
        };
        fetchClientes();
    }, []);

    // Efecto para manejar el resultado de la acción del formulario
    useEffect(() => {
        if (formSubmitState.submitted) {
            if (!formSubmitState.error && formSubmitState.newCliente) {
                // Actualización optimista podría ir aquí con useOptimistic
                // Por ahora, actualizamos después de la confirmación
                setClientes((prevClientes) => [
                    ...prevClientes,
                    formSubmitState.newCliente,
                ]);
                // Resetear campos del formulario
                setRazonSocial("");
                setAlias("");
                setLimiteEncuestas("");
                setActivo(false);
                // Opcional: podrías querer resetear el formSubmitState.message después de un tiempo
                // o en la siguiente interacción. Por simplicidad, se queda hasta el próximo envío.
            }
            // El mensaje de error/éxito se muestra directamente desde formSubmitState.message
        }
    }, [formSubmitState]); // Depende del objeto de estado completo

    if (initialLoading) {
        return <p>Cargando clientes...</p>;
    }

    if (fetchError) {
        return <p>Error al cargar datos: {fetchError.message}</p>;
    }

    return (
        <>
            <h1 className="titulo-principal">Clientes</h1>

            {/* El prop 'action' ahora usa la función 'formAction' de useFormState */}
            <form action={formAction} className="formulario-cliente">
                <input
                    type="text"
                    name="razon_social" // 'name' es crucial para FormData
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
                <div className="checkbox-container">
                    <input
                        type="checkbox"
                        name="activo"
                        id="activo"
                        checked={activo}
                        onChange={(e) => setActivo(e.target.checked)}
                    />
                    <label htmlFor="activo">Activo</label>
                </div>
                <input
                    type="number"
                    name="limite_encuestas"
                    placeholder="Límite de encuestas"
                    value={limiteEncuestas}
                    onChange={(e) => setLimiteEncuestas(e.target.value)}
                    required
                />
                <SubmitButton />{" "}
                {/* Componente de botón que usa useFormStatus */}
                {formSubmitState.message && (
                    <p
                        style={{
                            color: formSubmitState.error ? "red" : "green",
                        }}
                    >
                        {formSubmitState.message}
                    </p>
                )}
            </form>

            <h2 className="subtitulo">Lista de Clientes</h2>
            {clientes.length === 0 && !initialLoading && (
                <p>No hay clientes para mostrar.</p>
            )}
            <ul className="lista-clientes">
                {clientes.map((cliente) => (
                    <li key={cliente.id_cliente || cliente.razon_social}>
                        {" "}
                        {/* Fallback para key si id_cliente no está en el nuevo */}
                        <div className="cliente-info">
                            <h3>{cliente.razon_social}</h3>
                            <p>Alias: {cliente.alias}</p>
                            <p>
                                Estado: {cliente.activo ? "Activo" : "Inactivo"}
                            </p>
                            <p>
                                Límite de encuestas: {cliente.limite_encuestas}
                            </p>
                            {cliente.ruta_logo && (
                                <img
                                    src={cliente.ruta_logo}
                                    alt={`Logo de ${cliente.razon_social}`}
                                    style={{
                                        maxWidth: "100px",
                                        maxHeight: "50px",
                                    }} // Estilo inline para ejemplo
                                />
                            )}
                        </div>
                    </li>
                ))}
            </ul>
        </>
    );
}

const rootElement = document.getElementById("app");
rootElement
    ? createRoot(rootElement).render(<App />)
    : console.error("Elemento #app no encontrado en el DOM.");
