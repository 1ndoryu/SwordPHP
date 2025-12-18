import React, {ReactNode} from 'react';

interface ModalProps {
    isOpen: boolean;
    onClose: () => void;
    children: ReactNode;
    title?: string;
    className?: string; // Additional classes for content
}

export const Modal = ({isOpen, onClose, children, title, className = ''}: ModalProps) => {
    if (!isOpen) return null;

    return (
        <div className="modalOverlay" onClick={onClose} style={{zIndex: 10000}}>
            <div className={`modalContenido ${className}`} onClick={e => e.stopPropagation()}>
                {title && <h3>{title}</h3>}
                {children}
            </div>
        </div>
    );
};
