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
 * Default editable copy shown by the map.
 *
 * @param string|null $country Country slug.
 * @return array<string, string>
 */
function rigpa_de_map_default_copy($country = null) {
    return rigpa_de_map_get_country($country)['copy'];
}

/**
 * @param string $key Copy field key.
 * @param string $value Raw field value.
 * @return string
 */
function rigpa_de_map_sanitize_copy_value($key, $value) {
    $value = trim((string) wp_unslash($value));

    if ($key === 'international_url') {
        return esc_url_raw($value);
    }

    return sanitize_text_field($value);
}

/**
 * @param string|null $country Country slug.
 * @return array<string, string>
 */
function rigpa_de_map_get_copy($country = null) {
    $country = $country === null ? rigpa_de_map_get_default_country() : rigpa_de_map_sanitize_country($country);
    $copy = get_option(RIGPA_DE_MAP_COPY_OPTION, array());
    $copy = is_array($copy) ? $copy : array();
    $default = rigpa_de_map_default_copy($country);

    if (isset($copy[$country]) && is_array($copy[$country])) {
        return array_merge($default, array_intersect_key($copy[$country], $default));
    }

    if ($country === 'germany') {
        return array_merge($default, array_intersect_key($copy, $default));
    }

    return $default;
}

/**
 * @param string|null $country Country slug.
 * @return array<string, array{name?: string, region?: string}>
 */
function rigpa_de_map_get_saved_location_texts($country = null) {
    $country = $country === null ? rigpa_de_map_get_default_country() : rigpa_de_map_sanitize_country($country);
    $texts = get_option(RIGPA_DE_MAP_LOCATION_TEXTS_OPTION, array());
    $texts = is_array($texts) ? $texts : array();

    if (isset($texts[$country]) && is_array($texts[$country])) {
        return $texts[$country];
    }

    return $country === 'germany' ? $texts : array();
}

/**
 * @param string $value Raw location text.
 * @return string
 */
function rigpa_de_map_sanitize_location_text($value) {
    return sanitize_text_field(trim((string) wp_unslash($value)));
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
 * @param string      $location_id Location slug.
 * @param string|null $country     Country slug.
 * @return string
 */
function rigpa_de_map_default_image_url($location_id, $country = null) {
    $country = $country === null ? rigpa_de_map_get_default_country() : rigpa_de_map_sanitize_country($country);
    if ($country !== 'germany') {
        return '';
    }

    $file_id = rigpa_de_map_default_image_file_id($location_id);
    $path    = RIGPA_DE_MAP_PATH . 'assets/images/' . $file_id . '.jpg';

    if (!file_exists($path)) {
        return '';
    }

    return RIGPA_DE_MAP_URL . 'assets/images/' . $file_id . '.jpg';
}

/**
 * @param string|null $country Country slug.
 * @return array<string, string>
 */
function rigpa_de_map_get_saved_images($country = null) {
    $country = $country === null ? rigpa_de_map_get_default_country() : rigpa_de_map_sanitize_country($country);
    $images = get_option(RIGPA_DE_MAP_IMAGES_OPTION, array());
    $images = is_array($images) ? $images : array();

    if (isset($images[$country]) && is_array($images[$country])) {
        return $images[$country];
    }

    return $country === 'germany' ? $images : array();
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
 * @param string|null $country Country slug.
 * @return array{left: array<int, array{id: string, name: string, region: string, url: string, image: string, coords: array{x: int, y: int}}>, right: array<int, array{id: string, name: string, region: string, url: string, image: string, coords: array{x: int, y: int}}>}
 */
function rigpa_de_map_get_locations($country = null) {
    $country = $country === null ? rigpa_de_map_get_default_country() : rigpa_de_map_sanitize_country($country);
    $locations = rigpa_de_map_get_country($country)['locations'];

    $saved_urls           = rigpa_de_map_get_saved_urls($country);
    $saved_images         = rigpa_de_map_get_saved_images($country);
    $saved_location_texts = rigpa_de_map_get_saved_location_texts($country);

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
            if (isset($saved_location_texts[$id]) && is_array($saved_location_texts[$id])) {
                if (isset($saved_location_texts[$id]['name']) && $saved_location_texts[$id]['name'] !== '') {
                    $locations[$column][$index]['name'] = $saved_location_texts[$id]['name'];
                }
                if (array_key_exists('region', $saved_location_texts[$id])) {
                    $locations[$column][$index]['region'] = $saved_location_texts[$id]['region'];
                }
            }
            $locations[$column][$index]['image'] = rigpa_de_map_get_location_image_url($id, $saved_images);
        }
    }

    return $locations;
}

/**
 * @param string|null $country Country slug.
 * @return array<string, string>
 */
function rigpa_de_map_get_saved_urls($country = null) {
    $country = $country === null ? rigpa_de_map_get_default_country() : rigpa_de_map_sanitize_country($country);
    $urls = get_option(RIGPA_DE_MAP_URLS_OPTION, array());
    $urls = is_array($urls) ? $urls : array();

    if (isset($urls[$country]) && is_array($urls[$country])) {
        return $urls[$country];
    }

    return $country === 'germany' ? $urls : array();
}
