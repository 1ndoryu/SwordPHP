import React, {useState} from 'react';

interface AlertProps {
    children: React.ReactNode;
    variant?: 'success' | 'error' | 'warning' | 'info';
    dismissible?: boolean;
    onDismiss?: () => void;
}

export const Alert: React.FC<AlertProps> = ({children, variant = 'info', dismissible, onDismiss}) => {
    const classType = {
        success: 'alertaExito',
        error: 'alertaError',
        warning: 'alertaAdvertencia',
        info: 'alertaInfo'
    }[variant];

    const [visible, setVisible] = useState(true);

    if (!visible) return null;

    return (
        <div className={classType}>
            {children}
            {dismissible && (
                <button
                    type="button"
                    className="btnCerrarAlerta"
                    onClick={() => {
                        setVisible(false);
                        onDismiss?.();
                    }}>
                    ×
                </button>
            )}
        </div>
    );
};
