<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TUM_Checker {

	const CRON_HOOK = 'tum_run_check';

	public static function init() {
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_schedule' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'run_check' ) );
	}

	public static function add_cron_schedule( $schedules ) {
		$minutes = (int) TUM_Settings::get( 'interval_minutes', 60 );
		$minutes = max( 5, $minutes );
		$schedules['tum_custom'] = array(
			'interval' => $minutes * 60,
			'display'  => sprintf( 'Cada %d minutos (Translation Monitor)', $minutes ),
		);
		return $schedules;
	}

	public static function schedule() {
		self::unschedule();
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 60, 'tum_custom', self::CRON_HOOK );
		}
	}

	public static function unschedule() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}

	/**
	 * Devuelve los posts de muestra a monitorear. Se mantienen fijos
	 * entre corridas (para comparar peras con peras) hasta que se
	 * fuerza un nuevo sorteo o alguno deja de estar publicado.
	 */
	public static function get_sample_posts( $force_new = false ) {
		$stored = get_option( 'tum_sample_posts', array() );
		$count  = (int) TUM_Settings::get( 'sample_count', 3 );

		if ( ! $force_new && ! empty( $stored ) ) {
			$valid = array_filter( $stored, function ( $id ) {
				return get_post_status( $id ) === 'publish';
			} );
			if ( count( $valid ) >= $count ) {
				return array_slice( array_values( $valid ), 0, $count );
			}
		}

		$query = new WP_Query( array(
			'post_type'      => 'any',
			'post_status'    => 'publish',
			'posts_per_page' => $count,
			'orderby'        => 'rand',
			'fields'         => 'ids',
		) );

		$ids = $query->posts;
		update_option( 'tum_sample_posts', $ids );
		return $ids;
	}

	private static function fetch_ok( $url ) {
		$response = wp_remote_get( $url, array(
			'timeout'     => 15,
			'redirection' => 3,
			'sslverify'   => true,
		) );

		if ( is_wp_error( $response ) ) {
			return array( false, $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( $code !== 200 ) {
			return array( false, 'HTTP ' . $code );
		}

		if ( strlen( $body ) < 500 ) {
			return array( false, 'Respuesta demasiado corta o vacía' );
		}

		return array( true, '' );
	}

	public static function run_check() {
		$codes = TUM_Settings::allowed_codes();
		$ids   = self::get_sample_posts();

		if ( empty( $ids ) || empty( $codes ) ) {
			return;
		}

		$home = trailingslashit( get_option( 'home' ) );

		// 1. Chequeo base: ¿el sitio en su idioma original responde?
		// Si esto falla, es una caída general, no un problema de traducción.
		$baseline_fail = 0;
		foreach ( $ids as $id ) {
			list( $ok ) = self::fetch_ok( get_permalink( $id ) );
			if ( ! $ok ) {
				$baseline_fail++;
			}
		}

		if ( $baseline_fail > ( count( $ids ) / 2 ) ) {
			self::maybe_alert_site_down();
			update_option( 'tum_last_run', current_time( 'mysql' ) );
			return;
		}

		// 2. Chequeo por cada código de idioma/país.
		$status    = get_option( 'tum_status', array() );
		$threshold = (int) TUM_Settings::get( 'fail_threshold', 2 );

		foreach ( $codes as $code ) {
			$fails      = 0;
			$last_error = '';

			foreach ( $ids as $id ) {
				$permalink = get_permalink( $id );
				$rest      = substr( $permalink, strlen( $home ) );
				$url       = $home . $code . '/' . $rest;

				list( $ok, $err ) = self::fetch_ok( $url );
				if ( ! $ok ) {
					$fails++;
					$last_error = $err;
				}
			}

			$lang_ok = $fails <= floor( count( $ids ) / 2 );

			$entry = isset( $status[ $code ] ) ? $status[ $code ] : array(
				'fail_streak' => 0,
				'alerted'     => false,
			);

			if ( $lang_ok ) {
				$entry['fail_streak'] = 0;
				$entry['alerted']     = false;
				$entry['last_error']  = '';
			} else {
				$entry['fail_streak'] = ( isset( $entry['fail_streak'] ) ? $entry['fail_streak'] : 0 ) + 1;
				$entry['last_error']  = $last_error;
			}

			$entry['last_ok']    = $lang_ok;
			$entry['last_check'] = current_time( 'mysql' );

			if ( ! $lang_ok && $entry['fail_streak'] >= $threshold && empty( $entry['alerted'] ) ) {
				self::send_alert( $code, $entry );
				$entry['alerted'] = true;
			}

			$status[ $code ] = $entry;
		}

		update_option( 'tum_status', $status );
		update_option( 'tum_last_run', current_time( 'mysql' ) );
	}

	private static function maybe_alert_site_down() {
		if ( get_option( 'tum_site_down_alerted', false ) ) {
			return; // Ya se avisó de esta caída, evita spam.
		}

		$to      = TUM_Settings::get( 'alert_email', get_option( 'admin_email' ) );
		$subject = '[' . get_bloginfo( 'name' ) . '] El sitio no está respondiendo';
		$message = "El chequeo automático no pudo cargar ni siquiera las páginas en el idioma original.\n\n"
			. "Esto probablemente significa que el sitio completo está caído, no un problema específico de traducción.\n\n"
			. 'Revisa: ' . home_url( '/' );

		wp_mail( $to, $subject, $message );
		update_option( 'tum_site_down_alerted', true );
	}

	private static function send_alert( $code, $entry ) {
		update_option( 'tum_site_down_alerted', false ); // El sitio base sí responde.

		$to      = TUM_Settings::get( 'alert_email', get_option( 'admin_email' ) );
		$subject = '[' . get_bloginfo( 'name' ) . '] Las URLs en "' . $code . '" dejaron de funcionar';
		$message = "El monitor detectó que las páginas traducidas con el código \"{$code}\" fallaron en las últimas {$entry['fail_streak']} revisiones seguidas.\n\n"
			. 'Último error: ' . $entry['last_error'] . "\n\n"
			. 'Revisa manualmente: ' . trailingslashit( get_option( 'home' ) ) . $code . "/\n\n"
			. 'Si esto se debe a que el plugin de traducción actual falló, activa el plugin de reemplazo (Local Translate URLs) para ese idioma.';

		wp_mail( $to, $subject, $message );
	}
}
