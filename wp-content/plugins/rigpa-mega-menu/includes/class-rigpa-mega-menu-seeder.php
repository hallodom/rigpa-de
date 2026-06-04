<?php
/**
 * Seeds WordPress nav menus from the built-in static menu data.
 *
 * Used by both the admin settings page and the WP-CLI seed script.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_Mega_Menu_Seeder {

    /**
     * Seed both English and German menus.
     *
     * @return array<string, array{menu_name: string, sections: int, links: int, location: string}>
     */
    public static function seed_all() {
        return array(
            'english' => self::seed_lang('english'),
            'german'  => self::seed_lang('german'),
        );
    }

    /**
     * Seed nav menu for one language and assign it to the plugin location.
     *
     * @param string $lang english|german
     * @return array{menu_name: string, sections: int, links: int, location: string}
     */
    public static function seed_lang($lang) {
        $menu_name = $lang === 'german' ? 'Mega Menu (German)' : 'Mega Menu (English)';
        $location  = rigpa_mega_menu_location_for_lang($lang);
        $sections  = rigpa_mega_menu_get_static_menus($lang);

        $menu_id = self::get_or_create_menu($menu_name);
        self::clear_menu($menu_id);

        $section_count = 0;
        $link_count    = 0;

        foreach ($sections as $section) {
            self::seed_section($menu_id, $section);
            $section_count++;
            $link_count += count($section['items']);
        }

        self::assign_location($menu_id, $location);

        return array(
            'menu_name' => $menu_name,
            'sections'  => $section_count,
            'links'     => $link_count,
            'location'  => $location,
        );
    }

    /**
     * @param string $name
     * @return int
     */
    public static function get_or_create_menu($name) {
        $existing = wp_get_nav_menu_object($name);
        if ($existing instanceof WP_Term) {
            return (int) $existing->term_id;
        }

        $menu_id = wp_create_nav_menu($name);
        if (is_wp_error($menu_id)) {
            return 0;
        }

        return (int) $menu_id;
    }

    /**
     * @param int $menu_id
     * @return int Number of items removed.
     */
    public static function clear_menu($menu_id) {
        $items = wp_get_nav_menu_items($menu_id, array('update_post_term_cache' => false));
        if (!is_array($items)) {
            return 0;
        }

        $count = count($items);
        foreach ($items as $item) {
            wp_delete_post((int) $item->ID, true);
        }

        return $count;
    }

    /**
     * Remove all items from a language menu and unassign the plugin location.
     *
     * @param string $lang english|german
     * @return array{menu_name: string, items_removed: int, location: string}|\WP_Error
     */
    public static function clear_lang($lang) {
        if ($lang !== 'english' && $lang !== 'german') {
            return new WP_Error('rigpa_mega_menu_invalid_lang', __('Invalid language.', 'rigpa-mega-menu'));
        }

        $menu_name = $lang === 'german' ? 'Mega Menu (German)' : 'Mega Menu (English)';
        $location  = rigpa_mega_menu_location_for_lang($lang);
        $removed   = 0;

        $menu = wp_get_nav_menu_object($menu_name);
        if ($menu instanceof WP_Term) {
            $removed = self::clear_menu((int) $menu->term_id);
        }

        self::unassign_location($location);

        return array(
            'menu_name'     => $menu_name,
            'items_removed' => $removed,
            'location'      => $location,
        );
    }

    /**
     * @param string $location
     */
    public static function unassign_location($location) {
        $locations = get_theme_mod('nav_menu_locations', array());
        if (!is_array($locations)) {
            return;
        }

        unset($locations[$location]);
        set_theme_mod('nav_menu_locations', $locations);
    }

    /**
     * @param int                 $menu_id
     * @param string              $location
     */
    public static function assign_location($menu_id, $location) {
        $locations = get_theme_mod('nav_menu_locations', array());
        if (!is_array($locations)) {
            $locations = array();
        }

        $locations[$location] = (int) $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    /**
     * @param int                 $menu_id
     * @param array<string,mixed> $section
     * @return int
     */
    public static function seed_section($menu_id, array $section) {
        $parent_id = wp_update_nav_menu_item(
            $menu_id,
            0,
            array(
                'menu-item-title'  => (string) $section['label'],
                'menu-item-url'    => '#',
                'menu-item-status' => 'publish',
                'menu-item-type'   => 'custom',
            )
        );

        if (is_wp_error($parent_id)) {
            return 0;
        }

        if (!empty($section['featured']) && is_array($section['featured'])) {
            update_post_meta((int) $parent_id, '_rigpa_mega_menu_featured', $section['featured']);
        }

        if (!empty($section['featuredCentres']) && is_array($section['featuredCentres'])) {
            $centres = Rigpa_Mega_Menu_Sanitize::featured_centres($section['featuredCentres']);
            if ($centres !== array()) {
                update_post_meta((int) $parent_id, '_rigpa_mega_menu_featured_centres', $centres);
            }
        }

        foreach ($section['items'] as $item) {
            $path    = trim((string) parse_url((string) $item['url'], PHP_URL_PATH), '/');
            $page    = $path !== '' ? get_page_by_path($path, OBJECT, 'page') : null;
            $page_id = $page instanceof WP_Post ? (int) $page->ID : 0;

        $args = array(
            'menu-item-title'       => Rigpa_Mega_Menu_Sanitize::text((string) $item['title']),
            'menu-item-description' => Rigpa_Mega_Menu_Sanitize::text((string) $item['description']),
            'menu-item-status'      => 'publish',
            'menu-item-parent-id'   => (int) $parent_id,
        );

            if ($page_id > 0) {
                $args['menu-item-type']      = 'post_type';
                $args['menu-item-object']    = 'page';
                $args['menu-item-object-id'] = $page_id;
            } else {
                $args['menu-item-type'] = 'custom';
                $args['menu-item-url']  = home_url((string) $item['url']);
            }

            wp_update_nav_menu_item($menu_id, 0, $args);
        }

        return (int) $parent_id;
    }
}
