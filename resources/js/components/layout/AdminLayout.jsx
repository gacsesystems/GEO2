import React from "react";
import { Outlet } from "react-router-dom";
import Navbar from "./Navbar"; // Tu Navbar existente
// import AdminSidebar from './AdminSidebar'; // Un nuevo Sidebar si lo creas
import "./AdminLayout.css";

const AdminLayout = () => {
    return (
        <div className="admin-layout">
            <Navbar /> {/* Tu navbar superior */}
            <div className="admin-layout-main">
                {/* <AdminSidebar /> */} {/* Descomenta si creas un sidebar */}
                <div className="admin-layout-content">
                    <Outlet /> {/* Aquí se renderizarán tus páginas de admin */}
                </div>
            </div>
        </div>
    );
};

export default AdminLayout;
