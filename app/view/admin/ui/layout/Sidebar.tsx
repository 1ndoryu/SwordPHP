import React from 'react';
import {NavLink} from 'react-router-dom';
import '../types';

export const Sidebar = () => {
    const sword = window.sword || {
        postTypes: {},
        user: 'Guest',
        title: 'Admin',
        currentRoute: '',
        currentPostType: null
    };

    const {postTypes} = sword;

    const activeClass = ({isActive}: {isActive: boolean}) => `enlaceNavegacion ${isActive ? 'activo' : ''}`;

    return (
        <aside className="barraLateral">
            <nav>
                <NavLink to="/" end className={activeClass}>
                    Dashboard
                </NavLink>

                <div className="seccionMenu">
                    <span className="tituloSeccion">Contenidos</span>
                    {Object.entries(postTypes).map(([slug, type]) => (
                        <NavLink key={slug} to={`/${slug}`} className={activeClass}>
                            {type.nombre}
                        </NavLink>
                    ))}
                </div>

                <div className="seccionMenu">
                    <span className="tituloSeccion">Sistema</span>
                    <NavLink to="/media" className={activeClass}>
                        Medios
                    </NavLink>
                    <NavLink to="/themes" className={activeClass}>
                        Temas
                    </NavLink>
                    <NavLink to="/users" className={activeClass}>
                        Usuarios
                    </NavLink>
                    <NavLink to="/settings" className={activeClass}>
                        Ajustes
                    </NavLink>
                </div>
            </nav>
        </aside>
    );
};
