import React, { useState, useEffect } from "react";
import { useLocation, Link, useNavigate } from "react-router-dom";
import { useAuth } from "../../contexts/AuthContext";
import { resendVerification } from "../../services/auth"; // Importa la función del servicio
import "./VerificaCorreoPage.css";
import {
    MailSentIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from "../../components/ui/Icons";

function VerificaCorreoPage() {
    const {
        user,
        setUser,
        setIsAuthenticated,
        isLoading: authIsLoading,
    } = useAuth();
    const location = useLocation();
    const navigate = useNavigate();
    const [statusMessage, setStatusMessage] = useState("");
    const [messageType, setMessageType] = useState(""); // 'success', 'error', 'info'
    const [isResending, setIsResending] = useState(false);

    useEffect(() => {
        // Si el usuario llega a esta página y ya está verificado, redirigir.
        if (user && user.email_verified_at) {
            navigate(
                user.rol === "Administrador"
                    ? "/admin/dashboard"
                    : "/cliente/dashboard",
                { replace: true }
            );
            return;
        }

        // Comprobar si la URL indica un resultado de verificación
        // Tu backend redirige a /email-verificado-exitosamente o /email-already-verified
        if (location.pathname.endsWith("/email-verificado-exitosamente")) {
            setStatusMessage(
                "¡Tu correo electrónico ha sido verificado exitosamente! Ahora tienes acceso completo."
            );
            setMessageType("success");
            // Actualizar el estado del usuario en AuthContext (simulado, idealmente la API /me devolvería el estado actualizado)
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
                // Corregir si el estado local estaba desactualizado
                setUser({
                    ...user,
                    email_verified_at: new Date().toISOString(),
                });
            }
        } else if (user && !user.email_verified_at) {
            // Mensaje por defecto si el usuario llega aquí y no está verificado
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
            const response = await resendVerification();
            setStatusMessage(
                response.message ||
                    "Se ha enviado un nuevo enlace de verificación a tu correo."
            );
            setMessageType("success");
        } catch (error) {
            setStatusMessage(
                error.message ||
                    "Ocurrió un error al reenviar el correo de verificación."
            );
            setMessageType("error");
        } finally {
            setIsResending(false);
        }
    };

    if (authIsLoading) {
        return <div className="vcp-loading">Cargando...</div>;
    }

    if (!user) {
        // Si por alguna razón el usuario no está cargado pero no estamos en loading, redirigir a login
        // Esto podría pasar si el usuario accede directamente a esta URL sin estar logueado.
        // ProtectedRoute debería manejar esto, pero es una salvaguarda.
        return <Navigate to="/login" replace />;
    }

    // Si el usuario YA está verificado y de alguna manera llegó aquí (useEffect no lo redirigió aún)
    // no mostrar el botón de reenvío. El mensaje de useEffect debería cubrir esto.
    const showResendButton =
        !user.email_verified_at ||
        location.pathname.endsWith("/email-already-verified") || // Permitir reenvío si el mensaje es 'ya verificado' por si acaso
        messageType === "error"; // Permitir reenvío si hubo un error

    return (
        <div className="vcp-container">
            {" "}
            {/* vcp: Verify Email Page */}
            <div className="vcp-card">
                {messageType === "success" && <CheckCircleIcon />}
                {messageType === "error" && <ExclamationCircleIcon />}
                {messageType === "info" && <MailSentIcon />}

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
                                ? "Reenviando..."
                                : "Reenviar Correo de Verificación"}
                        </button>
                    </div>
                )}

                {user &&
                    user.email_verified_at &&
                    !location.pathname.includes("exitosamente") &&
                    !location.pathname.includes("already-verified") && (
                        // Si el usuario está verificado y no estamos mostrando un mensaje de éxito/info de la redirección
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

export default VerificaCorreoPage;
