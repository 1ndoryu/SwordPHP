<?php
declare(strict_types=1);

namespace App\Service;

class Config
{
    protected array $config = [];

    /**
     * Constructor que recibe directamente el array de configuración.
     *
     * @param array $config El array con toda la configuración de la aplicación.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Obtiene un valor de la configuración usando notación de puntos.
     * Ejemplo: get('view.path')
     *
     * @param string $key La clave a buscar (ej: 'database.host').
     * @param mixed $default El valor por defecto a retornar si no se encuentra la clave.
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}
