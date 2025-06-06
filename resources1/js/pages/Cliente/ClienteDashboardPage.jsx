import React from "react";
import { Link } from "react-router-dom";
import { useAuth } from "../../contexts/AuthContext";
import "./ClienteDashboardPage.css"; // Crearemos este CSS
// Importa iconos que necesites
import {
    PlusIcon as IconoAgregar,
    CogIcon as IconoEncuestas,
    ChartBarIcon as IconoReportes,
} from "../../components/ui/Icons";

const ClienteDashboardPage = () => {
    const { user } = useAuth();

    return (
        <div className="cliente-dashboard-container">
            <header className="dashboard-header">
                <h1>Panel de Cliente</h1>
                {user && (
                    <p className="welcome-message">
                        Bienvenido de nuevo,{" "}
                        {user.nombre_completo || user.email}!
                    </p>
                )}
            </header>

            <section className="dashboard-quick-actions">
                <Link
                    // to="/cliente/encuestas/crear"
                    to="/cliente/encuestas"
                    className="action-card crear-encuesta"
                >
                    {" "}
                    {/* Asume una ruta para crear directamente */}
                    <IconoAgregar />
                    <h2>Crear Nueva Encuesta</h2>
                    <p>Comienza a diseñar tu próxima encuesta desde cero.</p>
                </Link>
                <Link
                    to="/cliente/encuestas"
                    className="action-card gestionar-encuestas"
                >
                    <IconoEncuestas />
                    <h2>Gestionar Mis Encuestas</h2>
                    <p>
                        Edita, visualiza y obtén enlaces de tus encuestas
                        existentes.
                    </p>
                </Link>
                <Link
                    to="/cliente/reportes"
                    className="action-card ver-reportes"
                >
                    {" "}
                    {/* Asume una ruta general de reportes */}
                    <IconoReportes />
                    <h2>Ver Reportes</h2>
                    <p>
                        Analiza los resultados y obtén información valiosa de
                        tus encuestas.
                    </p>
                </Link>
            </section>

            <section className="dashboard-stats-overview">
                {/* Aquí podrías mostrar algunas estadísticas rápidas si tienes un endpoint para ello */}
                {/* Ejemplo:
                <div className="stat-item">
                    <h4>Encuestas Activas</h4>
                    <p className="stat-value">5</p>
                </div>
                <div className="stat-item">
                    <h4>Respuestas Totales</h4>
                    <p className="stat-value">1,234</p>
                </div>
                <div className="stat-item">
                    <h4>Última Actividad</h4>
                    <p className="stat-value-small">Hoy</p>
                </div>
                */}
                <p className="info-message">
                    Resumen de actividad y estadísticas próximamente.
                </p>
            </section>
        </div>
    );
};

export default ClienteDashboardPage;
