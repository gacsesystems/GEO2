import React from "react";
import "../css/encuesta-designer.css";
import "../css/app.css"; // Estilos globales
import { createRoot } from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import { AuthProvider } from "./contexts/AuthContext";
import App from "./app";
import "./bootstrap";

const container = document.getElementById("app");
const root = createRoot(container);

root.render(
    <React.StrictMode>
        <BrowserRouter>
            <AuthProvider>
                <App />
            </AuthProvider>
        </BrowserRouter>
    </React.StrictMode>
);
