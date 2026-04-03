<?php

function tsa_find_similar_posts($post_id){

    global $wpdb;

    $table = $wpdb->prefix . 'title_word_index';

    $title = get_the_title($post_id);

    $words = tsa_clean_words($title);

    if(empty($words)){
        return array();
    }

    $normalized = array();

    foreach($words as $w){
        $n = tsa_normalize_word($w);
        if(strlen($n) > 3){
            $normalized[] = $n;
        }
    }

    // evitar duplicados
    $normalized = array_unique($normalized);

    if(empty($normalized)){
        return array();
    }

    // preparar placeholders dinámicos
    $placeholders = implode(',', array_fill(0, count($normalized), '%s'));

    // QUERY con score (clave para ranking real)
    $query = "
        SELECT post_id, COUNT(*) as score
        FROM $table
        WHERE word IN ($placeholders)
        AND post_id != %d
        GROUP BY post_id
        HAVING score >= 1
        ORDER BY score DESC
        LIMIT 50
    ";

    // preparar valores
    $params = $normalized;
    $params[] = $post_id;

    $prepared = $wpdb->prepare($query, $params);

    $results = $wpdb->get_results($prepared);

    if(empty($results)){
        return array();
    }

    // devolver solo IDs ordenados por relevancia
    $post_ids = array();

    foreach($results as $row){
        $post_ids[] = $row->post_id;
    }

    return $post_ids;
}


///el principal problema, no detecta el porcentaje de similitud de titulos. de texto. variaciones de palabras. Palabras en medio, genera a cada momento falsos positivos. coincidiendo solo en la primera palabra. Fallanado en las demas. 

//con un post de titulo de 3 palabras iguales deberian de rankear mas alto que tiene 1 solsa palabra. 