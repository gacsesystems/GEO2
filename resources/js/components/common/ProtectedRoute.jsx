import React from "react";
import { Navigate, Outlet, useLocation } from "react-router-dom";
import { useAuth } from "../../contexts/AuthContext";

export const ProtectedRoute = ({ rolesPermitidos }) => {
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

    if (
        rolesPermitidos &&
        rolesPermitidos.length > 0 &&
        !rolesPermitidos.includes(user?.role)
    ) {
        console.warn(
            `Acceso denegado para el usuario ${user?.email} con rol ${
                user?.role
            }. Roles permitidos: ${rolesPermitidos.join(", ")}`
        );
        const fallbackDashboard =
            user?.role === "Administrador"
                ? "/admin/dashboard"
                : "/cliente/dashboard";
        return (
            <Navigate
                to={fallbackDashboard}
                // state={{ from: location }}
                replace
            />
        );
    }

    return <Outlet />;
};

export default ProtectedRoute;
