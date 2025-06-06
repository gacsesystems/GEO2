import React, { useContext } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../contexts/AuthContext";
import "./Header.css";

export default function Header() {
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    const handleLogout = async () => {
        await logout();
        navigate("/login");
    };

    return (
        <header className="header">
            <div className="header-left">
                <Link to="/" className="logo">
                    GEO Encuestas
                </Link>
            </div>
            <div className="header-right">
                {user ? (
                    <>
                        <span className="header-user">
                            Hola, {user.nombre || user.email}
                        </span>
                        <button onClick={handleLogout} className="btn-logout">
                            Cerrar sesión
                        </button>
                    </>
                ) : (
                    <Link to="/login" className="header-login">
                        Iniciar sesión
                    </Link>
                )}
            </div>
        </header>
    );
}
