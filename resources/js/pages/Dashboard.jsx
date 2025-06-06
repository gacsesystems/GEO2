import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { fetchEncuestas } from "../services/encuestaService";
import "./Dashboard.css";

export default function Dashboard() {
    const [encuestas, setEncuestas] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        async function load() {
            try {
                const data = await fetchEncuestas();
                setEncuestas(data);
            } catch (err) {
                console.error(err);
            } finally {
                setLoading(false);
            }
        }
        load();
    }, []);

    if (loading) {
        return <div>Cargando encuestas…</div>;
    }

    return (
        <div className="dashboard">
            <h2>Mis Encuestas</h2>
            {encuestas.length === 0 ? (
                <p>
                    No tienes encuestas.{" "}
                    <Link to="/encuesta/nueva">Crear nueva</Link>
                </p>
            ) : (
                <ul className="encuesta-list">
                    {encuestas.map((enc) => (
                        <li key={enc.id_encuesta}>
                            <Link to={`/encuesta/${enc.id_encuesta}`}>
                                {enc.nombre}
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
