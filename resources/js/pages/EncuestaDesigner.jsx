import React, { useState, useEffect } from "react";
import SectionEditor from "../components/SectionEditor";
import {
    fetchEstructuraEncuesta,
    actualizarEncuesta,
    crearEncuesta,
    eliminarEncuesta,
} from "../services/encuestaService";
import "../../css/encuesta-designer.css";
import { useParams, useNavigate } from "react-router-dom";

export default function EncuestaDesigner() {
    const { encuestaId } = useParams(); // undefined si es “/encuestas/nuevo”
    const navigate = useNavigate();
    const esEdicion = Boolean(encuestaId);

    const [tituloEncuesta, setTituloEncuesta] = useState("");
    const [descripcion, setDescripcion] = useState("");
    const [sections, setSections] = useState([]); // cada sección: { id_seccion, titulo, preguntas: [...] }
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    // Si es edición, traemos la estructura completa
    useEffect(() => {
        if (esEdicion) {
            fetchEstructuraEncuesta(encuestaId)
                .then((data) => {
                    setTituloEncuesta(data.nombre);
                    setDescripcion(data.descripcion);
                    // Adaptamos las secciones para incluir un array “preguntas”
                    const s = data.seccionesEncuesta.map((sec) => ({
                        id_seccion: sec.id_seccion,
                        titulo: sec.nombre,
                        preguntas: (sec.preguntas || []).map((p) => ({
                            id_pregunta: p.id_pregunta,
                            texto_pregunta: p.texto_pregunta,
                            id_tipo_pregunta: p.id_tipo_pregunta,
                            es_obligatoria: p.es_obligatoria,
                            numero_minimo: p.numero_minimo,
                            numero_maximo: p.numero_maximo,
                            fecha_minima: p.fecha_minima,
                            fecha_maxima: p.fecha_maxima,
                            hora_minima: p.hora_minima,
                            hora_maxima: p.hora_maxima,
                            texto_ayuda: p.texto_ayuda,
                            id_pregunta_padre: p.id_pregunta_padre,
                            valor_condicion_padre: p.valor_condicion_padre,
                            id_opcion_condicion_padre:
                                p.id_opcion_condicion_padre,
                            opciones: (p.opcionesPregunta || []).map((op) => ({
                                id_opcion_pregunta: op.id_opcion_pregunta,
                                texto_opcion: op.texto_opcion,
                                valor_opcion: op.valor_opcion,
                                orden: op.orden,
                            })),
                            mapeo: {
                                // Suponemos que tu Resource carga el mapeo
                                entidad_id: p.mapeo?.entidad_id || null,
                                campo_id: p.mapeo?.campo_id || null,
                            },
                        })),
                    }));
                    setSections(s);
                })
                .catch(() => {
                    setError("Error cargando la encuesta.");
                })
                .finally(() => setLoading(false));
        } else {
            setLoading(false);
        }
    }, [encuestaId, esEdicion]);

    /* Agregar nueva sección “vacía” */
    const handleAddSection = () => {
        setSections([
            ...sections,
            { id_seccion: null, titulo: "", preguntas: [] },
        ]);
    };

    /* Actualizar título o preguntas de una sección según índice */
    const handleUpdateSection = (idx, nuevoValor, moverA = null) => {
        const arr = [...sections];
        if (nuevoValor === null && moverA !== null) {
            // caso “mover” (swap)
            const temp = arr[idx];
            arr[idx] = arr[moverA];
            arr[moverA] = temp;
            setSections(arr);
            return;
        }
        if (nuevoValor === null) {
            // se indicó “delete”
            arr.splice(idx, 1);
            setSections(arr);
            return;
        }
        // solo actualizar sección en idx
        arr[idx] = { ...arr[idx], ...nuevoValor };
        setSections(arr);
    };

    /* Eliminar sección */
    const handleDeleteSection = (idx) => {
        const arr = [...sections];
        arr.splice(idx, 1);
        setSections(arr);
    };

    /* Guardar encuesta completa (crear o actualizar) */
    const handleSaveEncuesta = async () => {
        if (!tituloEncuesta.trim()) {
            return alert("El título de la encuesta es obligatorio.");
        }
        const payload = {
            nombre: tituloEncuesta,
            descripcion,
            // el backend crea solo la cabecera de encuesta; las secciones/preguntas
            // se guardan por separado dentro de la lógica de SectionEditor y QuestionEditor.
        };
        try {
            let res;
            if (esEdicion) {
                res = await actualizarEncuesta(encuestaId, payload);
            } else {
                res = await crearEncuesta(payload);
            }
            if (!esEdicion) {
                navigate(`/encuestas/${res.id_encuesta}/diseño`);
            } else {
                alert("Encuesta actualizada exitosamente.");
            }
        } catch {
            alert("Error guardando la encuesta.");
        }
    };

    /* Eliminar encuesta */
    const handleDeleteEncuesta = async () => {
        if (
            encuestaId &&
            confirm("¿Seguro que quieres eliminar esta encuesta?")
        ) {
            await eliminarEncuesta(encuestaId);
            navigate("/encuestas");
        }
    };

    if (loading) return <p>Cargando diseñador...</p>;
    if (error) return <p className="text-red-600">{error}</p>;

    return (
        <div className="survey-designer">
            <div className="survey-designer__header">
                <h1>{esEdicion ? "Editar Encuesta" : "Nueva Encuesta"}</h1>
                <div>
                    {esEdicion && (
                        <button
                            style={{ marginRight: "var(--space-sm)" }}
                            onClick={handleDeleteEncuesta}
                        >
                            Eliminar Encuesta
                        </button>
                    )}
                    <button onClick={handleSaveEncuesta}>
                        {esEdicion ? "Actualizar" : "Crear Encuesta"}
                    </button>
                </div>
            </div>

            <div style={{ marginBottom: "var(--space-md)" }}>
                <label>
                    Título:
                    <input
                        type="text"
                        value={tituloEncuesta}
                        onChange={(e) => setTituloEncuesta(e.target.value)}
                        style={{
                            width: "100%",
                            padding: "var(--space-sm)",
                            fontSize: "var(--font-base)",
                            marginTop: "var(--space-xs)",
                        }}
                        placeholder="Introduce el título de la encuesta..."
                    />
                </label>
                <label
                    style={{ marginTop: "var(--space-sm)", display: "block" }}
                >
                    Descripción:
                    <textarea
                        value={descripcion}
                        onChange={(e) => setDescripcion(e.target.value)}
                        rows="2"
                        style={{
                            width: "100%",
                            padding: "var(--space-sm)",
                            fontSize: "var(--font-base)",
                            marginTop: "var(--space-xs)",
                        }}
                        placeholder="Breve descripción (opcional)"
                    />
                </label>
            </div>

            {/* Lista de secciones */}
            <div className="section-list">
                {sections.map((sec, idx) => (
                    <SectionEditor
                        key={idx}
                        encuestaId={encuestaId}
                        section={sec}
                        index={idx}
                        totalSections={sections.length}
                        onUpdateSection={handleUpdateSection}
                        onDeleteSection={handleDeleteSection}
                        onDragStart={(e, i) => {
                            e.dataTransfer.setData("text/plain", i);
                            e.currentTarget.classList.add("dragging");
                            handleUpdateSection(i, null, null); // marca arrastrando
                        }}
                        onDragOver={(e, i) => e.preventDefault()}
                        onDrop={(e, i) => {
                            const fromIdx = Number(
                                e.dataTransfer.getData("text/plain")
                            );
                            handleUpdateSection(fromIdx, null, i);
                            e.currentTarget.classList.remove("dragging");
                        }}
                    />
                ))}
            </div>

            <div className="survey-designer__add-section">
                <button onClick={handleAddSection}>+ Añadir sección</button>
            </div>
        </div>
    );
}
