import React from 'react';
import {BrowserRouter, Routes, Route} from 'react-router-dom';
import {AdminLayout} from './layout/AdminLayout';
import {Dashboard} from './pages/Dashboard';

import {Contents} from './pages/Contents';
import {Editor} from './pages/Editor';
import {Media} from './pages/Media';
import {Themes} from './pages/Themes';

const App = () => {
    return (
        <BrowserRouter basename="/admin">
            <Routes>
                <Route path="/" element={<AdminLayout />}>
                    <Route index element={<Dashboard />} />
                    <Route path="media" element={<Media />} />
                    <Route path="themes" element={<Themes />} />
                    <Route path=":postType" element={<Contents />} />
                    <Route path=":postType/create" element={<Editor />} />
                    <Route path=":postType/:id/edit" element={<Editor />} />
                    {/* Add other routes here later */}
                </Route>
            </Routes>
        </BrowserRouter>
    );
};

export default App;
