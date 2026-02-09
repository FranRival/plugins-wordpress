<?php
/**
 * Plugin Name: Auto Last 24 Hours Tag
 * Description: Agrega automáticamente la etiqueta "last 24 hours" a cada post publicado y la elimina después de 24 horas.
 * Version: 1.0
 * Author: ChatGPT
 */

if (!defined('ABSPATH')) exit;

/* Añadir etiqueta al publicar */
function agregar_etiqueta_last24_al_publicar($post_id, $post) {
    if ($post->post_type !== 'post') return;
    wp_set_post_tags($post_id, 'last 24 hours', true);
}
add_action('publish_post', 'agregar_etiqueta_last24_al_publicar', 10, 2);

/* Cron cada hora */
if (!wp_next_scheduled('remover_etiqueta_last24_evento')) {
    wp_schedule_event(time(), 'hourly', 'remover_etiqueta_last24_evento');
}

/* Remover etiqueta si pasaron 24 horas */
function remover_etiqueta_last24_funcion() {
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'tag'            => 'last-24-hours'
    );

    $posts = get_posts($args);

    foreach ($posts as $post) {
        $post_time = get_the_time('U', $post->ID);
        $now = current_time('timestamp');

        if (($now - $post_time) > 86400) {
            $tags = wp_get_post_tags($post->ID, array('fields' => 'names'));
            $tags = array_diff($tags, array('last 24 hours'));
            wp_set_post_tags($post->ID, $tags, false);
        }
    }
}
add_action('remover_etiqueta_last24_evento', 'remover_etiqueta_last24_funcion');
