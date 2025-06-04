import React from "react";
import "./Modal.css";

const CloseIcon = () => (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        strokeWidth={1.5}
        stroke="currentColor"
        className="modal-close-icon-svg"
    >
        <path
            strokeLinecap="round"
            strokeLinejoin="round"
            d="M6 18L18 6M6 6l12 12"
        />
    </svg>
);

const Modal = ({ isOpen, onClose, children, title }) => {
    if (!isOpen) return null;

    return (
        <div className="modal-overlay" onClick={onClose}>
            <div className="modal-content" onClick={(e) => e.stopPropagation()}>
                <button
                    className="modal-close-button"
                    onClick={onClose}
                    aria-label="Cerrar modal"
                >
                    <CloseIcon />
                </button>
                {title && <h2 className="modal-title">{title}</h2>}
                <div className="modal-body">{children}</div>
            </div>
        </div>
    );
};

export default Modal;
