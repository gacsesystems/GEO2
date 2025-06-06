import React from "react";

export default function InputField({
    label,
    type = "text",
    name,
    value,
    onChange,
    required = false,
}) {
    return (
        <div className="input-group">
            <label htmlFor={name} className="input-label">
                {label}
                {required && <span className="input-required">*</span>}
            </label>
            <input
                id={name}
                name={name}
                type={type}
                value={value}
                onChange={onChange}
                className="input-text"
                required={required}
            />
        </div>
    );
}
