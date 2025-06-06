import React from "react";

export default function Button({
    onClick,
    children,
    disabled = false,
    type = "button",
}) {
    return (
        <button
            onClick={onClick}
            disabled={disabled}
            type={type}
            className="btn"
        >
            {children}
        </button>
    );
}
