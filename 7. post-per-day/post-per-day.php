<?php
/*
Plugin Name: Posts por Día
Description: Muestra cuántos posts se publican por día en el admin.
Version: 1.0
*/

if (!defined('ABSPATH')) exit;

// Crear menú en admin
add_action('admin_menu', function() {
    add_menu_page(
        'Posts por Día',
        'Posts por Día',
        'manage_options',
        'posts-por-dia',
        'ppd_render_admin_page',
        'dashicons-chart-bar',
        20
    );
});

// Renderizar página
function ppd_render_admin_page() {
    global $wpdb;

    // Fechas clave
    $today = date('Y-m-d');
    $end_current_month = date('Y-m-t'); // último día del mes actual
    $end_next_month = date('Y-m-t', strtotime('+1 month'));

    // Query: posts desde hoy hasta fin del siguiente mes
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT 
            DATE(post_date) as fecha,
            COUNT(*) as total
        FROM {$wpdb->posts}
        WHERE post_type = 'post'
        AND post_status IN ('publish','future')
        AND post_date >= %s
        AND post_date <= %s
        GROUP BY DATE(post_date)
        ORDER BY fecha ASC
    ", $today, $end_next_month));

    // Convertir resultados en array asociativo
    $data = [];
    foreach ($results as $row) {
        $data[$row->fecha] = $row->total;
    }

    // Generar TODOS los días (aunque sean 0)
    $start = new DateTime($today);
    $end = new DateTime($end_next_month);

    echo '<div class="wrap">';
    echo '<h1>Posts por día (resto del mes + siguiente)</h1>';

    echo '<table class="widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>Fecha</th>
                <th>Posts programados/publicados</th>
            </tr>
          </thead>';
    echo '<tbody>';

    while ($start <= $end) {
        $date = $start->format('Y-m-d');
        $count = isset($data[$date]) ? $data[$date] : 0;

        echo '<tr>';
        echo '<td>' . esc_html($date) . '</td>';
        echo '<td>' . esc_html($count) . '</td>';
        echo '</tr>';

        $start->modify('+1 day');
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}