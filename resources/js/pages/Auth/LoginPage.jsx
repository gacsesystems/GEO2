import React, { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useAuth } from "../../contexts/AuthContext";
import "./LoginPage.css";

const LoginPage = () => {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { login } = useAuth();
    const navigate = useNavigate();
    const location = useLocation();
    const from = location.state?.from?.pathname || "/";

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError("");
        setIsSubmitting(true);
        try {
            const loggedInUser = await login(email, password);
            if (from) {
                navigate(from, { replace: true });
            } else if (loggedInUser?.rol === "Administrador") {
                navigate("/admin/dashboard", { replace: true });
            } else if (loggedInUser?.rol === "Cliente") {
                navigate("/cliente/dashboard", { replace: true });
            } else {
                navigate("/", { replace: true });
            }
        } catch (error) {
            setError(
                err.response?.data?.message ||
                    err.message ||
                    "Error al iniciar sesión."
            );
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="login-page">
            <div className="login-form-container">
                {/* Opcional: icono de casa aquí */}
                {/* <img src="/img/home-icon.svg" alt="Home" className="login-home-icon" /> */}
                <h1 className="login-title">Iniciar Sesión</h1>
                {error && <div className="login-error-message">{error}</div>}
                <form onSubmit={handleSubmit}>
                    <div>
                        <input
                            text="text"
                            placeholder="Usuario"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            className="login-input"
                            required
                        />
                    </div>
                    <div>
                        <input
                            type="password"
                            placeholder="Contraseña"
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
                    <div>
                        <a href="#" className="login-forgot-password">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default LoginPage;
