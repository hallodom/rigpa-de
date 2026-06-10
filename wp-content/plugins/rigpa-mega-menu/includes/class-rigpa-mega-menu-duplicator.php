<?php
/**
 * Duplicate an existing WordPress nav menu into a Rigpa Mega Menu location.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_Mega_Menu_Duplicator {

    /** @var array<int, string> */
    const MAIN_LOCATION_CANDIDATES = array(
        'primary',
        'main',
        'main-menu',
        'header',
        'top',
        'navigation',
    );

    /** @var array<int, string> */
    const MAIN_NAME_CANDIDATES = array(
        'Main Menu',
        'Main',
        'Primary Menu',
        'Primary',
        'Header Menu',
        'Header',
    );

    /**
     * Copy the site main menu into Mega Menu (English).
     *
     * @return array{source_menu_name: string, source_menu_id: int, target_menu_name: string, sections: int, links: int, location: string}|\WP_Error
     */
    public static function copy_main_to_english() {
        $source = self::resolve_main_menu();
        if ($source === null) {
            return new WP_Error(
                'rigpa_mega_menu_no_main',
                __('No main menu found. Assign a menu to a theme location such as Primary or Main, or name a menu "Main Menu".', 'rigpa-mega-menu')
            );
        }

        return self::copy_to_lang((int) $source['id'], 'english');
    }

    /**
     * Copy the site main menu into Mega Menu (German).
     *
     * @return array{source_menu_name: string, source_menu_id: int, target_menu_name: string, sections: int, links: int, location: string, descriptions_updated: int}|\WP_Error
     */
    public static function copy_main_to_german() {
        $source = self::resolve_main_menu();
        if ($source === null) {
            return new WP_Error(
                'rigpa_mega_menu_no_main',
                __('No main menu found. Assign a menu to a theme location such as Primary or Main, or name a menu "Main Menu".', 'rigpa-mega-menu')
            );
        }

        return self::copy_to_lang((int) $source['id'], 'german');
    }

    /**
     * @return array{id: int, name: string, location: string}|null
     */
    public static function resolve_main_menu() {
        $locations = get_nav_menu_locations();
        if (!is_array($locations)) {
            $locations = array();
        }

        foreach (self::MAIN_LOCATION_CANDIDATES as $slug) {
            if (empty($locations[$slug])) {
                continue;
            }

            $menu = wp_get_nav_menu_object((int) $locations[$slug]);
            if (!$menu instanceof WP_Term) {
                continue;
            }

            if ((string) $menu->slug === 'mega-menu-english' || (string) $menu->slug === 'mega-menu-german') {
                continue;
            }

            return array(
                'id'       => (int) $menu->term_id,
                'name'     => (string) $menu->name,
                'location' => $slug,
            );
        }

        foreach (self::MAIN_NAME_CANDIDATES as $name) {
            $menu = wp_get_nav_menu_object($name);
            if (!$menu instanceof WP_Term) {
                continue;
            }

            if ((string) $menu->slug === 'mega-menu-english' || (string) $menu->slug === 'mega-menu-german') {
                continue;
            }

            return array(
                'id'       => (int) $menu->term_id,
                'name'     => (string) $menu->name,
                'location' => '',
            );
        }

        return null;
    }

    /**
     * @param int    $source_menu_id
     * @param string $lang english|german
     * @return array{source_menu_name: string, source_menu_id: int, target_menu_name: string, sections: int, links: int, location: string}|\WP_Error
     */
    public static function copy_to_lang($source_menu_id, $lang) {
        $source_menu = wp_get_nav_menu_object($source_menu_id);
        if (!$source_menu instanceof WP_Term) {
            return new WP_Error(
                'rigpa_mega_menu_invalid_source',
                __('Source menu not found.', 'rigpa-mega-menu')
            );
        }

        $target_name = $lang === 'german' ? 'Mega Menu (German)' : 'Mega Menu (English)';
        $location    = rigpa_mega_menu_location_for_lang($lang);
        $target_id   = Rigpa_Mega_Menu_Seeder::get_or_create_menu($target_name);

        if ($target_id <= 0) {
            return new WP_Error(
                'rigpa_mega_menu_target_failed',
                __('Could not create the target mega menu.', 'rigpa-mega-menu')
            );
        }

        $copied = self::copy_items((int) $source_menu->term_id, $target_id);
        if (is_wp_error($copied)) {
            return $copied;
        }

        // Append the built-in "Near You" section after the copied items
        // (all location links + featured Dharma Mati panel).
        if (function_exists('rigpa_mega_menu_get_near_you_section')) {
            $near_you_section = rigpa_mega_menu_get_near_you_section($lang);
            if (is_array($near_you_section) && !empty($near_you_section['label'])) {
                Rigpa_Mega_Menu_Seeder::seed_section($target_id, $near_you_section);
                $copied['sections'] = (int) $copied['sections'] + 1;
                $copied['links']    = (int) $copied['links'] + count((array) ($near_you_section['items'] ?? array()));
            }
        }

        Rigpa_Mega_Menu_Seeder::assign_location($target_id, $location);

        // Sync descriptions and featured panels for both languages so whichever
        // menu already exists also picks up the seeded data.
        $descriptions_updated = 0;
        $featured_updated     = 0;

        foreach (array('english', 'german') as $sync_lang) {
            $description_sync = Rigpa_Mega_Menu_Description_Sync::add_lang($sync_lang);
            if (!is_wp_error($description_sync)) {
                $descriptions_updated += (int) $description_sync['updated'];
            }

            $featured_sync = Rigpa_Mega_Menu_Description_Sync::apply_featured_lang($sync_lang);
            if (!is_wp_error($featured_sync)) {
                $featured_updated += (int) $featured_sync['updated'];
            }
        }

        return array(
            'source_menu_name'     => (string) $source_menu->name,
            'source_menu_id'       => (int) $source_menu->term_id,
            'target_menu_name'     => $target_name,
            'sections'             => (int) $copied['sections'],
            'links'                => (int) $copied['links'],
            'location'             => $location,
            'descriptions_updated' => $descriptions_updated,
            'featured_updated'     => $featured_updated,
        );
    }

    /**
     * @param int $source_menu_id
     * @param int $target_menu_id
     * @return array{sections: int, links: int}|\WP_Error
     */
    private static function copy_items($source_menu_id, $target_menu_id) {
        $items = wp_get_nav_menu_items($source_menu_id, array('update_post_term_cache' => false));
        if (!is_array($items) || $items === array()) {
            return new WP_Error(
                'rigpa_mega_menu_empty_source',
                __('The source menu has no items to copy.', 'rigpa-mega-menu')
            );
        }

        Rigpa_Mega_Menu_Seeder::clear_menu($target_menu_id);

        usort(
            $items,
            static function ($a, $b) {
                return (int) $a->menu_order <=> (int) $b->menu_order;
            }
        );

        /** @var array<int, int> */
        $id_map = array();
        $sections = 0;
        $links    = 0;

        foreach ($items as $item) {
            if (!$item instanceof WP_Post) {
                continue;
            }

            $parent_old = (int) $item->menu_item_parent;
            $parent_new = ($parent_old > 0 && isset($id_map[$parent_old])) ? $id_map[$parent_old] : 0;

            $args = Rigpa_Mega_Menu_Sanitize::menu_item_args(
                array(
                    'menu-item-object-id'   => (int) $item->object_id,
                    'menu-item-object'      => (string) $item->object,
                    'menu-item-parent-id'   => $parent_new,
                    'menu-item-position'    => (int) $item->menu_order,
                    'menu-item-type'        => (string) $item->type,
                    'menu-item-title'       => (string) $item->title,
                    'menu-item-url'         => (string) $item->url,
                    'menu-item-description' => (string) $item->description,
                    'menu-item-attr-title'  => (string) $item->attr_title,
                    'menu-item-target'      => (string) $item->target,
                    'menu-item-xfn'         => (string) $item->xfn,
                    'menu-item-status'      => 'publish',
                )
            );

            $new_id = wp_update_nav_menu_item($target_menu_id, 0, $args);
            if (is_wp_error($new_id)) {
                continue;
            }

            $id_map[(int) $item->ID] = (int) $new_id;
            self::copy_item_meta((int) $item->ID, (int) $new_id);

            if ($parent_new === 0) {
                $sections++;
            } else {
                $links++;
            }
        }

        if ($sections === 0 && $links === 0) {
            return new WP_Error(
                'rigpa_mega_menu_copy_failed',
                __('No menu items could be copied.', 'rigpa-mega-menu')
            );
        }

        return array(
            'sections' => $sections,
            'links'    => $links,
        );
    }

    /**
     * @param int $from_id
     * @param int $to_id
     */
    private static function copy_item_meta($from_id, $to_id) {
        $featured = get_post_meta($from_id, '_rigpa_mega_menu_featured', true);
        if (is_array($featured)) {
            $clean = Rigpa_Mega_Menu_Sanitize::featured($featured);
            if ($clean !== null) {
                update_post_meta($to_id, '_rigpa_mega_menu_featured', $clean);
            }
        }

        $centres = get_post_meta($from_id, '_rigpa_mega_menu_featured_centres', true);
        if (is_array($centres) && $centres !== array()) {
            $clean_centres = Rigpa_Mega_Menu_Sanitize::featured_centres($centres);
            if ($clean_centres !== array()) {
                update_post_meta($to_id, '_rigpa_mega_menu_featured_centres', $clean_centres);
            }
        }
    }
}
