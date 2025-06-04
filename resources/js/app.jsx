import "./bootstrap";
import React from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import { AuthProvider } from "./contexts/AuthContext";
import Menu from "./menu";
import "../css/app.css"; // IMPORTANTE: Tu CSS global

createRoot(document.getElementById("app")).render(
    // <React.StrictMode>
    <BrowserRouter>
        <AuthProvider>
            <Menu />
        </AuthProvider>
    </BrowserRouter>
    // </React.StrictMode>
);
