<?php
/**
 * Rigpa Mega Menu shortcode and assets.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_Mega_Menu {

    /** @var bool */
    private static $assets_enqueued = false;

    /** @var int */
    private static $instance_count = 0;

    /** @var string */
    private static $resolved_lang = 'english';

    public static function init() {
        add_shortcode('rigpa_mega_menu', array(__CLASS__, 'render_shortcode'));
        add_shortcode('rigpa-mega-menu', array(__CLASS__, 'render_shortcode'));
    }

    /**
     * @param array<string, string>|string $atts
     */
    public static function render_shortcode($atts = array()) {
        $atts = shortcode_atts(
            array(
                'lang' => 'auto',
            ),
            $atts,
            'rigpa_mega_menu'
        );

        self::$resolved_lang = rigpa_mega_menu_resolve_lang($atts['lang']);
        self::enqueue_assets();

        self::$instance_count++;
        $id = self::$instance_count === 1
            ? 'rigpa-mega-menu-root'
            : 'rigpa-mega-menu-root-' . self::$instance_count;

        $mode_class = Rigpa_Mega_Menu_Settings::is_transparent()
            ? 'rigpa-mega-menu-root--transparent'
            : 'rigpa-mega-menu-root--solid';

        return sprintf(
            '<div id="%s" class="rigpa-mega-menu-wrapper rigpa-mega-menu-root %s"%s role="navigation" aria-label="%s"></div>',
            esc_attr($id),
            esc_attr($mode_class),
            Rigpa_Mega_Menu_Settings::get_root_color_style_attribute(),
            esc_attr__('Main', 'rigpa-mega-menu')
        );
    }

    public static function enqueue_assets() {
        if (self::$assets_enqueued) {
            return;
        }

        $css_path = RIGPA_MEGA_MENU_PATH . 'assets/css/rigpa-mega-menu.css';
        $js_path  = RIGPA_MEGA_MENU_PATH . 'assets/js/rigpa-mega-menu.js';

        if (!file_exists($css_path) || !file_exists($js_path)) {
            return;
        }

        self::$assets_enqueued = true;

        wp_enqueue_style(
            'rigpa-mega-menu-fonts',
            'https://fonts.googleapis.com/css2?family=Bitter:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap',
            array(),
            null
        );

        wp_enqueue_style(
            'rigpa-mega-menu',
            RIGPA_MEGA_MENU_URL . 'assets/css/rigpa-mega-menu.css',
            array('rigpa-mega-menu-fonts'),
            RIGPA_MEGA_MENU_VERSION . '.' . (string) filemtime($css_path)
        );

        wp_enqueue_script(
            'rigpa-mega-menu',
            RIGPA_MEGA_MENU_URL . 'assets/js/rigpa-mega-menu.js',
            array(),
            RIGPA_MEGA_MENU_VERSION . '.' . (string) filemtime($js_path),
            true
        );

        $menus = rigpa_mega_menu_get_menus(self::$resolved_lang);

        wp_localize_script(
            'rigpa-mega-menu',
            'rigpaMegaMenu',
            array(
                'assetsUrl'   => RIGPA_MEGA_MENU_URL . 'assets/',
                'lang'        => self::$resolved_lang,
                'menus'       => $menus,
                'transparent'   => Rigpa_Mega_Menu_Settings::is_transparent(),
                'menuTextColor' => Rigpa_Mega_Menu_Settings::get_menu_text_color(),
            )
        );

        wp_add_inline_style(
            'rigpa-mega-menu',
            '.rigpa-mega-menu-root {' . Rigpa_Mega_Menu_Settings::get_root_color_style_declaration() . '}'
        );

        wp_add_inline_style(
            'rigpa-mega-menu',
            '.rigpa-mega-menu-wrapper, .rigpa-mega-menu-root { width: 100vw !important; max-width: 100vw !important; margin-left: calc(50% - 50vw) !important; margin-right: calc(50% - 50vw) !important; position: relative; overflow: visible !important; z-index: 9999; }'
        );

        // Ensure every ancestor of the menu root that could clip the absolutely-
        // positioned dropdown panel allows overflow. Twenty Twenty-Five and most
        // block themes set overflow:hidden on layout containers.
        wp_add_inline_style(
            'rigpa-mega-menu',
            '.wp-block-group:has(.rigpa-mega-menu-root), header:has(.rigpa-mega-menu-root), .wp-block-template-part:has(.rigpa-mega-menu-root) { overflow: visible !important; }'
        );

        wp_add_inline_style(
            'rigpa-mega-menu',
            self::get_hardening_css()
        );

        add_action('wp_print_styles', array(__CLASS__, 'reorder_stylesheet'), 9999);
    }

    /**
     * High-specificity overrides scoped to .rigpa-mega-menu-root.
     *
     * Neutralises hostile rules from themes / page builders (Elementor,
     * block themes, BuddyBoss, etc.) that style generic tags site-wide.
     */
    private static function get_hardening_css() {
        return <<<'CSS'
.rigpa-mega-menu-wrapper,
.rigpa-mega-menu-root {
    display: block !important;
    width: 100vw !important;
    max-width: 100vw !important;
    margin-left: calc(50% - 50vw) !important;
    margin-right: calc(50% - 50vw) !important;
    background: transparent !important;
    overflow: visible !important;
    position: relative !important;
    isolation: isolate;
    font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
    line-height: 1.5;
    color: #171717;
}

.rigpa-mega-menu-root,
.rigpa-mega-menu-root *,
.rigpa-mega-menu-root *::before,
.rigpa-mega-menu-root *::after {
    box-sizing: border-box !important;
}

.rigpa-mega-menu-root img,
.rigpa-mega-menu-root svg,
.elementor .rigpa-mega-menu-root img,
.elementor .rigpa-mega-menu-root svg,
.elementor-widget .rigpa-mega-menu-root img,
.elementor-widget .rigpa-mega-menu-root svg {
    max-width: 100% !important;
    width: auto;
    height: auto;
    border-radius: 0;
    box-shadow: none;
    background: transparent;
    margin: 0;
    padding: 0;
    display: block;
    vertical-align: middle;
}

.rigpa-mega-menu-root .rigpa-mega-menu-panel-featured-image-wrap img,
.rigpa-mega-menu-root .rigpa-mega-menu-panel-featured-image,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-panel-featured-image-wrap img,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-panel-featured-image-wrap img {
    width: 100% !important;
    height: 100% !important;
    max-width: 100% !important;
    object-fit: cover !important;
    display: block !important;
}

.rigpa-mega-menu-root .rigpa-mega-menu-panel-links img,
.rigpa-mega-menu-root .rigpa-mega-menu-panel-links svg,
.rigpa-mega-menu-root .rigpa-mega-menu-panel-link img,
.rigpa-mega-menu-root .rigpa-mega-menu-panel-link svg,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-panel-links img,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-panel-link img,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-panel-links img,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-panel-link img {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
    max-width: 0 !important;
    max-height: 0 !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

.rigpa-mega-menu-root .rigpa-mega-menu-panel-link::before,
.rigpa-mega-menu-root .rigpa-mega-menu-panel-link::after,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-panel-link::before,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-panel-link::after,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-panel-link::before,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-panel-link::after {
    content: none !important;
    display: none !important;
    background: none !important;
    background-image: none !important;
    width: 0 !important;
    height: 0 !important;
}

.rigpa-mega-menu-root .rigpa-mega-menu-panel-link,
.rigpa-mega-menu-root .rigpa-mega-menu-panel-links a,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-panel-link,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-panel-link {
    background: transparent !important;
    background-color: transparent !important;
    background-image: none !important;
    box-shadow: none !important;
    border: 0 !important;
    padding: 0.625rem 0.75rem !important;
    border-radius: 0.5rem !important;
    transition: background-color 0.15s ease !important;
}

.rigpa-mega-menu-root .rigpa-mega-menu-panel-link:hover,
.rigpa-mega-menu-root .rigpa-mega-menu-panel-link:focus,
.rigpa-mega-menu-root .rigpa-mega-menu-panel-link:active,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-panel-link:hover,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-panel-link:hover {
    background: rgba(0, 0, 0, 0.04) !important;
    background-color: rgba(0, 0, 0, 0.04) !important;
    background-image: none !important;
}

.rigpa-mega-menu-root nav,
.rigpa-mega-menu-root ul,
.rigpa-mega-menu-root ol,
.rigpa-mega-menu-root li,
.elementor .rigpa-mega-menu-root nav,
.elementor .rigpa-mega-menu-root ul,
.elementor .rigpa-mega-menu-root ol,
.elementor .rigpa-mega-menu-root li {
    list-style: none !important;
    margin: 0;
    padding: 0;
}

.rigpa-mega-menu-root button,
.rigpa-mega-menu-root input,
.rigpa-mega-menu-root .rigpa-mega-menu-header button,
.elementor .rigpa-mega-menu-root button,
.elementor-widget .rigpa-mega-menu-root button {
    appearance: none !important;
    -webkit-appearance: none !important;
    border-radius: 0;
    box-shadow: none;
    text-transform: none;
    letter-spacing: normal;
    line-height: inherit;
    font-family: inherit;
    min-height: 0;
    height: auto;
    width: auto;
    margin: 0;
}

.rigpa-mega-menu-root a,
.rigpa-mega-menu-root .rigpa-mega-menu-panel a,
.elementor .rigpa-mega-menu-root a,
.elementor-widget .rigpa-mega-menu-root a {
    text-decoration: none !important;
    border-bottom: none !important;
    box-shadow: none;
    background: transparent;
    color: inherit;
}

.rigpa-mega-menu-root a:hover,
.rigpa-mega-menu-root a:focus,
.rigpa-mega-menu-root a:active,
.elementor .rigpa-mega-menu-root a:hover,
.elementor .rigpa-mega-menu-root a:focus,
.elementor .rigpa-mega-menu-root a:active {
    text-decoration: none !important;
    outline: none;
}

.rigpa-mega-menu-root p,
.rigpa-mega-menu-root h1,
.rigpa-mega-menu-root h2,
.rigpa-mega-menu-root h3,
.rigpa-mega-menu-root h4,
.rigpa-mega-menu-root span,
.elementor .rigpa-mega-menu-root p,
.elementor .rigpa-mega-menu-root h1,
.elementor .rigpa-mega-menu-root h2,
.elementor .rigpa-mega-menu-root h3,
.elementor .rigpa-mega-menu-root h4,
.elementor .rigpa-mega-menu-root span {
    margin: 0;
    padding: 0;
    background: transparent;
    text-shadow: none;
    font-weight: inherit;
    font-size: inherit;
    line-height: inherit;
    letter-spacing: normal;
    text-transform: none;
}

.rigpa-mega-menu-root .rigpa-mega-menu-dropdown {
    background: transparent !important;
    box-shadow: none !important;
    border: 0 !important;
}

.rigpa-mega-menu-root.rigpa-mega-menu-root--solid .rigpa-mega-menu-header {
    background-color: #fff !important;
}

.rigpa-mega-menu-root.rigpa-mega-menu-root--transparent .rigpa-mega-menu-header {
    background-color: transparent !important;
}

.rigpa-mega-menu-root .rigpa-mega-menu-header,
.rigpa-mega-menu-root .rigpa-mega-menu-nav-btn,
.rigpa-mega-menu-root .rigpa-mega-menu-mobile-toggle,
.rigpa-mega-menu-root .rigpa-mega-menu-mobile-section-btn {
    border: 0 !important;
    border-bottom: 0 !important;
    box-shadow: none !important;
}

.rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-nav-btn,
.rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-mobile-toggle,
.rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-mobile-toggle-label,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-nav-btn,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-mobile-toggle,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-nav-btn,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-mobile-toggle {
    color: var(--rigpa-mega-menu-item-color, #ffffff) !important;
    background: transparent !important;
    background-color: transparent !important;
}

.rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-nav-btn:hover,
.rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-nav-btn:focus,
.rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-nav-btn:active,
.rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-nav-btn--active,
.rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-mobile-toggle:hover,
.rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-mobile-toggle:focus,
.rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-mobile-toggle:active,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-nav-btn:hover,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-nav-btn:focus,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-nav-btn:active,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-nav-btn:hover,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-mobile-toggle:hover {
    color: var(--rigpa-mega-menu-item-color, #ffffff) !important;
    background: transparent !important;
    background-color: transparent !important;
    box-shadow: none !important;
}

.rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-mobile-toggle-icon {
    color: var(--rigpa-mega-menu-item-color, #ffffff) !important;
    stroke: currentColor !important;
}

/* Dropdown wrapper enter/exit — prevent Elementor/theme from overriding the transition */
.rigpa-mega-menu-root .rigpa-mega-menu-dropdown,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-dropdown,
.elementor-widget .rigpa-mega-menu-root .rigpa-mega-menu-dropdown {
    transition: opacity 0.22s ease, transform 0.28s cubic-bezier(0.22, 1, 0.36, 1) !important;
}

/* Inner panel/links must NOT animate — swap is instant when hovering between items */
.rigpa-mega-menu-root .rigpa-mega-menu-dropdown .rigpa-mega-menu-panel,
.rigpa-mega-menu-root .rigpa-mega-menu-dropdown .rigpa-mega-menu-panel-link,
.rigpa-mega-menu-root .rigpa-mega-menu-dropdown .rigpa-mega-menu-panel-featured,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-dropdown .rigpa-mega-menu-panel,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-dropdown .rigpa-mega-menu-panel-link,
.elementor .rigpa-mega-menu-root .rigpa-mega-menu-dropdown .rigpa-mega-menu-panel-featured {
    opacity: 1 !important;
    transform: none !important;
    animation: none !important;
}

@media (prefers-reduced-motion: reduce) {
    .rigpa-mega-menu-root .rigpa-mega-menu-dropdown {
        transition: none !important;
    }
}
CSS;
    }

    /**
     * Re-print the plugin stylesheet last so it wins source-order ties
     * against theme / page-builder CSS.
     */
    public static function reorder_stylesheet() {
        global $wp_styles;
        if (!isset($wp_styles->registered['rigpa-mega-menu'])) {
            return;
        }
        if (!in_array('rigpa-mega-menu', (array) $wp_styles->done, true)) {
            return;
        }

        $wp_styles->done = array_values(array_filter(
            (array) $wp_styles->done,
            static function ($handle) {
                return $handle !== 'rigpa-mega-menu';
            }
        ));
        $wp_styles->do_items('rigpa-mega-menu');
    }
}
