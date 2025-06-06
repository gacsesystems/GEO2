import React from "react";
import "../css/app.css";
import Navbar from "./components/layout/Navbar";
import AppRoutes from "./AppRoutes";

export default function App() {
    return (
        <>
            <Navbar />
            <main className="container">
                <AppRoutes />
            </main>
            {/* TODO: Opcional: <Footer /> */}
        </>
    );
}
//Todo: Agregar un footer
