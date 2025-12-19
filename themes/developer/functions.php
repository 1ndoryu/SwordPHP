<?php

/**
 * Funciones del tema Developer
 * 
 * Este archivo se carga automáticamente cuando el tema está activo.
 * Aquí puedes definir funciones personalizadas, registrar menús,
 * y configurar características del tema.
 */

/**
 * Configuración inicial del tema
 */
function developerConfiguracion(): void
{
    /* 
     * Registrar ubicaciones de menús soportadas por el tema
     * Esto será usado en futuras fases cuando se implemente el sistema de menús
     */
}

/**
 * Scripts y estilos del tema
 * 
 * @return array Lista de assets a cargar
 */
function developerAssets(): array
{
    return [
        'css' => [
            'style.css'
        ],
        'js' => []
    ];
}
