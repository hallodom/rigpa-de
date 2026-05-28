<?php
/**
 * Sanitize menu text and import payloads for Rigpa Mega Menu.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_Mega_Menu_Sanitize {

    /**
     * Plain text only — strips HTML, shortcodes, and extra whitespace.
     */
    public static function text($value) {
        $value = is_string($value) ? $value : '';

        $value = strip_shortcodes($value);
        $value = wp_strip_all_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = wp_strip_all_tags($value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim((string) $value);
    }

    /**
     * @param string $url
     */
    public static function url($url) {
        $url = trim((string) $url);
        if ($url === '' || $url === '#') {
            return $url === '#' ? '#' : '';
        }

        $sanitized = esc_url_raw($url);

        return is_string($sanitized) ? $sanitized : '';
    }

    /**
     * @param array<string, mixed> $featured
     * @return array<string, string>|null
     */
    public static function featured(array $featured) {
        if (empty($featured['title'])) {
            return null;
        }

        $clean = array(
            'title'       => self::text((string) $featured['title']),
            'description' => self::text((string) ($featured['description'] ?? '')),
            'url'         => self::url((string) ($featured['url'] ?? '')),
            'image'       => self::url((string) ($featured['image'] ?? '')),
        );

        if ($clean['title'] === '') {
            return null;
        }

        return $clean;
    }

    /**
     * Sanitize nav menu item args before writing to the database.
     *
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    public static function menu_item_args(array $args) {
        if (isset($args['menu-item-title'])) {
            $args['menu-item-title'] = self::text((string) $args['menu-item-title']);
        }

        if (isset($args['menu-item-description'])) {
            $args['menu-item-description'] = self::text((string) $args['menu-item-description']);
        }

        if (isset($args['menu-item-attr-title'])) {
            $args['menu-item-attr-title'] = self::text((string) $args['menu-item-attr-title']);
        }

        if (isset($args['menu-item-url'])) {
            $args['menu-item-url'] = self::url((string) $args['menu-item-url']);
        }

        // Never copy theme/plugin CSS classes (dashicons, menu-image, etc.).
        $args['menu-item-classes'] = '';

        return $args;
    }

    /**
     * @param array<string, mixed> $item
     * @return array{title: string, description: string, url: string}
     */
    public static function menu_link(array $item) {
        return array(
            'title'       => self::text((string) ($item['title'] ?? '')),
            'description' => self::text((string) ($item['description'] ?? '')),
            'url'         => self::url((string) ($item['url'] ?? '')),
        );
    }
}
