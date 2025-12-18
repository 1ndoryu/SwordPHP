import React, {useMemo} from 'react';
import {useLocation} from 'react-router-dom';
import {PostTypeDef} from '../types';

export const Header = () => {
    const sword = window.sword || {user: 'Guest', postTypes: {}};
    const location = useLocation();

    const title = useMemo(() => {
        const path = location.pathname;

        /* Dashboard */
        if (path === '/' || path === '') {
            return 'Dashboard';
        }

        /* Media */
        if (path.startsWith('/media')) {
            return 'Biblioteca de Medios';
        }

        /* Post Types dinamicos */
        const postTypes = sword.postTypes || {};
        for (const [slug, config] of Object.entries(postTypes) as [string, PostTypeDef][]) {
            if (path.startsWith(`/${slug}`)) {
                if (path.includes('/create')) {
                    return `Nuevo ${config.nombre || slug}`;
                }
                if (path.includes('/edit')) {
                    return `Editar ${config.nombre || slug}`;
                }
                if (path.includes('/trash')) {
                    return `Papelera - ${config.nombre || slug}`;
                }
                return config.nombre || slug;
            }
        }

        return 'Admin';
    }, [location.pathname, sword.postTypes]);

    /* Actualizar titulo del documento */
    React.useEffect(() => {
        document.title = `${title} - SwordPHP Admin`;
    }, [title]);

    return (
        <header className="encabezado">
            <h1>{title}</h1>
            <div className="menuUsuario">
                Hola, {sword.user} |{' '}
                <a href="/admin/logout" data-no-spa="true">
                    Salir
                </a>
            </div>
        </header>
    );
};
