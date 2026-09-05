<?php
/**
 * WordPress navigation-menu integration.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the standard and enhanced header locations editable under
 * Appearance → Menus.
 */
function md_mega_menu_register_nav_menus() {
    register_nav_menus(
        array(
            'md-header-menu' => __('Header Menu (standard)', 'mindful-design-mega-menu'),
            'md-mega-menu'   => __('Mega Menu', 'mindful-design-mega-menu'),
        )
    );
}
add_action('after_setup_theme', 'md_mega_menu_register_nav_menus');

/**
 * @return string
 */
function md_mega_menu_location() {
    return 'md-mega-menu';
}

/**
 * @return string
 */
function md_standard_menu_location() {
    return 'md-header-menu';
}

/**
 * Build mega menu JSON from the WordPress nav menu assigned to Mega Menu.
 *
 * @return array<int, array<string, mixed>>|null
 */
function md_mega_menu_build_menus_from_nav() {
    $location = md_mega_menu_location();
    $locations = get_nav_menu_locations();

    if (empty($locations[$location])) {
        return null;
    }

    $menu = wp_get_nav_menu_object((int) $locations[$location]);
    if (!$menu instanceof WP_Term) {
        return null;
    }

    $items = wp_get_nav_menu_items((int) $menu->term_id, array('update_post_term_cache' => false));
    if (!is_array($items) || $items === array()) {
        return null;
    }

    $sections = array();
    $children_by_parent = array();

    foreach ($items as $item) {
        if (!$item instanceof WP_Post) {
            continue;
        }

        $parent_id = (int) $item->menu_item_parent;
        if ($parent_id === 0) {
            $section_url = MD_Mega_Menu_Sanitize::text((string) $item->url);
            if ($section_url === '#' || $section_url === '') {
                $section_url = '';
            }

            $sections[(int) $item->ID] = array(
                'label' => MD_Mega_Menu_Sanitize::text((string) $item->title),
                'url'   => $section_url,
                'items' => array(),
            );

            $featured = get_post_meta((int) $item->ID, '_md_mega_menu_featured', true);
            if (is_array($featured)) {
                $clean_featured = MD_Mega_Menu_Sanitize::featured($featured);
                if ($clean_featured !== null) {
                    if (!empty($clean_featured['image']) && !str_starts_with($clean_featured['image'], 'http')) {
                        $clean_featured['image'] = home_url($clean_featured['image']);
                    }
                    $sections[(int) $item->ID]['featured'] = $clean_featured;
                }
            }

            $featured_centres = get_post_meta((int) $item->ID, '_md_mega_menu_featured_centres', true);
            if (is_array($featured_centres) && $featured_centres !== array()) {
                $clean_centres = MD_Mega_Menu_Sanitize::featured_centres($featured_centres);
                if ($clean_centres !== array()) {
                    foreach ($clean_centres as $index => $centre) {
                        if (!empty($centre['image']) && !str_starts_with($centre['image'], 'http')) {
                            $clean_centres[$index]['image'] = home_url($centre['image']);
                        }
                    }
                    $sections[(int) $item->ID]['featuredCentres'] = $clean_centres;
                }
            }
            continue;
        }

        if (!isset($children_by_parent[$parent_id])) {
            $children_by_parent[$parent_id] = array();
        }

        $children_by_parent[$parent_id][] = $item;
    }

    if ($sections === array()) {
        return null;
    }

    $append_descendants = null;
    $append_descendants = static function ($parent_id, array &$section) use (&$append_descendants, $children_by_parent) {
        foreach ($children_by_parent[$parent_id] ?? array() as $child) {
            $link = MD_Mega_Menu_Sanitize::menu_link(
                array(
                    'title'       => (string) $child->title,
                    'description' => (string) $child->description,
                    'url'         => (string) $child->url,
                )
            );

            if ($link['title'] !== '') {
                $section['items'][] = $link;
            }

            // Existing WordPress menus may use more than one nesting level.
            // The mega-menu panel is flat, so retain every descendant in order.
            $append_descendants((int) $child->ID, $section);
        }
    };

    foreach ($sections as $section_id => $section) {
        $append_descendants($section_id, $section);
        $sections[$section_id] = $section;
    }

    return array_values($sections);
}

/**
 * @return array<int, array<string, mixed>>
 */
function md_mega_menu_get_menus() {
    $from_nav = md_mega_menu_build_menus_from_nav();

    return $from_nav !== null ? $from_nav : array();
}
