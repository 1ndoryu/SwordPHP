import React from 'react';
import {Sidebar} from './Sidebar';
import {Header} from './Header';
import {Outlet} from 'react-router-dom';

export const AdminLayout = () => {
    return (
        <div className="layoutAdministracion">
            <div className="bloque admin">
                <Sidebar />
                <main className="contenidoPrincipal">
                    <Header />
                    <div className="contenido" id="contenidoPrincipal">
                        <Outlet />
                    </div>
                </main>
            </div>
        </div>
    );
};
