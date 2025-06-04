import React from "react";
import { createRoot } from "react-dom/client";
import "../css/app.css";

function App() {
    return (
        <h1 className="text-3xl font-bold text-purple-700">
            ¡Hola, GeoEncuestas!
        </h1>
    );
}

const container = document.getElementById("app");
createRoot(container).render(<App />);
