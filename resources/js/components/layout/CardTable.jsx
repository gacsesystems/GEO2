import React from "react";
import "./CardTable.css";
// Importa tus iconos de edición/eliminación si los necesitas aquí
// import { PencilIcon, TrashIcon } from '../ui/Icons';

const CardTable = ({ title, data, columns, color = "light" }) => {
    // data: array de objetos. columns: array de {Header: 'Nombre Col', accessor: 'nombre_propiedad' o función}
    // O columns puede ser solo un array de strings si los accessors son iguales a los headers (simplificado)

    // Encabezados de la tabla
    const tableHeaders = columns.map((col) =>
        typeof col === "string" ? col : col.Header
    );

    // Función para obtener el valor de la celda
    const getCellValue = (row, col) => {
        if (typeof col === "string") {
            return row[col.toLowerCase().replace(/\s+/g, "_")];
        }

        // Si el accessor es una función, llamarla con la fila
        if (typeof col.accessor === "function") {
            return col.accessor(row);
        }

        // Si hay un componente Cell personalizado, usarlo
        if (col.Cell) {
            return col.Cell({ value: row[col.accessor], row });
        }

        // Caso por defecto: acceder a la propiedad
        return row[col.accessor] ?? "N/A";
    };

    return (
        <div
            className={`card-table-wrapper ${
                color === "dark" ? "dark-mode" : ""
            }`}
        >
            {title && <h3 className="card-table-title">{title}</h3>}
            <div className="card-table-scroll-container">
                <table className="card-table-element">
                    <thead>
                        <tr>
                            {tableHeaders.map((header, index) => (
                                <th key={index} className="card-table-th">
                                    {header}
                                </th>
                            ))}
                            {/* Opcional: Columna para acciones */}
                            {/* <th className="card-table-th">Acciones</th> */}
                        </tr>
                    </thead>
                    <tbody>
                        {(!data || data.length === 0) && (
                            <tr>
                                <td
                                    colSpan={tableHeaders.length + 1}
                                    className="card-table-td text-center"
                                >
                                    No hay datos para mostrar.
                                </td>
                            </tr>
                        )}
                        {data &&
                            data.map((row, rowIndex) => (
                                <tr key={rowIndex} className="card-table-tr">
                                    {columns.map((col, colIndex) => (
                                        <td
                                            key={colIndex}
                                            className="card-table-td"
                                        >
                                            {getCellValue(row, col)}
                                            {/* Ejemplo: Si la columna es 'activo' y quieres mostrar un badge
                                        {col.accessor === 'activo' && (
                                            <span className={`status-badge ${row.activo ? 'active' : 'inactive'}`}>
                                                {row.activo ? 'Activo' : 'Inactivo'}
                                            </span>
                                        )} */}
                                        </td>
                                    ))}
                                    {/* Opcional: Celda para acciones */}
                                    {/* <td className="card-table-td card-table-actions">
                                    <button className="action-btn edit"><PencilIcon /></button>
                                    <button className="action-btn delete"><TrashIcon /></button>
                                </td> */}
                                </tr>
                            ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default CardTable;
