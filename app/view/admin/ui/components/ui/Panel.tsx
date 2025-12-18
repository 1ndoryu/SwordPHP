import React from 'react';

interface PanelProps {
    title?: string;
    children: React.ReactNode;
    footer?: React.ReactNode;
    className?: string;
    id?: string;
}

export const Panel: React.FC<PanelProps> = ({title, children, footer, className = '', id}) => {
    return (
        <div className={`panelLateral ${className}`.trim()} id={id}>
            {title && <h3 className="tituloPanelLateral">{title}</h3>}
            <div className="contenidoPanelLateral">{children}</div>
            {footer && <div className="piePanelLateral">{footer}</div>}
        </div>
    );
};
