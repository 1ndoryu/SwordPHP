import {Home, FileText, Image, Users, Settings, LogOut} from 'lucide-react';
import {NavLink} from 'react-router-dom';
import './Sidebar.css';

const Sidebar = () => {
    return (
        <aside className="barraLateral">
            <div className="cabeceraBarraLateral">
                <div className="logo">Sword v2</div>
            </div>

            <nav className="navegacionBarraLateral">
                <ul>
                    <li>
                        <NavLink to="/" className={({isActive}) => (isActive ? 'activo' : '')}>
                            <Home size={18} />
                            <span>Dashboard</span>
                        </NavLink>
                    </li>
                    <li>
                        <NavLink to="/contents" className={({isActive}) => (isActive ? 'activo' : '')}>
                            <FileText size={18} />
                            <span>Contenidos</span>
                        </NavLink>
                    </li>
                    <li>
                        <NavLink to="/media" className={({isActive}) => (isActive ? 'activo' : '')}>
                            <Image size={18} />
                            <span>Medios</span>
                        </NavLink>
                    </li>
                    <li>
                        <NavLink to="/users" className={({isActive}) => (isActive ? 'activo' : '')}>
                            <Users size={18} />
                            <span>Usuarios</span>
                        </NavLink>
                    </li>
                    <li>
                        <NavLink to="/settings" className={({isActive}) => (isActive ? 'activo' : '')}>
                            <Settings size={18} />
                            <span>Ajustes</span>
                        </NavLink>
                    </li>
                </ul>
            </nav>

            <div className="pieBarraLateral">
                <button className="botonCerrarSesion">
                    <LogOut size={18} />
                    <span>Cerrar Sesión</span>
                </button>
            </div>
        </aside>
    );
};

export default Sidebar;
