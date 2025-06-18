<?php

namespace App\service;

/**
 * Servicio para gestionar el sistema de Hooks (Acciones y Filtros).
 * Implementa el patrón Singleton para garantizar un único registro de hooks.
 */
class HookService
{
    private static ?self $instancia = null;

    /** @var array<string, array<int, array<int, array{callback: callable, accepted_args: int}>>> */
    private array $acciones = [];

    /** @var array<string, array<int, array<int, array{callback: callable, accepted_args: int}>>> */
    private array $filtros = [];

    private function __construct() {}
    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("No se puede deserializar un singleton.");
    }

    public static function getInstancia(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Añade un callback a un hook específico (acción o filtro).
     *
     * @param array $hooks El array de hooks (acciones o filtros) por referencia.
     * @param string $nombreHook El nombre del hook.
     * @param callable $callback El callback a ejecutar.
     * @param int $prioridad La prioridad de ejecución.
     * @param int $argumentosAceptados El número de argumentos que acepta el callback.
     */
    private function agregar(array &$hooks, string $nombreHook, callable $callback, int $prioridad, int $argumentosAceptados): void
    {
        $hooks[$nombreHook][$prioridad][] = [
            'callback' => $callback,
            'accepted_args' => $argumentosAceptados,
        ];

        if (isset($hooks[$nombreHook])) {
            ksort($hooks[$nombreHook]);
        }
    }

    public function agregarAccion(string $nombreAccion, callable $callback, int $prioridad = 10, int $argumentosAceptados = 1): void
    {
        $this->agregar($this->acciones, $nombreAccion, $callback, $prioridad, $argumentosAceptados);
    }

    public function agregarFiltro(string $nombreFiltro, callable $callback, int $prioridad = 10, int $argumentosAceptados = 1): void
    {
        $this->agregar($this->filtros, $nombreFiltro, $callback, $prioridad, $argumentosAceptados);
    }

    /**
     * Ejecuta todos los callbacks asociados a una acción.
     *
     * @param string $nombreAccion El nombre de la acción.
     * @param mixed ...$args Argumentos para pasar a los callbacks.
     */
    public function hacerAccion(string $nombreAccion, ...$args): void
    {
        if (!isset($this->acciones[$nombreAccion])) {
            return;
        }

        foreach ($this->acciones[$nombreAccion] as $callbacksPorPrioridad) {
            foreach ($callbacksPorPrioridad as $hook) {
                $argumentosParaCallback = array_slice($args, 0, $hook['accepted_args']);
                call_user_func_array($hook['callback'], $argumentosParaCallback);
            }
        }
    }

    /**
     * Aplica todos los filtros a un valor.
     *
     * @param string $nombreFiltro El nombre del filtro.
     * @param mixed $valor El valor inicial a filtrar.
     * @param mixed ...$args Argumentos adicionales para pasar a los callbacks.
     * @return mixed El valor modificado después de aplicar todos los filtros.
     */
    public function aplicarFiltro(string $nombreFiltro, $valor, ...$args): mixed
    {
        if (!isset($this->filtros[$nombreFiltro])) {
            return $valor;
        }

        $argumentos = array_merge([$valor], $args);

        foreach ($this->filtros[$nombreFiltro] as $callbacksPorPrioridad) {
            foreach ($callbacksPorPrioridad as $hook) {
                $argumentosParaCallback = array_slice($argumentos, 0, $hook['accepted_args']);
                $valor = call_user_func_array($hook['callback'], $argumentosParaCallback);
                $argumentos[0] = $valor;
            }
        }

        return $valor;
    }
}
