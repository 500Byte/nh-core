<?php
namespace NormaHana\Core\Common;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface HookSubscriberInterface {
    /**
     * Registra las acciones y filtros de WordPress para esta clase.
     */
    public function register_hooks(): void;
}
