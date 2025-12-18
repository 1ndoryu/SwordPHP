import React from 'react';
import {Panel} from '../components/ui/Panel';
import {Alert} from '../components/ui/Alert';
import {Badge} from '../components/ui/Badge';
import {Button} from '../components/ui/Button';

export const Dashboard = () => {
    return (
        <div id="dashboard">
            <div className="margenInferior">
                <Alert variant="info" dismissible>
                    Bienvenido a SwordPHP v2. El panel está migrando a React.
                </Alert>
            </div>

            <div className="grillaDashboard">
                <Panel title="Estado del Sistema">
                    <div className="grupoInfoSistema">
                        <div>
                            Versión: <Badge variant="primary">v2.0.0</Badge>
                        </div>
                        <div>Frontend: React + Vite</div>
                        <div>Backend: Workerman + PHP</div>
                    </div>
                </Panel>

                <Panel title="Accesos Rápidos">
                    <div className="grupoAccesosRapidos">
                        <Button variant="primary" href="/admin/post/create">
                            Nuevo Post
                        </Button>
                        <Button variant="secondary" href="/admin/media">
                            Gestión de Medios
                        </Button>
                    </div>
                </Panel>
            </div>
        </div>
    );
};
