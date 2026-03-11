<?php

add_filter('the_content','tsa_insert_similar_posts');

function tsa_insert_similar_posts($content){

/* solo en posts individuales */

if(!is_single()){
return $content;
}

global $post;

$post_id = $post->ID;

/* buscar similares */

$similar = tsa_find_similar_posts($post_id);

/* si no hay coincidencias */

if(empty($similar)){
return $content;
}

/* limitar a 6 */

$similar = array_slice($similar,0,6);

/* construir bloque */

$html = '<div class="tsa-similar-posts">';

$html .= '<h3>Similar Posts</h3>';

$html .= '<ul>';

foreach($similar as $pid){

$title = get_the_title($pid);
$link = get_permalink($pid);

$html .= '<li><a href="'.$link.'">'.$title.'</a></li>';

}

$html .= '</ul>';

$html .= '</div>';

/* insertar después del contenido */

return $content . $html;

}