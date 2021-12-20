<?php
/*
 * Plugin Name: Follow Tags & Categories
 * Plugin URI: https://kamal.pw/follow-tag-categories
 * Description: User's can follow specific Tags or category and show only those tags/category posts.
 * Version: 1.0
 * Author: Kamal Hosen
 * Author URI: https://kamal.pw/
 * Text Domain: nfc
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

if ( !defined( 'ABSPATH' ) ) {
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';

class NewsfeedFolllow {

    public function __construct() {
        $this->define_constant();
        add_action( 'plugins_loaded', [$this, 'plugin_init'] );
        add_action( 'wp_enqueue_scripts', [$this, 'nfc_enqueue_script'] );
        add_action( 'widgets_init', [new \KAMAL\NFC\Admin\NFC_Widget, 'nfc_widget_reg'] );
        add_action( 'widgets_init', [new \KAMAL\NFC\Admin\NFC_Widget_Show, 'nfc_widget_show'] );
        add_action( 'widgets_init', [new \KAMAL\NFC\Admin\NFC_Tag_Widget, 'nfc_tag_widget_reg'] );
        register_activation_hook( __FILE__, [$this, 'activate'] );

    }

    /**
     * @return mixed
     */
    public static function init() {
        /**
         * @var mixed
         */
        static $instance = false;
        if ( !$instance ) {
            $instance = new self();
        }
        return $instance;

    }

    /**
     * plugin_init
     *
     * @return void
     */
    public function plugin_init() {
        load_plugin_textdomain( 'nfc', false, NFC_PATH . '/languages' );
    }

    /**
     * define_constant
     *
     * @return void
     */
    public function define_constant() {
        define( 'NFC_VERSION', '1.0' );
        define( 'NFC_FILE', __FILE__ );
        define( 'NFC_PATH', __DIR__ );
        define( 'NFC_URL', plugins_url( '', NFC_FILE ) );
        define( 'NFC_ASSETS', NFC_URL . '/assets' );
    }

    /**
     * activate
     *
     * @return void
     */
    public function activate() {

    }

    /**
     * deactivate
     *
     * @return void
     */
    public function deactivate() {

    }

    public function nfc_enqueue_script() {

        wp_enqueue_script( 'nfc_script', NFC_ASSETS . '/js/nfc_scripts.js', ['jquery'], NFC_VERSION, true );
        wp_enqueue_script( 'tag_cat-js', NFC_ASSETS . '/js/simply-tag.js', false );
        wp_localize_script(
            'nfc_script',
            'nfc_data',
            [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            ]
        );
        wp_enqueue_style( 'nfc_style', NFC_ASSETS . '/css/nfc_style.css', null, NFC_VERSION, 'all' );
        wp_enqueue_style( 'tag_cat-css', NFC_ASSETS . '/css/simply-tag.css', false );
    }

}

/**
 * nfc_init
 *
 * @return void
 */
function nfc_init() {
    return NewsfeedFolllow::init();
}

nfc_init();
