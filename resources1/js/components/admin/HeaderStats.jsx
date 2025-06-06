import React from "react";
import CardStats from "./CardStats"; // Adaptaremos este componente también
import "./HeaderStats.css";
import { UsersIcon, ClientsIcon, SurveyIcon } from "../ui/Icons"; // Tus iconos

const HeaderStats = ({ stats }) => {
    // stats será un objeto con los datos
    return (
        <div className="header-stats-container">
            <div className="header-stats-grid">
                <CardStats
                    statSubtitle="TOTAL USUARIOS"
                    statTitle={stats?.totalUsuarios || "0"}
                    statIconName={UsersIcon}
                    statIconColor="bg-brand-orange" // Clase para el color naranja de tu paleta
                />
                <CardStats
                    statSubtitle="CLIENTES ACTIVOS"
                    statTitle={stats?.clientesActivos || "0"}
                    statIconName={ClientsIcon}
                    statIconColor="bg-brand-purple" // Clase para el morado
                />
                <CardStats
                    statSubtitle="ENCUESTAS CREADAS"
                    statTitle={stats?.encuestasCreadas || "0"}
                    statIconName={SurveyIcon}
                    statIconColor="bg-brand-yellow" // Clase para el amarillo
                />
                <CardStats
                    statSubtitle="RESPUESTAS HOY"
                    statTitle={stats?.respuestasHoy || "0"}
                    // statArrow="up"
                    // statPercent="3.48"
                    // statPercentColor="text-emerald-500"
                    // statDescripiron="Desde ayer"
                    statIconName={SurveyIcon} // Podrías usar otro icono
                    statIconColor="bg-brand-success" // Verde
                />
            </div>
        </div>
    );
};

export default HeaderStats;
