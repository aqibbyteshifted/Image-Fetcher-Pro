<?php
/**
 * Plugin Name: Stock Image Fetcher Pro
 * Description: Advanced AI-powered stock image fetcher with optimization, SEO scoring, and modern UI for Elementor (2026 Edition)
 * Version: 2.0.0
 * Author: Brandix_Team
 * Text Domain: stock-image-fetcher-pro
 * Requires PHP: 8.0
 * Requires at least: 6.0
 * Elementor tested up to: 3.20
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Main Stock Image Fetcher Pro Class
 */
final class Stock_Image_Fetcher_Pro
{

    const VERSION = '2.0.0';
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';
    const MINIMUM_PHP_VERSION = '8.0';

    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        add_action('init', [$this, 'i18n']);
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function i18n()
    {
        load_plugin_textdomain('stock-image-fetcher-pro');
    }

    public function init()
    {
        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }

        // Register settings page
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);

        // Register widgets
        add_action('elementor/widgets/register', [$this, 'init_widgets']);

        // Register custom control
        add_action('elementor/controls/register', [$this, 'init_controls']);

        // Register AJAX handlers
        require_once(__DIR__ . '/includes/class-ajax-handler.php');
        $ajax_handler = new \Stock_Image_Fetcher_Pro\AJAX_Handler();
        $ajax_handler->init();

        // Register Image Optimizer
        require_once(__DIR__ . '/includes/class-image-optimizer.php');

        // Enqueue editor assets
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'editor_scripts']);
        add_action('elementor/editor/after_enqueue_styles', [$this, 'editor_styles']);

        // Register admin assets
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);

        // Register frontend assets
        add_action('wp_enqueue_scripts', [$this, 'frontend_styles']);
    }

    public function admin_notice_missing_main_plugin()
    {
        if (isset($_GET['activate']))
            unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'stock-image-fetcher-pro'),
            '<strong>' . esc_html__('Stock Image Fetcher Pro', 'stock-image-fetcher-pro') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'stock-image-fetcher-pro') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function admin_notice_minimum_elementor_version()
    {
        if (isset($_GET['activate']))
            unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'stock-image-fetcher-pro'),
            '<strong>' . esc_html__('Stock Image Fetcher Pro', 'stock-image-fetcher-pro') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'stock-image-fetcher-pro') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function admin_notice_minimum_php_version()
    {
        if (isset($_GET['activate']))
            unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'stock-image-fetcher-pro'),
            '<strong>' . esc_html__('Stock Image Fetcher Pro', 'stock-image-fetcher-pro') . '</strong>',
            '<strong>' . esc_html__('PHP', 'stock-image-fetcher-pro') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function init_widgets($widgets_manager)
    {
        require_once(__DIR__ . '/includes/class-widget.php');
        $widgets_manager->register(new \Stock_Image_Fetcher_Pro\Widget());
    }

    public function init_controls($controls_manager)
    {
        require_once(__DIR__ . '/includes/class-control.php');
        $controls_manager->register(new \Stock_Image_Fetcher_Pro\Control());
    }

    public function editor_styles()
    {
        wp_enqueue_style(
            'stock-image-fetcher-pro-editor',
            plugins_url('/assets/css/editor.css', __FILE__),
            [],
            self::VERSION
        );
    }

    public function editor_scripts()
    {
        wp_enqueue_script(
            'stock-image-fetcher-pro-editor',
            plugins_url('/assets/js/editor.js', __FILE__),
            ['jquery', 'elementor-editor'],
            self::VERSION,
            true
        );

        wp_localize_script('stock-image-fetcher-pro-editor', 'stockFetcherProConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('stock_fetcher_pro_nonce'),
            'pluginUrl' => plugins_url('', __FILE__),
            'strings' => [
                'searching' => esc_html__('Searching...', 'stock-image-fetcher-pro'),
                'downloading' => esc_html__('Downloading...', 'stock-image-fetcher-pro'),
                'optimizing' => esc_html__('Optimizing...', 'stock-image-fetcher-pro'),
                'success' => esc_html__('Image downloaded successfully!', 'stock-image-fetcher-pro'),
                'error' => esc_html__('An error occurred.', 'stock-image-fetcher-pro'),
            ]
        ]);
    }

    public function admin_scripts($hook)
    {
        if ('settings_page_stock-image-fetcher-pro' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'stock-image-fetcher-pro-admin',
            plugins_url('/assets/css/admin.css', __FILE__),
            [],
            self::VERSION
        );

        wp_enqueue_script(
            'stock-image-fetcher-pro-admin',
            plugins_url('/assets/js/admin.js', __FILE__),
            ['jquery'],
            self::VERSION,
            true
        );

        wp_localize_script('stock-image-fetcher-pro-admin', 'stockFetcherProAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('stock_fetcher_pro_admin_nonce'),
        ]);
    }

    public function frontend_styles()
    {
        wp_enqueue_style(
            'stock-image-fetcher-pro-frontend',
            plugins_url('/assets/css/frontend.css', __FILE__),
            [],
            self::VERSION
        );
    }

    public function add_settings_page()
    {
        add_options_page(
            esc_html__('Stock Image Fetcher Pro', 'stock-image-fetcher-pro'),
            esc_html__('Stock Fetcher Pro', 'stock-image-fetcher-pro'),
            'manage_options',
            'stock-image-fetcher-pro',
            [$this, 'settings_page_html']
        );
    }

    public function register_settings()
    {
        register_setting('stock_image_fetcher_pro_settings', 'sifp_freepik_api_key');
        register_setting('stock_image_fetcher_pro_settings', 'sifp_pexels_api_key');
        register_setting('stock_image_fetcher_pro_settings', 'sifp_unsplash_api_key');
        register_setting('stock_image_fetcher_pro_settings', 'sifp_default_quality', [
            'default' => 80
        ]);
        register_setting('stock_image_fetcher_pro_settings', 'sifp_auto_webp', [
            'default' => 'yes'
        ]);
        register_setting('stock_image_fetcher_pro_settings', 'sifp_auto_optimize', [
            'default' => 'yes'
        ]);
        register_setting('stock_image_fetcher_pro_settings', 'sifp_max_file_size', [
            'default' => 100
        ]);
        register_setting('stock_image_fetcher_pro_settings', 'sifp_ai_alt_text', [
            'default' => 'yes'
        ]);
    }

    public function settings_page_html()
    {
        require_once(__DIR__ . '/includes/admin-settings-page.php');
    }
}

Stock_Image_Fetcher_Pro::instance();
