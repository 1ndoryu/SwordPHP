import React, {TextareaHTMLAttributes} from 'react';

interface TextareaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
    label?: string;
    helpText?: string;
    error?: string;
}

export const Textarea: React.FC<TextareaProps> = props => {
    const {label, name, className = '', id, helpText, error, required, rows = 5, ...rest} = props;

    const inputId = id || `textarea_${name}`;

    return (
        <div className={`grupoFormulario ${error ? 'conError' : ''}`.trim()}>
            {label && (
                <label htmlFor={inputId} className="etiquetaCampo">
                    {label}
                    {required && <span className="requerido">*</span>}
                </label>
            )}

            <textarea id={inputId} name={name} className={`${className} textareaFormulario`.trim()} required={required} rows={rows} {...rest} />

            {helpText && <span className="ayudaCampo">{helpText}</span>}
            {error && <span className="mensajeError">{error}</span>}
        </div>
    );
};
