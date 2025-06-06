import React, { useState, useEffect } from "react";
import { tipoPreguntaService } from "../../services/tipoPreguntaService";
import { encuestaService } from "../../services/encuestaService"; // Para obtener preguntas padre
// OpcionesPreguntaEditor se usa dentro de DiseñadorEncuestaPage, no directamente aquí por ahora para simplificar.
// Si quieres que se muestre DENTRO de este formulario, habría que pasarle más props.
import "./PreguntaForm.css"; // Asegúrate que exista y tenga los estilos

const PreguntaForm = ({
    // --- Estado de los campos (manejado por el padre: DiseñadorEncuestaPage) ---
    textoPregunta,
    setTextoPregunta,
    idTipoPregunta,
    setIdTipoPregunta,
    esObligatoria,
    setEsObligatoria,
    // Campos para tipos específicos
    numeroMinimo,
    setNumeroMinimo,
    numeroMaximo,
    setNumeroMaximo,
    fechaMinima,
    setFechaMinima,
    fechaMaxima,
    setFechaMaxima,
    horaMinima,
    setHoraMinima,
    horaMaxima,
    setHoraMaxima,
    // Campos condicionales
    idPreguntaPadre,
    setIdPreguntaPadre,
    valorCondicionPadre,
    setValorCondicionPadre,
    idOpcionCondicionPadre,
    setIdOpcionCondicionPadre,

    // --- Datos para selects ---
    tiposPregunta, // Cargado en el padre
    preguntasCandidatasPadre, // Cargado en el padre
    opcionesPreguntaPadre, // Cargado en el padre, basado en idPreguntaPadre

    // --- Estado de la UI y errores ---
    esEdicion,
    isLoading,
    formErrorLocal,
    apiError,
    preguntaInicial, // Para determinar qué mostrar
}) => {
    const tipoSeleccionado = tiposPregunta.find(
        (tp) => String(tp.id_tipo_pregunta) === idTipoPregunta
    );
    const preguntaPadreSeleccionada = preguntasCandidatasPadre.find(
        (p) => String(p.id_pregunta) === idPreguntaPadre
    );

    // El useEffect para cargar tiposPregunta, preguntasCandidatasPadre,
    // y opcionesPreguntaPadre se maneja en DiseñadorEncuestaPage.
    // Lo mismo para el useEffect que setea los valores iniciales.

    return (
        <>
            {" "}
            {/* No hay <form> tag aquí */}
            {(formErrorLocal || apiError) && (
                <p className="error-message form-error">
                    {formErrorLocal || apiError}
                </p>
            )}
            <div className="form-grid">
                <div className="form-group span-2">
                    <label htmlFor="textoPreguntaForm">
                        Texto de la Pregunta
                    </label>
                    <textarea
                        id="textoPreguntaForm"
                        name="texto_pregunta"
                        value={textoPregunta}
                        onChange={(e) => setTextoPregunta(e.target.value)}
                        required
                        disabled={isLoading}
                        rows="2"
                    />
                </div>
                <div className="form-group">
                    <label htmlFor="idTipoPreguntaForm">Tipo de Pregunta</label>
                    <select
                        id="idTipoPreguntaForm"
                        name="id_tipo_pregunta"
                        value={idTipoPregunta}
                        onChange={(e) => setIdTipoPregunta(e.target.value)}
                        required
                        disabled={isLoading}
                    >
                        <option value="">Seleccione un tipo...</option>
                        {tiposPregunta.map((tipo) => (
                            <option
                                key={tipo.id_tipo_pregunta}
                                value={String(tipo.id_tipo_pregunta)}
                            >
                                {tipo.nombre}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="form-group checkbox-group align-self-end">
                    <input
                        type="checkbox"
                        id="esObligatoriaForm"
                        name="es_obligatoria"
                        checked={esObligatoria}
                        onChange={(e) => setEsObligatoria(e.target.checked)}
                        disabled={isLoading}
                    />
                    <label
                        htmlFor="esObligatoriaForm"
                        className="checkbox-label"
                    >
                        Es obligatoria
                    </label>
                </div>

                {/* Campos condicionales basados en el tipo de pregunta */}
                {tipoSeleccionado?.permite_min_max_numerico && (
                    <>
                        <div className="form-group">
                            <label htmlFor="numeroMinimoForm">
                                Número Mínimo
                            </label>
                            <input
                                type="number"
                                id="numeroMinimoForm"
                                name="numero_minimo"
                                value={numeroMinimo}
                                onChange={(e) =>
                                    setNumeroMinimo(e.target.value)
                                }
                                disabled={isLoading}
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="numeroMaximoForm">
                                Número Máximo
                            </label>
                            <input
                                type="number"
                                id="numeroMaximoForm"
                                name="numero_maximo"
                                value={numeroMaximo}
                                onChange={(e) =>
                                    setNumeroMaximo(e.target.value)
                                }
                                disabled={isLoading}
                            />
                        </div>
                    </>
                )}
                {tipoSeleccionado?.permite_min_max_fecha && (
                    <>
                        <div className="form-group">
                            <label htmlFor="fechaMinimaForm">
                                Fecha Mínima
                            </label>
                            <input
                                type="date"
                                id="fechaMinimaForm"
                                name="fecha_minima"
                                value={fechaMinima}
                                onChange={(e) => setFechaMinima(e.target.value)}
                                disabled={isLoading}
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="fechaMaximaForm">
                                Fecha Máxima
                            </label>
                            <input
                                type="date"
                                id="fechaMaximaForm"
                                name="fecha_maxima"
                                value={fechaMaxima}
                                onChange={(e) => setFechaMaxima(e.target.value)}
                                disabled={isLoading}
                            />
                        </div>
                    </>
                )}
                {/* Podrías añadir Hora aquí similar a Fecha si es necesario para algún tipo específico */}

                {/* Lógica condicional de Pregunta Padre */}
                <div className="form-group span-2 conditional-logic-group">
                    <label className="group-title-label">
                        Lógica Condicional (Opcional)
                    </label>
                    <div className="form-grid nested-grid">
                        <div className="form-group">
                            <label htmlFor="idPreguntaPadreForm">
                                Mostrar si la pregunta:
                            </label>
                            <select
                                id="idPreguntaPadreForm"
                                name="id_pregunta_padre"
                                value={idPreguntaPadre}
                                onChange={(e) => {
                                    setIdPreguntaPadre(e.target.value);
                                    setIdOpcionCondicionPadre(""); // Resetear dependientes
                                    setValorCondicionPadre("");
                                }}
                                disabled={isLoading}
                            >
                                <option value="">
                                    Ninguna (siempre visible)
                                </option>
                                {preguntasCandidatasPadre.map((p) => (
                                    <option
                                        key={p.id_pregunta}
                                        value={String(p.id_pregunta)}
                                    >
                                        #{p.orden}.{" "}
                                        {p.texto_pregunta.substring(0, 50)}
                                        {p.texto_pregunta.length > 50
                                            ? "..."
                                            : ""}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {idPreguntaPadre &&
                            preguntaPadreSeleccionada?.tipo_pregunta_info
                                ?.requiere_opciones && (
                                <div className="form-group">
                                    <label htmlFor="idOpcionCondicionPadreForm">
                                        ...tiene la opción seleccionada:
                                    </label>
                                    <select
                                        id="idOpcionCondicionPadreForm"
                                        name="id_opcion_condicion_padre"
                                        value={idOpcionCondicionPadre}
                                        onChange={(e) =>
                                            setIdOpcionCondicionPadre(
                                                e.target.value
                                            )
                                        }
                                        disabled={
                                            isLoading ||
                                            opcionesPreguntaPadre.length === 0
                                        }
                                    >
                                        <option value="">
                                            Seleccione una opción del padre...
                                        </option>
                                        {opcionesPreguntaPadre.map((op) => (
                                            <option
                                                key={op.id_opcion_pregunta}
                                                value={String(
                                                    op.id_opcion_pregunta
                                                )}
                                            >
                                                {op.texto_opcion}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            )}
                        {idPreguntaPadre &&
                            !preguntaPadreSeleccionada?.tipo_pregunta_info
                                ?.requiere_opciones && (
                                <div className="form-group">
                                    <label htmlFor="valorCondicionPadreForm">
                                        ...tiene el valor (texto/número):
                                    </label>
                                    <input
                                        type="text"
                                        id="valorCondicionPadreForm"
                                        name="valor_condicion_padre"
                                        value={valorCondicionPadre}
                                        onChange={(e) =>
                                            setValorCondicionPadre(
                                                e.target.value
                                            )
                                        }
                                        disabled={isLoading}
                                        placeholder="Ej: Sí, o un número"
                                    />
                                </div>
                            )}
                    </div>
                </div>
                {/* El editor de opciones para ESTA pregunta se manejará en un modal separado
                    o en otra sección del DiseñadorEncuestaPage. */}
            </div>
        </>
    );
};

export default PreguntaForm;
