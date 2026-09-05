<?php
/**
 * Plugin settings for Mindful Design Mega Menu.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MD_Mega_Menu_Settings {

    const OPTION_AUTO_RENDER     = 'md_mega_menu_auto_render';
    const OPTION_TRANSPARENT     = 'md_mega_menu_transparent';
    const OPTION_MENU_TEXT_COLOR = 'md_mega_menu_text_color';

    const DEFAULT_SOLID_TEXT_COLOR       = '#171717';
    const DEFAULT_TRANSPARENT_TEXT_COLOR = '#ffffff';

    /**
     * Whether the plugin automatically renders a menu through wp_body_open.
     *
     * Enabled by default for existing installations. Disable this when the
     * shortcode is placed deliberately in an Elementor layout.
     */
    public static function is_auto_render_enabled() {
        $value = get_option(self::OPTION_AUTO_RENDER, '1');

        return $value !== '0' && $value !== false;
    }

    /**
     * @param bool $enabled
     */
    public static function set_auto_render_enabled($enabled) {
        update_option(self::OPTION_AUTO_RENDER, $enabled ? '1' : '0');
    }

    /**
     * Whether the header bar uses a transparent background (default: off / solid).
     *
     * Most pages render the menu over a light page background, so the
     * built-in default is solid + dark text. Pages that sit over a hero
     * image or video (e.g. the homepage) can flip this on per-page via
     * the Mega Menu Header metabox.
     */
    public static function is_transparent() {
        $value = get_option(self::OPTION_TRANSPARENT, '0');

        return $value !== '0' && $value !== false;
    }

    /**
     * @param bool $enabled
     */
    public static function set_transparent($enabled) {
        update_option(self::OPTION_TRANSPARENT, $enabled ? '1' : '0');
    }

    /**
     * Hex colour for top-level menu bar item labels.
     *
     * When no explicit colour has been saved, the default is derived
     * from $transparent: white on a transparent header (over a hero
     * background), dark on a solid header (light page background).
     * Pass null to inherit the resolved value of the global transparency
     * setting.
     *
     * @param bool|null $transparent
     */
    public static function get_menu_text_color($transparent = null) {
        if ($transparent === null) {
            $transparent = self::is_transparent();
        }

        $auto = $transparent
            ? self::DEFAULT_TRANSPARENT_TEXT_COLOR
            : self::DEFAULT_SOLID_TEXT_COLOR;

        $stored = (string) get_option(self::OPTION_MENU_TEXT_COLOR, '');
        $color  = $stored === '' ? '' : (string) sanitize_hex_color($stored);

        if ($color === '' || self::is_auto_default_color($color)) {
            return $auto;
        }

        return $color;
    }

    /**
     * True when $color is one of the built-in defaults, not a custom pick.
     *
     * The admin colour input always submits a value. Saving settings while
     * the header is solid persists #171717; toggling transparent on must
     * not keep that leftover as an "explicit" override.
     */
    public static function is_auto_default_color($color) {
        $sanitized = sanitize_hex_color((string) $color);
        if (!$sanitized) {
            return true;
        }

        return strcasecmp($sanitized, self::DEFAULT_SOLID_TEXT_COLOR) === 0
            || strcasecmp($sanitized, self::DEFAULT_TRANSPARENT_TEXT_COLOR) === 0;
    }

    /**
     * @param string $color
     */
    public static function set_menu_text_color($color) {
        $sanitized = sanitize_hex_color($color);
        if ($sanitized) {
            update_option(self::OPTION_MENU_TEXT_COLOR, $sanitized);
        } else {
            delete_option(self::OPTION_MENU_TEXT_COLOR);
        }
    }

    /**
     * Inline style attribute for the menu root (CSS custom property).
     */
    public static function get_root_color_style_attribute() {
        return sprintf(
            ' style="%s"',
            esc_attr(self::get_root_color_style_declaration())
        );
    }

    /**
     * CSS declaration for --md-mega-menu-item-color.
     */
    public static function get_root_color_style_declaration() {
        return '--md-mega-menu-item-color:' . self::get_menu_text_color() . ';';
    }
}
