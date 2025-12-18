import React, {useState} from 'react';
import {Modal} from '../ui/Modal';
import {MediaLibrary} from './MediaLibrary';
import {Button} from '../ui/Button';
import {Media} from '../../types';

interface MediaSelectorProps {
    isOpen: boolean;
    onClose: () => void;
    onSelect: (media: Media) => void;
    title?: string;
}

export const MediaSelector = ({isOpen, onClose, onSelect, title = 'Seleccionar Medio'}: MediaSelectorProps) => {
    const [selected, setSelected] = useState<Media | null>(null);

    const handleConfirm = () => {
        if (selected) {
            onSelect(selected);
            onClose();
        }
    };

    return (
        <Modal
            isOpen={isOpen}
            onClose={onClose}
            title={title}
            className="selectorMediosContenedor" // Reuse existing CSS class for size/layout
        >
            <div className="contenedorFlexModal">
                {/* Override some styles via style prop if needed because MediaLibrary has toolbar which we want */}
                <MediaLibrary
                    onSelect={setSelected}
                    className="selectorMediosContenido" // This makes it scrollable
                    embedded={true}
                />

                <div className="selectorMediosPie">
                    <div className="selectorMediosInfo">{selected ? `Seleccionado: ${selected.metadata.original_name}` : 'Ningún archivo seleccionado'}</div>
                    <div className="selectorMediosAcciones">
                        <Button variant="secondary" onClick={onClose}>
                            Cancelar
                        </Button>
                        <Button variant="primary" disabled={!selected} onClick={handleConfirm}>
                            Insertar
                        </Button>
                    </div>
                </div>
            </div>
        </Modal>
    );
};
