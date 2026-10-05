<?php
namespace NormaHana\Core\Common;

if (! defined('ABSPATH')) {
    exit;
}

interface ServiceProviderInterface
{
    /**
     * Registra servicios y engancha acciones/filtros del módulo.
     */
    public function register(): void;
}
