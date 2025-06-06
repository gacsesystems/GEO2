/**
 * Contexto de autenticación
 *
 * - Maneja el estado de autenticación y el usuario actual.
 * - Proporciona funciones para iniciar sesión, cerrar sesión y actualizar datos.
 * - Utiliza axios para realizar solicitudes HTTP.
 * - Gestiona el token de autenticación y lo almacena en localStorage.
 * - Maneja errores y estados de carga.
 */

import React, {
    createContext,
    useState,
    useEffect,
    useCallback,
    useContext,
} from "react";
import { axios } from "../bootstrap";
import { useNavigate } from "react-router-dom";

const AuthContext = createContext({
    user: null,
    isAuthenticated: false,
    loading: true,
    error: null,
    login: async (email, password) => {},
    logout: async () => {},
});

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [isAuthenticated, setIsAuthenticated] = useState(false);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const navigate = useNavigate();

    /**
     * 1) Al montar, intento ver si ya existe un token en localStorage.
     *    - Si hay token, disparo /api/me para obtener datos del usuario.
     *    - Si no hay token o falla, dejo isAuthenticated = false.
     */
    useEffect(() => {
        const initializeAuth = async () => {
            setLoading(true);
            setError(null);

            const token = localStorage.getItem("auth_token");
            if (!token) {
                setUser(null);
                setIsAuthenticated(false);
                setLoading(false);
                return;
            }

            try {
                // Intenta obtener datos de usuario actual (GET /api/me)
                const { data } = await axios.get("/api/me");
                setUser(data);
                setIsAuthenticated(true);
            } catch (err) {
                // Si 401 o 403, limpiamos token
                localStorage.removeItem("auth_token");
                setUser(null);
                setIsAuthenticated(false);

                // Si devuelven un mensaje específico (por ejemplo, "email no verificado"),
                // podrías redirigir a una ruta de verificación de correo:
                if (
                    err.response?.status === 403 &&
                    err.response?.data?.message
                ) {
                    // Por ejemplo: "Debes verificar tu correo primero"
                    setError(err.response.data.message);
                    navigate("/verify-email");
                }
            } finally {
                setLoading(false);
            }
        };

        initializeAuth();
    }, [navigate]);

    /**
     * Función login:
     *  - POST /api/login  { email, password }
     *  - Laravel verifica credenciales, "activo" y email verificado.
     *  - Si ok, devuelve { token, user: { id, nombre_completo, email, id_rol } }
     *  - Guardamos token en localStorage, seteamos Axios header automáticamente por el interceptor
     *  - Seteamos user e isAuthenticated = true
     */
    const login = useCallback(async (email, password) => {
        setLoading(true);
        setError(null);

        try {
            await axios.get("/sanctum/csrf-cookie"); // a) Obtener cookie CSRF

            // b) Enviar credenciales
            const res = await axios.post("/api/login", {
                email,
                password,
            });
            const { token, user: userData } = res.data;

            // Guardar token en localStorage
            localStorage.setItem("auth_token", token);
            // c) Guardar token (axios.defaults.headers.common) y usuario devuelto
            axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

            // Setear user en contexto
            setUser(userData);
            setIsAuthenticated(true);

            return userData;
        } catch (err) {
            // Si Laravel devuelve validación o credenciales inválidas
            if (err.response?.data?.errors) {
                // Ejemplo: { errors: { email: ['Credenciales inválidas.'] } }
                const firstError = Object.values(err.response.data.errors)[0];
                setError(
                    Array.isArray(firstError) ? firstError[0] : firstError
                );
            } else if (err.response?.data?.message) {
                setError(err.response.data.message);
            } else if (err.response?.status === 401) {
                setError(
                    "Credenciales inválidas. Por favor, verifica tu correo y contraseña."
                );
            } else {
                setError(
                    "Error al iniciar sesión. Por favor, inténtalo de nuevo."
                );
            }
            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    /**
     * Función logout:
     *  - POST /api/logout  (Sanctum revoca token en backend)
     *  - Limpiar localStorage y context
     */
    const logout = useCallback(async () => {
        setLoading(true);
        setError(null);

        try {
            await axios.post("/api/logout");
        } catch (err) {
            console.error("Error en logout:", err);
            // Podemos ignorar el error en logout y limpiar de todas formas:
        } finally {
            localStorage.removeItem("auth_token");
            delete axios.defaults.headers.common["Authorization"];
            setUser(null);
            setIsAuthenticated(false);
            setLoading(false);
            // Opcional: redirigir a login
            navigate("/login");
        }
    }, [navigate]);

    // Si quieres exponer una función para "refrescar" datos:
    const refreshUser = useCallback(async () => {
        setLoading(true);
        try {
            const { data } = await axios.get("/api/me");
            setUser(data);
            setIsAuthenticated(true);
        } catch {
            setUser(null);
            setIsAuthenticated(false);
        } finally {
            setLoading(false);
        }
    }, []);

    return (
        <AuthContext.Provider
            value={{
                user,
                isAuthenticated,
                loading,
                error,
                login,
                logout,
                refreshUser,
            }}
        >
            {children}
        </AuthContext.Provider>
    );
}

export const useAuth = () => useContext(AuthContext);
