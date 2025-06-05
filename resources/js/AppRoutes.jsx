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
import VerificaCorreoPage from "./pages/Auth/VerificaCorreoPage";
import AdminLayout from "./components/layout/AdminLayout";
import GestionUsuariosPage from "./pages/Admin/GestionUsuariosPage";
import DiseñadorEncuestaPage from "./pages/Cliente/DiseñadorEncuestaPage";

function AppRoutes() {
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
            {/* RUTAS PÚBLICAS O QUE MANEJAN AUTENTICACIÓN */}
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
                path="/register" // Asumiendo que tendrás una página de registro eventualmente
                element={
                    isAuthenticated ? (
                        <Navigate to={getRedirectPath()} replace />
                    ) : (
                        <PlaceholderPage pageName="Registro de Usuario" />
                    )
                }
            />

            {/* RUTA RAÍZ */}
            <Route
                path="/"
                element={
                    isAuthenticated ? (
                        <Navigate to="/dashboard" replace /> // Redirige a un dashboard genérico que luego redirige por rol
                    ) : (
                        <Navigate to="/login" replace />
                    )
                }
            />

            {/* RUTA DE DASHBOARD GENÉRICO (PROTEGIDA) */}
            {/* Esta ruta requiere autenticación pero no necesariamente verificación de correo aún,
                ya que su único propósito es redirigir. */}
            <Route element={<ProtectedRoute requireVerifiedEmail={false} />}>
                <Route
                    path="/dashboard"
                    element={
                        user?.rol === "Administrador" ? (
                            <Navigate to="/admin/dashboard" replace />
                        ) : user?.rol === "Cliente" ? (
                            <Navigate to="/cliente/dashboard" replace />
                        ) : (
                            // Si el rol no es reconocido o el usuario es null (aunque no debería serlo aquí)
                            <Navigate to="/login" replace />
                        )
                    }
                />
            </Route>

            {/* --- RUTAS DE ADMINISTRADOR --- */}
            {/* Estas rutas requieren autenticación, rol de Administrador Y correo verificado */}
            <Route element={<AdminLayout />}>
                <Route
                    element={
                        <ProtectedRoute
                            rolesPermitidos={["Administrador"]}
                            requireVerifiedEmail={true} // <--- Protección de correo verificado
                        />
                    }
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
                        element={<GestionUsuariosPage />}
                    />
                    {/* Añade más rutas de admin aquí dentro si todas requieren la misma protección */}
                </Route>
            </Route>

            {/* --- RUTAS DE CLIENTE --- */}
            {/* Estas rutas requieren autenticación, rol de Cliente Y correo verificado */}
            <Route
                element={
                    <ProtectedRoute
                        rolesPermitidos={["Cliente"]}
                        requireVerifiedEmail={true} // <--- Protección de correo verificado
                    />
                }
            >
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
                <Route
                    path="/cliente/encuestas/:idEncuesta/disenar"
                    element={<DiseñadorEncuestaPage />}
                />
                {/* Añade más rutas de cliente aquí dentro */}
            </Route>

            {/* --- RUTA DE VERIFICACIÓN DE CORREO --- */}
            {/* Esta ruta solo requiere autenticación (para saber QUIÉN está intentando reenviar),
                NO requiere que el email ya esté verificado. */}
            <Route element={<ProtectedRoute requireVerifiedEmail={false} />}>
                <Route
                    path="/verifica-tu-correo"
                    element={<VerificaCorreoPage />}
                />
            </Route>

            {/* RUTAS DE RESULTADO DE VERIFICACIÓN (públicas en el sentido de acceso, pero el usuario estará logueado) */}
            {/* Laravel redirigirá aquí. VerificaCorreoPage manejará el mensaje. */}
            <Route
                path="/email-verificado-exitosamente"
                element={<VerificaCorreoPage />}
            />
            <Route
                path="/email-already-verified"
                element={<VerificaCorreoPage />}
            />

            {/* RUTA CATCH-ALL PARA 404 */}
            <Route
                path="*"
                element={
                    <PlaceholderPage pageName="404 - Página no Encontrada" />
                }
            />
        </Routes>
    );
}

export default AppRoutes;
