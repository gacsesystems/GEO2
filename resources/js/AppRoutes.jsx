import React from "react";
import { Routes, Route, Navigate, Outlet } from "react-router-dom";
import { useAuth } from "./contexts/AuthContext";
// Pages
import Login from "./pages/Auth/Login";
import VerificaCorreo from "./pages/Auth/VerificaCorreo";

import AdminDashboard from "./pages/Admin/AdminDashboard";
import GestionClientes from "./pages/Admin/GestionClientes";
import GestionUsuarios from "./pages/Admin/GestionUsuarios";

import EncuestaDesigner from "./pages/EncuestaDesigner";

// import ClienteDashboardPage from "./pages/Cliente/ClienteDashboardPage";
import GestionEncuestas from "./pages/Client/GestionEncuestas";
// import DiseñadorEncuestaPage from "./pages/Cliente/DiseñadorEncuestaPage";

// import EncuestaPublicaPage from "./pages/Public/EncuestaPublicaPage";
// import RespuestaPublicaPage from "./pages/Public/RespuestaPublicaPage";

import NotFound from "./pages/NotFound";

// Un componente para mostrar mientras AuthContext está validando
function LoadingScreen() {
    return (
        <div className="app-loading-screen">
            <div className="spinner"></div>
            <p>Cargando aplicación...</p>
        </div>
    );
}

/**
 * ProtectedRoute:
 * - Si loading=true, muestra pantalla de carga.
 * - Si no está autenticado, redirige a /login.
 * - Si se especifica `rolesPermitidos`, revisa user.rol en el contexto.
 * - Si se especifica `requireVerifiedEmail`, revisa user.email_verified_at.
 */
function ProtectedRoute({ rolesPermitidos = [], requireVerifiedEmail = true }) {
    const { isAuthenticated, user, loading } = useAuth();

    if (loading) return <LoadingScreen />; // Si loading=true, muestra pantalla de carga.

    if (!isAuthenticated) return <Navigate to="/login" replace />; // Si no está autenticado, redirige a /login

    // Si requiere email verificado y no lo está
    if (requireVerifiedEmail && !user.email_verified_at) {
        return <Navigate to="/verifica-tu-correo" replace />;
    }

    // Si se piden roles específicos y el usuario no coincide
    if (rolesPermitidos.length > 0 && !rolesPermitidos.includes(user.rol)) {
        return <Navigate to="/login" replace />;
    }

    // Todo OK: renderizar la ruta hija
    return <Outlet />;
}

export default function AppRoutes() {
    const { isAuthenticated, user, loading } = useAuth();

    if (loading) {
        // En teoría, ProtectedRoute ya muestra LoadingScreen, pero aquí cubrimos el caso general
        return <LoadingScreen />;
    }

    // Si el usuario está autenticado, definimos un redirect genérico al dashboard por rol
    const getRedirectPath = () => {
        if (!isAuthenticated) return "/login";
        return user.rol === "Administrador"
            ? "/admin/dashboard"
            : "/cliente/dashboard";
    };

    return (
        <Routes>
            {/* 1) RUTAS PÚBLICAS / AUTENTICACIÓN */}
            <Route
                path="/login"
                element={
                    isAuthenticated ? (
                        <Navigate to={getRedirectPath()} replace />
                    ) : (
                        <Login />
                    )
                }
            />
            <Route
                path="/verifica-tu-correo"
                element={
                    isAuthenticated ? (
                        user.email_verified_at ? (
                            <Navigate to={getRedirectPath()} replace />
                        ) : (
                            <VerificaCorreo />
                        )
                    ) : (
                        <Navigate to="/login" replace />
                    )
                }
            />

            {/* Cuando Laravel envíe al usuario tras verificar correo a /email-verificado-exitosamente */}
            <Route
                path="/email-verificado-exitosamente"
                element={<VerificaCorreo />}
            />
            <Route
                path="/email-already-verified"
                element={<VerificaCorreo />}
            />

            {/* 2) RUTA RAÍZ */}
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

            {/* 3) DASHBOARD REDIRECTOR */}
            <Route element={<ProtectedRoute requireVerifiedEmail={false} />}>
                <Route
                    path="/dashboard"
                    element={
                        user?.rol === "Administrador" ? (
                            <Navigate to="/admin/dashboard" replace />
                        ) : (
                            <Navigate to="/cliente/dashboard" replace />
                        )
                    }
                />
            </Route>

            {/* 4) RUTAS ADMINISTRADOR */}
            <Route
                element={<ProtectedRoute rolesPermitidos={["Administrador"]} />}
            >
                <Route path="/admin/dashboard" element={<AdminDashboard />} />
                <Route path="/admin/clientes" element={<GestionClientes />} />
                <Route path="/admin/usuarios" element={<GestionUsuarios />} />
                {/* ...otras rutas de admin anidadas aquí... */}
            </Route>

            {/* 5) RUTAS CLIENTE */}
            <Route element={<ProtectedRoute rolesPermitidos={["Cliente"]} />}>
                <Route
                    path="/cliente/dashboard"
                    // element={<ClienteDashboardPage />}
                />
                <Route
                    path="/cliente/encuestas"
                    element={<GestionEncuestas />}
                />
                <Route
                    path="/cliente/encuestas/:idEncuesta/disenar"
                    // element={<DiseñadorEncuestaPage />}
                />
                <Route path="encuestas/nuevo" element={<EncuestaDesigner />} />
                <Route
                    path="encuestas/:encuestaId/diseño"
                    element={<EncuestaDesigner />}
                />
                {/* ...otras rutas de cliente... */}
            </Route>

            {/* 6) RUTAS PÚBLICAS DE ENCUESTAS/RESPUESTAS */}
            <Route
                path="/encuestas/publica/:idEncuesta"
                // element={<EncuestaPublicaPage />}
            />
            <Route
                path="/encuestas/publica/code/:codigoUrl"
                // element={<EncuestaPublicaPage />}
            />
            <Route
                path="/encuestas/:encuestaId/respuestas/:respondidaId"
                // element={<RespuestaPublicaPage />}
            />

            {/* 7) CUALQUIER OTRA RUTA → 404 */}
            <Route path="*" element={<NotFound />} />
        </Routes>
    );
}
