<?php
/**
 * Plugin Name: Mindful Design Mega Menu
 * Description: Interactive header mega menu powered by WordPress navigation menu locations.
 * Version: 2.0.1
 * Author: Mindful Design
 * Text Domain: mindful-design-mega-menu
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MD_MEGA_MENU_VERSION', '2.0.1');
define('MD_MEGA_MENU_PATH', plugin_dir_path(__FILE__));
define('MD_MEGA_MENU_URL', plugin_dir_url(__FILE__));

require_once MD_MEGA_MENU_PATH . 'includes/menus.php';
require_once MD_MEGA_MENU_PATH . 'includes/class-md-mega-menu-settings.php';
require_once MD_MEGA_MENU_PATH . 'includes/class-md-mega-menu-sanitize.php';
require_once MD_MEGA_MENU_PATH . 'includes/class-md-mega-menu.php';
require_once MD_MEGA_MENU_PATH . 'includes/class-md-mega-menu-page-settings.php';
require_once MD_MEGA_MENU_PATH . 'includes/class-md-mega-menu-admin.php';

MD_Mega_Menu::init();
MD_Mega_Menu_Page_Settings::init();
MD_Mega_Menu_Admin::init();
