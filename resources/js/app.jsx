import React, { useState, useEffect } from "react";
import axios from "axios";
import { createRoot } from "react-dom/client";
import "../css/app.css";

function App() {
    const [clientes, setClientes] = useState([]);
    const [error, setError] = useState(null);
    const [razonSocial, setRazonSocial] = useState("");
    const [alias, setAlias] = useState("");
    const [limiteEncuestas, setLimiteEncuestas] = useState("");
    const [activo, setActivo] = useState(false);

    useEffect(() => fetchClientes(), []);

    const fetchClientes = async () => {
        try {
            const response = await axios.get("/api/clientes");
            setClientes(response.data);
        } catch (error) {
            setError(error);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const response = await axios.post("/api/clientes", {
            razon_social: razonSocial,
            alias: alias,
            limite_encuestas: limiteEncuestas,
            activo: true,
        });
        setClientes([...clientes, response.data]);
        setRazonSocial("");
        setAlias("");
        setLimiteEncuestas("");
        setActivo(false);
    };

    return (
        <>
            <h1 className="titulo-principal">Clientes</h1>
            <form onSubmit={handleSubmit} className="formulario-cliente">
                <input
                    type="text"
                    name="razon_social"
                    placeholder="Razón social"
                    value={razonSocial}
                    onChange={(e) => setRazonSocial(e.target.value)}
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
                />
                <button type="submit">Guardar</button>
            </form>
            <ul className="lista-clientes">
                {clientes.map((cliente) => (
                    <li key={cliente.id_cliente}>
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
                                />
                            )}
                        </div>
                    </li>
                ))}
            </ul>
        </>
    );
}

createRoot(document.getElementById("app")).render(<App />);
