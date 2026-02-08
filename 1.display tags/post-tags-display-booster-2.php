<?php
/**
 * Plugin Name: Post Tags Display Booster 2
 * Description: Muestra las etiquetas del post debajo del título y al final del contenido con colores variables.
 * Version: 1.2
 * Author: Emmanuel + ChatGPT
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CSS NEÓN
 */
add_action('wp_head', function () {
    ?>
    <style>
        .ptdb2-tag-section {
            margin: 14px 0;
        }
        .ptdb2-tag-badge {
            display: inline-block;
            padding: 6px 12px;
            margin: 0 6px 6px 0;
            color: #fff !important;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px;
            text-decoration: none !important;
            box-shadow: 0 0 6px rgba(255,255,255,.4);
        }
    </style>
    <?php
});

/**
 * Renderizado de etiquetas
 */
function ptdb2_render_tags($post_id) {

    if ( ! is_singular('post') ) return '';
    if ( ! is_main_query() ) return '';

    $tags = get_the_tags($post_id);
    if ( ! $tags ) return '';

    $colors = [
        '#ff2fd7',
        '#00e5ff',
        '#39ff14',
        '#9d4edd',
        '#ff9100',
        '#faff00',
        '#00ffd5'
    ];

    $html = '<div class="ptdb2-tag-section">';
    $i = 0;

    foreach ($tags as $tag) {
        $color = $colors[$i % count($colors)];
        $count = intval($tag->count);

        $html .= '<a class="ptdb2-tag-badge" style="background:' . esc_attr($color) . '; box-shadow: 0 0 10px ' . esc_attr($color) . ';" href="' . esc_url(get_tag_link($tag->term_id)) . '">';
        $html .= esc_html($tag->name) . ' (' . $count . ')';
        $html .= '</a>';

        $i++;
    }

    $html .= '</div>';

    return $html;
}

/**
 * Debajo del título (solo post principal)
 */
add_filter('the_title', function ($title, $post_id) {

    if (
        ! is_singular('post') ||
        ! in_the_loop() ||
        ! is_main_query()
    ) {
        return $title;
    }

    global $post;
    if ( ! $post || $post->ID !== $post_id ) {
        return $title;
    }

    return $title . ptdb2_render_tags($post_id);

}, 10, 2);

/**
 * Al final del contenido (solo post principal)
 */
add_filter('the_content', function ($content) {

    if (
        ! is_singular('post') ||
        ! in_the_loop() ||
        ! is_main_query()
    ) {
        return $content;
    }

    global $post;
    if ( ! $post ) return $content;

    return $content . ptdb2_render_tags($post->ID);
});
