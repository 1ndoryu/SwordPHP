import React from 'react';

interface BadgeProps {
    children: React.ReactNode;
    variant?: 'default' | 'primary' | 'secondary' | 'success' | 'danger' | 'warning' | 'info';
    className?: string;
}

export const Badge: React.FC<BadgeProps> = ({children, variant = 'default', className = ''}) => {
    // Map variants to specific classes in indicadores.css
    const variantMap: Record<string, string> = {
        default: '',
        primary: 'etiquetaTipoPost',
        secondary: 'etiquetaTipoPage',
        success: 'estadoPublicado',
        danger: 'estadoError',
        warning: 'estadoBorrador',
        info: 'etiquetaTipoPost'
    };

    const variantClass = variantMap[variant] || '';

    // Determine base class if not provided
    // indicadores.css has 'etiquetaEstado' (large) and 'etiquetaEstadoContenido' (small)
    // If the user hasn't specified a specific structural class, default to 'etiquetaEstado'
    const hasBaseClass = className.includes('etiqueta');
    const baseClass = hasBaseClass ? '' : 'etiquetaEstado';

    return <span className={`${baseClass} ${variantClass} ${className}`.trim()}>{children}</span>;
};
