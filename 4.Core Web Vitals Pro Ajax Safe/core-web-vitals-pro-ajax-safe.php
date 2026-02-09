<?php
/*
Plugin Name: Core Web Vitals Pro (AJAX Safe)
Description: Progressive image loading via AJAX for tagged posts.
Version: 1.0.0
*/

if (!defined('ABSPATH')) exit;

class CWV_Ajax_Safe {

    const INITIAL = 6;
    const BATCH = 10;
    const TAG = 'diciembre-2025';

    public function __construct() {
        add_filter('the_content', [$this, 'render_initial'], 9);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_action('wp_ajax_cwv_load_more', [$this, 'ajax_load']);
        add_action('wp_ajax_nopriv_cwv_load_more', [$this, 'ajax_load']);
    }

    public function assets() {
        if (!is_single() || !has_tag(self::TAG)) return;

        wp_enqueue_script('cwv-ajax-js', plugins_url('cwv.js', __FILE__), ['jquery'], '1.0', true);
        wp_enqueue_style('cwv-ajax-css', plugins_url('cwv.css', __FILE__), [], '1.0');

        wp_localize_script('cwv-ajax-js', 'CWV', [
            'ajax' => admin_url('admin-ajax.php'),
            'post' => get_the_ID(),
            'batch' => self::BATCH,
            'nonce' => wp_create_nonce('cwv_nonce')
        ]);
    }

    public function render_initial($content) {
        if (!is_single() || !has_tag(self::TAG)) return $content;

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
}

new CWV_Ajax_Safe();
