<?php
/**
 * Mindful Design Mega Menu shortcode and assets.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MD_Mega_Menu {

    /** @var bool */
    private static $assets_enqueued = false;

    /** @var int */
    private static $instance_count = 0;

    public static function init() {
        add_shortcode('md_mega_menu', array(__CLASS__, 'render_shortcode'));
        add_shortcode('md-mega-menu', array(__CLASS__, 'render_shortcode'));
        add_action('wp_body_open', array(__CLASS__, 'render_location_menu'), 5);
    }

    /**
     * Render the header directly from WordPress menu locations.
     *
     * Mega Menu wins when both locations have a menu, making the Display
     * location checkboxes an immediate normal-versus-mega switch.
     */
    public static function render_location_menu() {
        if (is_admin() || !MD_Mega_Menu_Settings::is_auto_render_enabled()) {
            return;
        }

        $locations = get_nav_menu_locations();
        if (!empty($locations[md_mega_menu_location()])) {
            echo self::render_root_markup(array('source' => 'location'));
            return;
        }

        if (empty($locations[md_standard_menu_location()])) {
            return;
        }

        $menu = wp_nav_menu(array(
            'theme_location' => md_standard_menu_location(),
            'container'      => false,
            'menu_class'     => 'md-standard-menu__list',
            'fallback_cb'    => false,
            'echo'           => false,
        ));

        if ($menu === '') {
            return;
        }

        echo '<style>.md-standard-menu{padding:1rem 3rem;background:#fff}.md-standard-menu__list{display:flex;flex-wrap:wrap;gap:1.25rem;margin:0;padding:0;list-style:none}.md-standard-menu__list a{color:#171717;text-decoration:none}.md-standard-menu__list .sub-menu{display:none}</style>';
        echo '<nav class="md-standard-menu" aria-label="' . esc_attr__('Main', 'mindful-design-mega-menu') . '">' . $menu . '</nav>';
    }

    /**
     * @param array<string, string>|string $atts
     */
    public static function render_shortcode($atts = array()) {
        $atts = shortcode_atts(
            array(
                'transparent' => null,
                'color'       => null,
            ),
            $atts,
            'md_mega_menu'
        );

        return self::render_root_markup(array(
            'transparent' => $atts['transparent'],
            'color'       => $atts['color'],
            'source'      => 'shortcode',
        ));
    }

    /**
     * Render the mega-menu root mount element.
     *
     * Shared entry point for the shortcode and any non-shortcode mount path
     * (e.g. a theme template part rendering the header via a nav-menu
     * location). Pass `transparent` / `color` to seed the per-instance
     * override; both run through the `md_mega_menu_is_transparent`
     * and `md_mega_menu_text_color` filters so per-page meta and
     * other extensions can apply.
     *
     * @param array{transparent?:mixed, color?:mixed, source?:string} $args
     */
    public static function render_root_markup($args = array()) {
        $args = array_merge(
            array(
                'transparent' => null,
                'color'       => null,
                'source'      => 'manual',
            ),
            is_array($args) ? $args : array()
        );

        // The shortcode is only a mount point. Without an assigned WordPress
        // menu, it must not render a stale built-in navigation.
        if (md_mega_menu_get_menus() === array()) {
            return '';
        }

        self::enqueue_assets();

        self::$instance_count++;
        $id = self::$instance_count === 1
            ? 'md-mega-menu-root'
            : 'md-mega-menu-root-' . self::$instance_count;

        $context = array(
            'source' => (string) $args['source'],
            'args'   => $args,
        );

        // Resolve transparency first so the colour default can derive from
        // the *final* transparency state (transparent → white, solid → dark
        // when no explicit colour override is set).
        $transparent = self::resolve_transparent_attr($args['transparent']);
        $transparent = (bool) apply_filters('md_mega_menu_is_transparent', $transparent, $context);

        $color = self::resolve_color_attr($args['color'], $transparent);
        $color_filtered = apply_filters('md_mega_menu_text_color', $color, $context);
        $color_sanitized = is_string($color_filtered) ? sanitize_hex_color($color_filtered) : '';
        $color = $color_sanitized ? $color_sanitized : $color;

        $mode_class = $transparent
            ? 'md-mega-menu-root--transparent'
            : 'md-mega-menu-root--solid';

        $style_attr = sprintf(
            ' style="%s"',
            esc_attr('--md-mega-menu-item-color:' . $color . ';')
        );

        return sprintf(
            '<div id="%s" class="md-mega-menu-wrapper md-mega-menu-root %s"%s role="navigation" aria-label="%s"></div>',
            esc_attr($id),
            esc_attr($mode_class),
            $style_attr,
            esc_attr__('Main', 'mindful-design-mega-menu')
        );
    }

    /**
     * Resolve the `transparent` shortcode attribute, falling back to the
     * global setting when the attribute is missing or unparseable.
     */
    private static function resolve_transparent_attr($value) {
        if ($value === null || $value === '') {
            return MD_Mega_Menu_Settings::is_transparent();
        }

        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($parsed === null) {
            return MD_Mega_Menu_Settings::is_transparent();
        }

        return $parsed;
    }

    /**
     * Resolve the `color` shortcode attribute, falling back to the global
     * setting when the attribute is missing or not a valid hex colour.
     * The fallback is transparency-aware: white on a transparent header,
     * dark on a solid one when no explicit colour has been saved.
     *
     * @param mixed $value
     * @param bool  $transparent
     */
    private static function resolve_color_attr($value, $transparent) {
        if ($value === null || $value === '') {
            return MD_Mega_Menu_Settings::get_menu_text_color($transparent);
        }

        $sanitized = sanitize_hex_color((string) $value);

        return $sanitized ? $sanitized : MD_Mega_Menu_Settings::get_menu_text_color($transparent);
    }

    public static function enqueue_assets() {
        if (self::$assets_enqueued) {
            return;
        }

        $css_path = MD_MEGA_MENU_PATH . 'assets/css/md-mega-menu.css';
        $js_path  = MD_MEGA_MENU_PATH . 'assets/js/md-mega-menu.js';

        if (!file_exists($css_path) || !file_exists($js_path)) {
            return;
        }

        self::$assets_enqueued = true;

        wp_enqueue_style(
            'md-mega-menu-fonts',
            'https://fonts.googleapis.com/css2?family=Bitter:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap',
            array(),
            null
        );

        wp_enqueue_style(
            'md-mega-menu',
            MD_MEGA_MENU_URL . 'assets/css/md-mega-menu.css',
            array('md-mega-menu-fonts'),
            MD_MEGA_MENU_VERSION . '.' . (string) filemtime($css_path)
        );

        wp_enqueue_script(
            'md-mega-menu',
            MD_MEGA_MENU_URL . 'assets/js/md-mega-menu.js',
            array(),
            MD_MEGA_MENU_VERSION . '.' . (string) filemtime($js_path),
            true
        );

        $menus = md_mega_menu_get_menus();

        wp_localize_script(
            'md-mega-menu',
            'mdMegaMenu',
            array(
                'assetsUrl'   => MD_MEGA_MENU_URL . 'assets/',
                'menus'       => $menus,
                'labels'      => array(
                    'menu'      => __('Menu', 'mindful-design-mega-menu'),
                    'openMenu'  => __('Open menu', 'mindful-design-mega-menu'),
                    'closeMenu' => __('Close menu', 'mindful-design-mega-menu'),
                    'learnMore' => __('Learn more →', 'mindful-design-mega-menu'),
                ),
                'transparent'   => MD_Mega_Menu_Settings::is_transparent(),
                'menuTextColor' => MD_Mega_Menu_Settings::get_menu_text_color(),
            )
        );

        wp_add_inline_style(
            'md-mega-menu',
            '.md-mega-menu-root {' . MD_Mega_Menu_Settings::get_root_color_style_declaration() . '}'
        );

        // Full-bleed alignment fix. The CSS breakout below (margin-left: calc(50% - 50vw))
        // only lands at the viewport origin when the menu's host element happens to sit at
        // the horizontal centre of the viewport. Inside some Elementor layouts the host is
        // off-centre on mobile, so the formula overshoots and pushes the header (and the
        // mobile burger toggle) off-screen. This measures the host's real left edge and pins
        // the wrapper to the viewport origin, recomputed on load and resize.
        wp_add_inline_script(
            'md-mega-menu',
            self::get_fullbleed_fix_js()
        );

        wp_add_inline_style(
            'md-mega-menu',
            '.md-mega-menu-wrapper, .md-mega-menu-root { width: 100vw !important; max-width: 100vw !important; margin-left: calc(50% - 50vw) !important; margin-right: calc(50% - 50vw) !important; position: relative; overflow: visible !important; z-index: 9999; }'
        );

        // Ensure every ancestor of the menu root that could clip the absolutely-
        // positioned dropdown panel allows overflow. Twenty Twenty-Five and most
        // block themes set overflow:hidden on layout containers.
        wp_add_inline_style(
            'md-mega-menu',
            '.wp-block-group:has(.md-mega-menu-root), header:has(.md-mega-menu-root), .wp-block-template-part:has(.md-mega-menu-root) { overflow: visible !important; }'
        );

        // The mobile menu panel must overlay the page rather than occupy layout
        // space. In normal flow its expansion grows the header and pushes sibling
        // header elements (site logo, login/account) down. Anchoring it absolutely
        // to the (position:relative) header keeps the header height fixed when open.
        // The +28px top offset opens the panel just below the (taller, centred) site
        // logo so it isn't clipped on mobile; pages that hide the theme header can
        // override this back to 100% via per-page CSS.
        wp_add_inline_style(
            'md-mega-menu',
            '.md-mega-menu-root .md-mega-menu-mobile-panel { position: absolute !important; top: calc(100% + 28px) !important; left: 0 !important; right: 0 !important; width: 100% !important; z-index: 60 !important; }'
        );

        // Mobile burger: larger icon, larger tap target, and a snappier open/close.
        // - The icon is bumped from 18px to 28px (visual size only).
        // - padding 12px + matching negative margin grows the hit area to ~52px
        //   without shifting where the icon visually sits.
        // - touch-action:manipulation removes the ~300ms iOS tap delay.
        // - the panel/content transitions are shortened so open/close feels immediate.
        wp_add_inline_style(
            'md-mega-menu',
            '.md-mega-menu-root svg.md-mega-menu-mobile-toggle-icon { width: 28px !important; height: 28px !important; }'
            . ' .md-mega-menu-root .md-mega-menu-header .md-mega-menu-mobile-toggle { padding: 12px !important; margin: -12px !important; touch-action: manipulation; -webkit-tap-highlight-color: transparent; }'
            . ' .md-mega-menu-root .md-mega-menu-mobile-panel { transition: grid-template-rows 0.22s cubic-bezier(0.22, 1, 0.36, 1) !important; }'
            . ' .md-mega-menu-root .md-mega-menu-mobile-panel-content { transition: opacity 0.16s ease !important; }'
        );

        wp_add_inline_style(
            'md-mega-menu',
            self::get_hardening_css()
        );

        add_action('wp_print_styles', array(__CLASS__, 'reorder_stylesheet'), 9999);
    }

    /**
     * Runtime full-bleed alignment.
     *
     * The CSS full-bleed uses `margin-left: calc(50% - 50vw)`, which only resolves
     * to the viewport origin when the menu's host element is horizontally centred.
     * Some Elementor layouts place the (zero-width) host off-centre on mobile, so the
     * header is dragged off-screen and the burger toggle becomes invisible. This pass
     * measures the host's actual left edge and pins the wrapper to x=0 with a 100vw
     * width, re-running on load, after late hydration, and on resize.
     */
    private static function get_fullbleed_fix_js() {
        return <<<'JS'
(function () {
    var SEL = '.md-mega-menu-wrapper, .md-mega-menu-root';
    function align() {
        var nodes = document.querySelectorAll(SEL);
        for (var i = 0; i < nodes.length; i++) {
            var el = nodes[i];
            el.style.setProperty('margin-left', '0', 'important');
            el.style.setProperty('margin-right', '0', 'important');
            var left = el.getBoundingClientRect().left;
            el.style.setProperty('margin-left', (-left) + 'px', 'important');
            el.style.setProperty('margin-right', '0', 'important');
        }
    }
    function schedule() {
        align();
        if (window.requestAnimationFrame) { window.requestAnimationFrame(align); }
        setTimeout(align, 150);
        setTimeout(align, 600);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', schedule);
    } else {
        schedule();
    }
    window.addEventListener('load', schedule);
    var rt;
    window.addEventListener('resize', function () {
        clearTimeout(rt);
        rt = setTimeout(align, 100);
    });
})();
JS;
    }

    /**
     * High-specificity overrides scoped to .md-mega-menu-root.
     *
     * Neutralises hostile rules from themes / page builders (Elementor,
     * block themes, BuddyBoss, etc.) that style generic tags site-wide.
     */
    private static function get_hardening_css() {
        return <<<'CSS'
.md-mega-menu-wrapper,
.md-mega-menu-root {
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

.md-mega-menu-root,
.md-mega-menu-root *,
.md-mega-menu-root *::before,
.md-mega-menu-root *::after {
    box-sizing: border-box !important;
}

.md-mega-menu-root img,
.md-mega-menu-root svg,
.elementor .md-mega-menu-root img,
.elementor .md-mega-menu-root svg,
.elementor-widget .md-mega-menu-root img,
.elementor-widget .md-mega-menu-root svg {
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

.md-mega-menu-root .md-mega-menu-panel-featured-image-wrap img,
.md-mega-menu-root .md-mega-menu-panel-featured-image,
.elementor .md-mega-menu-root .md-mega-menu-panel-featured-image-wrap img,
.elementor-widget .md-mega-menu-root .md-mega-menu-panel-featured-image-wrap img {
    width: 100% !important;
    height: 100% !important;
    max-width: 100% !important;
    object-fit: cover !important;
    display: block !important;
}

.md-mega-menu-root .md-mega-menu-panel-links img,
.md-mega-menu-root .md-mega-menu-panel-links svg,
.md-mega-menu-root .md-mega-menu-panel-link img,
.md-mega-menu-root .md-mega-menu-panel-link svg,
.elementor .md-mega-menu-root .md-mega-menu-panel-links img,
.elementor .md-mega-menu-root .md-mega-menu-panel-link img,
.elementor-widget .md-mega-menu-root .md-mega-menu-panel-links img,
.elementor-widget .md-mega-menu-root .md-mega-menu-panel-link img {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
    max-width: 0 !important;
    max-height: 0 !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

.md-mega-menu-root .md-mega-menu-panel-link::before,
.md-mega-menu-root .md-mega-menu-panel-link::after,
.elementor .md-mega-menu-root .md-mega-menu-panel-link::before,
.elementor .md-mega-menu-root .md-mega-menu-panel-link::after,
.elementor-widget .md-mega-menu-root .md-mega-menu-panel-link::before,
.elementor-widget .md-mega-menu-root .md-mega-menu-panel-link::after {
    content: none !important;
    display: none !important;
    background: none !important;
    background-image: none !important;
    width: 0 !important;
    height: 0 !important;
}

.md-mega-menu-root .md-mega-menu-panel-link,
.md-mega-menu-root .md-mega-menu-panel-links a,
.elementor .md-mega-menu-root .md-mega-menu-panel-link,
.elementor-widget .md-mega-menu-root .md-mega-menu-panel-link {
    background: transparent !important;
    background-color: transparent !important;
    background-image: none !important;
    box-shadow: none !important;
    border: 0 !important;
    padding: 0.625rem 0.75rem !important;
    border-radius: 0.5rem !important;
    transition: background-color 0.15s ease !important;
}

.md-mega-menu-root .md-mega-menu-panel-link:hover,
.md-mega-menu-root .md-mega-menu-panel-link:focus,
.md-mega-menu-root .md-mega-menu-panel-link:active,
.elementor .md-mega-menu-root .md-mega-menu-panel-link:hover,
.elementor-widget .md-mega-menu-root .md-mega-menu-panel-link:hover {
    background: rgba(0, 0, 0, 0.04) !important;
    background-color: rgba(0, 0, 0, 0.04) !important;
    background-image: none !important;
}

.md-mega-menu-root nav,
.md-mega-menu-root ul,
.md-mega-menu-root ol,
.md-mega-menu-root li,
.elementor .md-mega-menu-root nav,
.elementor .md-mega-menu-root ul,
.elementor .md-mega-menu-root ol,
.elementor .md-mega-menu-root li {
    list-style: none !important;
    margin: 0;
    padding: 0;
}

.md-mega-menu-root button,
.md-mega-menu-root input,
.md-mega-menu-root .md-mega-menu-header button,
.elementor .md-mega-menu-root button,
.elementor-widget .md-mega-menu-root button {
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

.md-mega-menu-root a,
.md-mega-menu-root .md-mega-menu-panel a,
.elementor .md-mega-menu-root a,
.elementor-widget .md-mega-menu-root a {
    text-decoration: none !important;
    border-bottom: none !important;
    box-shadow: none;
    background: transparent;
    color: inherit;
}

.md-mega-menu-root a:hover,
.md-mega-menu-root a:focus,
.md-mega-menu-root a:active,
.elementor .md-mega-menu-root a:hover,
.elementor .md-mega-menu-root a:focus,
.elementor .md-mega-menu-root a:active {
    text-decoration: none !important;
    outline: none;
}

.md-mega-menu-root p,
.md-mega-menu-root h1,
.md-mega-menu-root h2,
.md-mega-menu-root h3,
.md-mega-menu-root h4,
.md-mega-menu-root span,
.elementor .md-mega-menu-root p,
.elementor .md-mega-menu-root h1,
.elementor .md-mega-menu-root h2,
.elementor .md-mega-menu-root h3,
.elementor .md-mega-menu-root h4,
.elementor .md-mega-menu-root span {
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

.md-mega-menu-root .md-mega-menu-dropdown {
    background: transparent !important;
    box-shadow: none !important;
    border: 0 !important;
}

.md-mega-menu-root.md-mega-menu-root--solid .md-mega-menu-header {
    background-color: #fff !important;
}

.md-mega-menu-root.md-mega-menu-root--transparent .md-mega-menu-header {
    background-color: transparent !important;
}

.md-mega-menu-root .md-mega-menu-header,
.md-mega-menu-root .md-mega-menu-nav-btn,
.md-mega-menu-root .md-mega-menu-mobile-toggle,
.md-mega-menu-root .md-mega-menu-mobile-section-btn {
    border: 0 !important;
    border-bottom: 0 !important;
    box-shadow: none !important;
}

.md-mega-menu-root .md-mega-menu-header .md-mega-menu-nav-btn,
.md-mega-menu-root .md-mega-menu-header .md-mega-menu-mobile-toggle,
.md-mega-menu-root .md-mega-menu-header .md-mega-menu-mobile-toggle-label,
.elementor .md-mega-menu-root .md-mega-menu-header .md-mega-menu-nav-btn,
.elementor .md-mega-menu-root .md-mega-menu-header .md-mega-menu-mobile-toggle,
.elementor-widget .md-mega-menu-root .md-mega-menu-header .md-mega-menu-nav-btn,
.elementor-widget .md-mega-menu-root .md-mega-menu-header .md-mega-menu-mobile-toggle {
    color: var(--md-mega-menu-item-color, #ffffff) !important;
    background: transparent !important;
    background-color: transparent !important;
}

.md-mega-menu-root .md-mega-menu-header .md-mega-menu-nav-btn:hover,
.md-mega-menu-root .md-mega-menu-header .md-mega-menu-nav-btn:focus,
.md-mega-menu-root .md-mega-menu-header .md-mega-menu-nav-btn:active,
.md-mega-menu-root .md-mega-menu-header .md-mega-menu-nav-btn--active,
.md-mega-menu-root .md-mega-menu-header .md-mega-menu-mobile-toggle:hover,
.md-mega-menu-root .md-mega-menu-header .md-mega-menu-mobile-toggle:focus,
.md-mega-menu-root .md-mega-menu-header .md-mega-menu-mobile-toggle:active,
.elementor .md-mega-menu-root .md-mega-menu-header .md-mega-menu-nav-btn:hover,
.elementor .md-mega-menu-root .md-mega-menu-header .md-mega-menu-nav-btn:focus,
.elementor .md-mega-menu-root .md-mega-menu-header .md-mega-menu-nav-btn:active,
.elementor-widget .md-mega-menu-root .md-mega-menu-header .md-mega-menu-nav-btn:hover,
.elementor-widget .md-mega-menu-root .md-mega-menu-header .md-mega-menu-mobile-toggle:hover {
    color: var(--md-mega-menu-item-color, #ffffff) !important;
    background: transparent !important;
    background-color: transparent !important;
    box-shadow: none !important;
}

.md-mega-menu-root .md-mega-menu-header .md-mega-menu-mobile-toggle-icon {
    color: var(--md-mega-menu-item-color, #ffffff) !important;
    stroke: currentColor !important;
}

/* Dropdown wrapper enter/exit — prevent Elementor/theme from overriding the transition */
.md-mega-menu-root .md-mega-menu-dropdown,
.elementor .md-mega-menu-root .md-mega-menu-dropdown,
.elementor-widget .md-mega-menu-root .md-mega-menu-dropdown {
    transition: opacity 0.22s ease, transform 0.28s cubic-bezier(0.22, 1, 0.36, 1) !important;
}

/* Inner panel/links must NOT animate — swap is instant when hovering between items */
.md-mega-menu-root .md-mega-menu-dropdown .md-mega-menu-panel,
.md-mega-menu-root .md-mega-menu-dropdown .md-mega-menu-panel-link,
.md-mega-menu-root .md-mega-menu-dropdown .md-mega-menu-panel-featured,
.elementor .md-mega-menu-root .md-mega-menu-dropdown .md-mega-menu-panel,
.elementor .md-mega-menu-root .md-mega-menu-dropdown .md-mega-menu-panel-link,
.elementor .md-mega-menu-root .md-mega-menu-dropdown .md-mega-menu-panel-featured {
    opacity: 1 !important;
    transform: none !important;
    animation: none !important;
}

@media (prefers-reduced-motion: reduce) {
    .md-mega-menu-root .md-mega-menu-dropdown {
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
        if (!isset($wp_styles->registered['md-mega-menu'])) {
            return;
        }
        if (!in_array('md-mega-menu', (array) $wp_styles->done, true)) {
            return;
        }

        $wp_styles->done = array_values(array_filter(
            (array) $wp_styles->done,
            static function ($handle) {
                return $handle !== 'md-mega-menu';
            }
        ));
        $wp_styles->do_items('md-mega-menu');
    }
}
