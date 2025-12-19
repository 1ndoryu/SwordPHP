import React, {useEffect, useState} from 'react';
import {Badge} from '../components/ui/Badge';
import {Button} from '../components/ui/Button';

interface Theme {
    slug: string;
    nombre: string;
    version: string;
    autor: string;
    descripcion: string;
    screenshot: string | null;
    modo: string;
    activo: boolean;
}

interface ThemesResponse {
    success: boolean;
    data: {
        temas: Theme[];
        temaActivo: string;
    };
}

export const Themes = () => {
    const [temas, setTemas] = useState<Theme[]>([]);
    const [temaActivo, setTemaActivo] = useState<string>('');
    const [cargando, setCargando] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [activando, setActivando] = useState<string | null>(null);

    const cargarTemas = async () => {
        setCargando(true);
        setError(null);

        try {
            const response = await fetch('/admin/themes', {
                headers: {Accept: 'application/json'}
            });

            if (!response.ok) throw new Error('Error cargando temas');

            const data: ThemesResponse = await response.json();

            if (data.success) {
                setTemas(data.data.temas);
                setTemaActivo(data.data.temaActivo);
            }
        } catch (err: unknown) {
            const mensaje = err instanceof Error ? err.message : 'Error desconocido';
            setError(mensaje);
        } finally {
            setCargando(false);
        }
    };

    const activarTema = async (slug: string) => {
        if (slug === temaActivo) return;

        setActivando(slug);

        try {
            const response = await fetch(`/admin/themes/${slug}/activate`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                setTemaActivo(slug);
                setTemas(prev =>
                    prev.map(t => ({
                        ...t,
                        activo: t.slug === slug
                    }))
                );
            }
        } catch (err: unknown) {
            console.error('Error activando tema:', err);
        } finally {
            setActivando(null);
        }
    };

    useEffect(() => {
        cargarTemas();
    }, []);

    if (cargando) {
        return (
            <div id="pagina-temas" className="paginaTemas">
                <div className="temasEstadoVacio">
                    <div className="temasSpinner" />
                    <p>Cargando temas...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div id="pagina-temas" className="paginaTemas">
                <div className="temasEstadoError">
                    <span className="temasIconoError">!</span>
                    <p>{error}</p>
                    <Button variant="secondary" onClick={cargarTemas}>
                        Reintentar
                    </Button>
                </div>
            </div>
        );
    }

    return (
        <div id="pagina-temas" className="paginaTemas">
            <div className="temasEncabezado">
                <span className="temasContador">{temas.length} temas instalados</span>
            </div>

            <div className="temasGrilla">
                {temas.map(tema => (
                    <article key={tema.slug} id={`tema-${tema.slug}`} className={`temaTarjeta ${tema.activo ? 'temaTarjetaActiva' : ''}`}>
                        <div className="temaPreview">
                            {tema.screenshot ? (
                                <img src={tema.screenshot} alt={`Preview de ${tema.nombre}`} className="temaScreenshot" />
                            ) : (
                                <div className="temaPreviewVacio">
                                    <span className="temaPreviewIcono">T</span>
                                </div>
                            )}
                            {tema.activo && (
                                <div className="temaActivoIndicador">
                                    <span>Activo</span>
                                </div>
                            )}
                        </div>

                        <div className="temaContenido">
                            <div className="temaInfo">
                                <h3 className="temaNombre">{tema.nombre}</h3>
                                <div className="temaMeta">
                                    <Badge variant="secondary">{tema.modo.toUpperCase()}</Badge>
                                    <span className="temaVersion">v{tema.version}</span>
                                </div>
                            </div>

                            {tema.descripcion && <p className="temaDescripcion">{tema.descripcion}</p>}

                            <div className="temaAutor">
                                <span className="temaAutorLabel">por</span>
                                <span className="temaAutorNombre">{tema.autor}</span>
                            </div>

                            <div className="temaAcciones">
                                {tema.activo ? (
                                    <Button variant="secondary" disabled className="temaBotonActivo">
                                        Tema Activo
                                    </Button>
                                ) : (
                                    <Button variant="primary" onClick={() => activarTema(tema.slug)} disabled={activando === tema.slug} className="temaBotonActivar">
                                        {activando === tema.slug ? 'Activando...' : 'Activar'}
                                    </Button>
                                )}
                            </div>
                        </div>
                    </article>
                ))}
            </div>

            {temas.length === 0 && (
                <div className="temasEstadoVacio">
                    <span className="temasIconoVacio">T</span>
                    <p>No hay temas instalados</p>
                    <span className="temasAyuda">Coloca tus temas en la carpeta /themes</span>
                </div>
            )}
        </div>
    );
};
