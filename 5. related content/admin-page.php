<?php

add_action('admin_menu', 'tsa_admin_menu');

function tsa_admin_menu() {

add_menu_page(
'Title Analyzer',
'Title Analyzer',
'manage_options',
'title-analyzer',
'tsa_admin_page'
);

}


function tsa_admin_page() {

global $wpdb;

$table = $wpdb->prefix . 'title_word_index';

$total = $wpdb->get_var("SELECT COUNT(*) FROM $table");

echo '<div class="wrap">';
echo '<h1>Title Similarity Analyzer</h1>';

echo '<p>Total indexed words: '.$total.'</p>';

echo '<form method="post">';

submit_button('Build Index', 'primary', 'build_index');

echo '</form>';

if(isset($_POST['build_index'])) {

tsa_build_index();

echo '<p><strong>Index built successfully.</strong></p>';

}

echo '</div>';

}