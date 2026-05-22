<?php
/**
 * Plugin Name: Rigpa.de Map
 * Description: Rigpa Standorte Deutschland map with Elementor-compatible shortcode.
 * Version: 1.0.0
 * Author: Rigpa DE
 * Text Domain: rigpa-de-map
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RIGPA_DE_MAP_VERSION', '1.0.0');
define('RIGPA_DE_MAP_PATH', plugin_dir_path(__FILE__));
define('RIGPA_DE_MAP_URL', plugin_dir_url(__FILE__));
define('RIGPA_DE_MAP_URLS_OPTION', 'rigpa_de_map_urls');

require_once RIGPA_DE_MAP_PATH . 'includes/locations.php';
require_once RIGPA_DE_MAP_PATH . 'includes/class-rigpa-de-map.php';
require_once RIGPA_DE_MAP_PATH . 'includes/class-rigpa-de-map-admin.php';

Rigpa_De_Map::init();
Rigpa_De_Map_Admin::init();
