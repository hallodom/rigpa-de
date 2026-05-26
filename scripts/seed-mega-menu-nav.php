<?php
/**
 * Seed WordPress nav menus for Rigpa Mega Menu (Appearance → Menus).
 *
 * Usage (from project root):
 *   make seed-mega-menu-nav
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "Run via WP-CLI inside WordPress.\n");
    exit(1);
}

require_once WP_PLUGIN_DIR . '/rigpa-mega-menu/includes/menus.php';

/**
 * @return int
 */
function rigpa_mega_menu_get_or_create_nav_menu($name) {
    $existing = wp_get_nav_menu_object($name);
    if ($existing instanceof WP_Term) {
        return (int) $existing->term_id;
    }

    $menu_id = wp_create_nav_menu($name);
    if (is_wp_error($menu_id)) {
        WP_CLI::error('Failed to create menu "' . $name . '": ' . $menu_id->get_error_message());
    }

    return (int) $menu_id;
}

/**
 * @param int $menu_id
 */
function rigpa_mega_menu_clear_nav_menu($menu_id) {
    $items = wp_get_nav_menu_items($menu_id, array('update_post_term_cache' => false));
    if (!is_array($items)) {
        return;
    }

    foreach ($items as $item) {
        wp_delete_post((int) $item->ID, true);
    }
}

/**
 * @param int    $menu_id
 * @param string $location
 */
function rigpa_mega_menu_assign_menu_location($menu_id, $location) {
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
 * @return int Parent menu item ID.
 */
function rigpa_mega_menu_seed_section($menu_id, array $section) {
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
        WP_CLI::warning('Failed section "' . $section['label'] . '": ' . $parent_id->get_error_message());
        return 0;
    }

    if (!empty($section['featured']) && is_array($section['featured'])) {
        update_post_meta((int) $parent_id, '_rigpa_mega_menu_featured', $section['featured']);
    }

    foreach ($section['items'] as $item) {
        $page_id = rigpa_mega_menu_page_id_from_url((string) $item['url']);
        $args = array(
            'menu-item-title'       => (string) $item['title'],
            'menu-item-description' => (string) $item['description'],
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

        $child_id = wp_update_nav_menu_item($menu_id, 0, $args);
        if (is_wp_error($child_id)) {
            WP_CLI::warning('Failed item "' . $item['title'] . '": ' . $child_id->get_error_message());
        }
    }

    return (int) $parent_id;
}

/**
 * @param string $lang english|german
 */
function rigpa_mega_menu_seed_nav_for_lang($lang) {
    $menu_name = $lang === 'german' ? 'Mega Menu (German)' : 'Mega Menu (English)';
    $location  = rigpa_mega_menu_location_for_lang($lang);
    $sections  = rigpa_mega_menu_get_static_menus($lang);

    WP_CLI::log('Seeding ' . $menu_name . '…');

    $menu_id = rigpa_mega_menu_get_or_create_nav_menu($menu_name);
    rigpa_mega_menu_clear_nav_menu($menu_id);

    $section_count = 0;
    $link_count    = 0;

    foreach ($sections as $section) {
        rigpa_mega_menu_seed_section($menu_id, $section);
        $section_count++;
        $link_count += count($section['items']);
    }

    rigpa_mega_menu_assign_menu_location($menu_id, $location);

    WP_CLI::success(
        sprintf(
            '%s: %d sections, %d links (location: %s)',
            $menu_name,
            $section_count,
            $link_count,
            $location
        )
    );
}

rigpa_mega_menu_seed_nav_for_lang('english');
rigpa_mega_menu_seed_nav_for_lang('german');

WP_CLI::success('Nav menus ready under Appearance → Menus.');
