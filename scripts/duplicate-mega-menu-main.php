<?php
/**
 * Copy the site main menu into Mega Menu (English).
 *
 * Usage (from project root):
 *   make duplicate-mega-menu-main
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "Run via WP-CLI inside WordPress.\n");
    exit(1);
}

require_once WP_PLUGIN_DIR . '/rigpa-mega-menu/includes/menus.php';
require_once WP_PLUGIN_DIR . '/rigpa-mega-menu/includes/class-rigpa-mega-menu-seeder.php';
require_once WP_PLUGIN_DIR . '/rigpa-mega-menu/includes/class-rigpa-mega-menu-duplicator.php';

$result = Rigpa_Mega_Menu_Duplicator::copy_main_to_english();

if (is_wp_error($result)) {
    WP_CLI::error($result->get_error_message());
}

WP_CLI::success(sprintf(
    'Copied "%s" → "%s": %d sections, %d links, %d descriptions synced (location: %s)',
    $result['source_menu_name'],
    $result['target_menu_name'],
    $result['sections'],
    $result['links'],
    (int) ($result['descriptions_updated'] ?? 0),
    $result['location']
));
