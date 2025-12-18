import React from 'react';
import {Toolbar} from '../components/structure/Toolbar';
import {MediaLibrary} from '../components/media/MediaLibrary';

export const Media = () => {
    return (
        <div className="contenedorAnimado">
            <Toolbar left={<h1 className="tituloPagina">Medios</h1>} />
            <MediaLibrary className="contenedorMediosPage" />
        </div>
    );
};
