<?php

if (!defined('ABSPATH')) {
    exit;
}

add_filter('the_content', 'tsa_insert_similar_posts');

function tsa_insert_similar_posts($content) {

    if (!is_single()) {
        return $content;
    }

    if (!in_the_loop() || !is_main_query()) {
        return $content;
    }

    if (!function_exists('tsa_find_similar_posts')) {
        return $content;
    }

    global $post;

    if (!$post) {
        return $content;
    }

    $similar = tsa_find_similar_posts($post->ID);

    if (empty($similar)) {
        return $content;
    }

    shuffle($similar);
    $similar = array_slice($similar, 0, 6);

    $html  = '<div class="tsa-similar-posts">';
    $html .= '<h3 style="margin-bottom:16px;">Posts similares</h3>';
    $html .= '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">';

    foreach ($similar as $pid) {

        $title     = get_post_field('post_title', $pid);
        $link      = get_permalink($pid);
        $thumb_url = get_the_post_thumbnail_url($pid, 'medium');

        if (!$title || !$link) {
            continue;
        }

        $html .= '<div style="display:flex;flex-direction:column;gap:8px;">';

        if ($thumb_url) {
            $html .= '<a href="' . esc_url($link) . '">';
            $html .= '<img src="' . esc_url($thumb_url) . '" alt="' . esc_attr($title) . '" style="width:100%;height:160px;object-fit:cover;border-radius:6px;">';
            $html .= '</a>';
        }

        $html .= '<a href="' . esc_url($link) . '" style="font-weight:bold;text-decoration:none;font-size:14px;line-height:1.4;">';
        $html .= esc_html($title);
        $html .= '</a>';

        $html .= '</div>';
    }

    $html .= '</div>';
    $html .= '</div>';

    return $content . $html;
}

//no funciona en las fechas de...
//actuales de momento. Creo que tenemos que recargar los titulos. todos 