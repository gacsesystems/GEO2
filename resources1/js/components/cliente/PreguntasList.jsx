import React from "react";
import {
    PencilIcon,
    TrashIcon,
    CogIcon,
    ArrowUpIcon,
    ArrowDownIcon,
} from "../ui/Icons";
import "./PreguntasList.css"; // Tu CSS

const PreguntasList = ({
    preguntas,
    idSeccion,
    onEditarPregunta,
    onEliminarPregunta,
    onGestionarOpciones,
    onReordenarPregunta,
    isLoading, // Para deshabilitar botones durante operaciones
}) => {
    if (!preguntas || preguntas.length === 0) {
        return (
            <p className="info-message-small">
                No hay preguntas en esta sección todavía.
            </p>
        );
    }
    const totalPreguntasEnSeccion = preguntas.length;

    return (
        <ul className="preguntas-lista">
            {preguntas.map((pregunta) => (
                <li key={pregunta.id_pregunta} className="pregunta-item">
                    <div className="pregunta-info">
                        <span className="pregunta-orden">
                            {pregunta.orden}.
                        </span>
                        <span className="pregunta-texto">
                            {pregunta.texto_pregunta}
                        </span>
                        <span className="pregunta-tipo">
                            (
                            {pregunta.tipo_pregunta?.nombre || // Acceder al nombre desde la relación cargada
                                (pregunta.tipo_pregunta_info
                                    ? pregunta.tipo_pregunta_info.nombre
                                    : "Tipo Desconocido")}
                            )
                        </span>
                        {pregunta.es_obligatoria && (
                            <span className="pregunta-obligatoria">
                                * Obligatoria
                            </span>
                        )}
                    </div>
                    <div className="pregunta-acciones-item">
                        <button
                            onClick={() =>
                                onReordenarPregunta(
                                    pregunta.id_pregunta,
                                    idSeccion,
                                    "arriba"
                                )
                            }
                            className="btn-accion-secundario"
                            disabled={pregunta.orden === 1 || isLoading}
                            title="Mover Arriba"
                        >
                            <ArrowUpIcon />
                        </button>
                        <button
                            onClick={() =>
                                onReordenarPregunta(
                                    pregunta.id_pregunta,
                                    idSeccion,
                                    "abajo"
                                )
                            }
                            className="btn-accion-secundario"
                            disabled={
                                pregunta.orden === totalPreguntasEnSeccion ||
                                isLoading
                            }
                            title="Mover Abajo"
                        >
                            <ArrowDownIcon />
                        </button>
                        {(pregunta.tipo_pregunta?.requiere_opciones ||
                            pregunta.tipo_pregunta_info?.requiere_opciones) && (
                            <button
                                onClick={() => onGestionarOpciones(pregunta)}
                                className="btn-accion-secundario"
                                title="Gestionar Opciones"
                                disabled={isLoading}
                            >
                                <CogIcon />{" "}
                                {/* Icono para "opciones" o "configuración" */}
                            </button>
                        )}
                        <button
                            onClick={() => onEditarPregunta(pregunta)}
                            className="btn-accion-secundario"
                            title="Editar Pregunta"
                            disabled={isLoading}
                        >
                            <PencilIcon />
                        </button>
                        <button
                            onClick={() =>
                                onEliminarPregunta(pregunta.id_pregunta)
                            }
                            className="btn-accion-secundario btn-peligro" // Clase para estilo de peligro
                            title="Eliminar Pregunta"
                            disabled={isLoading}
                        >
                            <TrashIcon />
                        </button>
                    </div>
                </li>
            ))}
        </ul>
    );
};

export default PreguntasList;
