import "./bootstrap";
import React from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import { AuthProvider } from "./contexts/AuthContext";
import App from "./app";
import "../css/app.css";

createRoot(document.getElementById("app")).render(
    // <React.StrictMode>
    <BrowserRouter>
        <AuthProvider>
            <App />
        </AuthProvider>
    </BrowserRouter>
    // </React.StrictMode>
);
