import React from "react";
import Navbar from "./components/layout/Navbar"; // Importa tu Navbar
import AppRoutes from "./AppRoutes"; // Importa tus rutas (anteriormente Menu.jsx)
import { useAuth } from "./contexts/AuthContext"; // Para el loader principal

function App() {
    const { isLoading } = useAuth(); // Solo para el loader inicial de la app

    // El loader de AuthContext ya maneja la carga inicial antes de que se renderice App
    // Pero si quieres un loader general aquí también, puedes usar `isLoading` de AuthContext.
    // Sin embargo, el Navbar y AppRoutes ya reciben el estado de isLoading de useAuth
    // y pueden manejar su propia lógica de carga si es necesario.
    // Por lo general, el loader en AuthProvider y el que tienes en AppRoutes
    // son suficientes.

    // El loader principal ahora está en AuthProvider y en AppRoutes (anteriormente Menu)
    // Si el estado de carga de AuthContext ya está resuelto, podemos renderizar.

    return (
        <div className="app-container">
            {" "}
            {/* Un div contenedor general opcional */}
            <Navbar />
            <main className="main-content">
                {" "}
                {/* Para aplicar padding y evitar que el contenido quede debajo del navbar fijo */}
                <AppRoutes />
            </main>
            {/* Aquí podrías tener un Footer si lo necesitas */}
        </div>
    );
}

export default App;
