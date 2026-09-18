<?php
/**
 * Plugin Name: URL Exporter (multi-idioma)
 * Description: Exporta todas las URLs publicadas del sitio a CSV, opcionalmente multiplicadas por códigos de idioma/país, para reconstruir el mapa completo de URLs que genera un plugin como GTranslate.
 * Version: 1.0.0
 * Author: Emmanuel Ibarra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UE_PATH', plugin_dir_path( __FILE__ ) );

require_once UE_PATH . 'includes/class-ue-core.php';
require_once UE_PATH . 'includes/class-ue-admin.php';

add_action( 'plugins_loaded', function () {
	if ( is_admin() ) {
		UE_Admin::instance();
	}
} );
