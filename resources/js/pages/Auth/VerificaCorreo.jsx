import React, { useState, useEffect } from "react";
import { useLocation, Link, useNavigate, Navigate } from "react-router-dom";
import { useAuth } from "../../contexts/AuthContext";
import axios from "axios";
import {
    MailSentIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from "../../components/ui/Icons";
import "./VerificaCorreo.css";

export default function VerificaCorreo() {
    const {
        user,
        setUser,
        setIsAuthenticated,
        isLoading: authIsLoading,
    } = useAuth();
    const location = useLocation();
    const navigate = useNavigate();

    const [statusMessage, setStatusMessage] = useState("");
    const [messageType, setMessageType] = useState(""); // "success" | "error" | "info"
    const [isResending, setIsResending] = useState(false);

    useEffect(() => {
        // Si el usuario ya está verificado, redirigir al dashboard correspondiente
        if (user && user.email_verified_at) {
            const destino =
                user.rol === "Administrador"
                    ? "/admin/dashboard"
                    : "/cliente/dashboard";
            navigate(destino, { replace: true });
            return;
        }

        // Mensajes según ruta
        if (location.pathname.endsWith("/email-verificado-exitosamente")) {
            setStatusMessage(
                "¡Tu correo electrónico ha sido verificado exitosamente! Ahora tienes acceso completo."
            );
            setMessageType("success");
            if (user) {
                setUser({
                    ...user,
                    email_verified_at: new Date().toISOString(),
                });
            }
        } else if (location.pathname.endsWith("/email-already-verified")) {
            setStatusMessage(
                "Este correo electrónico ya había sido verificado previamente."
            );
            setMessageType("info");
            if (user && !user.email_verified_at) {
                setUser({
                    ...user,
                    email_verified_at: new Date().toISOString(),
                });
            }
        } else if (user && !user.email_verified_at) {
            setStatusMessage(
                "Por favor, verifica tu dirección de correo electrónico para continuar. Hemos enviado un enlace de verificación a tu correo."
            );
            setMessageType("info");
        }
    }, [location.pathname, user, navigate, setUser]);

    const handleResendVerification = async () => {
        setIsResending(true);
        setStatusMessage("");
        setMessageType("");
        try {
            await axios.post("/api/email/verification-notification");
            setStatusMessage(
                "Se ha enviado un nuevo enlace de verificación a tu correo."
            );
            setMessageType("success");
        } catch (error) {
            setStatusMessage(
                error.response?.data?.message ||
                    "Ocurrió un error al reenviar el correo de verificación."
            );
            setMessageType("error");
        } finally {
            setIsResending(false);
        }
    };

    if (authIsLoading) {
        return <div className="vcp-loading">Cargando…</div>;
    }
    if (!user) {
        return <Navigate to="/login" replace />;
    }

    const showResendButton =
        !user.email_verified_at ||
        location.pathname.endsWith("/email-already-verified") ||
        messageType === "error";

    return (
        <div className="vcp-container">
            <div className="vcp-card">
                {messageType === "success" && (
                    <CheckCircleIcon className="vcp-icon success" />
                )}
                {messageType === "error" && (
                    <ExclamationCircleIcon className="vcp-icon error" />
                )}
                {messageType === "info" && (
                    <MailSentIcon className="vcp-icon info" />
                )}

                <h1 className="vcp-title">
                    Verificación de Correo Electrónico
                </h1>

                {statusMessage && (
                    <p className={`vcp-message ${messageType}`}>
                        {statusMessage}
                    </p>
                )}

                {showResendButton && (
                    <div className="vcp-actions">
                        <p className="vcp-resend-info">
                            ¿No recibiste el correo o el enlace expiró?
                        </p>
                        <button
                            onClick={handleResendVerification}
                            disabled={isResending}
                            className="vcp-button"
                        >
                            {isResending
                                ? "Reenviando…"
                                : "Reenviar Correo de Verificación"}
                        </button>
                    </div>
                )}

                {user.email_verified_at &&
                    !location.pathname.includes("exitosamente") &&
                    !location.pathname.includes("already-verified") && (
                        <p className="vcp-message success">
                            Tu correo ya está verificado.{" "}
                            <Link to="/dashboard">Ir al Dashboard</Link>
                        </p>
                    )}

                <div className="vcp-footer-links">
                    <Link to="/dashboard" className="vcp-link">
                        Ir al Dashboard
                    </Link>
                    <span className="vcp-separator">|</span>
                    <button
                        onClick={() => {
                            setIsAuthenticated(false);
                            setUser(null);
                            navigate("/login");
                        }}
                        className="vcp-link-button"
                    >
                        Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>
    );
}
