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

echo '<h2>Index Status</h2>';

echo '<p><strong>Total indexed words:</strong> '.$total.'</p>';

echo '<form method="post">';
submit_button('Build Index', 'primary', 'build_index');
echo '</form>';


if(isset($_POST['build_index'])) {

tsa_build_index();

echo '<p><strong>Index built successfully.</strong></p>';

}



echo '<hr>';

echo '<h2>Test Post Similarity</h2>';

echo '<form method="post">';

echo '<input type="number" name="post_id" placeholder="Enter Post ID" style="width:200px;"> ';

submit_button('Find Similar Posts', 'secondary', 'find_similar', false);

echo '</form>';



if(isset($_POST['find_similar'])){

$post_id = intval($_POST['post_id']);

$similar = tsa_find_similar_posts($post_id);

if(empty($similar)){

echo '<p>No similar posts found.</p>';

}else{

echo '<h3>Similar Posts:</h3>';

echo '<ul>';

foreach($similar as $pid){

$title = get_the_title($pid);

$link = get_edit_post_link($pid);

echo '<li><a href="'.$link.'" target="_blank">'.$title.'</a></li>';

}

echo '</ul>';

}

}


echo '</div>';

}