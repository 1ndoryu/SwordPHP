import React, {useEffect, useState} from 'react';
import {useParams, useSearchParams, Link} from 'react-router-dom';
import {Toolbar} from '../components/structure/Toolbar';
import {Button} from '../components/ui/Button';
import {Badge} from '../components/ui/Badge';
import {Content, Pagination} from '../types';

export const Contents = () => {
    const {postType} = useParams<{postType: string}>();
    const [searchParams, setSearchParams] = useSearchParams();

    // State
    const [contents, setContents] = useState<Content[]>([]);
    const [pagination, setPagination] = useState<Pagination | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    // Filters from URL
    const page = parseInt(searchParams.get('page') || '1');
    const status = searchParams.get('status') || '';
    const search = searchParams.get('search') || '';

    const fetchContents = async () => {
        setLoading(true);
        setError(null);
        try {
            const typePath = postType ? `/${postType}` : '/contents';
            const query = new URLSearchParams({
                page: page.toString(),
                status,
                search
            });

            const response = await fetch(`/admin${typePath}?${query.toString()}`, {
                headers: {
                    Accept: 'application/json'
                }
            });

            if (!response.ok) throw new Error('Error cargando contenidos');

            const data = await response.json();
            setContents(data.contents);
            setPagination(data.pagination);
        } catch (err: any) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchContents();
    }, [postType, page, status, search]);

    const handleSearch = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const form = e.currentTarget;
        const searchValue = (form.elements.namedItem('search') as HTMLInputElement).value;
        setSearchParams(prev => {
            const newParams = new URLSearchParams(prev);
            newParams.set('search', searchValue);
            newParams.set('page', '1');
            return newParams;
        });
    };

    const handlePageChange = (newPage: number) => {
        setSearchParams(prev => {
            const newParams = new URLSearchParams(prev);
            newParams.set('page', newPage.toString());
            return newParams;
        });
    };

    return (
        <div className="contenedorListado">
            <Toolbar
                left={
                    <>
                        <Button href={`/admin/${postType === 'contents' ? 'post' : postType || 'post'}/create`} className="botonNuevo" variant="primary">
                            + Nuevo {postType === 'page' ? 'Página' : 'Contenido'}
                        </Button>
                        <Button href="/admin/contents/trash" className="enlacePapelera" variant="secondary">
                            Papelera
                        </Button>
                    </>
                }
                right={
                    <>
                        <div className="formularioFiltros">{/* Status Filter could go here */}</div>
                        <form onSubmit={handleSearch} className="grupoBusqueda">
                            <input type="text" name="search" defaultValue={search} className="inputBusqueda" placeholder="Buscar por título..." />
                            <button type="submit" className="botonBuscar">
                                Buscar
                            </button>
                        </form>
                    </>
                }
            />

            <div className="resumenListado">
                <span className="contadorTotal">{pagination?.total_items || 0} contenidos encontrados</span>
                <span className="ayudaSeleccion">Ctrl+clic para seleccionar varios</span>
                {(search || status) && (
                    <Link to={`/admin/${postType || 'contents'}`} className="enlaceLimpiarFiltros">
                        Limpiar filtros
                    </Link>
                )}
            </div>

            <div className="contenedorTabla">
                {loading ? (
                    <div className="mensajeVacio">
                        <p>Cargando contenidos...</p>
                        <div className="contenido cargando"></div>
                    </div>
                ) : error ? (
                    <div className="mensajeVacio estadoError">{error}</div>
                ) : contents.length === 0 ? (
                    <div className="mensajeVacio">
                        <p>No se encontraron contenidos</p>
                        <Button href={`/admin/${postType === 'contents' ? 'post' : postType || 'post'}/create`}>Crear el primero</Button>
                    </div>
                ) : (
                    <table className="tablaContenidos" id="tablaContenidos">
                        <thead>
                            <tr>
                                <th className="columnaTitulo">Título</th>
                                <th className="columnaTipo">Tipo</th>
                                <th className="columnaEstado">Estado</th>
                                <th className="columnaAutor">Autor</th>
                                <th className="columnaFecha">Fecha</th>
                                <th className="columnaAcciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {contents.map(item => (
                                <tr key={item.id} className="filaContenido">
                                    <td className="columnaTitulo">
                                        <Link to={`/${item.type}/${item.id}/edit`} className="enlaceTitulo">
                                            {item.content_data?.title || item.title || '(Sin título)'}
                                        </Link>
                                        <span className="slugContenido">/{item.slug}</span>
                                    </td>
                                    <td className="columnaTipo">
                                        <span className={`etiquetaTipo etiquetaTipo${item.type.charAt(0).toUpperCase() + item.type.slice(1)}`}>{item.type.charAt(0).toUpperCase() + item.type.slice(1)}</span>
                                    </td>
                                    <td className="columnaEstado">{item.status === 'published' ? <Badge variant="success">Publicado</Badge> : <Badge variant="secondary">Borrador</Badge>}</td>
                                    <td className="columnaAutor">{item.user?.username || 'Desconocido'}</td>
                                    <td className="columnaFecha">{new Date(item.created_at).toLocaleDateString()}</td>
                                    <td className="columnaAcciones">
                                        <div className="grupoAcciones">
                                            <a href={`/${item.slug}`} target="_blank" className="botonAccion botonVer" rel="noopener noreferrer">
                                                Ver
                                            </a>
                                            <button
                                                className="botonAccion botonEliminar"
                                                onClick={e => {
                                                    e.stopPropagation();
                                                    console.log('Delete', item.id);
                                                }}>
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>

            {pagination && pagination.total_pages > 1 && (
                <div className="paginacion">
                    <Button variant="secondary" disabled={pagination.current <= 1} onClick={() => handlePageChange(pagination!.current - 1)} className="botonPagina">
                        Anterior
                    </Button>
                    <span className="paginacionElipsis">
                        Página {pagination.current} de {pagination.total_pages}
                    </span>
                    <Button variant="secondary" disabled={pagination.current >= pagination.total_pages} onClick={() => handlePageChange(pagination!.current + 1)} className="botonPagina">
                        Siguiente
                    </Button>
                </div>
            )}
        </div>
    );
};
