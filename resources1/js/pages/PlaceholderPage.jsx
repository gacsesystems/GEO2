import React from "react";
import "./PlaceholderPage.css";

const PlaceholderPage = ({ pageName }) => (
    <div className="placeholder-container">
        <h1 className="placeholder-title">
            {pageName || "Página en Construcción"}
        </h1>
        <p className="placeholder-text">
            Este contenido estará disponible pronto.
        </p>
    </div>
);

export default PlaceholderPage;
