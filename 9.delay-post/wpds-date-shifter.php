<?php
/**
 * Plugin Name: WPDS - Desplazador de Fechas de Posts Programados
 * Description: Desplaza (retrasa) en X días la fecha de publicación de todos los posts con estado "Programado" (future), reprogramando también el evento de wp-cron correspondiente para que el post se publique realmente en la nueva fecha. Incluye vista previa y opción de revertir el último desplazamiento.
 * Version: 1.0
 * Author: EIV
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPDS_Date_Shifter {

    const OPTION_LOG    = 'wpds_last_shift_log';
    const NONCE_ACTION  = 'wpds_shift_action';
    const MAX_PREVIEW_ROWS = 50;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
    }

    public function add_menu() {
        add_menu_page(
            'Desplazar Fechas',
            'Desplazar Fechas',
            'manage_options',
            'wpds-shift',
            array( $this, 'render_page' ),
            'dashicons-clock',
            80
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No autorizado.' );
        }

        $preview  = null;
        $applied  = null;
        $reverted = false;

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && isset( $_POST['wpds_nonce'] )
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpds_nonce'] ) ), self::NONCE_ACTION )
        ) {

            if ( isset( $_POST['wpds_action'] ) && $_POST['wpds_action'] === 'revert' ) {
                $reverted = $this->revert_last_shift();
            } else {
                $days       = isset( $_POST['wpds_days'] ) ? intval( $_POST['wpds_days'] ) : 0;
                $post_types = isset( $_POST['wpds_post_types'] )
                    ? array_map( 'sanitize_text_field', (array) $_POST['wpds_post_types'] )
                    : array( 'post' );
                $dry_run = isset( $_POST['wpds_dry_run'] );

                if ( $days > 0 && ! empty( $post_types ) ) {
                    $result = $this->process_shift( $days, $post_types, $dry_run );
                    if ( $dry_run ) {
                        $preview = $result;
                    } else {
                        $applied = $result;
                    }
                }
            }
        }

        $all_types = get_post_types( array( 'public' => true ), 'objects' );
        $last_log  = get_option( self::OPTION_LOG, array() );

        ?>
        <div class="wrap">
            <h1>Desplazar fechas de posts programados</h1>
            <p>
                Esta herramienta suma <strong>X días</strong> a la fecha de publicación de todos los posts
                con estado <strong>"Programado" (future)</strong>, y reprograma correctamente el evento de
                wp-cron que dispara la publicación real. No modifica ningún otro dato del post.
            </p>

            <?php if ( $reverted ) : ?>
                <div class="notice notice-success"><p>✅ Se revirtió el último desplazamiento correctamente.</p></div>
            <?php endif; ?>

            <?php if ( $applied ) : ?>
                <div class="notice notice-success">
                    <p>✅ Se desplazaron <strong><?php echo count( $applied ); ?></strong> posts correctamente.</p>
                </div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field( self::NONCE_ACTION, 'wpds_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="wpds_days">Días a desplazar</label></th>
                        <td>
                            <input type="number" min="1" name="wpds_days" id="wpds_days"
                                value="<?php echo isset( $_POST['wpds_days'] ) ? intval( $_POST['wpds_days'] ) : 1; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th>Tipos de contenido</th>
                        <td>
                            <?php foreach ( $all_types as $type ) :
                                $checked = isset( $_POST['wpds_post_types'] )
                                    ? in_array( $type->name, (array) $_POST['wpds_post_types'], true )
                                    : ( $type->name === 'post' );
                            ?>
                                <label style="margin-right:15px;">
                                    <input type="checkbox" name="wpds_post_types[]" value="<?php echo esc_attr( $type->name ); ?>" <?php checked( $checked ); ?>>
                                    <?php echo esc_html( $type->labels->singular_name ); ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" name="wpds_dry_run" value="1" class="button button-secondary">
                        👁 Vista previa (no aplica cambios)
                    </button>
                    <button type="submit" class="button button-primary"
                        onclick="return confirm('¿Confirmas aplicar el desplazamiento? Esto modificará las fechas reales de los posts programados.');">
                        ✅ Aplicar desplazamiento
                    </button>
                </p>
            </form>

            <?php if ( $preview !== null ) : ?>
                <h2>Vista previa (<?php echo count( $preview ); ?> posts afectados)</h2>
                <?php $this->render_table( $preview ); ?>
            <?php endif; ?>

            <?php if ( $applied !== null ) : ?>
                <h2>Detalle de cambios aplicados</h2>
                <?php $this->render_table( $applied ); ?>
            <?php endif; ?>

            <?php if ( ! empty( $last_log ) && $applied === null ) : ?>
                <hr>
                <h2>Último desplazamiento aplicado</h2>
                <p>
                    Fecha de ejecución: <?php echo esc_html( $last_log['timestamp'] ?? '' ); ?>
                    — <?php echo count( $last_log['items'] ?? array() ); ?> posts
                    — desplazados +<?php echo esc_html( $last_log['days'] ?? '' ); ?> día(s).
                </p>
                <form method="post">
                    <?php wp_nonce_field( self::NONCE_ACTION, 'wpds_nonce' ); ?>
                    <input type="hidden" name="wpds_action" value="revert">
                    <button type="submit" class="button button-secondary"
                        onclick="return confirm('¿Revertir el último desplazamiento? Esto restaurará las fechas anteriores.');">
                        ↩ Revertir último desplazamiento
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_table( $items ) {
        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Título</th><th>Fecha anterior</th><th>Fecha nueva</th></tr></thead><tbody>';
        $count = 0;
        foreach ( $items as $item ) {
            if ( $count >= self::MAX_PREVIEW_ROWS ) {
                $remaining = count( $items ) - self::MAX_PREVIEW_ROWS;
                echo '<tr><td colspan="4">... y ' . intval( $remaining ) . ' más</td></tr>';
                break;
            }
            echo '<tr>';
            echo '<td>' . esc_html( $item['id'] ) . '</td>';
            echo '<td>' . esc_html( $item['title'] ) . '</td>';
            echo '<td>' . esc_html( $item['old_date'] ) . '</td>';
            echo '<td>' . esc_html( $item['new_date'] ) . '</td>';
            echo '</tr>';
            $count++;
        }
        echo '</tbody></table>';
    }

    /**
     * Procesa el desplazamiento de fechas.
     * $dry_run = true -> solo calcula y devuelve, no toca la base de datos.
     */
    private function process_shift( $days, $post_types, $dry_run ) {
        global $wpdb;

        $placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
        $sql = $wpdb->prepare(
            "SELECT ID, post_date, post_date_gmt, post_title FROM {$wpdb->posts} WHERE post_status = 'future' AND post_type IN ($placeholders)",
            $post_types
        );
        $posts = $wpdb->get_results( $sql );

        $results   = array();
        $log_items = array();

        foreach ( $posts as $p ) {
            $old_date     = $p->post_date;
            $old_date_gmt = $p->post_date_gmt;

            $new_date     = date( 'Y-m-d H:i:s', strtotime( $old_date . " +{$days} days" ) );
            $new_date_gmt = date( 'Y-m-d H:i:s', strtotime( $old_date_gmt . " +{$days} days" ) );

            $results[] = array(
                'id'       => $p->ID,
                'title'    => $p->post_title ? $p->post_title : '(sin título)',
                'old_date' => $old_date,
                'new_date' => $new_date,
            );

            if ( ! $dry_run ) {
                $wpdb->update(
                    $wpdb->posts,
                    array(
                        'post_date'     => $new_date,
                        'post_date_gmt' => $new_date_gmt,
                    ),
                    array( 'ID' => $p->ID )
                );
                clean_post_cache( $p->ID );

                // Punto clave: reprogramar el evento de wp-cron que realmente publica el post.
                wp_clear_scheduled_hook( 'publish_future_post', array( (int) $p->ID ) );
                wp_schedule_single_event( strtotime( $new_date_gmt . ' GMT' ), 'publish_future_post', array( (int) $p->ID ) );

                $log_items[] = array(
                    'id'           => $p->ID,
                    'old_date'     => $old_date,
                    'old_date_gmt' => $old_date_gmt,
                );
            }
        }

        if ( ! $dry_run && ! empty( $log_items ) ) {
            update_option(
                self::OPTION_LOG,
                array(
                    'timestamp' => current_time( 'mysql' ),
                    'days'      => $days,
                    'items'     => $log_items,
                ),
                false
            );
        }

        return $results;
    }

    private function revert_last_shift() {
        global $wpdb;
        $log = get_option( self::OPTION_LOG, array() );
        if ( empty( $log['items'] ) ) {
            return false;
        }

        foreach ( $log['items'] as $item ) {
            $wpdb->update(
                $wpdb->posts,
                array(
                    'post_date'     => $item['old_date'],
                    'post_date_gmt' => $item['old_date_gmt'],
                ),
                array( 'ID' => $item['id'] )
            );
            clean_post_cache( $item['id'] );

            wp_clear_scheduled_hook( 'publish_future_post', array( (int) $item['id'] ) );
            wp_schedule_single_event( strtotime( $item['old_date_gmt'] . ' GMT' ), 'publish_future_post', array( (int) $item['id'] ) );
        }

        delete_option( self::OPTION_LOG );
        return true;
    }
}

new WPDS_Date_Shifter();