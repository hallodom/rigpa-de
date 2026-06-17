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
                'lang'        => 'auto',
                'transparent' => null,
                'color'       => null,
            ),
            $atts,
            'rigpa_mega_menu'
        );

        return self::render_root_markup(array(
            'lang'        => $atts['lang'],
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
     * override; both run through the `rigpa_mega_menu_is_transparent`
     * and `rigpa_mega_menu_text_color` filters so per-page meta and
     * other extensions can apply.
     *
     * @param array{lang?:string, transparent?:mixed, color?:mixed, source?:string} $args
     */
    public static function render_root_markup($args = array()) {
        $args = array_merge(
            array(
                'lang'        => 'auto',
                'transparent' => null,
                'color'       => null,
                'source'      => 'manual',
            ),
            is_array($args) ? $args : array()
        );

        self::$resolved_lang = rigpa_mega_menu_resolve_lang($args['lang']);
        self::enqueue_assets();

        self::$instance_count++;
        $id = self::$instance_count === 1
            ? 'rigpa-mega-menu-root'
            : 'rigpa-mega-menu-root-' . self::$instance_count;

        $context = array(
            'source' => (string) $args['source'],
            'args'   => $args,
        );

        // Resolve transparency first so the colour default can derive from
        // the *final* transparency state (transparent → white, solid → dark
        // when no explicit colour override is set).
        $transparent = self::resolve_transparent_attr($args['transparent']);
        $transparent = (bool) apply_filters('rigpa_mega_menu_is_transparent', $transparent, $context);

        $color = self::resolve_color_attr($args['color'], $transparent);
        $color_filtered = apply_filters('rigpa_mega_menu_text_color', $color, $context);
        $color_sanitized = is_string($color_filtered) ? sanitize_hex_color($color_filtered) : '';
        $color = $color_sanitized ? $color_sanitized : $color;

        $mode_class = $transparent
            ? 'rigpa-mega-menu-root--transparent'
            : 'rigpa-mega-menu-root--solid';

        $style_attr = sprintf(
            ' style="%s"',
            esc_attr('--rigpa-mega-menu-item-color:' . $color . ';')
        );

        return sprintf(
            '<div id="%s" class="rigpa-mega-menu-wrapper rigpa-mega-menu-root %s"%s role="navigation" aria-label="%s"></div>',
            esc_attr($id),
            esc_attr($mode_class),
            $style_attr,
            esc_attr__('Main', 'rigpa-mega-menu')
        );
    }

    /**
     * Resolve the `transparent` shortcode attribute, falling back to the
     * global setting when the attribute is missing or unparseable.
     */
    private static function resolve_transparent_attr($value) {
        if ($value === null || $value === '') {
            return Rigpa_Mega_Menu_Settings::is_transparent();
        }

        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($parsed === null) {
            return Rigpa_Mega_Menu_Settings::is_transparent();
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
            return Rigpa_Mega_Menu_Settings::get_menu_text_color($transparent);
        }

        $sanitized = sanitize_hex_color((string) $value);

        return $sanitized ? $sanitized : Rigpa_Mega_Menu_Settings::get_menu_text_color($transparent);
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

        // Full-bleed alignment fix. The CSS breakout below (margin-left: calc(50% - 50vw))
        // only lands at the viewport origin when the menu's host element happens to sit at
        // the horizontal centre of the viewport. Inside some Elementor layouts the host is
        // off-centre on mobile, so the formula overshoots and pushes the header (and the
        // mobile burger toggle) off-screen. This measures the host's real left edge and pins
        // the wrapper to the viewport origin, recomputed on load and resize.
        wp_add_inline_script(
            'rigpa-mega-menu',
            self::get_fullbleed_fix_js()
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

        // The mobile menu panel must overlay the page rather than occupy layout
        // space. In normal flow its expansion grows the header and pushes sibling
        // header elements (site logo, login/account) down. Anchoring it absolutely
        // to the (position:relative) header keeps the header height fixed when open.
        // The +28px top offset opens the panel just below the (taller, centred) site
        // logo so it isn't clipped on mobile; pages that hide the theme header can
        // override this back to 100% via per-page CSS.
        wp_add_inline_style(
            'rigpa-mega-menu',
            '.rigpa-mega-menu-root .rigpa-mega-menu-mobile-panel { position: absolute !important; top: calc(100% + 28px) !important; left: 0 !important; right: 0 !important; width: 100% !important; z-index: 60 !important; }'
        );

        // Mobile burger: larger icon, larger tap target, and a snappier open/close.
        // - The icon is bumped from 18px to 28px (visual size only).
        // - padding 12px + matching negative margin grows the hit area to ~52px
        //   without shifting where the icon visually sits.
        // - touch-action:manipulation removes the ~300ms iOS tap delay.
        // - the panel/content transitions are shortened so open/close feels immediate.
        wp_add_inline_style(
            'rigpa-mega-menu',
            '.rigpa-mega-menu-root svg.rigpa-mega-menu-mobile-toggle-icon { width: 28px !important; height: 28px !important; }'
            . ' .rigpa-mega-menu-root .rigpa-mega-menu-header .rigpa-mega-menu-mobile-toggle { padding: 12px !important; margin: -12px !important; touch-action: manipulation; -webkit-tap-highlight-color: transparent; }'
            . ' .rigpa-mega-menu-root .rigpa-mega-menu-mobile-panel { transition: grid-template-rows 0.22s cubic-bezier(0.22, 1, 0.36, 1) !important; }'
            . ' .rigpa-mega-menu-root .rigpa-mega-menu-mobile-panel-content { transition: opacity 0.16s ease !important; }'
        );

        wp_add_inline_style(
            'rigpa-mega-menu',
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
    var SEL = '.rigpa-mega-menu-wrapper, .rigpa-mega-menu-root';
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
