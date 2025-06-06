import React from "react";
import {
    PencilIcon,
    TrashIcon,
    CogIcon,
    LinkIcon,
    ChartBarIcon,
} from "../ui/Icons";
import "./EncuestasTable.css";

const EncuestasTable = ({
    encuestas,
    onEditar,
    onEliminar,
    onDisenar,
    onObtenerUrl,
    onVerResultados,
}) => {
    if (!encuestas || encuestas.length === 0) {
        return <p className="info-message">No hay encuestas para mostrar.</p>;
    }

    return (
        <div className="card-table-scroll-container">
            <table className="data-table encuestas-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th className="text-center">Secciones</th>
                        <th className="text-center">Preguntas</th>
                        <th>Fecha Creación</th>
                        <th className="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {encuestas.map((encuesta) => (
                        <tr key={encuesta.id_encuesta}>
                            <td data-label="Nombre">{encuesta.nombre}</td>
                            <td data-label="Descripción">
                                {encuesta.descripcion ? (
                                    `${encuesta.descripcion.substring(0, 70)}${
                                        encuesta.descripcion.length > 70
                                            ? "…"
                                            : ""
                                    }`
                                ) : (
                                    <span className="text-muted">N/A</span>
                                )}
                            </td>
                            <td data-label="Secciones" className="text-center">
                                {encuesta.cantidad_secciones ??
                                    encuesta.secciones_encuesta?.length ??
                                    0}
                            </td>
                            <td data-label="Preguntas" className="text-center">
                                {encuesta.cantidad_preguntas ??
                                    encuesta.secciones_encuesta?.reduce(
                                        (suma, s) =>
                                            suma + (s.preguntas?.length || 0),
                                        0
                                    ) ??
                                    0}
                            </td>
                            <td data-label="Fecha Creación">
                                {encuesta.fecha_registro ? (
                                    new Date(
                                        encuesta.fecha_registro
                                    ).toLocaleDateString()
                                ) : (
                                    <span className="text-muted">N/A</span>
                                )}
                            </td>
                            <td className="acciones-cell" data-label="Acciones">
                                <button
                                    onClick={() =>
                                        onDisenar(encuesta.id_encuesta)
                                    }
                                    className="btn-accion btn-design"
                                    title="Diseñar Encuesta"
                                >
                                    <CogIcon />
                                </button>
                                <button
                                    onClick={() => onEditar(encuesta)}
                                    className="btn-accion btn-editar"
                                    title="Editar"
                                >
                                    <PencilIcon />
                                </button>
                                <button
                                    onClick={() =>
                                        onVerResultados(encuesta.id_encuesta)
                                    }
                                    className="btn-accion btn-ver"
                                    title="Ver Resultados"
                                >
                                    <ChartBarIcon />
                                </button>
                                <button
                                    onClick={() =>
                                        onObtenerUrl(encuesta.id_encuesta)
                                    }
                                    className="btn-accion btn-link"
                                    title="Obtener URL"
                                >
                                    <LinkIcon />
                                </button>
                                <button
                                    onClick={() =>
                                        onEliminar(encuesta.id_encuesta)
                                    }
                                    className="btn-accion btn-eliminar"
                                    title="Eliminar"
                                >
                                    <TrashIcon />
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default EncuestasTable;
