<?php

function tsa_install_table() {

global $wpdb;

$table = $wpdb->prefix . 'title_word_index';

$charset = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table (
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
word VARCHAR(191) NOT NULL,
post_id BIGINT UNSIGNED NOT NULL,
PRIMARY KEY (id),
KEY word (word),
KEY post_id (post_id)
) $charset;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

dbDelta($sql);

}

register_activation_hook(dirname(__FILE__) . '/title-similarity-analyzer.php', 'tsa_install_table');