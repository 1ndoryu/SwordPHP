import React from 'react';

interface ToolbarProps {
    left?: React.ReactNode;
    right?: React.ReactNode;
    className?: string;
}

export const Toolbar: React.FC<ToolbarProps> = ({left, right, className = ''}) => {
    return (
        <div className={`barraHerramientas ${className}`.trim()}>
            <div className="barraHerramientasIzquierda">{left}</div>
            <div className="barraHerramientasDerecha">{right}</div>
        </div>
    );
};
