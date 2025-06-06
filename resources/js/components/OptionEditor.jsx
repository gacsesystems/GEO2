import React from "react";

export default function OptionEditor({ option, onChange, onRemove }) {
    return (
        <div className="option-item">
            <input
                type="text"
                placeholder="Texto de opción"
                value={option.texto_opcion}
                onChange={(e) => onChange(e.target.value)}
                required
            />
            <button type="button" onClick={onRemove}>
                🗑
            </button>
        </div>
    );
}
