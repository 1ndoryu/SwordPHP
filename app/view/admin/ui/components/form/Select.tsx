import React, {SelectHTMLAttributes} from 'react';

export interface SelectOption {
    value: string | number;
    label: string;
}

interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
    label?: string;
    options: SelectOption[];
    helpText?: string;
    error?: string;
    emptyOption?: string;
}

export const Select: React.FC<SelectProps> = props => {
    const {label, name, className = '', id, options, helpText, error, required, emptyOption, ...rest} = props;

    const inputId = id || `select_${name}`;

    return (
        <div className={`grupoFormulario ${error ? 'conError' : ''}`.trim()}>
            {label && (
                <label htmlFor={inputId} className="etiquetaCampo">
                    {label}
                    {required && <span className="requerido">*</span>}
                    {helpText && <span className="ayudaCampo">{helpText}</span>}
                </label>
            )}

            <select id={inputId} name={name} className={`${className} selectFormulario`.trim()} required={required} {...rest}>
                {emptyOption && <option value="">{emptyOption}</option>}
                {options.map(opt => (
                    <option key={opt.value} value={opt.value}>
                        {opt.label}
                    </option>
                ))}
            </select>

            {error && <span className="mensajeError">{error}</span>}
        </div>
    );
};
