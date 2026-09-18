<?php
/**
 * Plugin Name: Translation URL Monitor
 * Description: Revisa periódicamente (vía WP-Cron) si las URLs traducidas (/idioma/categoría/título) siguen funcionando, y avisa por correo apenas detecta fallas seguidas — para enterarte el mismo día que GTranslate (o cualquier plugin de traducción activo) se rompa, sin tener que revisar manualmente.
 * Version: 1.0.0
 * Author: Emmanuel Ibarra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TUM_PATH', plugin_dir_path( __FILE__ ) );

require_once TUM_PATH . 'includes/class-tum-settings.php';
require_once TUM_PATH . 'includes/class-tum-checker.php';
require_once TUM_PATH . 'includes/class-tum-admin.php';

register_activation_hook( __FILE__, function () {
	TUM_Checker::schedule();
} );

register_deactivation_hook( __FILE__, function () {
	TUM_Checker::unschedule();
} );

add_action( 'plugins_loaded', function () {
	TUM_Checker::init();
	if ( is_admin() ) {
		TUM_Admin::instance();
	}
} );
