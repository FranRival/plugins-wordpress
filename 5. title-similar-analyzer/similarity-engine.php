<?php

function tsa_normalize_word($word){

$word = strtolower($word);

/* quitar caracteres especiales */

$word = preg_replace('/[^a-z0-9]/','',$word);

/* normalizar variaciones */

$word = str_replace('sweetiefoxof','sweetiefox',$word);
$word = str_replace('sweetiefox_of','sweetiefox',$word);
$word = str_replace('sweetiefox','sweetiefox',$word);

return $word;

}



function tsa_find_similar_posts($post_id){

global $wpdb;

$table = $wpdb->prefix . 'title_word_index';

$title = get_the_title($post_id);

$words = tsa_clean_words($title);

if(empty($words)){
return array();
}

/* normalizar */

$normalized = array();

foreach($words as $w){

$n = tsa_normalize_word($w);

if(strlen($n) > 3){
$normalized[] = $n;
}

}

if(empty($normalized)){
return array();
}

/* usar la primera palabra fuerte */

$main = $normalized[0];

/* buscar posts con esa palabra */

$results = $wpdb->get_col(

$wpdb->prepare(

"SELECT post_id 
FROM $table 
WHERE word LIKE %s 
LIMIT 200",

$main.'%'

)

);

$results = array_unique($results);

/* quitar el post actual */

$results = array_diff($results, array($post_id));

return $results;

}