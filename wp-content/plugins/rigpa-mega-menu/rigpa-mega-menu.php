<?php
/**
 * Plugin Name: Rigpa Mega Menu
 * Description: Interactive header mega menu with Elementor-compatible shortcode.
 * Version: 1.0.1
 * Author: Rigpa DE
 * Text Domain: rigpa-mega-menu
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RIGPA_MEGA_MENU_VERSION', '1.0.1');
define('RIGPA_MEGA_MENU_PATH', plugin_dir_path(__FILE__));
define('RIGPA_MEGA_MENU_URL', plugin_dir_url(__FILE__));

require_once RIGPA_MEGA_MENU_PATH . 'includes/menus.php';
require_once RIGPA_MEGA_MENU_PATH . 'includes/menu-descriptions.php';
require_once RIGPA_MEGA_MENU_PATH . 'includes/class-rigpa-mega-menu-settings.php';
require_once RIGPA_MEGA_MENU_PATH . 'includes/class-rigpa-mega-menu-sanitize.php';
require_once RIGPA_MEGA_MENU_PATH . 'includes/class-rigpa-mega-menu-description-sync.php';
require_once RIGPA_MEGA_MENU_PATH . 'includes/class-rigpa-mega-menu-seeder.php';
require_once RIGPA_MEGA_MENU_PATH . 'includes/class-rigpa-mega-menu-duplicator.php';
require_once RIGPA_MEGA_MENU_PATH . 'includes/class-rigpa-mega-menu.php';
require_once RIGPA_MEGA_MENU_PATH . 'includes/class-rigpa-mega-menu-admin.php';

register_deactivation_hook(__FILE__, 'rigpa_mega_menu_deactivate');

/**
 * On deactivation, unassign plugin menu locations so the theme
 * does not reference slots that no longer render anything.
 */
function rigpa_mega_menu_deactivate() {
    $locations = get_theme_mod('nav_menu_locations', array());
    if (!is_array($locations)) {
        return;
    }

    unset($locations['rigpa-mega-menu-en'], $locations['rigpa-mega-menu-de']);
    set_theme_mod('nav_menu_locations', $locations);
}

Rigpa_Mega_Menu::init();
Rigpa_Mega_Menu_Admin::init();
