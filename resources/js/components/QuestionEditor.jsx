import React, { useState, useRef } from "react";
import OptionEditor from "./OptionEditor";
import {
    crearPregunta,
    actualizarPregunta,
    eliminarPregunta,
    reordenarPregunta,
} from "../services/encuestaService";
import {
    fetchEntidadesExternas,
    fetchCamposParaEntidad,
} from "../services/encuestaService";

export default function QuestionEditor({
    encuestaId,
    seccion,
    onQuestionsChange,
}) {
    const [editingQuestionIndex, setEditingQuestionIndex] = useState(null);
    const [modalData, setModalData] = useState(null);
    const [draggingIdx, setDraggingIdx] = useState(null);

    /* Al arrastrar pregunta */
    const handleDragStart = (e, idx) => {
        setDraggingIdx(idx);
        e.dataTransfer.effectAllowed = "move";
    };
    const handleDragOver = (e, idx) => {
        e.preventDefault();
    };
    const handleDrop = (e, idx) => {
        e.preventDefault();
        if (draggingIdx === null || draggingIdx === idx) return;
        const preguntas = [...seccion.preguntas];
        const [arrastrada] = preguntas.splice(draggingIdx, 1);
        preguntas.splice(idx, 0, arrastrada);
        // Mandar al backend el nuevo orden:
        reordenarPregunta(
            encuestaId,
            seccion.id_seccion,
            arrastrada.id_pregunta,
            idx + 1
        ).then(() => {
            onQuestionsChange(preguntas);
            setDraggingIdx(null);
        });
    };

    const preguntas = seccion.preguntas || [];

    /* Agregar pregunta vacía */
    const handleAddQuestion = () => {
        setModalData({
            modo: "nuevo",
            pregunta: {
                texto_pregunta: "",
                id_tipo_pregunta: null,
                es_obligatoria: false,
                numero_minimo: null,
                numero_maximo: null,
                fecha_minima: null,
                fecha_maxima: null,
                hora_minima: null,
                hora_maxima: null,
                texto_ayuda: "",
                id_pregunta_padre: null,
                valor_condicion_padre: null,
                id_opcion_condicion_padre: null,
                opciones: [], // si es tipo “opción”
                mapeo: { entidad_id: null, campo_id: null },
            },
        });
        setEditingQuestionIndex(preguntas.length);
    };

    /* Editar pregunta existente */
    const handleEditQuestion = (idx) => {
        setModalData({
            modo: "editar",
            pregunta: { ...preguntas[idx] },
        });
        setEditingQuestionIndex(idx);
    };

    /* Guardar cambios de pregunta (nuevo o editar) */
    const handleSaveQuestion = async (datosPregunta) => {
        const idx = editingQuestionIndex;
        if (modalData.modo === "nuevo") {
            // Crear por API
            const payload = {
                texto_pregunta: datosPregunta.texto_pregunta,
                id_tipo_pregunta: datosPregunta.id_tipo_pregunta,
                es_obligatoria: datosPregunta.es_obligatoria,
                numero_minimo: datosPregunta.numero_minimo,
                numero_maximo: datosPregunta.numero_maximo,
                fecha_minima: datosPregunta.fecha_minima,
                fecha_maxima: datosPregunta.fecha_maxima,
                hora_minima: datosPregunta.hora_minima,
                hora_maxima: datosPregunta.hora_maxima,
                texto_ayuda: datosPregunta.texto_ayuda,
                id_pregunta_padre: datosPregunta.id_pregunta_padre,
                valor_condicion_padre: datosPregunta.valor_condicion_padre,
                id_opcion_condicion_padre:
                    datosPregunta.id_opcion_condicion_padre,
                // Asumimos que el servicio backend también crea las opciones
            };
            const res = await crearPregunta(
                encuestaId,
                seccion.id_seccion,
                payload
            );
            // Res devuelve { id_pregunta, …datos… } + opciones (si aplica)
            preguntas.push({ ...res, opciones: datosPregunta.opciones });
            onQuestionsChange(preguntas);
        } else {
            // Actualizar por API
            const preguntaId = preguntas[idx].id_pregunta;
            const payload = {
                texto_pregunta: datosPregunta.texto_pregunta,
                id_tipo_pregunta: datosPregunta.id_tipo_pregunta,
                es_obligatoria: datosPregunta.es_obligatoria,
                numero_minimo: datosPregunta.numero_minimo,
                numero_maximo: datosPregunta.numero_maximo,
                fecha_minima: datosPregunta.fecha_minima,
                fecha_maxima: datosPregunta.fecha_maxima,
                hora_minima: datosPregunta.hora_minima,
                hora_maxima: datosPregunta.hora_maxima,
                texto_ayuda: datosPregunta.texto_ayuda,
                id_pregunta_padre: datosPregunta.id_pregunta_padre,
                valor_condicion_padre: datosPregunta.valor_condicion_padre,
                id_opcion_condicion_padre:
                    datosPregunta.id_opcion_condicion_padre,
            };
            await actualizarPregunta(
                encuestaId,
                seccion.id_seccion,
                preguntaId,
                payload
            );
            preguntas[idx] = { ...preguntas[idx], ...payload };
            onQuestionsChange(preguntas);
        }

        // Editar/Crear las opciones, si es necesario
        for (const op of modalData.pregunta.opciones || []) {
            if (!op.id_opcion_pregunta) {
                // crear opción
                const r = await crearOpcion(
                    encuestaId,
                    seccion.id_seccion,
                    preguntas[idx].id_pregunta,
                    {
                        texto_opcion: op.texto_opcion,
                        valor_opcion: op.valor_opcion,
                    }
                );
                op.id_opcion_pregunta = r.id_opcion_pregunta;
            } else {
                await actualizarOpcion(
                    encuestaId,
                    seccion.id_seccion,
                    preguntas[idx].id_pregunta,
                    op.id_opcion_pregunta,
                    {
                        texto_opcion: op.texto_opcion,
                        valor_opcion: op.valor_opcion,
                    }
                );
            }
        }

        // Si el usuario eliminó alguna opción, hay que borrarla:
        const idsActuales = modalData.pregunta.opciones.map(
            (o) => o.id_opcion_pregunta
        );
        for (const opVieja of preguntas[idx].opciones || []) {
            if (
                opVieja.id_opcion_pregunta &&
                !idsActuales.includes(opVieja.id_opcion_pregunta)
            ) {
                await eliminarOpcion(
                    encuestaId,
                    seccion.id_seccion,
                    preguntas[idx].id_pregunta,
                    opVieja.id_opcion_pregunta
                );
            }
        }

        // Manejar mapeo a entidad/campo externo
        if (
            modalData.pregunta.mapeo.entidad_id &&
            modalData.pregunta.mapeo.campo_id
        ) {
            // Aquí deberías llamar a tu API interna que guarde el mapeo
            // Ej: await savePreguntaMapeoExterno(encuestaId, preguntas[idx].id_pregunta, modalData.pregunta.mapeo);
            // (Asumimos que guarda en tabla PreguntaMapeoExterno)
        }

        setModalData(null);
        setEditingQuestionIndex(null);
    };

    /* Eliminar pregunta */
    const handleDeleteQuestion = async (idx) => {
        const preguntaId = preguntas[idx].id_pregunta;
        if (preguntaId && confirm("¿Eliminar esta pregunta?")) {
            await eliminarPregunta(encuestaId, seccion.id_seccion, preguntaId);
            preguntas.splice(idx, 1);
            onQuestionsChange(preguntas);
        }
    };

    return (
        <>
            <div className="question-list">
                {preguntas.map((q, idx) => (
                    <div
                        key={q.id_pregunta || idx}
                        className={`question-item ${
                            draggingIdx === idx ? "dragging" : ""
                        }`}
                        draggable
                        onDragStart={(e) => handleDragStart(e, idx)}
                        onDragOver={(e) => handleDragOver(e, idx)}
                        onDrop={(e) => handleDrop(e, idx)}
                    >
                        <div className="question-item__header">
                            <span className="question-item__text">
                                {q.texto_pregunta || "Pregunta sin título"}
                            </span>
                            <div className="question-item__actions">
                                <button onClick={() => handleEditQuestion(idx)}>
                                    ✎
                                </button>
                                <button
                                    onClick={() => handleDeleteQuestion(idx)}
                                >
                                    🗑
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            <div className="section-item__add-question">
                <button onClick={handleAddQuestion}>+ Añadir pregunta</button>
            </div>

            {/* Modal para crear/editar pregunta */}
            {modalData && (
                <div className="modal-backdrop">
                    <div className="modal-container">
                        <h2>
                            {modalData.modo === "nuevo"
                                ? "Nueva Pregunta"
                                : "Editar Pregunta"}
                        </h2>
                        {/* Componente reutilizable para editar los campos de la pregunta */}
                        <PreguntaForm
                            pregunta={modalData.pregunta}
                            onCancel={() => {
                                setModalData(null);
                                setEditingQuestionIndex(null);
                            }}
                            onSave={(datos) => handleSaveQuestion(datos)}
                        />
                    </div>
                </div>
            )}
        </>
    );
}

// Formulario “PreguntarForm” dentro de QuestionEditor
function PreguntaForm({ pregunta, onCancel, onSave }) {
    const [texto, setTexto] = useState(pregunta.texto_pregunta || "");
    const [tipo, setTipo] = useState(pregunta.id_tipo_pregunta || "");
    const [obligatoria, setObligatoria] = useState(
        pregunta.es_obligatoria || false
    );
    const [minNum, setMinNum] = useState(pregunta.numero_minimo || "");
    const [maxNum, setMaxNum] = useState(pregunta.numero_maximo || "");
    const [fechaMin, setFechaMin] = useState(pregunta.fecha_minima || "");
    const [fechaMax, setFechaMax] = useState(pregunta.fecha_maxima || "");
    const [horaMin, setHoraMin] = useState(pregunta.hora_minima || "");
    const [horaMax, setHoraMax] = useState(pregunta.hora_maxima || "");
    const [textoAyuda, setTextoAyuda] = useState(pregunta.texto_ayuda || "");
    const [preguntaPadre, setPreguntaPadre] = useState(
        pregunta.id_pregunta_padre || ""
    );
    const [valorCondicion, setValorCondicion] = useState(
        pregunta.valor_condicion_padre || ""
    );
    const [preguntaCondPadre, setPreguntaCondPadre] = useState(
        pregunta.id_opcion_condicion_padre || ""
    );

    const [opciones, setOpciones] = useState(pregunta.opciones || []);
    const [entidades, setEntidades] = useState([]);
    const [campos, setCampos] = useState([]);
    const [mapeoEntidad, setMapeoEntidad] = useState(
        pregunta.mapeo?.entidad_id || ""
    );
    const [mapeoCampo, setMapeoCampo] = useState(
        pregunta.mapeo?.campo_id || ""
    );

    // Al montar, traemos entidades externas
    React.useEffect(() => {
        fetchEntidadesExternas().then((data) => setEntidades(data));
    }, []);

    // Cuando cambie la entidad seleccionada, traemos sus campos
    React.useEffect(() => {
        if (mapeoEntidad) {
            fetchCamposParaEntidad(mapeoEntidad).then((data) =>
                setCampos(data)
            );
        } else {
            setCampos([]);
        }
    }, [mapeoEntidad]);

    // Cambiar el tipo de pregunta:
    const handleTipoChange = (e) => {
        const val = e.target.value;
        setTipo(val);
        // Si el tipo no es de “opción”, limpian las opciones previas
        if (
            Number(val) !== 5 && // Opción múltiple (única)
            Number(val) !== 6 && // Selección múltiple
            Number(val) !== 7 // Lista desplegable
        ) {
            setOpciones([]);
        }
    };

    // Añadir opción a “opciones” (sólo para tipos que requieran opciones)
    const añadirOpcion = () => {
        setOpciones([
            ...opciones,
            { texto_opcion: "", valor_opcion: "", id_opcion_pregunta: null },
        ]);
    };
    const removerOpcion = (idx) => {
        const arr = [...opciones];
        arr.splice(idx, 1);
        setOpciones(arr);
    };
    const cambiarOpcion = (idx, campo, valor) => {
        const arr = [...opciones];
        arr[idx][campo] = valor;
        setOpciones(arr);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        onSave({
            texto_pregunta: texto,
            id_tipo_pregunta: Number(tipo),
            es_obligatoria: obligatoria,
            numero_minimo: minNum || null,
            numero_maximo: maxNum || null,
            fecha_minima: fechaMin || null,
            fecha_maxima: fechaMax || null,
            hora_minima: horaMin || null,
            hora_maxima: horaMax || null,
            texto_ayuda: textoAyuda,
            id_pregunta_padre: preguntaPadre || null,
            valor_condicion_padre: valorCondicion || null,
            id_opcion_condicion_padre: preguntaCondPadre || null,
            opciones,
            mapeo: { entidad_id: mapeoEntidad, campo_id: mapeoCampo },
        });
    };

    return (
        <form onSubmit={handleSubmit}>
            <label>
                Texto de la pregunta:
                <textarea
                    value={texto}
                    onChange={(e) => setTexto(e.target.value)}
                    rows="2"
                    required
                />
            </label>
            <label>
                Tipo de Pregunta:
                <select value={tipo} onChange={handleTipoChange} required>
                    <option value="">-- Selecciona --</option>
                    <option value="1">Valoración</option>
                    <option value="2">Valor Numérico</option>
                    <option value="3">Texto Corto</option>
                    <option value="4">Texto Largo</option>
                    <option value="5">Opción Múltiple (única)</option>
                    <option value="6">Selección Múltiple (varias)</option>
                    <option value="7">Lista Desplegable (única)</option>
                    <option value="8">Fecha</option>
                    <option value="9">Hora</option>
                    <option value="10">Booleano (Sí/No)</option>
                </select>
            </label>
            <label>
                <input
                    type="checkbox"
                    checked={obligatoria}
                    onChange={(e) => setObligatoria(e.target.checked)}
                />
                Obligatoria
            </label>

            {/* Si tipo es numérico o valoración, mostrar rango */}
            {(tipo === "1" || tipo === "2") && (
                <>
                    <label>
                        Mínimo:
                        <input
                            type="number"
                            value={minNum}
                            onChange={(e) => setMinNum(e.target.value)}
                        />
                    </label>
                    <label>
                        Máximo:
                        <input
                            type="number"
                            value={maxNum}
                            onChange={(e) => setMaxNum(e.target.value)}
                        />
                    </label>
                </>
            )}

            {/* Si tipo es fecha, mostrar rango de fechas */}
            {tipo === "8" && (
                <>
                    <label>
                        Fecha Mínima:
                        <input
                            type="date"
                            value={fechaMin}
                            onChange={(e) => setFechaMin(e.target.value)}
                        />
                    </label>
                    <label>
                        Fecha Máxima:
                        <input
                            type="date"
                            value={fechaMax}
                            onChange={(e) => setFechaMax(e.target.value)}
                        />
                    </label>
                </>
            )}

            {/* Si tipo es hora, mostrar rango de horas */}
            {tipo === "9" && (
                <>
                    <label>
                        Hora Mínima:
                        <input
                            type="time"
                            value={horaMin}
                            onChange={(e) => setHoraMin(e.target.value)}
                        />
                    </label>
                    <label>
                        Hora Máxima:
                        <input
                            type="time"
                            value={horaMax}
                            onChange={(e) => setHoraMax(e.target.value)}
                        />
                    </label>
                </>
            )}

            {/* Si tipo es condicional, permitir vincular a pregunta padre */}
            {(tipo === "5" ||
                tipo === "6" ||
                tipo === "7" ||
                tipo === "8" ||
                tipo === "9" ||
                tipo === "10") &&
                /* asumimos que en seccion.preguntas hay las preguntas padre posibles */
                seccion.preguntas.length > 0 && (
                    <>
                        <label>
                            Pregunta Padre (opcional):
                            <select
                                value={preguntaPadre}
                                onChange={(e) =>
                                    setPreguntaPadre(e.target.value)
                                }
                            >
                                <option value="">(ninguno)</option>
                                {seccion.preguntas.map((p) => (
                                    <option
                                        key={p.id_pregunta}
                                        value={p.id_pregunta}
                                    >
                                        {p.texto_pregunta}
                                    </option>
                                ))}
                            </select>
                        </label>
                        {preguntaPadre && (
                            <label>
                                Valor Condición Padre:
                                <input
                                    type="text"
                                    value={valorCondicion}
                                    onChange={(e) =>
                                        setValorCondicion(e.target.value)
                                    }
                                />
                            </label>
                        )}
                        {preguntaPadre && (
                            <label>
                                Opción Condición Padre:
                                <select
                                    value={preguntaCondPadre}
                                    onChange={(e) =>
                                        setPreguntaCondPadre(e.target.value)
                                    }
                                >
                                    <option value="">(ninguno)</option>
                                    {seccion.preguntas
                                        .find(
                                            (p) =>
                                                p.id_pregunta.toString() ===
                                                preguntaPadre
                                        )
                                        ?.opciones?.map((op) => (
                                            <option
                                                key={op.id_opcion_pregunta}
                                                value={op.id_opcion_pregunta}
                                            >
                                                {op.texto_opcion}
                                            </option>
                                        ))}
                                </select>
                            </label>
                        )}
                    </>
                )}

            <label>
                Texto de Ayuda (opcional):
                <textarea
                    value={textoAyuda}
                    onChange={(e) => setTextoAyuda(e.target.value)}
                    rows="2"
                />
            </label>

            {/* Si el tipo requiere opciones, rendear OptionEditor */}
            {(tipo === "5" || tipo === "6" || tipo === "7") && (
                <div className="option-list">
                    <h4>Opciones</h4>
                    {opciones.map((op, idx) => (
                        <OptionEditor
                            key={idx}
                            option={op}
                            onChange={(newVal) =>
                                cambiarOpcion(idx, "texto_opcion", newVal)
                            }
                            onRemove={() => removerOpcion(idx)}
                        />
                    ))}
                    <button type="button" onClick={añadirOpcion}>
                        + Añadir opción
                    </button>
                </div>
            )}

            {/* Mapeo a entidad/campo externo */}
            <fieldset style={{ marginTop: "var(--space-md)" }}>
                <legend>Mapeo Externo (ERP)</legend>
                <label>
                    Entidad:
                    <select
                        value={mapeoEntidad}
                        onChange={(e) => setMapeoEntidad(e.target.value)}
                    >
                        <option value="">(ninguna)</option>
                        {entidades.map((en) => (
                            <option key={en.id_entidad} value={en.id_entidad}>
                                {en.nombre_entidad}
                            </option>
                        ))}
                    </select>
                </label>
                {mapeoEntidad && (
                    <label>
                        Campo:
                        <select
                            value={mapeoCampo}
                            onChange={(e) => setMapeoCampo(e.target.value)}
                        >
                            <option value="">(ninguno)</option>
                            {campos.map((c) => (
                                <option key={c.id_campo} value={c.id_campo}>
                                    {c.nombre_campo}
                                </option>
                            ))}
                        </select>
                    </label>
                )}
            </fieldset>

            <div className="modal-actions">
                <button type="button" className="btn-cancel" onClick={onCancel}>
                    Cancelar
                </button>
                <button type="submit" className="btn-save">
                    Guardar
                </button>
            </div>
        </form>
    );
}
