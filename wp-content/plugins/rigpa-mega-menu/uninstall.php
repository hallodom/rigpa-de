<?php
/**
 * Fired when the Rigpa Mega Menu plugin is deleted via the WordPress admin.
 *
 * Removes nav menus, their items, associated post meta, plugin options,
 * and menu location assignments so no orphaned data remains.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$menu_names = array('Mega Menu (English)', 'Mega Menu (German)');
$meta_keys  = array('_rigpa_mega_menu_featured', '_rigpa_mega_menu_featured_centres');

foreach ($menu_names as $menu_name) {
    $menu = wp_get_nav_menu_object($menu_name);
    if (!$menu instanceof WP_Term) {
        continue;
    }

    $items = wp_get_nav_menu_items((int) $menu->term_id, array('update_post_term_cache' => false));
    if (is_array($items)) {
        foreach ($items as $item) {
            foreach ($meta_keys as $key) {
                delete_post_meta((int) $item->ID, $key);
            }
            wp_delete_post((int) $item->ID, true);
        }
    }

    wp_delete_nav_menu((int) $menu->term_id);
}

$locations = get_theme_mod('nav_menu_locations', array());
if (is_array($locations)) {
    unset($locations['rigpa-mega-menu-en'], $locations['rigpa-mega-menu-de']);
    set_theme_mod('nav_menu_locations', $locations);
}

delete_option('rigpa_mega_menu_transparent');
delete_option('rigpa_mega_menu_text_color');
