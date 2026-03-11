<?php

function tsa_clean_words($title) {

$title = strtolower($title);

$title = preg_replace('/[^\p{L}\p{N}\s]/u', '', $title);

$words = explode(' ', $title);

$stop = tsa_stopwords();

$clean = array();

foreach ($words as $w) {

if(strlen($w) > 2 && !in_array($w, $stop)) {
$clean[] = $w;
}

}

return $clean;

}



function tsa_build_index() {

global $wpdb;

$table = $wpdb->prefix . 'title_word_index';

$wpdb->query("TRUNCATE TABLE $table");

$posts = $wpdb->get_results("
SELECT ID, post_title
FROM {$wpdb->posts}
WHERE post_type='post'
AND post_status='publish'
");

foreach ($posts as $post) {

$words = tsa_clean_words($post->post_title);

foreach ($words as $word) {

$wpdb->insert(
$table,
array(
'word' => $word,
'post_id' => $post->ID
)
);

}

}

}