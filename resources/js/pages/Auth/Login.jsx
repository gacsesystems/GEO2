import React, { useState, useEffect } from "react";
import { useNavigate, Link, useLocation } from "react-router-dom";
import { useAuth } from "../../contexts/AuthContext";
import "./Login.css";
import { useCurrentLogo } from "../../hooks/useCurrentLogo";

export default function Login() {
    const { login, isAuthenticated, user, error: authError } = useAuth();
    const navigate = useNavigate();
    const location = useLocation();
    const LogoActual = useCurrentLogo();

    // Si vino de alguna ruta protegida, intentará redirigir allí.
    // Ejemplo: { state: { from: "/admin/clientes" } }
    const from = location.state?.from?.pathname || "/";

    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Si el usuario ya está autenticado (y quizá su email verificado), redirige.
    if (isAuthenticated && user) {
        // Si el email no está verificado, déjalo en /verifica-tu-correo
        if (!user.email_verified_at) {
            navigate("/verifica-tu-correo", { replace: true });
        } else {
            // Si estaba intentando ir a "from", lo redirige allí, o sino según rol:
            if (from && from !== "/login") {
                navigate(from, { replace: true });
            } else if (user.rol === "Administrador") {
                navigate("/admin/dashboard", { replace: true });
            } else {
                navigate("/cliente/dashboard", { replace: true });
            }
        }
    }

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError("");
        setIsSubmitting(true);

        try {
            const loggedInUser = await login(email.trim(), password);
            // Si el email no está verificado, el AuthContext redirigirá a /verifica-tu-correo.
            // De lo contrario, redirigimos según rol o "from":
            if (!loggedInUser.email_verified_at) {
                navigate("/verifica-tu-correo", { replace: true });
                return;
            } else if (from && from !== "/login") {
                navigate(from, { replace: true });
            } else if (loggedInUser.rol === "Administrador") {
                navigate("/admin/dashboard", { replace: true });
            } else {
                navigate("/cliente/dashboard", { replace: true });
            }
        } catch (err) {
            // Si Laravel devolvió un mensaje en err.response.data.message, lo usamos.
            if (err.response?.data?.message) {
                setError(err.response.data.message);
            } else if (err.response?.data?.errors) {
                // Si hay errores de validación, tomamos el primer error
                const firstError = Object.values(err.response.data.errors)[0];
                setError(
                    Array.isArray(firstError) ? firstError[0] : firstError
                );
            } else {
                setError("Credenciales inválidas. Inténtalo de nuevo.");
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="login-page">
            <div className="login-form-container">
                <h1 className="login-title">GEO</h1>
                <p className="login-subtitle">Sistema de encuestas en líneas</p>

                <img src={LogoActual} alt="Logo GEO" className="login-logo" />

                {error && <div className="login-error-message">{error}</div>}

                <form onSubmit={handleSubmit} className="login-form">
                    <div className="form-group">
                        <label htmlFor="email">Correo electrónico</label>
                        <input
                            id="email"
                            type="email"
                            placeholder="usuario@ejemplo.com"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            className="login-input"
                            required
                        />
                    </div>

                    <div className="form-group">
                        <label htmlFor="password">Contraseña</label>
                        <input
                            id="password"
                            type="password"
                            placeholder="••••••••••"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            className="login-input"
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        className="login-button"
                        disabled={isSubmitting}
                    >
                        {isSubmitting
                            ? "Iniciando sesión..."
                            : "Iniciar Sesión"}
                    </button>

                    <div className="login-links">
                        <Link
                            to="/forgot-password"
                            className="login-forgot-password"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                        {/* Si más adelante agregas /register, pon aquí el enlace */}
                        {/* <Link to="/register" className="login-register-link">
                  Registrarse
                </Link> */}
                    </div>
                </form>
            </div>
        </div>
    );
}
