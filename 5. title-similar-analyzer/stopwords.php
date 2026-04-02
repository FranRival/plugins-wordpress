<?php

function tsa_stopwords() {
    return array(
        'de','la','las','el','los','y','o','en','con','para','del','al',
        'un','una','unas','por','onlyfans','sobre'
    );
}

function tsa_normalize_word($word) {
    $word = mb_strtolower($word, 'UTF-8');

    $from = array('á','é','í','ó','ú','ü','ñ','à','è','ì','ò','ù');
    $to   = array('a','e','i','o','u','u','n','a','e','i','o','u');
    $word = str_replace($from, $to, $word);

    $suffixes = array('aciones','acion','mente','ando','endo','ados','adas',
                      'idos','idas','eros','eras','ista','istas','es','os','as');
    foreach ($suffixes as $suffix) {
        $len = strlen($suffix);
        if (substr($word, -$len) === $suffix && strlen($word) - $len >= 4) {
            $word = substr($word, 0, strlen($word) - $len);
            break;
        }
    }

    return $word;
}