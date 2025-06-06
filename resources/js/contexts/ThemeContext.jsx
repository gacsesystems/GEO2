import React, { createContext, useContext, useState, useEffect } from "react";

// 1. Crear el Contexto
const ThemeContext = createContext();

// 2. Crear un Hook personalizado para usar el contexto fácilmente
export function ThemeProvider({ children }) {
    const [isDarkMode, setIsDarkMode] = useState(() => {
        // Intentar obtener el tema guardado en localStorage
        const savedTheme = localStorage.getItem("theme");
        // Si hay un tema guardado, usarlo, sino usar la preferencia del sistema
        return savedTheme
            ? savedTheme === "dark"
            : window.matchMedia("(prefers-color-scheme: dark)").matches;
    });

    // Efecto para actualizar el tema cuando cambia
    useEffect(() => {
        // Guardar el tema en localStorage
        localStorage.setItem("theme", isDarkMode ? "dark" : "light");

        // Actualizar las clases del body
        if (isDarkMode) {
            document.body.classList.add("dark-mode");
            document.body.classList.remove("light-mode");
        } else {
            document.body.classList.add("light-mode");
            document.body.classList.remove("dark-mode");
        }
    }, [isDarkMode]);

    // Función para alternar el tema
    const toggleDarkMode = () => {
        setIsDarkMode((prev) => !prev);
    };

    // Determinar el logo actual basado en el tema
    const currentLogo = isDarkMode
        ? "/img/gacse-white.png"
        : "/img/gacse-orange.png";

    return (
        <ThemeContext.Provider
            value={{ isDarkMode, currentLogo, toggleDarkMode }}
        >
            {children}
        </ThemeContext.Provider>
    );
}

export const useTheme = () => {
    const context = useContext(ThemeContext);
    if (context === undefined) {
        throw new Error("useTheme debe ser usado dentro de un ThemeProvider");
    }
    return context;
};
