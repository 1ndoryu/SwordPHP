<?php

/**
 * Componente de paginación reutilizable para el panel admin.
 * 
 * Variables requeridas:
 * @var string $baseUrl URL base para la paginación (ej: '/admin/media')
 * @var int $paginaActual Página actual (1-indexed)
 * @var int $totalPaginas Total de páginas disponibles
 * @var array $filtros Filtros adicionales a preservar en la URL (opcional)
 * @var string $idContenedor ID del contenedor de paginación (opcional, default: 'paginacion')
 */

$baseUrl = $baseUrl ?? '/admin';
$paginaActual = $paginaActual ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$filtros = $filtros ?? [];
$idContenedor = $idContenedor ?? 'paginacion';

if ($totalPaginas <= 1) {
    return;
}

/* 
 * Construir URL de paginación conservando filtros existentes
 */
$queryParams = [];
foreach ($filtros as $key => $value) {
    if (!empty($value) && $key !== 'page') {
        $queryParams[] = urlencode($key) . '=' . urlencode($value);
    }
}

$urlBase = $baseUrl . '?' . implode('&', $queryParams);
if (!empty($queryParams)) {
    $urlBase .= '&';
}
?>

<div class="paginacion" id="<?= htmlspecialchars($idContenedor) ?>">
    <?php if ($paginaActual > 1): ?>
        <a href="<?= $urlBase ?>page=<?= $paginaActual - 1 ?>" class="botonPagina">Anterior</a>
    <?php endif; ?>

    <?php
    /* 
     * Mostrar rango de páginas inteligente
     * Para muchas páginas, mostrar: 1 ... 4 5 [6] 7 8 ... 20
     */
    $rangoVisible = 2;
    $inicio = max(1, $paginaActual - $rangoVisible);
    $fin = min($totalPaginas, $paginaActual + $rangoVisible);
    ?>

    <?php if ($inicio > 1): ?>
        <a href="<?= $urlBase ?>page=1" class="botonPagina">1</a>
        <?php if ($inicio > 2): ?>
            <span class="paginacionElipsis">...</span>
        <?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
        <?php if ($i === $paginaActual): ?>
            <span class="botonPagina paginaActual"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= $urlBase ?>page=<?= $i ?>" class="botonPagina"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($fin < $totalPaginas): ?>
        <?php if ($fin < $totalPaginas - 1): ?>
            <span class="paginacionElipsis">...</span>
        <?php endif; ?>
        <a href="<?= $urlBase ?>page=<?= $totalPaginas ?>" class="botonPagina"><?= $totalPaginas ?></a>
    <?php endif; ?>

    <?php if ($paginaActual < $totalPaginas): ?>
        <a href="<?= $urlBase ?>page=<?= $paginaActual + 1 ?>" class="botonPagina">Siguiente</a>
    <?php endif; ?>
</div>