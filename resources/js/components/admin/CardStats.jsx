import React from "react";
import "./CardStats.css";

const CardStats = ({
    statSubtitle,
    statTitle,
    // statArrow, // Opcional: "up" o "down"
    // statPercent,
    // statPercentColor,
    // statDescripiron,
    statIconName: StatIcon, // Renombrado para usar como componente
    statIconColor, // ej: "bg-brand-orange"
}) => {
    return (
        <div className="card-stats-item">
            <div className="card-stats-content">
                <div className="card-stats-text">
                    <span className="card-stats-subtitle">{statSubtitle}</span>
                    <span className="card-stats-title">{statTitle}</span>
                </div>
                <div className={`card-stats-icon-wrapper ${statIconColor}`}>
                    {StatIcon && <StatIcon />}
                </div>
            </div>
            {/* Opcional: para mostrar porcentaje de cambio */}
            {/* <p className="card-stats-footer">
                <span className={statPercentColor + " card-stats-percent"}>
                    {statArrow === "up" ? <ArrowUpIcon /> : <ArrowDownIcon />} {statPercent}%
                </span>
                <span className="card-stats-description">{statDescripiron}</span>
            </p> */}
        </div>
    );
};

export default CardStats;
