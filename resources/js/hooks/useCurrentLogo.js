import { useState, useEffect } from "react";

// Función auxiliar para obtener el estado inicial de forma segura en el cliente
const getInitialColorScheme = () => {
    if (typeof window !== "undefined" && window.matchMedia) {
        return window.matchMedia("(prefers-color-scheme: dark)").matches;
    }
    return false; // Valor por defecto si window o matchMedia no están disponibles (ej. SSR)
};

export function useCurrentLogo() {
    // Inicializamos 'isDarkMode' con el valor actual del sistema para evitar un "flash"
    // si el modo oscuro es el predeterminado.
    const [isDarkMode, setIsDarkMode] = useState(getInitialColorScheme);

    useEffect(() => {
        // Asegurarse de que window y matchMedia existen
        if (typeof window === "undefined" || !window.matchMedia) {
            return;
        }

        const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");

        // Esta función se llamará tanto al montar como cuando cambie la preferencia del sistema
        const handleChange = (event) => {
            // 'event.matches' es true si el sistema prefiere el modo oscuro
            const newIsDarkMode = event ? event.matches : mediaQuery.matches;
            setIsDarkMode(newIsDarkMode);

            // // Aplicamos las clases al body como en tu código original
            // if (newIsDarkMode) {
            //     document.body.classList.add("dark-mode");
            //     document.body.classList.remove("light-mode");
            // } else {
            //     document.body.classList.add("light-mode");
            //     document.body.classList.remove("dark-mode");
            // }
        };

        // 1. Aplicar el estado y clases iniciales al montar el hook
        // (Se usa mediaQuery.matches directamente aquí en lugar de pasar un evento)
        handleChange({ matches: mediaQuery.matches });

        // 2. Escuchar los cambios en la preferencia del sistema
        mediaQuery.addEventListener("change", handleChange);

        // 3. Limpieza: remover el listener cuando el componente que usa el hook se desmonte
        return () => mediaQuery.removeEventListener("change", handleChange);
    }, []); // El array de dependencias vacío asegura que el efecto solo se ejecute al montar y desmontar

    const currentLogo = isDarkMode
        ? "/img/gacse-white.png"
        : "/img/gacse-orange.png";

    // Opcional: puedes hacer console.log aquí si necesitas depurar dentro del hook
    // console.log("Desde useCurrentLogo: isDarkMode =", isDarkMode);
    // console.log("Desde useCurrentLogo: currentLogo =", currentLogo);

    return currentLogo; // El hook devuelve solo la URL del logo actual
}
