import React, { createContext, useContext, useState, useEffect } from "react";
import {
    getCurrentUser,
    login as loginService,
    logout as logoutService,
} from "../services/auth";

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [isAuthenticated, setIsAuthenticated] = useState(false);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const checkLoggedIn = async () => {
            setIsLoading(true);
            try {
                const userData = await getCurrentUser(); // Verifica si el usuario está autenticado

                if (userData) {
                    setUser(userData);
                    setIsAuthenticated(true);
                } else {
                    setUser(null);
                    setIsAuthenticated(false);
                }
            } catch (error) {
                console.error("Error al verificar autenticación:", error);
                setUser(null);
                setIsAuthenticated(false);
            } finally {
                setIsLoading(false);
            }
        };
        checkLoggedIn();
    }, []);

    const login = async (email, password) => {
        setIsLoading(true);
        try {
            const userData = await loginService(email, password);
            setUser(userData);
            setIsAuthenticated(true);
            if (userData.token) {
                localStorage.setItem("authToken", userData.token);
                // Actualiza el header por defecto de Axios si quieres que las siguientes peticiones lo tengan
                // O confía en el interceptor de solicitud si lo activas
                window.axios.defaults.headers.common[
                    "Authorization"
                ] = `Bearer ${userData.token}`;
            }
            return userData;
        } catch (error) {
            console.error("Error al iniciar sesión:", error);
            throw error;
        } finally {
            setIsLoading(false);
        }
    };

    const logout = async () => {
        setIsLoading(true);
        try {
            await logoutService();
            setUser(null);
            setIsAuthenticated(false);
        } catch (error) {
            console.error("Error al cerrar sesión:", error);
            setUser(null);
            setIsAuthenticated(false);
            throw error;
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <AuthContext.Provider
            value={{
                user,
                isAuthenticated,
                isLoading,
                login,
                logout,
                setUser,
                setIsAuthenticated,
            }}
        >
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);
