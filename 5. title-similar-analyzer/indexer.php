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

    $word = tsa_normalize_word($word);

    if(strlen($word) < 2) continue;

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



// Indexar automáticamente al publicar o actualizar un post
add_action('save_post', 'tsa_index_single_post');

function tsa_index_single_post($post_id) {

    // Ignorar autosaves y revisiones
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    // Solo posts publicados
    if (get_post_status($post_id) !== 'publish') {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'title_word_index';

    // Borrar entradas anteriores de este post
    $wpdb->delete($table, array('post_id' => $post_id));

    // Reindexar con el título actual
    $title = get_the_title($post_id);
    $words = tsa_clean_words($title);

    foreach ($words as $word) {
        $word = tsa_normalize_word($word);
        if (strlen($word) < 2) continue;
        $wpdb->insert($table, array(
            'word'    => $word,
            'post_id' => $post_id
        ));
    }
}