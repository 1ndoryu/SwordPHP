import React from 'react';
import {Toolbar} from '../components/structure/Toolbar';
import {MediaLibrary} from '../components/media/MediaLibrary';

export const Media = () => {
    return (
        <div className="contenedorAnimado">
            <Toolbar />
            <MediaLibrary className="contenedorMediosPage" />
        </div>
    );
};
