<?php
/**
 * Rigpa.de Map shortcode and assets.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_De_Map {

    /** @var bool */
    private static $assets_enqueued = false;

    /** @var int */
    private static $instance_count = 0;

    public static function init() {
        add_shortcode('rigpa_de_map', array(__CLASS__, 'render_shortcode'));
        add_shortcode('rigpa-de-map', array(__CLASS__, 'render_shortcode'));
        add_filter('body_class', array(__CLASS__, 'filter_body_class'));
    }

    /**
     * @param array<int, string> $classes
     * @return array<int, string>
     */
    public static function filter_body_class($classes) {
        if (self::current_page_uses_full_width()) {
            $classes[] = 'rigpa-de-map-full-width';
        }

        return $classes;
    }

    private static function current_page_uses_full_width() {
        if (!is_singular()) {
            return false;
        }

        $post_id = get_queried_object_id();
        if ($post_id <= 0) {
            return false;
        }

        return get_post_meta($post_id, '_rigpa_de_map_full_width', true) === '1';
    }

    /**
     * @param array<string, string>|string $atts
     */
    public static function render_shortcode($atts = array()) {
        self::enqueue_assets();

        self::$instance_count++;
        $id = self::$instance_count === 1
            ? 'rigpa-de-map-root'
            : 'rigpa-de-map-root-' . self::$instance_count;

        return sprintf(
            '<div id="%s" class="rigpa-de-map-wrapper rigpa-de-map-root"></div>',
            esc_attr($id)
        );
    }

    public static function enqueue_assets() {
        if (self::$assets_enqueued) {
            return;
        }

        $css_path = RIGPA_DE_MAP_PATH . 'assets/css/rigpa-de-map.css';
        $js_path  = RIGPA_DE_MAP_PATH . 'assets/js/rigpa-de-map.js';

        if (!file_exists($css_path) || !file_exists($js_path)) {
            return;
        }

        self::$assets_enqueued = true;

        wp_enqueue_style(
            'rigpa-de-map-fonts',
            'https://fonts.googleapis.com/css2?family=Bitter:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap',
            array(),
            null
        );

        wp_enqueue_style(
            'rigpa-de-map',
            RIGPA_DE_MAP_URL . 'assets/css/rigpa-de-map.css',
            array('rigpa-de-map-fonts'),
            (string) filemtime($css_path)
        );

        wp_enqueue_script(
            'rigpa-de-map',
            RIGPA_DE_MAP_URL . 'assets/js/rigpa-de-map.js',
            array(),
            (string) filemtime($js_path),
            true
        );

        $locations = rigpa_de_map_get_locations();

        wp_localize_script(
            'rigpa-de-map',
            'rigpaDeMap',
            array(
                'assetsUrl'        => RIGPA_DE_MAP_URL . 'assets/',
                'germanyVectorUrl' => RIGPA_DE_MAP_URL . 'assets/germany-vector.svg',
                'locations'        => $locations,
            )
        );

        wp_add_inline_style(
            'rigpa-de-map',
            '.rigpa-de-map-wrapper { width: 100%; min-height: 1px; }'
        );

        if (self::current_page_uses_full_width()) {
            wp_add_inline_style(
                'rigpa-de-map',
                'body.rigpa-de-map-full-width main .wp-block-post-content.is-layout-constrained > * { max-width: none !important; width: 100% !important; }
                body.rigpa-de-map-full-width main .wp-block-post-content,
                body.rigpa-de-map-full-width main .wp-block-group { max-width: 100% !important; width: 100% !important; padding-left: 0 !important; padding-right: 0 !important; }
                body.rigpa-de-map-full-width .rigpa-de-map-section { max-width: none !important; width: 100% !important; margin-left: 0 !important; margin-right: 0 !important; }'
            );
        }
    }
}
