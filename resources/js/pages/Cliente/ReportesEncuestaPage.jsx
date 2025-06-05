import React, { useState, useEffect, useCallback } from "react";
import { useParams, Link, useNavigate } from "react-router-dom";
import { encuestaService } from "../../services/encuestaService"; // Necesitarás un servicio para reportes
import { reporteService } from "../../services/reporteService"; // Crear este servicio
import {
    ArrowLeftIcon as IconoVolver,
    DocumentArrowDownIcon as IconoExportar,
} from "../../components/ui/Icons"; // Asume que tienes DocumentArrowDownIcon
import "./ReportesEncuestaPage.css"; // Crearemos este CSS
// Opcional: Chart.js para gráficos
// import { Bar, Pie } from 'react-chartjs-2';
// import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';
// ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend);

const ReportesEncuestaPage = () => {
    const { idEncuesta } = useParams();
    const navigate = useNavigate();

    const [encuesta, setEncuesta] = useState(null);
    const [respuestasDetalladas, setRespuestasDetalladas] = useState([]);
    const [resumenPorPregunta, setResumenPorPregunta] = useState([]); // {pregunta_id, texto, tipo, opciones: [{texto, conteo, porcentaje}]}
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    const cargarReportes = useCallback(async () => {
        setLoading(true);
        setError("");
        try {
            // Cargar información básica de la encuesta
            const encuestaRes = await encuestaService.getById(idEncuesta); // O getDetalleCompleto si necesitas más info
            setEncuesta(encuestaRes.data.data);

            // Cargar resumen por pregunta
            const resumenRes = await reporteService.getResumenPorPregunta(
                idEncuesta
            );
            setResumenPorPregunta(resumenRes.data.data || resumenRes.data);

            // Opcional: Cargar respuestas detalladas (puede ser mucho, considerar paginación o carga bajo demanda)
            // const detalladasRes = await reporteService.getRespuestasDetalladas(idEncuesta);
            // setRespuestasDetalladas(detalladasRes.data.data || detalladasRes.data);
        } catch (err) {
            console.error("Error al cargar reportes:", err);
            setError(
                err.response?.data?.message ||
                    "No se pudieron cargar los reportes."
            );
            if (!encuesta) navigate("/cliente/encuestas"); // O ruta de admin si es admin
        } finally {
            setLoading(false);
        }
    }, [idEncuesta, navigate, encuesta]); // `encuesta` en deps por si se usa para no redirigir

    useEffect(() => {
        cargarReportes();
    }, [cargarReportes]); // Se llama una vez o si idEncuesta cambia

    const handleExportarResumen = async () => {
        try {
            // El backend genera el CSV y lo descarga
            // La URL directa al endpoint de exportación
            window.open(
                `/api/reportes/encuestas/${idEncuesta}/exportar/resumen-por-pregunta/csv`,
                "_blank"
            );
        } catch (err) {
            alert("Error al exportar el resumen.");
            console.error("Error exportando resumen:", err);
        }
    };
    const handleExportarDetalladas = async () => {
        try {
            window.open(
                `/api/reportes/encuestas/${idEncuesta}/exportar/respuestas-detalladas/csv`,
                "_blank"
            );
        } catch (err) {
            alert("Error al exportar respuestas detalladas.");
            console.error("Error exportando detalladas:", err);
        }
    };

    if (loading)
        return <div className="loading-fullscreen">Cargando reportes...</div>;
    if (error)
        return (
            <div className="error-message full-width-error">
                {error} <Link to="/cliente/encuestas">Volver</Link>
            </div>
        );
    if (!encuesta)
        return <div className="info-message">Encuesta no encontrada.</div>;

    return (
        <div className="gestion-page-container reportes-encuesta-container">
            <div className="page-header">
                <div>
                    <Link to={`/cliente/encuestas`} className="btn-accion-link">
                        {" "}
                        {/* O ruta dinámica si admin */}
                        <IconoVolver /> Volver a Mis Encuestas
                    </Link>
                    <h1>Reportes: {encuesta.nombre}</h1>
                    <p className="descripcion-encuesta-principal">
                        Total de respuestas:{" "}
                        {resumenPorPregunta.total_respuestas_completadas ??
                            "Calculando..."}
                    </p>
                </div>
                <div className="reportes-acciones-header">
                    <button
                        onClick={handleExportarResumen}
                        className="btn-accion-secundario"
                    >
                        <IconoExportar /> Exportar Resumen (CSV)
                    </button>
                    <button
                        onClick={handleExportarDetalladas}
                        className="btn-accion-secundario"
                    >
                        <IconoExportar /> Exportar Detalladas (CSV)
                    </button>
                </div>
            </div>

            <div className="reportes-contenido">
                {resumenPorPregunta.preguntas &&
                resumenPorPregunta.preguntas.length > 0 ? (
                    resumenPorPregunta.preguntas.map((itemPregunta) => (
                        <div
                            key={itemPregunta.id_pregunta}
                            className="reporte-pregunta-card"
                        >
                            <h3>
                                {itemPregunta.orden}.{" "}
                                {itemPregunta.texto_pregunta}
                            </h3>
                            <span className="tipo-pregunta-badge">
                                {itemPregunta.tipo_pregunta_nombre}
                            </span>

                            {itemPregunta.tipo_pregunta_requiere_opciones ? (
                                <ul className="opciones-resumen-lista">
                                    {itemPregunta.opciones_conteo.map(
                                        (opcion) => (
                                            <li
                                                key={
                                                    opcion.id_opcion_pregunta ||
                                                    opcion.texto_opcion
                                                }
                                            >
                                                <span className="opcion-texto">
                                                    {opcion.texto_opcion}
                                                </span>
                                                <div className="barra-progreso-contenedor">
                                                    <div
                                                        className="barra-progreso"
                                                        style={{
                                                            width: `${opcion.porcentaje}%`,
                                                        }}
                                                    >
                                                        {opcion.porcentaje >
                                                            10 &&
                                                            `${opcion.porcentaje.toFixed(
                                                                1
                                                            )}%`}
                                                    </div>
                                                </div>
                                                <span className="opcion-conteo">
                                                    ({opcion.conteo} resp.)
                                                </span>
                                            </li>
                                        )
                                    )}
                                </ul>
                            ) : itemPregunta.tipo_pregunta_nombre ===
                                  "Texto corto" ||
                              itemPregunta.tipo_pregunta_nombre ===
                                  "Texto largo" ? (
                                <div className="respuestas-abiertas-muestra">
                                    <h4>Muestra de Respuestas Abiertas:</h4>
                                    {itemPregunta.respuestas_muestra &&
                                    itemPregunta.respuestas_muestra.length >
                                        0 ? (
                                        <ul>
                                            {itemPregunta.respuestas_muestra
                                                .slice(0, 5)
                                                .map(
                                                    (
                                                        resp,
                                                        idx // Mostrar solo algunas
                                                    ) => (
                                                        <li
                                                            key={idx}
                                                            className="respuesta-abierta-item"
                                                        >
                                                            "
                                                            {
                                                                resp.respuesta_texto
                                                            }
                                                            "
                                                        </li>
                                                    )
                                                )}
                                        </ul>
                                    ) : (
                                        <p className="text-muted">
                                            No hay respuestas de texto para esta
                                            pregunta.
                                        </p>
                                    )}
                                    {
                                        itemPregunta.respuestas_muestra
                                            ?.length > 5 && (
                                            <p>
                                                <Link to={`#`}>
                                                    Ver todas las respuestas de
                                                    texto...
                                                </Link>
                                            </p>
                                        ) /* TODO: link a vista detallada */
                                    }
                                </div>
                            ) : (
                                <p className="text-muted">
                                    Reporte para este tipo de pregunta no
                                    implementado o sin datos.
                                </p>
                            )}
                            {/* Aquí podrías integrar un gráfico de Chart.js si quieres */}
                        </div>
                    ))
                ) : (
                    <p className="info-message">
                        No hay datos de resumen disponibles para esta encuesta.
                    </p>
                )}
            </div>
        </div>
    );
};

export default ReportesEncuestaPage;
