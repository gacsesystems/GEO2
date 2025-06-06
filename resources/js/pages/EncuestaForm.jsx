import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { fetchEncuestaDetalle } from "../services/encuestaService";
import Button from "../components/Button";
import InputField from "../components/InputField";

export default function EncuestaForm() {
    const { id } = useParams();
    const [encuesta, setEncuesta] = useState(null);
    const [respuestas, setRespuestas] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        async function load() {
            try {
                const data = await fetchEncuestaDetalle(id);
                setEncuesta(data);
            } catch (err) {
                console.error(err);
                setError("Error al cargar la encuesta");
            } finally {
                setLoading(false);
            }
        }
        load();
    }, [id]);

    const handleChange = (preguntaId, valor) => {
        setRespuestas((prev) => ({ ...prev, [preguntaId]: valor }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        // Aquí armarías el payload { respuestas: [ { id_pregunta, valor_respuesta, ... } ] }
        // y harías axios.post('/encuestas/{id}/respuestas', payload)
        console.log("Enviar respuestas:", respuestas);
    };

    if (loading) return <div>Cargando…</div>;
    if (error) return <div>{error}</div>;

    return (
        <div className="encuesta-form">
            <h2>{encuesta.nombre}</h2>
            <form onSubmit={handleSubmit}>
                {encuesta.seccionesEncuesta.map((seccion) => (
                    <div key={seccion.id_seccion} className="seccion">
                        <h3>{seccion.nombre}</h3>
                        {seccion.preguntas.map((preg) => (
                            <div key={preg.id_pregunta} className="pregunta">
                                <p className="pregunta-texto">
                                    {preg.texto_pregunta}
                                </p>
                                {/* Dependiendo del tipo, renderizas distinto control */}
                                {preg.tipoPregunta.nombre === "Texto corto" && (
                                    <InputField
                                        label=""
                                        name={`preg_${preg.id_pregunta}`}
                                        value={
                                            respuestas[preg.id_pregunta] || ""
                                        }
                                        onChange={(e) =>
                                            handleChange(
                                                preg.id_pregunta,
                                                e.target.value
                                            )
                                        }
                                    />
                                )}
                                {preg.tipoPregunta.nombre ===
                                    "Valor numérico" && (
                                    <InputField
                                        label=""
                                        type="number"
                                        name={`preg_${preg.id_pregunta}`}
                                        value={
                                            respuestas[preg.id_pregunta] || ""
                                        }
                                        onChange={(e) =>
                                            handleChange(
                                                preg.id_pregunta,
                                                e.target.value
                                            )
                                        }
                                    />
                                )}
                                {preg.tipoPregunta.requiere_opciones && (
                                    <select
                                        name={`preg_${preg.id_pregunta}`}
                                        value={
                                            respuestas[preg.id_pregunta] || ""
                                        }
                                        onChange={(e) =>
                                            handleChange(
                                                preg.id_pregunta,
                                                e.target.value
                                            )
                                        }
                                        className="select-opciones"
                                    >
                                        <option value="">
                                            -- Selecciona --
                                        </option>
                                        {preg.opcionesPregunta.map((op) => (
                                            <option
                                                key={op.id_opcion_pregunta}
                                                value={op.id_opcion_pregunta}
                                            >
                                                {op.texto_opcion}
                                            </option>
                                        ))}
                                    </select>
                                )}
                            </div>
                        ))}
                    </div>
                ))}
                <Button type="submit">Enviar Encuesta</Button>
            </form>
        </div>
    );
}
