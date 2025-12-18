import React from 'react';
import {Panel} from '../components/ui/Panel';
import {Alert} from '../components/ui/Alert';
import {Badge} from '../components/ui/Badge';
import {Button} from '../components/ui/Button';

export const Dashboard = () => {
    return (
        <div>
            <div style={{marginBottom: '20px'}}>
                <Alert variant="info" dismissible>
                    Bienvenido a SwordPHP v2. El panel está migrando a React.
                </Alert>
            </div>

            <div style={{display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '20px'}}>
                <Panel title="Estado del Sistema">
                    <div style={{display: 'flex', flexDirection: 'column', gap: '8px'}}>
                        <div>
                            Versión: <Badge variant="primary">v2.0.0</Badge>
                        </div>
                        <div>Frontend: React + Vite</div>
                        <div>Backend: Workerman + PHP</div>
                    </div>
                </Panel>

                <Panel title="Accesos Rápidos">
                    <div style={{display: 'flex', gap: '10px', flexWrap: 'wrap'}}>
                        {/* Note: href used with Button renders an anchor tag */}
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
