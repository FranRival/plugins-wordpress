<?php

if (!defined('ABSPATH')) {
    exit;
}

add_filter('the_content','tsa_insert_similar_posts');

function tsa_insert_similar_posts($content){

    if(!is_single()){
        return $content;
    }

    if(!function_exists('tsa_find_similar_posts')){
        return $content;
    }

    global $post;

    if(!$post){
        return $content;
    }

    $post_id = $post->ID;

    $similar = tsa_find_similar_posts($post_id);

    if(empty($similar)){
        return $content;
    }

    $similar = array_slice($similar,0,6);

    $html = '<div class="tsa-similar-posts">';
    $html .= '<h3>Similar Posts</h3>';
    $html .= '<ul>';

    foreach($similar as $pid){

        $title = get_the_title($pid);
        $link  = get_permalink($pid);

        if(!$title || !$link){
            continue;
        }

        $html .= '<li><a href="'.$link.'">'.$title.'</a></li>';
    }

    $html .= '</ul>';
    $html .= '</div>';

    return $content . $html;
}