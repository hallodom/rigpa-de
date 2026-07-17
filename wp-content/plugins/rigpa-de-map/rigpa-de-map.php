<?php
/**
 * Plugin Name: Rigpa Centre Maps
 * Description: Interactive maps of Rigpa centres and groups by country, with editable locations, images and copy. Embed via shortcode (Elementor-compatible).
 * Version: 1.0.0
 * Author: Mindful Design
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
define('RIGPA_DE_MAP_IMAGES_OPTION', 'rigpa_de_map_images');
define('RIGPA_DE_MAP_COPY_OPTION', 'rigpa_de_map_copy');
define('RIGPA_DE_MAP_LOCATION_TEXTS_OPTION', 'rigpa_de_map_location_texts');
define('RIGPA_DE_MAP_DEFAULT_COUNTRY_OPTION', 'rigpa_de_map_default_country');

require_once RIGPA_DE_MAP_PATH . 'includes/countries.php';
require_once RIGPA_DE_MAP_PATH . 'includes/locations.php';
require_once RIGPA_DE_MAP_PATH . 'includes/class-rigpa-de-map.php';
require_once RIGPA_DE_MAP_PATH . 'includes/class-rigpa-de-map-admin.php';

register_activation_hook(__FILE__, 'rigpa_de_map_activate');

/**
 * On first activation, seed the URLs option with the known German centre paths.
 * Uses add_option so manually saved URLs are never overwritten on re-activation.
 */
function rigpa_de_map_activate() {
    add_option(RIGPA_DE_MAP_DEFAULT_COUNTRY_OPTION, 'germany', '', false);

    $seed = array(
        'aachen'             => '/centres/aachen/',
        'bad-saarow'         => '/centres/bad-saarow/',
        'bielefeld'          => '/centres/bielefeld/',
        'bremen'             => '/centres/bremen/',
        'dharma-mati-berlin' => '/centres/dharma-mati-berlin/',
        'duesseldorf'        => '/centres/dusseldorf/',
        'frankfurt'          => '/centres/frankfurt/',
        'freiburg'           => '/centres/freiburg/',
        'fuerth'             => '/centres/furth/',
        'hamburg'            => '/centres/hamburg/',
        'hannover'           => '/centres/hannover/',
        'heidelberg'         => '/centres/heidelberg/',
        'kassel'             => '/centres/kassel/',
        'koeln'              => '/centres/cologne/',
        'muenchen'           => '/centres/munich/',
        'stuttgart'          => '/centres/stuttgart/',
        'wiesbaden'          => '/centres/wiesbaden/',
    );

    add_option(RIGPA_DE_MAP_URLS_OPTION, $seed, '', false);
}

Rigpa_De_Map::init();
Rigpa_De_Map_Admin::init();
