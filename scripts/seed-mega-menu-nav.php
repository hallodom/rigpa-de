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
require_once WP_PLUGIN_DIR . '/rigpa-mega-menu/includes/class-rigpa-mega-menu-seeder.php';

$results = Rigpa_Mega_Menu_Seeder::seed_all();

foreach ($results as $lang => $result) {
    WP_CLI::success(sprintf(
        '%s: %d sections, %d links (location: %s)',
        $result['menu_name'],
        $result['sections'],
        $result['links'],
        $result['location']
    ));
}

WP_CLI::success('Nav menus ready under Appearance → Menus.');
