import React, { useState, useEffect, useCallback } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { encuestaService } from "../../services/encuestaService"; // Para obtener la encuesta
import { respuestaService } from "../../services/respuestaService"; // Crear este servicio
import "./EncuestaPublicaPage.css"; // Crearemos este CSS

const EncuestaPublicaPage = () => {
    const { idEncuesta, codigoUrl } = useParams(); // Puede venir por ID o por código
    const navigate = useNavigate();

    const [encuesta, setEncuesta] = useState(null);
    const [respuestas, setRespuestas] = useState({}); // { pregunta_id: valor, ... } o { pregunta_id: [opcion_id1, opcion_id2], ... }
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitSuccess, setSubmitSuccess] = useState(false);

    const cargarEncuesta = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            let response;
            if (codigoUrl) {
                response = await encuestaService.getPublicaPorCodigo(codigoUrl);
            } else if (idEncuesta) {
                response = await encuestaService.getPublica(idEncuesta);
            } else {
                throw new Error("No se proporcionó ID ni código de encuesta.");
            }
            const encuestaData = response.data.data;
            if (!encuestaData || !encuestaData.cliente?.activo) {
                // Chequear si la encuesta y el cliente están activos
                throw new Error("Encuesta no disponible o no encontrada.");
            }
            // Inicializar respuestas
            const initialRespuestas = {};
            (encuestaData.secciones_encuesta || []).forEach((seccion) => {
                (seccion.preguntas || []).forEach((pregunta) => {
                    initialRespuestas[pregunta.id_pregunta] = pregunta
                        .tipo_pregunta_info?.es_seleccion_multiple
                        ? []
                        : "";
                });
            });
            setRespuestas(initialRespuestas);
            setEncuesta(encuestaData);
        } catch (err) {
            console.error("Error al cargar encuesta pública:", err);
            setError(
                err.response?.data?.message ||
                    err.message ||
                    "No se pudo cargar la encuesta."
            );
        } finally {
            setLoading(false);
        }
    }, [idEncuesta, codigoUrl]);

    useEffect(() => {
        cargarEncuesta();
    }, [cargarEncuesta]);

    const handleInputChange = (
        preguntaId,
        tipoPregunta,
        valor,
        opcionId = null
    ) => {
        setRespuestas((prev) => {
            const nuevasRespuestas = { ...prev };
            if (tipoPregunta.es_seleccion_multiple) {
                const actuales = Array.isArray(nuevasRespuestas[preguntaId])
                    ? nuevasRespuestas[preguntaId]
                    : [];
                if (valor) {
                    // `valor` aquí es el `checked` del checkbox
                    nuevasRespuestas[preguntaId] = [...actuales, opcionId];
                } else {
                    nuevasRespuestas[preguntaId] = actuales.filter(
                        (id) => id !== opcionId
                    );
                }
            } else if (tipoPregunta.requiere_opciones) {
                // Opción única o lista
                nuevasRespuestas[preguntaId] = opcionId; // Guardar el ID de la opción
            } else {
                // Texto, número, fecha, etc.
                nuevasRespuestas[preguntaId] = valor;
            }
            return nuevasRespuestas;
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsSubmitting(true);
        setError("");
        setSubmitSuccess(false);

        // Validar respuestas obligatorias (básico)
        for (const seccion of encuesta.secciones_encuesta) {
            for (const pregunta of seccion.preguntas) {
                if (pregunta.es_obligatoria) {
                    const respuesta = respuestas[pregunta.id_pregunta];
                    if (
                        (Array.isArray(respuesta) && respuesta.length === 0) ||
                        (!Array.isArray(respuesta) && !String(respuesta).trim())
                    ) {
                        setError(
                            `La pregunta "${pregunta.texto_pregunta}" es obligatoria.`
                        );
                        setIsSubmitting(false);
                        // Hacer scroll a la pregunta
                        document
                            .getElementById(`pregunta-${pregunta.id_pregunta}`)
                            ?.scrollIntoView({ behavior: "smooth" });
                        return;
                    }
                }
            }
        }
        // Formatear respuestas para el backend
        const respuestasFormateadas = Object.entries(respuestas)
            .map(([id_pregunta, valor_respuesta]) => {
                const pregunta = encuesta.secciones_encuesta
                    .flatMap((s) => s.preguntas)
                    .find((p) => p.id_pregunta === parseInt(id_pregunta));
                if (
                    pregunta?.tipo_pregunta_info?.es_seleccion_multiple &&
                    Array.isArray(valor_respuesta)
                ) {
                    // Para selección múltiple, enviar un array de objetos respuesta_opcion_id
                    return valor_respuesta.map((opcion_id) => ({
                        id_pregunta: parseInt(id_pregunta),
                        respuesta_opcion_id: parseInt(opcion_id),
                    }));
                } else if (
                    pregunta?.tipo_pregunta_info?.requiere_opciones &&
                    !Array.isArray(valor_respuesta)
                ) {
                    // Opción única, enviar respuesta_opcion_id
                    return {
                        id_pregunta: parseInt(id_pregunta),
                        respuesta_opcion_id: valor_respuesta
                            ? parseInt(valor_respuesta)
                            : null,
                    };
                }
                // Otros tipos, enviar respuesta_texto
                return {
                    id_pregunta: parseInt(id_pregunta),
                    respuesta_texto: String(valor_respuesta),
                };
            })
            .flat(); // Aplanar en caso de selección múltiple

        try {
            await respuestaService.submitRespuestas(encuesta.id_encuesta, {
                respuestas: respuestasFormateadas,
            });
            setSubmitSuccess(true);
        } catch (err) {
            console.error("Error al enviar respuestas:", err);
            setError(
                err.response?.data?.message ||
                    "Ocurrió un error al enviar tus respuestas."
            );
        } finally {
            setIsSubmitting(false);
        }
    };

    if (loading)
        return <div className="loading-fullscreen">Cargando encuesta...</div>;
    if (error && !submitSuccess)
        return <div className="error-message full-width-error">{error}</div>;
    if (!encuesta && !loading)
        return <div className="info-message">Encuesta no disponible.</div>;

    if (submitSuccess) {
        return (
            <div className="encuesta-publica-container encuesta-agradecimiento">
                <CheckCircleIcon /> {/* Asume que tienes este icono */}
                <h2>¡Gracias por tus respuestas!</h2>
                <p>
                    Tu participación en la encuesta "{encuesta?.nombre}" ha sido
                    registrada exitosamente.
                </p>
                {/* Podrías añadir un link a la página principal de la empresa/cliente */}
            </div>
        );
    }

    return (
        <div className="encuesta-publica-container">
            <header className="encuesta-publica-header">
                <h1>{encuesta?.nombre}</h1>
                {encuesta?.descripcion && (
                    <p className="encuesta-publica-descripcion">
                        {encuesta.descripcion}
                    </p>
                )}
            </header>
            <form onSubmit={handleSubmit} className="encuesta-publica-form">
                {encuesta?.secciones_encuesta?.map((seccion) => (
                    <section
                        key={seccion.id_seccion}
                        className="encuesta-seccion"
                    >
                        <h2>{seccion.nombre}</h2>
                        {seccion.descripcion && (
                            <p className="seccion-descripcion">
                                {seccion.descripcion}
                            </p>
                        )}
                        {seccion.preguntas?.map((pregunta) => (
                            <div
                                key={pregunta.id_pregunta}
                                className="pregunta-container"
                                id={`pregunta-${pregunta.id_pregunta}`}
                            >
                                <label
                                    htmlFor={`respuesta-${pregunta.id_pregunta}`}
                                    className="pregunta-label"
                                >
                                    {pregunta.orden}. {pregunta.texto_pregunta}
                                    {pregunta.es_obligatoria && (
                                        <span className="obligatorio">*</span>
                                    )}
                                </label>
                                {pregunta.texto_ayuda && (
                                    <p className="texto-ayuda">
                                        {pregunta.texto_ayuda}
                                    </p>
                                )}

                                {/* Renderizar input según tipo de pregunta */}
                                {renderInputPorTipo(
                                    pregunta,
                                    respuestas[pregunta.id_pregunta],
                                    handleInputChange
                                )}
                            </div>
                        ))}
                    </section>
                ))}
                {error && <p className="error-message form-error">{error}</p>}
                <button
                    type="submit"
                    className="btn-submit-encuesta"
                    disabled={isSubmitting}
                >
                    {isSubmitting ? "Enviando..." : "Enviar Respuestas"}
                </button>
            </form>
        </div>
    );
};

// Helper para renderizar el input correcto
const renderInputPorTipo = (pregunta, valorActual, handleChange) => {
    const tipo = pregunta.tipo_pregunta_info || pregunta.tipo_pregunta; // `tipo_pregunta_info` si lo añades a tu resource
    if (!tipo) return <p>Tipo de pregunta no definido.</p>;

    const commonProps = {
        id: `respuesta-${pregunta.id_pregunta}`,
        name: `pregunta_${pregunta.id_pregunta}`,
        required: pregunta.es_obligatoria,
    };

    if (tipo.nombre === "Texto corto") {
        return (
            <input
                type="text"
                {...commonProps}
                value={valorActual || ""}
                onChange={(e) =>
                    handleChange(pregunta.id_pregunta, tipo, e.target.value)
                }
            />
        );
    }
    if (tipo.nombre === "Texto largo") {
        return (
            <textarea
                {...commonProps}
                value={valorActual || ""}
                onChange={(e) =>
                    handleChange(pregunta.id_pregunta, tipo, e.target.value)
                }
                rows="4"
            />
        );
    }
    if (tipo.nombre === "Valor numérico" || tipo.nombre === "Valoración") {
        return (
            <input
                type="number"
                {...commonProps}
                value={valorActual || ""}
                min={pregunta.numero_minimo ?? undefined}
                max={pregunta.numero_maximo ?? undefined}
                onChange={(e) =>
                    handleChange(pregunta.id_pregunta, tipo, e.target.value)
                }
            />
        );
    }
    if (tipo.nombre === "Booleano (Sí/No)") {
        return (
            <div className="radio-group">
                <label>
                    <input
                        type="radio"
                        {...commonProps}
                        name={`pregunta_${pregunta.id_pregunta}`}
                        value="1"
                        checked={valorActual === "1"}
                        onChange={(e) =>
                            handleChange(
                                pregunta.id_pregunta,
                                tipo,
                                e.target.value,
                                "1"
                            )
                        }
                    />{" "}
                    Sí
                </label>
                <label>
                    <input
                        type="radio"
                        {...commonProps}
                        name={`pregunta_${pregunta.id_pregunta}`}
                        value="0"
                        checked={valorActual === "0"}
                        onChange={(e) =>
                            handleChange(
                                pregunta.id_pregunta,
                                tipo,
                                e.target.value,
                                "0"
                            )
                        }
                    />{" "}
                    No
                </label>
            </div>
        );
    }
    if (tipo.nombre === "Fecha") {
        return (
            <input
                type="date"
                {...commonProps}
                value={valorActual || ""}
                min={pregunta.fecha_minima?.substring(0, 10) ?? undefined}
                max={pregunta.fecha_maxima?.substring(0, 10) ?? undefined}
                onChange={(e) =>
                    handleChange(pregunta.id_pregunta, tipo, e.target.value)
                }
            />
        );
    }
    if (tipo.nombre === "Hora") {
        return (
            <input
                type="time"
                {...commonProps}
                value={valorActual || ""}
                min={pregunta.hora_minima ?? undefined}
                max={pregunta.hora_maxima ?? undefined}
                onChange={(e) =>
                    handleChange(pregunta.id_pregunta, tipo, e.target.value)
                }
            />
        );
    }
    if (tipo.requiere_opciones && !tipo.es_seleccion_multiple) {
        // Opción única o Lista desplegable
        if (tipo.nombre === "Lista desplegable (única respuesta)") {
            return (
                <select
                    {...commonProps}
                    value={valorActual || ""}
                    onChange={(e) =>
                        handleChange(
                            pregunta.id_pregunta,
                            tipo,
                            e.target.value,
                            e.target.value
                        )
                    }
                >
                    <option value="">Seleccione una opción...</option>
                    {(pregunta.opciones_pregunta || []).map((op) => (
                        <option
                            key={op.id_opcion_pregunta}
                            value={String(op.id_opcion_pregunta)}
                        >
                            {op.texto_opcion}
                        </option>
                    ))}
                </select>
            );
        } else {
            // Opción múltiple (única respuesta) - radios
            return (
                <div className="radio-group">
                    {(pregunta.opciones_pregunta || []).map((op) => (
                        <label key={op.id_opcion_pregunta}>
                            <input
                                type="radio"
                                {...commonProps}
                                name={`pregunta_${pregunta.id_pregunta}`}
                                value={String(op.id_opcion_pregunta)}
                                checked={
                                    String(valorActual) ===
                                    String(op.id_opcion_pregunta)
                                }
                                onChange={(e) =>
                                    handleChange(
                                        pregunta.id_pregunta,
                                        tipo,
                                        e.target.value,
                                        op.id_opcion_pregunta
                                    )
                                }
                            />{" "}
                            {op.texto_opcion}
                        </label>
                    ))}
                </div>
            );
        }
    }
    if (tipo.es_seleccion_multiple) {
        // Selección múltiple (varias respuestas) - checkboxes
        return (
            <div className="checkbox-group">
                {(pregunta.opciones_pregunta || []).map((op) => (
                    <label key={op.id_opcion_pregunta}>
                        <input
                            type="checkbox"
                            {...commonProps}
                            name={`pregunta_${pregunta.id_pregunta}_${op.id_opcion_pregunta}`}
                            value={String(op.id_opcion_pregunta)}
                            checked={(valorActual || []).includes(
                                op.id_opcion_pregunta
                            )}
                            onChange={(e) =>
                                handleChange(
                                    pregunta.id_pregunta,
                                    tipo,
                                    e.target.checked,
                                    op.id_opcion_pregunta
                                )
                            }
                        />{" "}
                        {op.texto_opcion}
                    </label>
                ))}
            </div>
        );
    }

    return <p>Input para "{tipo.nombre}" no implementado.</p>;
};

export default EncuestaPublicaPage;
