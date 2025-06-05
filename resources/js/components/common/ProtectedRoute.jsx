import React from "react";
import { Navigate, Outlet, useLocation } from "react-router-dom";
import { useAuth } from "../../contexts/AuthContext";

export const ProtectedRoute = ({
    rolesPermitidos,
    requireVerifiedEmail = false,
}) => {
    const { isAuthenticated, user, isLoading } = useAuth();
    const location = useLocation();

    if (isLoading) {
        return (
            <div className="loading-fullscreen">
                Verificando autenticación...
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    // Nueva comprobación: Si la ruta requiere email verificado y el usuario no lo tiene
    if (requireVerifiedEmail && user && !user.email_verified_at) {
        console.warn(
            `Acceso denegado a ${location.pathname} para ${user?.email}. El correo no está verificado.`
        );
        // Redirigir a la página de verificación, guardando la ubicación original
        return (
            <Navigate
                to="/verifica-tu-correo"
                state={{ from: location }}
                replace
            />
        );
    }

    // Comprobación de roles (como la tenías)
    if (
        rolesPermitidos &&
        rolesPermitidos.length > 0 &&
        user && // Asegurarse que user no sea null aquí
        !rolesPermitidos.includes(user.rol) // Usar user.rol directamente ya que arriba verificamos que no sea null
    ) {
        console.warn(
            `Acceso denegado a ${location.pathname} para el usuario ${
                user?.email
            } con rol ${user?.rol}. Roles permitidos: ${rolesPermitidos.join(
                ", "
            )}`
        );
        const fallbackDashboard =
            user.rol === "Administrador" // Usar user.rol
                ? "/admin/dashboard"
                : "/cliente/dashboard";
        return <Navigate to={fallbackDashboard} replace />;
    }

    return <Outlet />;
};

export default ProtectedRoute;
