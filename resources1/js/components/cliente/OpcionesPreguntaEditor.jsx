import React, { useState, useEffect } from "react";
import { opcionPreguntaService } from "../../services/opcionPreguntaService";
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    CheckIcon,
    CancelIcon,
} from "../ui/Icons"; // Asume que tienes CheckIcon y CancelIcon
import "./OpcionesPreguntaEditor.css";

const OpcionesPreguntaEditor = ({
    pregunta, // La pregunta completa, incluyendo id_encuesta, id_seccion, id_pregunta y sus opciones
    onOpcionesActualizadas, // Callback para notificar al padre que las opciones cambiaron
    disabled = false, // Para deshabilitar durante la carga del formulario principal
}) => {
    const [opciones, setOpciones] = useState([]);
    const [editandoOpcionId, setEditandoOpcionId] = useState(null);
    const [textoOpcionEdit, setTextoOpcionEdit] = useState("");
    const [valorOpcionEdit, setValorOpcionEdit] = useState(""); // Opcional, si tus opciones tienen un valor distinto al texto
    const [nuevaOpcionTexto, setNuevaOpcionTexto] = useState("");
    const [nuevaOpcionValor, setNuevaOpcionValor] = useState("");
    const [isLoadingOpciones, setIsLoadingOpciones] = useState(false);
    const [errorOpciones, setErrorOpciones] = useState("");

    useEffect(() => {
        if (pregunta && pregunta.opciones_pregunta) {
            setOpciones(
                pregunta.opciones_pregunta.sort((a, b) => a.orden - b.orden)
            );
        } else {
            setOpciones([]);
        }
    }, [pregunta]);

    const handleIniciarEdicion = (opcion) => {
        setEditandoOpcionId(opcion.id_opcion_pregunta);
        setTextoOpcionEdit(opcion.texto_opcion);
        setValorOpcionEdit(opcion.valor_opcion || "");
        setErrorOpciones("");
    };

    const handleCancelarEdicion = () => {
        setEditandoOpcionId(null);
        setTextoOpcionEdit("");
        setValorOpcionEdit("");
    };

    const handleGuardarEdicion = async (idOpcion) => {
        if (!textoOpcionEdit.trim()) {
            setErrorOpciones("El texto de la opción no puede estar vacío.");
            return;
        }
        setIsLoadingOpciones(true);
        setErrorOpciones("");
        try {
            await opcionPreguntaService.update(idOpcion, {
                texto_opcion: textoOpcionEdit,
                valor_opcion: valorOpcionEdit || textoOpcionEdit, // Si valor es opcional, usa texto
            });
            // Recargar opciones o actualizar localmente
            const opcionesActualizadas = opciones.map((op) =>
                op.id_opcion_pregunta === idOpcion
                    ? {
                          ...op,
                          texto_opcion: textoOpcionEdit,
                          valor_opcion: valorOpcionEdit || textoOpcionEdit,
                      }
                    : op
            );
            setOpciones(opcionesActualizadas);
            onOpcionesActualizadas(opcionesActualizadas); // Notificar al padre
            handleCancelarEdicion();
        } catch (err) {
            setErrorOpciones(
                err.response?.data?.message || "Error al actualizar opción."
            );
        } finally {
            setIsLoadingOpciones(false);
        }
    };

    const handleAgregarOpcion = async () => {
        if (!nuevaOpcionTexto.trim()) {
            setErrorOpciones(
                "El texto de la nueva opción no puede estar vacío."
            );
            return;
        }
        setIsLoadingOpciones(true);
        setErrorOpciones("");
        try {
            const response = await opcionPreguntaService.create(
                pregunta.id_encuesta, // Necesitas estos IDs si el endpoint los requiere
                pregunta.id_seccion,
                pregunta.id_pregunta,
                {
                    texto_opcion: nuevaOpcionTexto,
                    valor_opcion: nuevaOpcionValor || nuevaOpcionTexto,
                }
            );
            const nuevaOpcionConId = response.data.data; // Asume que la API devuelve la opción creada
            const opcionesActualizadas = [...opciones, nuevaOpcionConId].sort(
                (a, b) => a.orden - b.orden
            );
            setOpciones(opcionesActualizadas);
            onOpcionesActualizadas(opcionesActualizadas);
            setNuevaOpcionTexto("");
            setNuevaOpcionValor("");
        } catch (err) {
            setErrorOpciones(
                err.response?.data?.message || "Error al agregar opción."
            );
        } finally {
            setIsLoadingOpciones(false);
        }
    };

    const handleEliminarOpcion = async (idOpcion) => {
        if (window.confirm("¿Eliminar esta opción?")) {
            setIsLoadingOpciones(true);
            setErrorOpciones("");
            try {
                await opcionPreguntaService.remove(idOpcion);
                const opcionesActualizadas = opciones.filter(
                    (op) => op.id_opcion_pregunta !== idOpcion
                );
                setOpciones(opcionesActualizadas);
                onOpcionesActualizadas(opcionesActualizadas);
            } catch (err) {
                setErrorOpciones(
                    err.response?.data?.message || "Error al eliminar opción."
                );
            } finally {
                setIsLoadingOpciones(false);
            }
        }
    };

    // Faltaría lógica de reordenamiento de opciones si la necesitas aquí

    if (!pregunta || !pregunta.tipo_pregunta?.requiere_opciones) {
        return null; // No mostrar nada si la pregunta no requiere opciones
    }

    return (
        <div className="opciones-editor-container">
            <h4>Opciones para esta pregunta:</h4>
            {errorOpciones && (
                <p className="error-message small-error">{errorOpciones}</p>
            )}
            <ul className="opciones-lista">
                {opciones.map((opcion) => (
                    <li key={opcion.id_opcion_pregunta} className="opcion-item">
                        {editandoOpcionId === opcion.id_opcion_pregunta ? (
                            <div className="opcion-edit-form">
                                <input
                                    type="text"
                                    value={textoOpcionEdit}
                                    onChange={(e) =>
                                        setTextoOpcionEdit(e.target.value)
                                    }
                                    placeholder="Texto de la opción"
                                    disabled={isLoadingOpciones || disabled}
                                />
                                <input
                                    type="text"
                                    value={valorOpcionEdit}
                                    onChange={(e) =>
                                        setValorOpcionEdit(e.target.value)
                                    }
                                    placeholder="Valor (opcional)"
                                    disabled={isLoadingOpciones || disabled}
                                />
                                <button
                                    onClick={() =>
                                        handleGuardarEdicion(
                                            opcion.id_opcion_pregunta
                                        )
                                    }
                                    className="btn-accion-inline success"
                                    disabled={isLoadingOpciones || disabled}
                                >
                                    <CheckIcon />
                                </button>
                                <button
                                    onClick={handleCancelarEdicion}
                                    className="btn-accion-inline cancel"
                                    disabled={isLoadingOpciones || disabled}
                                >
                                    <CancelIcon />
                                </button>
                            </div>
                        ) : (
                            <>
                                <span className="opcion-texto">
                                    {opcion.orden}. {opcion.texto_opcion}
                                </span>
                                {opcion.valor_opcion &&
                                    opcion.valor_opcion !==
                                        opcion.texto_opcion && (
                                        <span className="opcion-valor">
                                            (Valor: {opcion.valor_opcion})
                                        </span>
                                    )}
                                <div className="opcion-acciones">
                                    <button
                                        onClick={() =>
                                            handleIniciarEdicion(opcion)
                                        }
                                        className="btn-accion-inline"
                                        disabled={isLoadingOpciones || disabled}
                                    >
                                        <PencilIcon />
                                    </button>
                                    <button
                                        onClick={() =>
                                            handleEliminarOpcion(
                                                opcion.id_opcion_pregunta
                                            )
                                        }
                                        className="btn-accion-inline danger"
                                        disabled={isLoadingOpciones || disabled}
                                    >
                                        <TrashIcon />
                                    </button>
                                    {/* Botones de reordenar opción aquí */}
                                </div>
                            </>
                        )}
                    </li>
                ))}
            </ul>
            <div className="nueva-opcion-form">
                <input
                    type="text"
                    value={nuevaOpcionTexto}
                    onChange={(e) => setNuevaOpcionTexto(e.target.value)}
                    placeholder="Texto nueva opción"
                    disabled={isLoadingOpciones || disabled}
                />
                <input
                    type="text"
                    value={nuevaOpcionValor}
                    onChange={(e) => setNuevaOpcionValor(e.target.value)}
                    placeholder="Valor (opcional)"
                    disabled={isLoadingOpciones || disabled}
                />
                <button
                    onClick={handleAgregarOpcion}
                    className="btn-accion add-option"
                    disabled={isLoadingOpciones || disabled}
                >
                    <PlusIcon /> Agregar Opción
                </button>
            </div>
        </div>
    );
};

export default OpcionesPreguntaEditor;
