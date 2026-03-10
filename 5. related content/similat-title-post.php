<?php
/*
Plugin Name: Similar Title Posts
Description: Shows posts with similar titles based on keyword matching.
Version: 0.1
Author: Emmanuel
*/

if (!defined('ABSPATH')) exit;


/* ==========================
STOPWORDS
========================== */

function stp_stopwords(){

return array(
'de','la','las','el','los','y','o','en','para','con','del','al','por','un','una'
);

}


/* ==========================
EXTRAER PALABRAS DEL TITULO
========================== */

function stp_get_title_words($title){

$title = strtolower($title);

$title = preg_replace('/[^\p{L}\p{N}\s]/u', '', $title);

$words = explode(' ', $title);

$stop = stp_stopwords();

$clean = array();

foreach($words as $word){

if(strlen($word) > 2 && !in_array($word,$stop)){

$clean[] = $word;

}

}

return $clean;

}


/* ==========================
BUSCAR POSTS SIMILARES
========================== */

function stp_find_similar_posts($post_id){

$title = get_the_title($post_id);

$words = stp_get_title_words($title);

if(empty($words)) return array();


$args = array(
'post_type'=>'post',
'posts_per_page'=>50,
'post__not_in'=>array($post_id)
);

$query = new WP_Query($args);

$scores = array();


foreach($query->posts as $post){

$post_title = strtolower($post->post_title);

$score = 0;

foreach($words as $word){

if(strpos($post_title,$word)!==false){

$score++;

}

}

if($score>0){

$scores[$post->ID]=$score;

}

}


arsort($scores);

return array_slice(array_keys($scores),0,5);

}



/* ==========================
RENDER HTML
========================== */

function stp_render_similar($post_id){

$posts = stp_find_similar_posts($post_id);

if(empty($posts)) return '';

$html = '<div class="similar-title-posts">';

$html .= '<h3>Similar Posts</h3>';

$html .= '<div class="stp-grid">';


foreach($posts as $pid){

$link = get_permalink($pid);

$title = get_the_title($pid);

$thumb = get_the_post_thumbnail($pid,'medium');

$html .= '<div class="stp-item">';

$html .= '<a href="'.$link.'">';

$html .= $thumb;

$html .= '<p>'.$title.'</p>';

$html .= '</a>';

$html .= '</div>';

}

$html .= '</div>';

$html .= '</div>';

return $html;

}



/* ==========================
INSERTAR EN POST
========================== */

function stp_append_to_content($content){

if(!is_single()) return $content;

global $post;

$similar = stp_render_similar($post->ID);

return $content . $similar;

}

add_filter('the_content','stp_append_to_content');



/* ==========================
CSS
========================== */

function stp_css(){

echo '

<style>

.similar-title-posts{
margin-top:40px;
}

.similar-title-posts h3{
font-size:22px;
margin-bottom:20px;
}

.stp-grid{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:20px;
}

.stp-item img{
width:100%;
height:auto;
}

.stp-item p{
font-size:14px;
text-align:center;
margin-top:5px;
}

</style>

';

}

add_action('wp_head','stp_css');