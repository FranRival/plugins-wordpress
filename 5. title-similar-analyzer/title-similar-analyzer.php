<?php
/*
Plugin Name: Title Similarity Analyzer
Description: Analiza títulos de posts y detecta similitudes.
Version: 1.5
Author: Blender
*/

if (!defined('ABSPATH')) {
    exit;
}

define('TSA_PATH', plugin_dir_path(__FILE__));

require_once TSA_PATH . 'install.php';
require_once TSA_PATH . 'stopwords.php';
require_once TSA_PATH . 'indexer.php';
require_once TSA_PATH . 'similarity-engine.php';
require_once TSA_PATH . 'admin-page.php';
require_once TSA_PATH . 'fronted-display.php';


register_activation_hook(__FILE__, 'tsa_install_table');

/*
Cambios: 
1. aleatoriedad
2. se colocan debajo del titulo. y ahi mismo aparecen las tags. es decir, rompen el codigo
*/