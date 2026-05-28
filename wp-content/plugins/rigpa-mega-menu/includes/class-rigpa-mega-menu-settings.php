<?php
/**
 * Plugin settings for Rigpa Mega Menu.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_Mega_Menu_Settings {

    const OPTION_TRANSPARENT     = 'rigpa_mega_menu_transparent';
    const OPTION_MENU_TEXT_COLOR = 'rigpa_mega_menu_text_color';

    const DEFAULT_MENU_TEXT_COLOR = '#ffffff';

    /**
     * Whether the header bar uses a transparent background (default: on).
     */
    public static function is_transparent() {
        $value = get_option(self::OPTION_TRANSPARENT, '1');

        return $value !== '0' && $value !== false;
    }

    /**
     * @param bool $enabled
     */
    public static function set_transparent($enabled) {
        update_option(self::OPTION_TRANSPARENT, $enabled ? '1' : '0');
    }

    /**
     * Hex colour for top-level menu bar item labels (default: white).
     */
    public static function get_menu_text_color() {
        $stored = get_option(self::OPTION_MENU_TEXT_COLOR, self::DEFAULT_MENU_TEXT_COLOR);
        $color  = sanitize_hex_color((string) $stored);

        return $color ? $color : self::DEFAULT_MENU_TEXT_COLOR;
    }

    /**
     * @param string $color
     */
    public static function set_menu_text_color($color) {
        $sanitized = sanitize_hex_color($color);
        update_option(
            self::OPTION_MENU_TEXT_COLOR,
            $sanitized ? $sanitized : self::DEFAULT_MENU_TEXT_COLOR
        );
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
     * CSS declaration for --rigpa-mega-menu-item-color.
     */
    public static function get_root_color_style_declaration() {
        return '--rigpa-mega-menu-item-color:' . self::get_menu_text_color() . ';';
    }
}
