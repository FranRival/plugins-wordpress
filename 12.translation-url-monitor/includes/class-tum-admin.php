<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TUM_Admin {

	private static $instance = null;

	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_post_tum_save_settings', array( $this, 'save_settings' ) );
		add_action( 'wp_ajax_tum_run_now', array( $this, 'ajax_run_now' ) );
		add_action( 'wp_ajax_tum_reshuffle', array( $this, 'ajax_reshuffle' ) );
	}

	public function add_menu() {
		add_management_page(
			'Translation URL Monitor',
			'Translation Monitor',
			'manage_options',
			'tum-monitor',
			array( $this, 'render_page' )
		);
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = TUM_Settings::get_all();
		$status   = get_option( 'tum_status', array() );
		$last_run = get_option( 'tum_last_run', '' );
		$sample   = get_option( 'tum_sample_posts', array() );
		$nonce    = wp_create_nonce( 'tum_actions' );
		?>
		<div class="wrap">
			<h1>Translation URL Monitor</h1>
			<p>Revisa periódicamente si las URLs traducidas (<code>/idioma/categoría/título</code>) siguen funcionando, y avisa por correo apenas detecta fallas seguidas.</p>

			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success"><p>Configuración guardada.</p></div>
			<?php endif; ?>

			<h2>Estado actual</h2>
			<p>Última revisión: <strong><?php echo esc_html( $last_run ? $last_run : 'Todavía no se ha ejecutado' ); ?></strong></p>
			<p>
				Posts de muestra usados: <code><?php echo esc_html( implode( ', ', $sample ) ); ?></code>
				<button id="tum-reshuffle" class="button">Elegir otros posts al azar</button>
			</p>

			<table class="widefat striped" style="max-width:800px;">
				<thead>
					<tr><th>Código</th><th>Estado</th><th>Fallas seguidas</th><th>Último chequeo</th><th>Último error</th></tr>
				</thead>
				<tbody>
					<?php if ( empty( $status ) ) : ?>
						<tr><td colspan="5">Todavía no hay datos. Corre el chequeo manual abajo.</td></tr>
					<?php else : foreach ( $status as $code => $entry ) : ?>
						<tr>
							<td><?php echo esc_html( $code ); ?></td>
							<td><?php echo ! empty( $entry['last_ok'] ) ? '✅ OK' : '❌ Falla'; ?></td>
							<td><?php echo esc_html( isset( $entry['fail_streak'] ) ? $entry['fail_streak'] : 0 ); ?></td>
							<td><?php echo esc_html( isset( $entry['last_check'] ) ? $entry['last_check'] : '-' ); ?></td>
							<td><?php echo esc_html( isset( $entry['last_error'] ) ? $entry['last_error'] : '' ); ?></td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>

			<p style="margin-top:15px;">
				<button id="tum-run-now" class="button button-primary">Revisar ahora</button>
				<span id="tum-run-status"></span>
			</p>

			<hr />

			<h2>Configuración</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="tum_save_settings" />
				<?php wp_nonce_field( 'tum_save_settings' ); ?>

				<table class="form-table">
					<tr>
						<th><label for="tum_email">Correo para avisos</label></th>
						<td><input type="email" class="regular-text" name="tum_email" id="tum_email" value="<?php echo esc_attr( $settings['alert_email'] ); ?>" /></td>
					</tr>
					<tr>
						<th><label for="tum_codes">Códigos a monitorear (uno por línea)</label></th>
						<td><textarea name="tum_codes" id="tum_codes" rows="8" class="large-text code"><?php echo esc_textarea( implode( "\n", $settings['codes'] ) ); ?></textarea></td>
					</tr>
					<tr>
						<th><label for="tum_sample_count">Posts de muestra por chequeo</label></th>
						<td><input type="number" min="1" max="10" name="tum_sample_count" id="tum_sample_count" value="<?php echo esc_attr( $settings['sample_count'] ); ?>" /></td>
					</tr>
					<tr>
						<th><label for="tum_interval">Revisar cada</label></th>
						<td>
							<select name="tum_interval" id="tum_interval">
								<?php foreach ( array( 15, 30, 60, 180, 360, 1440 ) as $min ) : ?>
									<option value="<?php echo esc_attr( $min ); ?>" <?php selected( (int) $settings['interval_minutes'], $min ); ?>><?php echo esc_html( $min ); ?> minutos</option>
								<?php endforeach; ?>
							</select>
							<p class="description">Depende de que WP-Cron se dispare con visitas al sitio — con tu tráfico no debería ser un problema.</p>
						</td>
					</tr>
					<tr>
						<th><label for="tum_threshold">Fallas seguidas antes de avisar</label></th>
						<td>
							<input type="number" min="1" max="10" name="tum_threshold" id="tum_threshold" value="<?php echo esc_attr( $settings['fail_threshold'] ); ?>" />
							<p class="description">Evita falsas alarmas por un error de red pasajero.</p>
						</td>
					</tr>
				</table>

				<?php submit_button( 'Guardar configuración' ); ?>
			</form>
		</div>

		<script>
		( function () {
			var nonce   = '<?php echo esc_js( $nonce ); ?>';
			var ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

			function post( action, cb ) {
				var body = new URLSearchParams();
				body.append( 'action', action );
				body.append( 'nonce', nonce );
				fetch( ajaxUrl, { method: 'POST', body: body } )
					.then( function ( r ) { return r.json(); } )
					.then( cb );
			}

			document.getElementById( 'tum-run-now' ).addEventListener( 'click', function () {
				var btn = this;
				var statusEl = document.getElementById( 'tum-run-status' );
				btn.disabled = true;
				statusEl.textContent = ' Revisando... esto puede tardar unos segundos.';
				post( 'tum_run_now', function () {
					location.reload();
				} );
			} );

			document.getElementById( 'tum-reshuffle' ).addEventListener( 'click', function () {
				post( 'tum_reshuffle', function () {
					location.reload();
				} );
			} );
		} )();
		</script>
		<?php
	}

	public function save_settings() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'tum_save_settings' ) ) {
			wp_die( 'No autorizado' );
		}

		$codes_raw = isset( $_POST['tum_codes'] ) ? wp_unslash( $_POST['tum_codes'] ) : '';
		$codes     = array_filter( array_map( 'trim', preg_split( '/[\r\n]+/', $codes_raw ) ) );

		TUM_Settings::update( array(
			'alert_email'      => isset( $_POST['tum_email'] ) ? sanitize_email( wp_unslash( $_POST['tum_email'] ) ) : get_option( 'admin_email' ),
			'codes'            => array_values( $codes ),
			'sample_count'     => isset( $_POST['tum_sample_count'] ) ? max( 1, (int) $_POST['tum_sample_count'] ) : 3,
			'interval_minutes' => isset( $_POST['tum_interval'] ) ? (int) $_POST['tum_interval'] : 60,
			'fail_threshold'   => isset( $_POST['tum_threshold'] ) ? max( 1, (int) $_POST['tum_threshold'] ) : 2,
		) );

		TUM_Checker::schedule();

		wp_safe_redirect( add_query_arg( array( 'page' => 'tum-monitor', 'saved' => 1 ), admin_url( 'tools.php' ) ) );
		exit;
	}

	public function ajax_run_now() {
		check_ajax_referer( 'tum_actions', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		TUM_Checker::run_check();
		wp_send_json_success();
	}

	public function ajax_reshuffle() {
		check_ajax_referer( 'tum_actions', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		TUM_Checker::get_sample_posts( true );
		wp_send_json_success();
	}
}
