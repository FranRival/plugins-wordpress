<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TUM_Settings {

	const OPTION_KEY = 'tum_settings';

	public static function get_all() {
		$defaults = array(
			'alert_email'      => get_option( 'admin_email' ),
			'codes'            => array( 'fr', 'ar', 'zh-CN', 'de', 'cs', 'nl', 'en', 'it', 'ja', 'pt', 'ru', 'es', 'uk', 'vi' ),
			'sample_count'     => 3,
			'interval_minutes' => 60,
			'fail_threshold'   => 2,
		);
		$saved = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( $saved, $defaults );
	}

	public static function get( $key, $default = null ) {
		$all = self::get_all();
		return isset( $all[ $key ] ) ? $all[ $key ] : $default;
	}

	public static function update( $data ) {
		$current = self::get_all();
		$merged  = wp_parse_args( $data, $current );
		update_option( self::OPTION_KEY, $merged );
	}

	public static function allowed_codes() {
		$codes = self::get( 'codes', array() );
		return is_array( $codes ) ? $codes : array();
	}
}
