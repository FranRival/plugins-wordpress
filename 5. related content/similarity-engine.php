<?php

function tsa_find_similar_posts($post_id) {

global $wpdb;

$table = $wpdb->prefix . 'title_word_index';

$title = get_the_title($post_id);

$words = tsa_clean_words($title);

if(empty($words)) {
return array();
}

$ids = array();

foreach ($words as $word) {

$results = $wpdb->get_col(
$wpdb->prepare(
"SELECT post_id FROM $table WHERE word=%s",
$word
)
);

foreach($results as $id) {

if($id != $post_id) {

if(!isset($ids[$id])) {
$ids[$id] = 0;
}

$ids[$id]++;

}

}

}

arsort($ids);

return array_slice(array_keys($ids), 0, 6);

}