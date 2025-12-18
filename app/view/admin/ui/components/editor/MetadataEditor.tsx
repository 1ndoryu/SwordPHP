import React from 'react';

interface MetadataParams {
    key: string;
    value: string;
    isJson: boolean;
}

interface MetadataEditorProps {
    metaData: MetadataParams[];
    onAdd: () => void;
    onUpdate: (index: number, field: keyof MetadataParams, value: any) => void;
    onRemove: (index: number) => void;
}

export const MetadataEditor: React.FC<MetadataEditorProps> = ({metaData, onAdd, onUpdate, onRemove}) => {
    return (
        <div className="seccionMetadatos" id="seccionMetadatos">
            <div className="encabezadoMetadatos">
                <h3 className="tituloSeccion">Metadatos</h3>
                <button type="button" className="botonSecundario botonAgregarMeta" onClick={onAdd}>
                    + Agregar campo
                </button>
            </div>

            <div className="listaMetadatos">
                {metaData.length === 0 ? (
                    <div className="mensajeSinMetadatos">
                        <p>No hay metadatos adicionales. Haz clic en "Agregar campo" para crear uno.</p>
                    </div>
                ) : (
                    metaData.map((meta, index) => (
                        <div key={index} className="filaMetadato">
                            <div className="campoMetaClave">
                                <input type="text" className="inputMetaClave" placeholder="Clave" value={meta.key} onChange={e => onUpdate(index, 'key', e.target.value)} />
                            </div>
                            <div className="campoMetaValor">
                                <textarea placeholder="Valor" value={meta.value} onChange={e => onUpdate(index, 'value', e.target.value)} className="inputMetaValor textareaMetaValor" />
                            </div>
                            <div className="accionesMetadato">
                                <button type="button" className="botonEditarClave">
                                    Editar
                                </button>
                                <button type="button" className="botonEliminarMeta" onClick={() => onRemove(index)}>
                                    X
                                </button>
                            </div>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
};
