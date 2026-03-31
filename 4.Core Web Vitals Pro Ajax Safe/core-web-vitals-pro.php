<?php
/*
Plugin Name: Core Web Vitals Pro (AJAX Safe)
Description: Progressive image loading via AJAX for tagged posts.
Version: 1.5
*/

if (!defined('ABSPATH')) exit;

class CWV_Ajax_Safe {

    const INITIAL = 6;
    const BATCH = 10;

    public function __construct() {
        add_filter('the_content', [$this, 'render_initial'], 9);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_action('wp_ajax_cwv_load_more', [$this, 'ajax_load']);
        add_action('wp_ajax_nopriv_cwv_load_more', [$this, 'ajax_load']);

        // Admin
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    // 🔹 Obtener tags dinámicos desde WP Admin
    private function get_tags_array() {
        $tags = get_option('cwv_tags', '');
        if (!$tags) return [];

        return array_filter(array_map('trim', explode(',', $tags)));
    }

    public function assets() {
        $tags = $this->get_tags_array();

        if (!is_single() || empty($tags) || !has_tag($tags)) return;

        wp_enqueue_script('cwv-ajax-js', plugins_url('cwv.js', __FILE__), ['jquery'], '1.3', true);
        wp_enqueue_style('cwv-ajax-css', plugins_url('cwv.css', __FILE__), [], '1.3');

        wp_localize_script('cwv-ajax-js', 'CWV', [
            'ajax' => admin_url('admin-ajax.php'),
            'post' => get_the_ID(),
            'batch' => self::BATCH,
            'nonce' => wp_create_nonce('cwv_nonce')
        ]);
    }

    public function render_initial($content) {
        $tags = $this->get_tags_array();

        if (!is_single() || empty($tags) || !has_tag($tags)) return $content;

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        $imgs = $dom->getElementsByTagName('img');

        if ($imgs->length <= self::INITIAL) return $content;

        $out = '';
        $i = 0;

        foreach ($imgs as $img) {
            $i++;
            if ($i > self::INITIAL) break;
            $out .= $dom->saveHTML($img);
        }

        $out .= '<button id="cwv-load-more" data-offset="'.self::INITIAL.'">Load more images</button>';

        return $out;
    }

    public function ajax_load() {
        check_ajax_referer('cwv_nonce','nonce');

        $post_id = intval($_POST['post']);
        $offset  = intval($_POST['offset']);

        if (!$post_id) wp_send_json_error();

        $tags = $this->get_tags_array();
        if (empty($tags) || !has_tag($tags, $post_id)) {
            wp_send_json_error();
        }

        $post = get_post($post_id);
        if (!$post) wp_send_json_error();

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $post->post_content);
        $imgs = $dom->getElementsByTagName('img');

        $html = '';
        $count = 0;

        foreach ($imgs as $index => $img) {
            if ($index < $offset) continue;
            if ($count >= self::BATCH) break;

            $html .= $dom->saveHTML($img);
            $count++;
        }

        wp_send_json_success([
            'html' => $html,
            'loaded' => $count
        ]);
    }

    // 🔹 Registrar setting
    public function register_settings() {
        register_setting('cwv_settings_group', 'cwv_tags');
    }

    // 🔹 Menú admin
    public function admin_menu() {
        add_menu_page(
            'CWV Settings',
            'CWV Pro',
            'manage_options',
            'cwv-settings',
            [$this, 'settings_page'],
            'dashicons-performance',
            80
        );
    }

    // 🔹 Página de configuración
    public function settings_page() {
        $tags = get_option('cwv_tags', '');
        ?>
        <div class="wrap">
            <h1>Core Web Vitals Pro</h1>

            <form method="post" action="options.php">
                <?php settings_fields('cwv_settings_group'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">Tags (comma separated)</th>
                        <td>
                            <input type="text" 
                                   name="cwv_tags" 
                                   value="<?php echo esc_attr($tags); ?>" 
                                   style="width:100%; max-width:600px;" />
                            <p>Example: march-2026, april-2026, may-2026</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

new CWV_Ajax_Safe();