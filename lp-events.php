<?php defined('ABSPATH') or die(); // Silence is golden

class lp_events {
    public function __construct(){
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('lp_events', array($this, 'lp_events_shortcode'));
        add_shortcode('lp_my_events', array($this, 'lp_my_events_shortcode'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('dashicons');
        wp_enqueue_script('jquery.cookie', plugin_dir_url(LP_PLUGIN_FILE) . 'js/jquery.cookie.js', array('jquery'), LP_VER);
        wp_enqueue_script('jquery.hypher', plugin_dir_url(LP_PLUGIN_FILE) . 'js/jquery.hypher.js', array('jquery'), LP_VER);
        wp_enqueue_script('jspdf', plugin_dir_url(LP_PLUGIN_FILE) . 'js/jspdf.min.js', array('jquery'), LP_VER);
        wp_enqueue_script('lp_schedule_render_common', plugin_dir_url(LP_PLUGIN_FILE) . 'js/schedule_render_common.js', array('jquery', 'jquery.cookie', 'jquery.hypher', 'jspdf'), LP_VER);
        wp_enqueue_script('lp_pdf_schedule_render', plugin_dir_url(LP_PLUGIN_FILE) . 'js/pdf_schedule_render.js', array('jquery', 'jquery.cookie', 'jquery.hypher', 'jspdf', 'lp_schedule_render_common'), LP_VER);
        wp_enqueue_script('lp_pdf_my_schedule_render', plugin_dir_url(LP_PLUGIN_FILE) . 'js/pdf_my_schedule_render.js', array('jquery', 'jquery.cookie', 'jquery.hypher', 'jspdf', 'lp_schedule_render_common'), LP_VER);
        wp_enqueue_script('lp_events', plugin_dir_url(LP_PLUGIN_FILE) . 'js/events.js', array('jquery', 'jquery.cookie', 'jquery.hypher', 'jspdf', 'lp_schedule_render_common'), LP_VER, true);
    }

    public function lp_events_shortcode() {
        require_once 'lp-events-shorcode-view.php';
        return lp_events_shortcode_html();
    }

    public function lp_my_events_shortcode() {
        require_once 'lp-events-shorcode-view.php';
        return lp_my_events_shortcode_html();
    }
}