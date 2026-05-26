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

        wp_add_inline_style(
            'rigpa-de-map',
            self::get_hardening_css()
        );

        // Move the plugin stylesheet to the end of the head so it loads after
        // theme/page-builder CSS (Elementor, BuddyBoss, etc.). At equal
        // !important + specificity, source order decides — ours must be last.
        add_action('wp_print_styles', array(__CLASS__, 'reorder_stylesheet'), 9999);

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

    /**
     * High-specificity overrides scoped to .rigpa-de-map-root.
     *
     * These exist to neutralise hostile rules from themes / page builders
     * (BuddyBoss, Elementor, Astra, etc.) that style generic tags like
     * `img`, `div`, `button` site-wide and would otherwise break the map.
     */
    private static function get_hardening_css() {
        return <<<'CSS'
.rigpa-de-map-wrapper,
.rigpa-de-map-root {
    background: transparent !important;
}

.rigpa-de-map-root,
.rigpa-de-map-root *,
.rigpa-de-map-root *::before,
.rigpa-de-map-root *::after {
    box-sizing: border-box !important;
}

.rigpa-de-map-root img,
.rigpa-de-map-root svg,
.rigpa-de-map-root .rigpa-de-map-section img,
.rigpa-de-map-root .rigpa-de-map-section svg,
.elementor .rigpa-de-map-root img,
.elementor .rigpa-de-map-root svg,
.elementor-widget .rigpa-de-map-root img,
.elementor-widget .rigpa-de-map-root svg {
    max-width: none !important;
    width: auto;
    height: auto;
    border-radius: 0;
    box-shadow: none;
    background: transparent;
    margin: 0;
    padding: 0;
}

.rigpa-de-map-root div,
.rigpa-de-map-root dl,
.rigpa-de-map-root li,
.rigpa-de-map-root .rigpa-de-map-section div,
.rigpa-de-map-root .rigpa-de-map-section dl,
.rigpa-de-map-root .rigpa-de-map-section li {
    border-radius: 0;
}

.rigpa-de-map-root .rigpa-de-marker-bump,
.rigpa-de-map-root [data-name="Map"] div[data-marker] > div {
    border-radius: 9999px !important;
}

.rigpa-de-map-root .rigpa-de-hover-card,
.rigpa-de-map-root .rigpa-de-hover-card-exit {
    border-radius: 0.5rem !important;
}

.rigpa-de-map-root .rigpa-de-hover-card img,
.rigpa-de-map-root .rigpa-de-hover-card-exit img {
    border-radius: 0 !important;
}

.rigpa-de-map-root button,
.rigpa-de-map-root input,
.rigpa-de-map-root .rigpa-de-map-section button,
.rigpa-de-map-root .rigpa-de-map-section input {
    border-radius: 0;
    box-shadow: none;
    text-transform: none;
    letter-spacing: normal;
    line-height: inherit;
}

.rigpa-de-map-root form[data-name="Form"] input[type="text"],
.rigpa-de-map-root form[data-name="Form"] button[type="submit"],
.elementor .rigpa-de-map-root form[data-name="Form"] input[type="text"],
.elementor .rigpa-de-map-root form[data-name="Form"] button[type="submit"] {
    height: 50px !important;
    min-height: 50px !important;
    max-height: 50px !important;
    box-sizing: border-box !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    line-height: 20px !important;
}

.rigpa-de-map-root a,
.rigpa-de-map-root .rigpa-de-map-section a,
.elementor .rigpa-de-map-root a,
.elementor-widget .rigpa-de-map-root a {
    text-decoration: none !important;
    border-bottom: none !important;
    box-shadow: none;
    background: transparent;
}

.rigpa-de-map-root p,
.rigpa-de-map-root h1,
.rigpa-de-map-root h2,
.rigpa-de-map-root h3,
.rigpa-de-map-root h4 {
    margin: 0;
    padding: 0;
    background: transparent;
    text-shadow: none;
}
CSS;
    }

    /**
     * Re-print the plugin stylesheet last so it wins source-order ties
     * against theme / page-builder CSS that uses the same !important + specificity.
     */
    public static function reorder_stylesheet() {
        global $wp_styles;
        if (!isset($wp_styles->registered['rigpa-de-map'])) {
            return;
        }
        if (!in_array('rigpa-de-map', (array) $wp_styles->done, true)) {
            return;
        }

        $wp_styles->done = array_values(array_filter(
            (array) $wp_styles->done,
            static function ($handle) {
                return $handle !== 'rigpa-de-map';
            }
        ));
        $wp_styles->do_items('rigpa-de-map');
    }
}
