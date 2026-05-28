<?php
/**
 * Add or clear menu item descriptions from includes/menus.php.
 *
 * Usage (from project root):
 *   make sync-mega-menu-descriptions
 *   make clear-mega-menu-descriptions
 *
 * Optional first argument via WP-CLI:
 *   add|clear (default: add)
 * Optional second argument:
 *   english|german (default: both)
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "Run via WP-CLI inside WordPress.\n");
    exit(1);
}

require_once WP_PLUGIN_DIR . '/rigpa-mega-menu/includes/menus.php';
require_once WP_PLUGIN_DIR . '/rigpa-mega-menu/includes/menu-descriptions.php';
require_once WP_PLUGIN_DIR . '/rigpa-mega-menu/includes/class-rigpa-mega-menu-sanitize.php';
require_once WP_PLUGIN_DIR . '/rigpa-mega-menu/includes/class-rigpa-mega-menu-description-sync.php';

$mode = 'add';
$lang = 'all';

if (isset($args) && is_array($args)) {
    if (isset($args[0]) && $args[0] !== '') {
        $mode = strtolower((string) $args[0]);
    }
    if (isset($args[1]) && $args[1] !== '') {
        $lang = strtolower((string) $args[1]);
    }
}

if (!in_array($mode, array('add', 'clear'), true)) {
    WP_CLI::error('Mode must be add or clear.');
}

if ($lang === 'all') {
    $results = $mode === 'clear'
        ? Rigpa_Mega_Menu_Description_Sync::clear_all()
        : Rigpa_Mega_Menu_Description_Sync::add_all();
} else {
    $result = $mode === 'clear'
        ? Rigpa_Mega_Menu_Description_Sync::clear_lang($lang)
        : Rigpa_Mega_Menu_Description_Sync::add_lang($lang);

    if (is_wp_error($result)) {
        WP_CLI::error($result->get_error_message());
    }

    $results = array($lang => $result);
}

foreach ($results as $key => $result) {
    if (is_wp_error($result)) {
        WP_CLI::warning($key . ': ' . $result->get_error_message());
        continue;
    }

    WP_CLI::success(sprintf(
        '%s: %d %s, %d unchanged, %d unmatched',
        $result['menu_name'],
        (int) $result['updated'],
        $mode === 'clear' ? 'cleared' : 'added',
        (int) $result['unchanged'],
        (int) $result['skipped']
    ));
}

// Also apply/clear featured panels.
WP_CLI::log('');
WP_CLI::log('Featured panels:');

if ($lang === 'all') {
    $featured_results = $mode === 'clear'
        ? Rigpa_Mega_Menu_Description_Sync::clear_featured_all()
        : Rigpa_Mega_Menu_Description_Sync::apply_featured_all();
} else {
    $fr = $mode === 'clear'
        ? Rigpa_Mega_Menu_Description_Sync::clear_featured_lang($lang)
        : Rigpa_Mega_Menu_Description_Sync::apply_featured_lang($lang);

    if (is_wp_error($fr)) {
        WP_CLI::warning($lang . ': ' . $fr->get_error_message());
        $featured_results = array();
    } else {
        $featured_results = array($lang => $fr);
    }
}

foreach ($featured_results as $key => $fr) {
    if (is_wp_error($fr)) {
        WP_CLI::warning($key . ': ' . $fr->get_error_message());
        continue;
    }

    WP_CLI::success(sprintf(
        '%s: %d %s, %d unchanged, %d unmatched',
        $fr['menu_name'],
        (int) $fr['updated'],
        $mode === 'clear' ? 'removed' : 'applied',
        (int) $fr['unchanged'],
        (int) $fr['skipped']
    ));
}
