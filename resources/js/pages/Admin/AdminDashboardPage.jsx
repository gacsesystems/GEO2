import React from "react";
import { Link } from "react-router-dom";
import "./AdminDashboardPage.css"; // Importar CSS

function AdminDashboardPage() {
    return (
        <div className="adp-container">
            {/* adp: Admin Dashboard Page */}
            <h1 className="adp-main-title">Dashboard de Administración</h1>
            <div className="adp-cards-grid">
                <div className="adp-card">
                    <h2 className="adp-card-title">Gestión de Clientes</h2>
                    <p className="adp-card-description">
                        Administra, agrega y edita los clientes de la
                        plataforma.
                    </p>
                    <Link to="/admin/clientes" className="adp-card-link">
                        Ir a Clientes →
                    </Link>
                </div>

                <div className="adp-card">
                    <h2 className="adp-card-title">Gestión de Usuarios</h2>
                    <p className="adp-card-description">
                        Crea y administra las cuentas de usuario.
                    </p>
                    <Link to="/admin/usuarios" className="adp-card-link">
                        Ir a Usuarios →
                    </Link>
                </div>

                {/* Puedes añadir más cards aquí */}
                <div className="adp-card">
                    <h2 className="adp-card-title">Configuración</h2>
                    <p className="adp-card-description">
                        Ajustes generales de la aplicación.
                    </p>
                    <Link to="/admin/configuracion" className="adp-card-link">
                        Ir a Configuración →
                    </Link>
                </div>
            </div>
        </div>
    );
}

export default AdminDashboardPage;
