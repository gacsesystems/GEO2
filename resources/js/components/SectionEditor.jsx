import React, { useState, useRef } from "react";
import QuestionEditor from "./QuestionEditor";
import {
    crearSeccion,
    actualizarSeccion,
    eliminarSeccion,
    reordenarSeccion,
} from "../services/encuestaService";

export default function SectionEditor({
    encuestaId,
    section,
    index,
    totalSections,
    onUpdateSection,
    onDeleteSection,
    onDragStart,
    onDragOver,
    onDrop,
}) {
    const [isEditing, setIsEditing] = useState(false);
    const [title, setTitle] = useState(section.titulo || "");
    const dragRef = useRef(null);

    /* Inicia edición de título */
    const handleEditClick = () => {
        setTitle(section.titulo);
        setIsEditing(true);
    };

    /* Guarda cambios de título */
    const handleSave = async () => {
        const payload = { nombre: title }; // tu API espera “nombre” o “titulo”
        if (section.id_seccion) {
            await actualizarSeccion(encuestaId, section.id_seccion, payload);
        } else {
            // sección nueva: creamos
            const res = await crearSeccion(encuestaId, payload);
            // el backend debe devolver la sección creada con su id_seccion
            onUpdateSection(index, { ...section, ...res });
            setIsEditing(false);
            return;
        }
        onUpdateSection(index, { ...section, titulo: title });
        setIsEditing(false);
    };

    /* Eliminar sección */
    const handleDelete = async () => {
        if (
            section.id_seccion &&
            confirm("¿Seguro que quieres eliminar esta sección?")
        ) {
            await eliminarSeccion(encuestaId, section.id_seccion);
            onDeleteSection(index);
        }
    };

    /* Mover arriba */
    const moveUp = () => {
        if (index === 0) return;
        reordenarSeccion(encuestaId, section.id_seccion, index); // suponemos que el índice nuevo será index (el backend reordena)
        onUpdateSection(index, null, index - 1);
    };
    /* Mover abajo */
    const moveDown = () => {
        if (index === totalSections - 1) return;
        reordenarSeccion(encuestaId, section.id_seccion, index + 2);
        onUpdateSection(index, null, index + 1);
    };

    return (
        <div
            className={`section-item`}
            draggable
            ref={dragRef}
            onDragStart={(e) => onDragStart(e, index)}
            onDragOver={(e) => onDragOver(e, index)}
            onDrop={(e) => onDrop(e, index)}
        >
            <div className="section-item__header">
                {isEditing ? (
                    <input
                        type="text"
                        value={title}
                        onChange={(e) => setTitle(e.target.value)}
                        className="section-item__title-input"
                        placeholder="Nombre de la sección"
                    />
                ) : (
                    <span className="section-item__title">
                        {section.titulo}
                    </span>
                )}
                <div className="section-item__actions">
                    {isEditing ? (
                        <>
                            <button onClick={() => setIsEditing(false)}>
                                ✖
                            </button>
                            <button onClick={handleSave}>✔</button>
                        </>
                    ) : (
                        <>
                            <button onClick={handleEditClick}>✎</button>
                            <button onClick={handleDelete}>🗑</button>
                            <button
                                onClick={moveUp}
                                disabled={index === 0}
                                title="Subir"
                                style={{ opacity: index === 0 ? 0.3 : 1 }}
                            >
                                ↑
                            </button>
                            <button
                                onClick={moveDown}
                                disabled={index === totalSections - 1}
                                title="Bajar"
                                style={{
                                    opacity:
                                        index === totalSections - 1 ? 0.3 : 1,
                                }}
                            >
                                ↓
                            </button>
                        </>
                    )}
                </div>
            </div>

            {/* Lista de preguntas */}
            <QuestionEditor
                encuestaId={encuestaId}
                seccion={section}
                onQuestionsChange={(nuevasPreguntas) =>
                    onUpdateSection(index, {
                        ...section,
                        preguntas: nuevasPreguntas,
                    })
                }
            />
        </div>
    );
}
