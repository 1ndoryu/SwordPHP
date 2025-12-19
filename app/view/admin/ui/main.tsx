import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';
import {inicializarScrollMinimalista} from './utils/scrollMinimalista';

const rootElement = document.getElementById('contenidoPrincipal'); // Mounting on the content area or the whole body?
// The current layout has sidebar + main.
// If we convert EVERYTHING, we should mount on a root div that replaces everything inside <body>.
// Let's verify layout.php again. It has <div class="layoutAdministracion">.
// I should probably replace the entire .layoutAdministracion with React.

const root = document.getElementById('root');

if (root) {
    console.log('[SwordPHP] React montandose en #root', window.sword);

    inicializarScrollMinimalista();

    ReactDOM.createRoot(root).render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
}
