<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UE_Admin {

	private static $instance = null;

	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'wp_ajax_ue_start_export', array( $this, 'ajax_start' ) );
		add_action( 'wp_ajax_ue_export_batch', array( $this, 'ajax_batch' ) );
	}

	public function add_menu() {
		add_management_page(
			'URL Exporter',
			'URL Exporter',
			'manage_options',
			'ue-exporter',
			array( $this, 'render_page' )
		);
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$post_types = UE_Core::get_public_post_types();
		$nonce      = wp_create_nonce( 'ue_export' );
		?>
		<div class="wrap">
			<h1>URL Exporter</h1>
			<p>Exporta todas las URLs publicadas de tu sitio a un CSV, opcionalmente multiplicadas por los códigos de idioma que uses en tus URLs (ej. GTranslate), para reconstruir el mapa completo de URLs indexadas sin depender de los límites de exportación de Search Console.</p>

			<h2>1. Elige qué exportar</h2>
			<?php foreach ( $post_types as $pt ) : ?>
				<label style="display:block;margin-bottom:6px;">
					<input type="checkbox" class="ue-post-type" value="<?php echo esc_attr( $pt['name'] ); ?>" checked />
					<?php echo esc_html( $pt['label'] ); ?> (<?php echo esc_html( $pt['published'] ); ?> publicados)
				</label>
			<?php endforeach; ?>

			<h2>2. Códigos de idioma/país (opcional)</h2>
			<p class="description">Uno por línea, tal como aparecen en tus URLs (ej. <code>es</code>, <code>en</code>, <code>fr</code>...). Si los dejas vacíos, solo se exporta la URL original sin variantes.</p>
			<textarea id="ue-langs" rows="8" class="large-text code" placeholder="es&#10;en&#10;fr&#10;de&#10;it&#10;pt&#10;ja&#10;ru&#10;ar&#10;cs&#10;nl&#10;uk&#10;vi&#10;zh-CN"></textarea>

			<h2>3. Exportar</h2>
			<button id="ue-start" class="button button-primary">Generar CSV</button>
			<div id="ue-progress" style="margin-top:15px;font-weight:600;"></div>

			<script>
			( function () {
				var startBtn = document.getElementById( 'ue-start' );
				var progress = document.getElementById( 'ue-progress' );
				var ajaxUrl  = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
				var nonce    = '<?php echo esc_js( $nonce ); ?>';

				startBtn.addEventListener( 'click', function () {
					var postTypes = Array.prototype.slice.call( document.querySelectorAll( '.ue-post-type:checked' ) ).map( function ( el ) { return el.value; } );
					var langs = document.getElementById( 'ue-langs' ).value.split( /[\r\n]+/ ).map( function ( s ) { return s.trim(); } ).filter( Boolean );

					if ( postTypes.length === 0 ) {
						alert( 'Selecciona al menos un tipo de contenido.' );
						return;
					}

					startBtn.disabled = true;
					progress.textContent = 'Iniciando...';

					var body = new URLSearchParams();
					body.append( 'action', 'ue_start_export' );
					body.append( 'nonce', nonce );
					postTypes.forEach( function ( pt ) { body.append( 'post_types[]', pt ); } );
					langs.forEach( function ( l ) { body.append( 'langs[]', l ); } );

					fetch( ajaxUrl, { method: 'POST', body: body } )
						.then( function ( r ) { return r.json(); } )
						.then( function ( res ) {
							if ( ! res.success ) {
								progress.textContent = 'Error al iniciar la exportación.';
								startBtn.disabled = false;
								return;
							}
							runBatch( res.data.job_id, postTypes, langs, 0, res.data.total );
						} );
				} );

				function runBatch( jobId, postTypes, langs, offset, total ) {
					var body = new URLSearchParams();
					body.append( 'action', 'ue_export_batch' );
					body.append( 'nonce', nonce );
					body.append( 'job_id', jobId );
					body.append( 'offset', offset );
					postTypes.forEach( function ( pt ) { body.append( 'post_types[]', pt ); } );
					langs.forEach( function ( l ) { body.append( 'langs[]', l ); } );

					fetch( ajaxUrl, { method: 'POST', body: body } )
						.then( function ( r ) { return r.json(); } )
						.then( function ( res ) {
							if ( ! res.success ) {
								progress.textContent = 'Error durante la exportación.';
								startBtn.disabled = false;
								return;
							}

							var nextOffset = res.data.next_offset;
							var pct = total > 0 ? Math.min( 100, Math.round( ( nextOffset / total ) * 100 ) ) : 100;
							progress.textContent = 'Procesando... ' + nextOffset + ' / ' + total + ' posts (' + pct + '%)';

							if ( res.data.processed > 0 && nextOffset < total ) {
								runBatch( jobId, postTypes, langs, nextOffset, total );
							} else {
								progress.innerHTML = '¡Listo! <a href="' + res.data.download_url + '" class="button button-primary">Descargar CSV</a>';
								startBtn.disabled = false;
							}
						} );
				}
			} )();
			</script>
		</div>
		<?php
	}

	public function ajax_start() {
		check_ajax_referer( 'ue_export', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$post_types = isset( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['post_types'] ) ) : array();
		$langs      = isset( $_POST['langs'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['langs'] ) ) : array();

		if ( empty( $post_types ) ) {
			wp_send_json_error();
		}

		$total  = UE_Core::count_total( $post_types );
		$job_id = UE_Core::start_job( $langs );

		wp_send_json_success( array(
			'job_id' => $job_id,
			'total'  => $total,
		) );
	}

	public function ajax_batch() {
		check_ajax_referer( 'ue_export', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$job_id     = isset( $_POST['job_id'] ) ? sanitize_text_field( wp_unslash( $_POST['job_id'] ) ) : '';
		$offset     = isset( $_POST['offset'] ) ? (int) $_POST['offset'] : 0;
		$post_types = isset( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['post_types'] ) ) : array();
		$langs      = isset( $_POST['langs'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['langs'] ) ) : array();

		if ( empty( $job_id ) || empty( $post_types ) ) {
			wp_send_json_error();
		}

		$result = UE_Core::process_batch( $job_id, $post_types, $offset, $langs );

		wp_send_json_success( array(
			'processed'    => $result['processed'],
			'next_offset'  => $result['next_offset'],
			'download_url' => UE_Core::job_url( $job_id ),
		) );
	}
}
