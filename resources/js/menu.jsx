import React from "react";
import { Routes, Route, Navigate } from "react-router-dom";
import { useAuth } from "./contexts/AuthContext";

import LoginPage from "./pages/Auth/LoginPage";
// import RegisterPage from "./pages/Auth/RegisterPage"; // Crear después

import AdminDashboardPage from "./pages/Admin/AdminDashboardPage";
import GestionClientesPage from "./pages/Admin/GestionClientesPage";
// import GestionUsuariosPage from "./pages/Admin/GestionUsuariosPage"; // Crear después

// import ClienteDashboardPage from "./pages/Cliente/ClienteDashboardPage"; // Crear después
// import GestionEncuestasPage from "./pages/Cliente/GestionEncuestasPage"; // Crear después

// import EncuestaPublicaPage from "./pages/Public/EncuestaPublicaPage"; // Crear después
// import HomePage from "./pages/HomePage"; // Crear después
// import NotFoundPage from "./pages/NotFoundPage"; // Crear después
import ProtectedRoute from "./components/common/ProtectedRoute";
import PlaceholderPage from "./pages/PlaceholderPage"; // Para páginas no creadas

function Menu() {
    const { isAuthenticated, user, isLoading } = useAuth();

    if (isLoading) {
        return (
            <div className="app-loading-screen">
                {" "}
                {/* Clase para estilizar el loader */}
                <div className="spinner"></div>
                <p>Cargando aplicación...</p>
            </div>
        );
    }

    const getRedirectPath = () => {
        if (!isAuthenticated) return "/login";
        return user?.rol === "Administrador"
            ? "/admin/dashboard"
            : "/cliente/dashboard";
    };

    return (
        <Routes>
            <Route
                path="/login"
                element={
                    isAuthenticated ? (
                        <Navigate to={getRedirectPath()} replace />
                    ) : (
                        <LoginPage />
                    )
                }
            />
            <Route
                path="/register"
                element={<PlaceholderPage pageName="Registro" />}
            />{" "}
            {/* Placeholder */}
            <Route
                path="/"
                element={
                    isAuthenticated ? (
                        <Navigate to="/dashboard" replace />
                    ) : (
                        <Navigate to="/login" replace />
                    )
                }
            />
            <Route element={<ProtectedRoute />}>
                <Route
                    path="/dashboard"
                    element={
                        user?.rol === "Administrador" ? (
                            <Navigate to="/admin/dashboard" replace />
                        ) : user?.rol === "Cliente" ? (
                            <Navigate to="/cliente/dashboard" replace />
                        ) : (
                            <Navigate to="/login" replace />
                        )
                    }
                />
            </Route>
            <Route
                element={<ProtectedRoute rolesPermitidos={["Administrador"]} />}
            >
                <Route
                    path="/admin/dashboard"
                    element={<AdminDashboardPage />}
                />
                <Route
                    path="/admin/clientes"
                    element={<GestionClientesPage />}
                />
                <Route
                    path="/admin/usuarios"
                    element={
                        <PlaceholderPage pageName="Gestión de Usuarios (Admin)" />
                    }
                />
            </Route>
            <Route element={<ProtectedRoute rolesPermitidos={["Cliente"]} />}>
                <Route
                    path="/cliente/dashboard"
                    element={<PlaceholderPage pageName="Dashboard Cliente" />}
                />
                <Route
                    path="/cliente/encuestas"
                    element={
                        <PlaceholderPage pageName="Gestión de Encuestas (Cliente)" />
                    }
                />
                <Route
                    path="/cliente/encuestas/:idEncuesta/disenar"
                    element={
                        <PlaceholderPage pageName="Diseñador de Encuesta" />
                    }
                />
                <Route
                    path="/cliente/encuestas/:idEncuesta/reportes"
                    element={
                        <PlaceholderPage pageName="Reportes de Encuesta" />
                    }
                />
            </Route>
            <Route
                path="*"
                element={
                    <PlaceholderPage pageName="404 - Página no Encontrada" />
                }
            />
        </Routes>
    );
}

export default Menu;
