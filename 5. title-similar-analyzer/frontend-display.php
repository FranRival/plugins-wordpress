<?php

if (!defined('ABSPATH')) {
    exit;
}

add_filter('the_content', 'tsa_insert_similar_posts', 99);

function tsa_insert_similar_posts($content) {

    // Evitar que corra fuera del loop principal
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

    $post_id = $post->ID;

    $similar = tsa_find_similar_posts($post_id);

    if (empty($similar)) {
        return $content;
    }

    shuffle($similar);
    $similar = array_slice($similar, 0, 6);

    $html = '<div class="tsa-similar-posts">';
    $html .= '<h3>Posts similares</h3>';
    $html .= '<ul>';

    foreach ($similar as $pid) {
        $title = get_the_title($pid);
        $link  = get_permalink($pid);
        if (!$title || !$link) continue;
        $html .= '<li><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></li>';
    }

    $html .= '</ul>';
    $html .= '</div>';

    $content = preg_replace('/<div[^>]*class="ptdb2-tag-section"[^>]*>.*?<\/div>/s', '', $content);

return $content . $html;
}