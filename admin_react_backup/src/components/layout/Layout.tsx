import {Outlet} from 'react-router-dom';
import Sidebar from './Sidebar';
import './Layout.css';

const Layout = () => {
    return (
        <div className="disenoPrincipal">
            <Sidebar />
            <main className="contenidoPrincipal">
                <Outlet />
            </main>
        </div>
    );
};

export default Layout;
