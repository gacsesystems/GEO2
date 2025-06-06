import React from "react";
import {
    PencilIcon,
    TrashIcon,
    CogIcon,
    LinkIcon,
    ChartBarIcon,
    EyeIcon,
} from "../ui/Icons"; // Ajusta los nombres/iconos
import "./EncuestasTable.css";

// Asumiendo que tienes CogIcon para Diseñar, ChartBarIcon para Resultados
// y EyeIcon si quieres un "Previsualizar"

const EncuestasTable = ({
    encuestas,
    onEditar,
    onEliminar,
    onDisenar,
    onObtenerUrl,
    onVerResultados,
    // onPrevisualizar, // Opcional
}) => {
    if (!encuestas || encuestas.length === 0) {
        return (
            <p className="info-message">Aún no has creado ninguna encuesta.</p>
        );
    }

    return (
        <div className="card-table-scroll-container">
            {" "}
            {/* Reutilizar clase de CardTable para scroll */}
            <table className="data-table encuestas-table">
                {" "}
                {/* data-table podría ser global */}
                <thead>
                    <tr>
                        <th>Nombre Encuesta</th>
                        <th>Descripción</th>
                        <th className="text-center">Secciones</th>
                        <th className="text-center">Preguntas</th>
                        <th>Fecha Creación</th>
                        <th className="text-right">Acciones</th>
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
                                            ? "..."
                                            : ""
                                    }`
                                ) : (
                                    <span className="text-muted">N/A</span>
                                )}
                            </td>
                            <td data-label="Secciones" className="text-center">
                                {encuesta.cantidad_secciones ??
                                    (encuesta.secciones_encuesta?.length || 0)}
                            </td>
                            <td data-label="Preguntas" className="text-center">
                                {encuesta.cantidad_preguntas ??
                                    (encuesta.secciones_encuesta?.reduce(
                                        (sum, s) =>
                                            sum + (s.preguntas?.length || 0),
                                        0
                                    ) ||
                                        0)}
                            </td>
                            <td data-label="Fecha Creación">
                                {encuesta.created_at ? ( // Tu modelo usa created_at
                                    new Date(
                                        encuesta.created_at
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
                                    title="Editar Datos Generales"
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
                                    title="Obtener Enlace Público"
                                >
                                    <LinkIcon />
                                </button>
                                {/* Opcional: Botón de previsualización
                                <button onClick={() => onPrevisualizar(encuesta.id_encuesta)} className="btn-accion btn-preview" title="Previsualizar">
                                    <EyeIcon />
                                </button>
                                */}
                                <button
                                    onClick={() =>
                                        onEliminar(encuesta.id_encuesta)
                                    }
                                    className="btn-accion btn-eliminar"
                                    title="Eliminar Encuesta"
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
