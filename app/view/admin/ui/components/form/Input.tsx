import React, {InputHTMLAttributes} from 'react';

interface InputProps extends Omit<InputHTMLAttributes<HTMLInputElement>, 'prefix'> {
    label?: string;
    helpText?: string;
    error?: string;
    prefix?: React.ReactNode;
    suffix?: React.ReactNode;
}

export const Input: React.FC<InputProps> = props => {
    const {label, name, className = '', id, helpText, error, prefix, suffix, required, ...rest} = props;

    // Fallback ID generation could be an issue in hydration if name is not unique or stable,
    // but usually it is.
    const inputId = id || `input_${name}`;

    return (
        <div className={`grupoFormulario ${error ? 'conError' : ''}`.trim()}>
            {label && (
                <label htmlFor={inputId} className="etiquetaCampo">
                    {label}
                    {required && <span className="requerido">*</span>}
                    {helpText && <span className="ayudaCampo">{helpText}</span>}
                </label>
            )}

            <div className="contenedorInput">
                {prefix && <span className="prefijoInput">{prefix}</span>}

                <input id={inputId} name={name} className={`${className} inputFormulario`.trim()} required={required} {...rest} />

                {suffix && <span className="sufijoInput">{suffix}</span>}
            </div>

            {error && <span className="mensajeError">{error}</span>}
        </div>
    );
};
