import {BrowserRouter, Routes, Route} from 'react-router-dom';
import Layout from './components/layout/Layout';
import Dashboard from './pages/Dashboard';

function App() {
    return (
        <BrowserRouter basename="/admin">
            <Routes>
                <Route path="/" element={<Layout />}>
                    <Route index element={<Dashboard />} />
                    <Route path="contents" element={<div>Contenidos</div>} />
                    <Route path="media" element={<div>Medios</div>} />
                    <Route path="users" element={<div>Usuarios</div>} />
                    <Route path="settings" element={<div>Ajustes</div>} />
                    <Route path="*" element={<div>Not Found</div>} />
                </Route>
            </Routes>
        </BrowserRouter>
    );
}

export default App;
