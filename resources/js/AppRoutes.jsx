import React from "react";
import { Routes, Route, Navigate, Outlet } from "react-router-dom";
import { useAuth } from "./contexts/AuthContext";
// Pages
import Login from "./pages/Auth/Login";
// import VerificaCorreoPage from "./pages/Auth/VerificaCorreoPage";

// import AdminDashboardPage from "./pages/Admin/AdminDashboardPage";
import GestionClientesPage from "./pages/Admin/GestionClientesPage";
// import GestionUsuariosPage from "./pages/Admin/GestionUsuariosPage";

// import ClienteDashboardPage from "./pages/Cliente/ClienteDashboardPage";
// import GestionEncuestasPage from "./pages/Cliente/GestionEncuestasPage";
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
                            {
                                /* <VerificaCorreoPage /> */
                            }
                        )
                    ) : (
                        <Navigate to="/login" replace />
                    )
                }
            />

            {/* Cuando Laravel envíe al usuario tras verificar correo a /email-verificado-exitosamente */}
            <Route
                path="/email-verificado-exitosamente"
                // element={<VerificaCorreoPage />}
            />
            <Route
                path="/email-already-verified"
                // element={<VerificaCorreoPage />}
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
                <Route
                    path="/admin/dashboard"
                    // element={<AdminDashboardPage />}
                />
                <Route
                    path="/admin/clientes"
                    element={<GestionClientesPage />}
                />
                <Route
                    path="/admin/usuarios"
                    // element={<GestionUsuariosPage />}
                />
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
                    // element={<GestionEncuestasPage />}
                />
                <Route
                    path="/cliente/encuestas/:idEncuesta/disenar"
                    // element={<DiseñadorEncuestaPage />}
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
