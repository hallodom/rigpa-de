<?php
/**
 * Plugin Name: Rigpa Mega Menu
 * Description: Interactive header mega menu with Elementor-compatible shortcode.
 * Version: 1.0.0
 * Author: Rigpa DE
 * Text Domain: rigpa-mega-menu
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RIGPA_MEGA_MENU_VERSION', '1.0.0');
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

Rigpa_Mega_Menu::init();
Rigpa_Mega_Menu_Admin::init();
