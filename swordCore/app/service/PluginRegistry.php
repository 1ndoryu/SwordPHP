<?php
namespace App\service;

/**
 * Un registro estático y simple para mantener datos a través del ciclo de vida de la aplicación.
 */
class PluginRegistry
{
    /** * @var array<string> Contiene los slugs de los plugins activos precargados desde la BD.
     */
    public static array $activePlugins = [];
}