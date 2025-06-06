import React, { useState, useEffect } from "react";
import { NavLink, Link, useNavigate } from "react-router-dom";
import { useAuth } from "../../contexts/AuthContext";
import { useCurrentLogo } from "../../hooks/useCurrentLogo";

// Importa los iconos SVG (pueden ser componentes o <img> según tu implementación)
import {
    UsersIcon,
    ClientsIcon,
    SurveyIcon,
    LogoutIcon,
    AlertIcon,
    HomeIcon,
} from "../ui/Icons";

import "./Navbar.css"; // Tus estilos personalizados

export default function Navbar() {
    const { user, isAuthenticated, logout, loading } = useAuth();
    const navigate = useNavigate();
    const LogoActual = useCurrentLogo();

    // Si aún se está verificando el estado de autenticación, no renderizamos nada
    if (loading) return null;

    // Si no hay usuario autenticado, ocultamos el navbar
    if (!isAuthenticated || !user) return null;

    const isAdmin = user.rol === "Administrador",
        isCliente = user.rol === "Cliente";

    const handleLogout = async () => {
        try {
            await logout();
            navigate("/login", { replace: true });
        } catch (error) {
            console.error("Error al cerrar sesión:", error);
            //Todo: Puedes mostrar toast o mensaje de error aquí si lo deseas
        }
    };

    return (
        <>
            <nav className="main-navbar">
                <div className="navbar-brand">
                    <Link
                        to={isAdmin ? "/admin/dashboard" : "/cliente/dashboard"}
                        className="navbar-logo"
                    >
                        <img
                            src={LogoActual}
                            alt="Logo GEO Encuestas"
                            className="navbar-logo-image"
                        />
                        <span className="navbar-logo-text">GEO</span>
                    </Link>
                </div>

                <ul className="navbar-links">
                    {/* === Enlaces para Administrador === */}
                    {isAdmin && (
                        <>
                            <li>
                                <NavLink
                                    to="/admin/dashboard"
                                    className={({ isActive }) =>
                                        isActive ? "active-link" : undefined
                                    }
                                >
                                    Dashboard
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    to="/admin/clientes"
                                    className={({ isActive }) =>
                                        isActive ? "active-link" : undefined
                                    }
                                >
                                    <ClientsIcon className="icon-inline" />{" "}
                                    Clientes
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    to="/admin/usuarios"
                                    className={({ isActive }) =>
                                        isActive ? "active-link" : undefined
                                    }
                                >
                                    <UsersIcon className="icon-inline" />{" "}
                                    Usuarios
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    to="/admin/encuestas"
                                    className={({ isActive }) =>
                                        isActive ? "active-link" : undefined
                                    }
                                >
                                    <SurveyIcon className="icon-inline" />{" "}
                                    Encuestas
                                </NavLink>
                            </li>
                            {/* Añade aquí más enlaces de admin si gustas */}
                        </>
                    )}

                    {isCliente && (
                        <>
                            <li>
                                <NavLink
                                    to="/cliente/dashboard"
                                    className={({ isActive }) =>
                                        isActive ? "active-link" : undefined
                                    }
                                >
                                    Dashboard
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    to="/cliente/encuestas"
                                    className={({ isActive }) =>
                                        isActive ? "active-link" : undefined
                                    }
                                >
                                    <SurveyIcon className="icon-inline" /> Mis
                                    Encuestas
                                </NavLink>
                            </li>
                            {/* Si luego agregas “Nueva Encuesta” u otras rutas de cliente, van aquí */}
                        </>
                    )}
                </ul>

                <div className="navbar-user-section">
                    <span className="user-greeting">
                        Hola, {user.nombre_completo || user.email}
                    </span>
                    <button onClick={handleLogout} className="logout-button">
                        <LogoutIcon className="icon-inline" />
                        Cerrar Sesión
                    </button>
                </div>
            </nav>

            {/* Alerta de correo no verificado (solo si el usuario está autenticado) */}
            {isAuthenticated && user && !user.email_verified_at && (
                <div className="email-verification-alert">
                    <AlertIcon className="icon-inline" />
                    <span>Tu correo electrónico no ha sido verificado.</span>
                    <Link
                        to="/verifica-tu-correo"
                        className="verify-email-link"
                    >
                        Verifica ahora
                    </Link>
                </div>
            )}
        </>
    );
}
