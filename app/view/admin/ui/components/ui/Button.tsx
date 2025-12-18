import React, {ButtonHTMLAttributes, AnchorHTMLAttributes} from 'react';
import {Link} from 'react-router-dom';

type Variant = 'primary' | 'secondary' | 'danger' | 'icon';

interface BaseProps {
    variant?: Variant;
    icon?: React.ReactNode;
    className?: string;
    children?: React.ReactNode;
}

type ButtonProps = BaseProps & ButtonHTMLAttributes<HTMLButtonElement> & {href?: never};
type AnchorProps = BaseProps & AnchorHTMLAttributes<HTMLAnchorElement> & {href: string};

export const Button: React.FC<ButtonProps | AnchorProps> = props => {
    const {className = '', variant = 'primary', children, icon, ...rest} = props;

    const variantClass =
        {
            primary: 'botonPrimario',
            secondary: 'botonSecundario',
            danger: 'botonPeligro',
            icon: 'botonIcono'
        }[variant] || 'botonPrimario';

    const combinedClass = `boton ${variantClass} ${className}`.trim();

    if ('href' in rest && rest.href) {
        // Use React Router Link for internal admin navigation
        if (rest.href.startsWith('/admin') && !rest.target) {
            // Remove /admin prefix for internal router navigation as Router is basename='/admin'
            const to = rest.href.replace(/^\/admin/, '') || '/';
            return (
                <Link to={to} className={combinedClass} {...(rest as Omit<AnchorHTMLAttributes<HTMLAnchorElement>, 'href'>)}>
                    {icon && <span className="iconoBoton">{icon}</span>}
                    {children}
                </Link>
            );
        }

        return (
            <a className={combinedClass} {...(rest as AnchorHTMLAttributes<HTMLAnchorElement>)}>
                {icon && <span className="iconoBoton">{icon}</span>}
                {children}
            </a>
        );
    }

    return (
        <button className={combinedClass} {...(rest as ButtonHTMLAttributes<HTMLButtonElement>)}>
            {icon && <span className="iconoBoton">{icon}</span>}
            {children}
        </button>
    );
};
