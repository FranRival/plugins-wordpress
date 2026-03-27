<?php
/**
 * Plugin Name: ImgBox Load More (Content-Based)
 * Description: Progressive loading of ImgBox images inside post content. Shows first 9 images and loads the rest via button. Activated only by tag.
 * Version: 1.1.1
 */

if (!defined('ABSPATH')) exit;

define('IMGBLM_TAGS', [
    'diciembre-2025',
    'january-2026',
    'february-2026',
    'march-2026',
    'april-2026',
    'may-2026',
    'june-2026',
    'july-2026',
    'august-2026',
    'september-2026',
    'october-2026',
    'november-2026',
    'december-2026'
]);

define('IMGBLM_INITIAL', 9);

add_filter('the_content', 'imgblm_filter_content', 20);

function imgblm_filter_content($content) {
    if (!is_singular('post')) return $content;
    if (!has_tag(imgblm_get_tags())) return $content;

    preg_match_all('/<img[^>]+>/i', $content, $matches);
    $images = $matches[0];

    $total = count($images);
    if ($total <= IMGBLM_INITIAL) return $content;

    $visible = array_slice($images, 0, IMGBLM_INITIAL);
    $hidden = array_slice($images, IMGBLM_INITIAL);

    $visible_html = implode("\n", $visible);
    $remaining = $total - IMGBLM_INITIAL;

    $button = '<button class="imgbox-load-more" data-post="' . get_the_ID() . '">View ' . $remaining . ' more images</button>';

    $content = preg_replace('/<img[^>]+>/i', '', $content);

    set_transient('imgblm_hidden_' . get_the_ID(), implode("\n", $hidden), HOUR_IN_SECONDS);

    return $content . $visible_html . $button;
}

add_action('wp_ajax_imgblm_load_more', 'imgblm_ajax_load_more');
add_action('wp_ajax_nopriv_imgblm_load_more', 'imgblm_ajax_load_more');

function imgblm_ajax_load_more() {
    $post_id = intval($_POST['post_id']);
    $hidden = get_transient('imgblm_hidden_' . $post_id);
    if ($hidden) {
        echo $hidden;
        delete_transient('imgblm_hidden_' . $post_id);
    }
    wp_die();
}

add_action('wp_enqueue_scripts', function() {
    if (!is_singular('post') || !has_tag(imgblm_get_tags())) return;

    wp_enqueue_script('imgblm-js', plugin_dir_url(__FILE__) . 'load-more.js', ['jquery'], '1.0', true);
    wp_localize_script('imgblm-js', 'imgblm_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);
    wp_enqueue_style('imgblm-css', plugin_dir_url(__FILE__) . 'style.css');
});




add_action('admin_menu', function() {
    add_management_page(
        'ImgBox Load More Settings', // título de la página
        'ImgBox Load More',          // texto en menú (Tools)
        'manage_options',            // permisos
        'imgblm-settings',           // slug
        'imgblm_settings_page'       // callback
    );
});


function imgblm_settings_page(){

    if(isset($_POST['imgblm_tags'])){
        check_admin_referer('imgblm_save_tags');

        $tags = sanitize_textarea_field($_POST['imgblm_tags']);

        // Convertir string → array
        $tags_array = array_filter(array_map('trim', explode(',', $tags)));

        update_option('imgblm_tags', $tags_array);

        echo '<div class="updated"><p>Saved.</p></div>';
    }

    // Obtener tags guardadas (o usar las actuales como fallback)
    $saved_tags = get_option('imgblm_tags', IMGBLM_TAGS);
    ?>

    <div class="wrap">
        <h1>ImgBox Load More</h1>

        <form method="post">
            <?php wp_nonce_field('imgblm_save_tags'); ?>

            <p><strong>Tags (separadas por coma):</strong></p>

            <textarea name="imgblm_tags" rows="6" style="width:100%;">
<?php echo esc_textarea(implode(', ', $saved_tags)); ?>
            </textarea>

            <p>
                <button class="button button-primary">Save</button>
            </p>
        </form>
    </div>

    <?php
}


function imgblm_get_tags(){
    static $tags = null;

    if($tags === null){
        $saved = get_option('imgblm_tags', []);

        if(!empty($saved) && is_array($saved)){
            $tags = $saved;
        } else {
            $tags = IMGBLM_TAGS; // fallback
        }
    }

    return $tags;
}


//agregar una hoja en el wp-admin. En ella hay que colocar las tags a las que actua el plugin de carga. 
//necesitamos una TAG enfocada al peso por anos. 2021 size, 2022 siza, 2023 size, 2024 size, 2025 siza. 