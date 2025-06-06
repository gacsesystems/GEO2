import React from "react";
import { NavLink, Link, useNavigate } from "react-router-dom";
import { useAuth } from "../../contexts/AuthContext";
import "./Navbar.css";
import {
    UsersIcon,
    ClientsIcon,
    SurveyIcon,
    LogoutIcon,
    AlertIcon,
    HomeIcon,
} from "../ui/Icons";

function Navbar() {
    const { isAuthenticated, user, logout } = useAuth();
    const navigate = useNavigate();

    const handleLogout = async () => {
        try {
            await logout();
            navigate("/login"); // Redirige a login después del logout
        } catch (error) {
            console.error("Error al cerrar sesión:", error);
            // Manejar error de logout si es necesario
        }
    };

    if (!isAuthenticated) {
        return null; // No mostrar navbar si no está autenticado (o mostrar una versión para login/register)
    }

    const isAdmin = user?.rol === "Administrador";
    const isCliente = user?.rol === "Cliente";

    return (
        <>
            <nav className="main-navbar">
                <div className="navbar-brand">
                    <Link
                        to={isAdmin ? "/admin/dashboard" : "/cliente/dashboard"}
                        className="navbar-logo"
                    >
                        {/* Aquí usamos la imagen del logo */}
                        <img
                            src="/img/logo-gsystems.png"
                            alt="AppEncuestas Logo"
                            className="navbar-logo-image"
                        />
                        {/* Opcionalmente, puedes mantener el texto si quieres que aparezca al lado o como fallback */}
                        {/* <span className="navbar-logo-text">AppEncuestas</span> */}
                        <span className="navbar-logo-text">GEO</span>
                    </Link>
                </div>
                <ul className="navbar-links">
                    {isAdmin && (
                        <>
                            <li>
                                <NavLink
                                    to="/admin/dashboard"
                                    className={({ isActive }) =>
                                        isActive ? "active" : ""
                                    }
                                    end
                                >
                                    <HomeIcon /> Dashboard
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    to="/admin/clientes"
                                    className={({ isActive }) =>
                                        isActive ? "active" : ""
                                    }
                                >
                                    <ClientsIcon /> Clientes
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    to="/admin/usuarios"
                                    className={({ isActive }) =>
                                        isActive ? "active" : ""
                                    }
                                >
                                    <UsersIcon /> Usuarios
                                </NavLink>
                            </li>
                        </>
                    )}
                    {isCliente && (
                        <>
                            <li>
                                <NavLink
                                    to="/cliente/dashboard"
                                    className={({ isActive }) =>
                                        isActive ? "active" : ""
                                    }
                                    end
                                >
                                    <HomeIcon /> Dashboard
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    to="/cliente/encuestas"
                                    className={({ isActive }) =>
                                        isActive ? "active" : ""
                                    }
                                >
                                    <SurveyIcon /> Mis Encuestas
                                </NavLink>
                            </li>
                        </>
                    )}
                </ul>
                <div className="navbar-user-section">
                    {user && (
                        <span className="user-greeting">
                            Hola, {user.name || user.email}
                        </span>
                    )}
                    <button onClick={handleLogout} className="logout-button">
                        <LogoutIcon />
                        {/* Opcionalmente: <LogoutIcon className="custom-svg-class" /> */}
                        Cerrar Sesión
                    </button>
                </div>
            </nav>
            {/* Alerta de verificación de correo */}
            {isAuthenticated && user && !user.email_verified_at && (
                <div className="email-verification-alert">
                    <AlertIcon />
                    <span>Tu correo electrónico no ha sido verificado.</span>
                    <Link
                        to="/verifica-tu-correo"
                        className="verify-email-link"
                    >
                        Verifica ahora
                    </Link>
                    {/* Podrías añadir un botón para reenviar el correo aquí */}
                </div>
            )}
        </>
    );
}

export default Navbar;
