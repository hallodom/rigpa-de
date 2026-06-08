<?php
/**
 * Location data for the Rigpa.de map (mirrors Replicate Design/src/data/locations.ts).
 */

/**
 * Sanitize a location link: absolute URLs (https://) or site-relative paths (/page).
 *
 * @param string $url Raw URL from admin input.
 * @return string Sanitized URL or empty string if invalid.
 */
function rigpa_de_map_sanitize_location_url($url) {
    $url = trim((string) wp_unslash($url));
    if ($url === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $url)) {
        return esc_url_raw($url);
    }

    // Reject other schemes and protocol-relative URLs.
    if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url) || strpos($url, '//') === 0) {
        return '';
    }

    if ($url[0] !== '/') {
        $url = '/' . $url;
    }

    $url = preg_replace('/[\x00-\x1F\x7F]/', '', $url);

    return sanitize_text_field($url);
}

/**
 * Sanitize a location image URL from admin input.
 *
 * @param string $url Raw URL from admin input.
 * @return string Sanitized URL or empty string if invalid.
 */
function rigpa_de_map_sanitize_location_image($url) {
    $url = trim((string) wp_unslash($url));
    if ($url === '') {
        return '';
    }

    return esc_url_raw($url);
}

/**
 * Bundled image filename stem for a location (handles aliases).
 *
 * @param string $location_id Location slug.
 * @return string
 */
function rigpa_de_map_default_image_file_id($location_id) {
    $aliases = array(
        'dharma-mati-berlin' => 'berlin',
    );

    return $aliases[$location_id] ?? $location_id;
}

/**
 * URL of the bundled default image for a location, or empty if none exists.
 *
 * @param string $location_id Location slug.
 * @return string
 */
function rigpa_de_map_default_image_url($location_id) {
    $file_id = rigpa_de_map_default_image_file_id($location_id);
    $path    = RIGPA_DE_MAP_PATH . 'assets/images/' . $file_id . '.jpg';

    if (!file_exists($path)) {
        return '';
    }

    return RIGPA_DE_MAP_URL . 'assets/images/' . $file_id . '.jpg';
}

/**
 * @return array<string, string>
 */
function rigpa_de_map_get_saved_images() {
    $images = get_option(RIGPA_DE_MAP_IMAGES_OPTION, array());

    return is_array($images) ? $images : array();
}

/**
 * Resolve the effective image URL for a location on the front end.
 *
 * @param string               $location_id   Location slug.
 * @param array<string, string>|null $saved_images Saved overrides (optional).
 * @return string Resolved URL, or empty string when explicitly cleared / unavailable.
 */
function rigpa_de_map_get_location_image_url($location_id, $saved_images = null) {
    if ($saved_images === null) {
        $saved_images = rigpa_de_map_get_saved_images();
    }

    if (!array_key_exists($location_id, $saved_images)) {
        return rigpa_de_map_default_image_url($location_id);
    }

    return (string) $saved_images[$location_id];
}

/**
 * Admin image field state: default, custom, or none.
 *
 * @param string               $location_id   Location slug.
 * @param array<string, string> $saved_images Saved overrides.
 * @return string One of default, custom, none.
 */
function rigpa_de_map_get_location_image_state($location_id, $saved_images) {
    if (!array_key_exists($location_id, $saved_images)) {
        return 'default';
    }

    if ($saved_images[$location_id] === '') {
        return 'none';
    }

    return 'custom';
}

/**
 * @return array{left: array<int, array{id: string, name: string, region: string, url: string, image: string, coords: array{x: int, y: int}}>, right: array<int, array{id: string, name: string, region: string, url: string, image: string, coords: array{x: int, y: int}}>}
 */
function rigpa_de_map_get_locations() {
    $locations = array(
        'left'  => array(
            array(
                'id'     => 'aachen',
                'name'   => 'Aachen',
                'region' => 'Group',
                'url'    => '',
                'coords' => array('x' => 162, 'y' => 217),
            ),
            array(
                'id'     => 'bad-saarow',
                'name'   => 'Bad Saarow',
                'region' => 'Group',
                'url'    => '',
                'coords' => array('x' => 406, 'y' => 142),
            ),
            array(
                'id'     => 'dharma-mati-berlin',
                'name'   => 'Dharma Mati Berlin',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 386, 'y' => 132),
            ),
            array(
                'id'     => 'bielefeld',
                'name'   => 'Bielefeld',
                'region' => 'Group',
                'url'    => '',
                'coords' => array('x' => 237, 'y' => 156),
            ),
            array(
                'id'     => 'bremen',
                'name'   => 'Bremen',
                'region' => 'Group',
                'url'    => '',
                'coords' => array('x' => 245, 'y' => 104),
            ),
            array(
                'id'     => 'duesseldorf',
                'name'   => 'Düsseldorf',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 184, 'y' => 195),
            ),
            array(
                'id'     => 'frankfurt',
                'name'   => 'Frankfurt',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 242, 'y' => 250),
            ),
            array(
                'id'     => 'freiburg',
                'name'   => 'Freiburg',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 216, 'y' => 354),
            ),
            array(
                'id'     => 'hannover',
                'name'   => 'Hannover',
                'region' => 'Group',
                'url'    => '',
                'coords' => array('x' => 263, 'y' => 125),
            ),
        ),
        'right' => array(
            array(
                'id'     => 'fuerth',
                'name'   => 'Fürth',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 313, 'y' => 281),
            ),
            array(
                'id'     => 'hamburg',
                'name'   => 'Hamburg',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 282, 'y' => 81),
            ),
            array(
                'id'     => 'heidelberg',
                'name'   => 'Heidelberg',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 242, 'y' => 285),
            ),
            array(
                'id'     => 'kassel',
                'name'   => 'Kassel',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 267, 'y' => 191),
            ),
            array(
                'id'     => 'koeln',
                'name'   => 'Köln',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 189, 'y' => 209),
            ),
            array(
                'id'     => 'muenchen',
                'name'   => 'München',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 330, 'y' => 346),
            ),
            array(
                'id'     => 'stuttgart',
                'name'   => 'Stuttgart',
                'region' => 'Group',
                'url'    => '',
                'coords' => array('x' => 257, 'y' => 314),
            ),
            array(
                'id'     => 'wiesbaden',
                'name'   => 'Wiesbaden',
                'region' => '',
                'url'    => '',
                'coords' => array('x' => 228, 'y' => 251),
            ),
        ),
    );

    $saved_urls   = get_option(RIGPA_DE_MAP_URLS_OPTION, array());
    $saved_images = rigpa_de_map_get_saved_images();

    if (!is_array($saved_urls)) {
        $saved_urls = array();
    }

    foreach (array('left', 'right') as $column) {
        if (!isset($locations[$column])) {
            continue;
        }
        foreach ($locations[$column] as $index => $item) {
            $id = $item['id'] ?? '';
            if ($id === '') {
                continue;
            }
            if (isset($saved_urls[$id])) {
                $locations[$column][$index]['url'] = $saved_urls[$id];
            }
            $locations[$column][$index]['image'] = rigpa_de_map_get_location_image_url($id, $saved_images);
        }
    }

    return $locations;
}
