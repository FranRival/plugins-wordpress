<?php
/**
 * Plugin Name: SEO Tag Grid Optimizer
 * Description: Ajusta la cantidad de posts por página para tags prioritarias en SEO, sin tocar el theme.
 * Version: 1.1
 * Author: Custom
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Seguridad: no acceso directo
}

class SEO_Tag_Grid_Optimizer {

    const OPTION_KEY = 'seo_tgo_config';

    public function __construct() {
        add_action( 'pre_get_posts', array( $this, 'ajustar_posts_por_pagina' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'registrar_settings' ) );
    }

    /**
     * Devuelve la config guardada: array [ 'slug' => posts_per_page ]
     */
    private function get_config() {
        $config = get_option( self::OPTION_KEY, array() );
        return is_array( $config ) ? $config : array();
    }

    /**
     * Ajusta cuántos posts se muestran en la página de tag, solo si está configurada.
     */
    public function ajustar_posts_por_pagina( $query ) {
        if ( is_admin() || ! $query->is_main_query() || ! is_tag() ) {
            return;
        }

        $tag    = get_queried_object();
        $config = $this->get_config();

        if ( isset( $config[ $tag->slug ] ) && $config[ $tag->slug ] > 0 ) {
            $query->set( 'posts_per_page', (int) $config[ $tag->slug ] );
        }
    }

    /**
     * Panel de administración: Ajustes > SEO Tag Optimizer
     */
    public function admin_menu() {
        add_options_page(
            'SEO Tag Grid Optimizer',
            'SEO Tag Optimizer',
            'manage_options',
            'seo-tgo',
            array( $this, 'render_admin_page' )
        );
    }

    public function registrar_settings() {
        register_setting( 'seo_tgo_group', self::OPTION_KEY, array( $this, 'sanitizar_config' ) );
    }

    /**
     * Sanitiza el input del formulario (llega como texto, uno por línea: slug|posts)
     */
    public function sanitizar_config( $input ) {
        $config = array();

        if ( empty( $input['raw'] ) ) {
            return $config;
        }

        $lineas = explode( "\n", trim( $input['raw'] ) );

        foreach ( $lineas as $linea ) {
            $partes = array_map( 'trim', explode( '|', $linea ) );
            if ( empty( $partes[0] ) ) {
                continue;
            }

            $slug = sanitize_title( $partes[0] );
            $config[ $slug ] = isset( $partes[1] ) ? absint( $partes[1] ) : 0;
        }

        return $config;
    }

    public function render_admin_page() {
        $config = $this->get_config();
        $raw    = '';

        foreach ( $config as $slug => $posts_per_page ) {
            $raw .= sprintf( "%s | %s\n", $slug, $posts_per_page );
        }
        ?>
        <div class="wrap">
            <h1>SEO Tag Grid Optimizer</h1>
            <p>Una línea por tag, formato: <code>slug | posts_por_pagina</code></p>
            <p>Ejemplo: <code>aves | 30</code></p>

            <form method="post" action="options.php">
                <?php settings_fields( 'seo_tgo_group' ); ?>
                <textarea name="<?php echo esc_attr( self::OPTION_KEY ); ?>[raw]" rows="12" style="width:100%; font-family:monospace;"><?php echo esc_textarea( $raw ); ?></textarea>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

new SEO_Tag_Grid_Optimizer();
