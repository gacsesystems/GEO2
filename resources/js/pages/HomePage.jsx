import React from "react";
import { useAuth } from "../contexts/AuthContext";
import { Link, Navigate } from "react-router-dom";
import "./HomePage.css"; // Crearemos este archivo CSS

const HomePage = () => {
    const { user, isAuthenticated, isLoading } = useAuth();

    if (isLoading) {
        return (
            <div className="loading-fullscreen">
                {" "}
                {/* Reutiliza tu clase de loader global */}
                Cargando...
            </div>
        );
    }

    // Si está autenticado, redirigir al dashboard correspondiente
    if (isAuthenticated && user) {
        if (user.rol === "Administrador") {
            return <Navigate to="/admin/dashboard" replace />;
        } else if (user.rol === "Cliente") {
            return <Navigate to="/cliente/dashboard" replace />;
        }
        // Si hay otros roles, o un usuario sin rol asignado que esté autenticado
        // podrías redirigir a una página por defecto o mostrar un mensaje.
        // Por ahora, si no es Admin o Cliente, se quedará en esta "Home" (que no debería pasar mucho)
        // o podrías redirigir a /login si es un estado inesperado.
        // return <Navigate to="/default-dashboard" replace />;
    }

    // Contenido para usuarios NO autenticados (Landing Page)
    return (
        <div className="home-page-container">
            <header className="home-header">
                {/* Podrías poner tu logo aquí si no tienes un Navbar visible para no autenticados */}
                {/* <img src="/img/logo.png" alt="GEO Encuestas Logo" className="home-logo" /> */}
                <h1>Bienvenido a GEO Encuestas</h1>
                <p className="home-subtitle">
                    La plataforma líder para la creación, gestión y análisis de
                    encuestas de manera eficiente y personalizada.
                </p>
            </header>

            <section className="home-features">
                <div className="feature-item">
                    {/* <IconoCrear /> Reemplaza con un icono SVG */}
                    <h3>Crea Fácilmente</h3>
                    <p>
                        Diseña encuestas intuitivas con múltiples tipos de
                        pregunta y lógica condicional.
                    </p>
                </div>
                <div className="feature-item">
                    {/* <IconoGestionar /> */}
                    <h3>Gestiona Centralizadamente</h3>
                    <p>
                        Organiza todas tus encuestas y respuestas en un solo
                        lugar.
                    </p>
                </div>
                <div className="feature-item">
                    {/* <IconoAnalizar /> */}
                    <h3>Analiza Resultados</h3>
                    <p>
                        Obtén información valiosa con reportes detallados y
                        visualizaciones.
                    </p>
                </div>
            </section>

            <footer className="home-actions">
                <p>¿Listo para empezar?</p>
                <Link to="/login" className="home-button login">
                    Iniciar Sesión
                </Link>
                <Link to="/register" className="home-button register">
                    Registrarse
                </Link>
                {/* <p className="contact-info">¿Necesitas ayuda? <a href="mailto:soporte@geoencuestas.com">Contáctanos</a></p> */}
            </footer>
        </div>
    );
};
export default HomePage;
