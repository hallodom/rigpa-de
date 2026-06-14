<?php
/**
 * Duplicate an existing WordPress nav menu into a Rigpa Mega Menu location.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_Mega_Menu_Duplicator {

    /** @var array<int, string> */
    const MAIN_LOCATION_CANDIDATES = array(
        'primary',
        'main',
        'main-menu',
        'header',
        'top',
        'navigation',
    );

    /** @var array<int, string> */
    const MAIN_NAME_CANDIDATES = array(
        'Main Menu',
        'Main',
        'Primary Menu',
        'Primary',
        'Header Menu',
        'Header',
    );

    /**
     * Copy the site main menu into Mega Menu (English).
     *
     * @return array{source_menu_name: string, source_menu_id: int, target_menu_name: string, sections: int, links: int, location: string}|\WP_Error
     */
    public static function copy_main_to_english() {
        $source = self::resolve_main_menu();
        if ($source === null) {
            return new WP_Error(
                'rigpa_mega_menu_no_main',
                __('No main menu found. Assign a menu to a theme location such as Primary or Main, or name a menu "Main Menu".', 'rigpa-mega-menu')
            );
        }

        return self::copy_to_lang((int) $source['id'], 'english');
    }

    /**
     * Copy the site main menu into Mega Menu (German).
     *
     * @return array{source_menu_name: string, source_menu_id: int, target_menu_name: string, sections: int, links: int, location: string, descriptions_updated: int}|\WP_Error
     */
    public static function copy_main_to_german() {
        $source = self::resolve_main_menu();
        if ($source === null) {
            return new WP_Error(
                'rigpa_mega_menu_no_main',
                __('No main menu found. Assign a menu to a theme location such as Primary or Main, or name a menu "Main Menu".', 'rigpa-mega-menu')
            );
        }

        return self::copy_to_lang((int) $source['id'], 'german');
    }

    /**
     * @return array{id: int, name: string, location: string}|null
     */
    public static function resolve_main_menu() {
        $locations = get_nav_menu_locations();
        if (!is_array($locations)) {
            $locations = array();
        }

        foreach (self::MAIN_LOCATION_CANDIDATES as $slug) {
            if (empty($locations[$slug])) {
                continue;
            }

            $menu = wp_get_nav_menu_object((int) $locations[$slug]);
            if (!$menu instanceof WP_Term) {
                continue;
            }

            if ((string) $menu->slug === 'mega-menu-english' || (string) $menu->slug === 'mega-menu-german') {
                continue;
            }

            return array(
                'id'       => (int) $menu->term_id,
                'name'     => (string) $menu->name,
                'location' => $slug,
            );
        }

        foreach (self::MAIN_NAME_CANDIDATES as $name) {
            $menu = wp_get_nav_menu_object($name);
            if (!$menu instanceof WP_Term) {
                continue;
            }

            if ((string) $menu->slug === 'mega-menu-english' || (string) $menu->slug === 'mega-menu-german') {
                continue;
            }

            return array(
                'id'       => (int) $menu->term_id,
                'name'     => (string) $menu->name,
                'location' => '',
            );
        }

        return null;
    }

    /**
     * @param int    $source_menu_id
     * @param string $lang english|german
     * @return array{source_menu_name: string, source_menu_id: int, target_menu_name: string, sections: int, links: int, location: string}|\WP_Error
     */
    public static function copy_to_lang($source_menu_id, $lang) {
        $source_menu = wp_get_nav_menu_object($source_menu_id);
        if (!$source_menu instanceof WP_Term) {
            return new WP_Error(
                'rigpa_mega_menu_invalid_source',
                __('Source menu not found.', 'rigpa-mega-menu')
            );
        }

        $target_name = $lang === 'german' ? 'Mega Menu (German)' : 'Mega Menu (English)';
        $location    = rigpa_mega_menu_location_for_lang($lang);
        $target_id   = Rigpa_Mega_Menu_Seeder::get_or_create_menu($target_name);

        if ($target_id <= 0) {
            return new WP_Error(
                'rigpa_mega_menu_target_failed',
                __('Could not create the target mega menu.', 'rigpa-mega-menu')
            );
        }

        $copied = self::copy_items((int) $source_menu->term_id, $target_id);
        if (is_wp_error($copied)) {
            return $copied;
        }

        // Groups section is intentionally NOT appended on copy. The static
        // fallback render (see rigpa_mega_menu_get_english_menus / _german_menus)
        // still uses rigpa_mega_menu_get_groups_section() when no nav menu is
        // configured for the location, but copied menus get only the items
        // that exist in the source main menu.

        Rigpa_Mega_Menu_Seeder::assign_location($target_id, $location);

        $description_sync = Rigpa_Mega_Menu_Description_Sync::add_lang($lang);
        $descriptions_updated = 0;
        if (!is_wp_error($description_sync)) {
            $descriptions_updated = (int) $description_sync['updated'];
        }

        // Top-level sections must NOT receive auto-applied featured panels on
        // copy (users manage those manually) — with a narrow curated exception
        // list defined in rigpa_mega_menu_get_auto_featured_sections() so a
        // few key sections always carry their default image/text on copy and
        // can then be edited via the standard mega menu admin UI.
        $featured_updated = self::apply_auto_featured($target_id, $lang);

        return array(
            'source_menu_name'     => (string) $source_menu->name,
            'source_menu_id'       => (int) $source_menu->term_id,
            'target_menu_name'     => $target_name,
            'sections'             => (int) $copied['sections'],
            'links'                => (int) $copied['links'],
            'location'             => $location,
            'descriptions_updated' => $descriptions_updated,
            'featured_updated'     => $featured_updated,
        );
    }

    /**
     * @param int $source_menu_id
     * @param int $target_menu_id
     * @return array{sections: int, links: int}|\WP_Error
     */
    private static function copy_items($source_menu_id, $target_menu_id) {
        $items = wp_get_nav_menu_items($source_menu_id, array('update_post_term_cache' => false));
        if (!is_array($items) || $items === array()) {
            return new WP_Error(
                'rigpa_mega_menu_empty_source',
                __('The source menu has no items to copy.', 'rigpa-mega-menu')
            );
        }

        Rigpa_Mega_Menu_Seeder::clear_menu($target_menu_id);

        usort(
            $items,
            static function ($a, $b) {
                return (int) $a->menu_order <=> (int) $b->menu_order;
            }
        );

        /** @var array<int, int> */
        $id_map = array();
        $sections = 0;
        $links    = 0;

        foreach ($items as $item) {
            if (!$item instanceof WP_Post) {
                continue;
            }

            $parent_old = (int) $item->menu_item_parent;
            $parent_new = ($parent_old > 0 && isset($id_map[$parent_old])) ? $id_map[$parent_old] : 0;

            $args = Rigpa_Mega_Menu_Sanitize::menu_item_args(
                array(
                    'menu-item-object-id'   => (int) $item->object_id,
                    'menu-item-object'      => (string) $item->object,
                    'menu-item-parent-id'   => $parent_new,
                    'menu-item-position'    => (int) $item->menu_order,
                    'menu-item-type'        => (string) $item->type,
                    'menu-item-title'       => self::strip_emoji((string) $item->title),
                    'menu-item-url'         => (string) $item->url,
                    'menu-item-description' => self::strip_emoji((string) $item->description),
                    'menu-item-attr-title'  => self::strip_emoji((string) $item->attr_title),
                    'menu-item-target'      => (string) $item->target,
                    'menu-item-xfn'         => (string) $item->xfn,
                    'menu-item-status'      => 'publish',
                )
            );

            $new_id = wp_update_nav_menu_item($target_menu_id, 0, $args);
            if (is_wp_error($new_id)) {
                continue;
            }

            $id_map[(int) $item->ID] = (int) $new_id;
            self::copy_item_meta((int) $item->ID, (int) $new_id);

            if ($parent_new === 0) {
                $sections++;
            } else {
                $links++;
            }
        }

        if ($sections === 0 && $links === 0) {
            return new WP_Error(
                'rigpa_mega_menu_copy_failed',
                __('No menu items could be copied.', 'rigpa-mega-menu')
            );
        }

        return array(
            'sections' => $sections,
            'links'    => $links,
        );
    }

    /**
     * Apply curated featured callouts (e.g. In Deiner Nähe, Termine & Angebot)
     * to the matching top-level items in the target menu. The list of sections
     * to auto-populate is defined in rigpa_mega_menu_get_auto_featured_sections().
     *
     * @param int    $target_menu_id
     * @param string $lang english|german
     * @return int Number of items whose featured meta was written.
     */
    private static function apply_auto_featured($target_menu_id, $lang) {
        if (!function_exists('rigpa_mega_menu_get_auto_featured_sections')) {
            return 0;
        }

        $sections = rigpa_mega_menu_get_auto_featured_sections();
        if (!is_array($sections) || $sections === array()) {
            return 0;
        }

        $items = wp_get_nav_menu_items($target_menu_id, array('update_post_term_cache' => false));
        if (!is_array($items) || $items === array()) {
            return 0;
        }

        // Pre-compute normalized needles + sanitized featured config per section.
        $compiled = array();
        foreach ($sections as $section) {
            $variants = isset($section['variants']) && is_array($section['variants'])
                ? $section['variants']
                : array();
            $needles = array();
            foreach ($variants as $variant) {
                $needle = self::normalize_title((string) $variant);
                if ($needle !== '') {
                    $needles[$needle] = true;
                }
            }
            if ($needles === array()) {
                continue;
            }

            $featured_clean = null;
            $featured_raw   = $section['featured'][$lang] ?? null;
            if (is_array($featured_raw)) {
                $featured_clean = Rigpa_Mega_Menu_Sanitize::featured($featured_raw);
            }

            $centres_clean = null;
            $centres_raw   = $section['featured_centres'][$lang] ?? null;
            if (is_array($centres_raw) && $centres_raw !== array()) {
                $sanitized = Rigpa_Mega_Menu_Sanitize::featured_centres($centres_raw);
                if (is_array($sanitized) && $sanitized !== array()) {
                    $centres_clean = $sanitized;
                }
            }

            if ($featured_clean === null && $centres_clean === null) {
                continue;
            }

            $compiled[] = array(
                'needles'  => $needles,
                'featured' => $featured_clean,
                'centres'  => $centres_clean,
            );
        }

        if ($compiled === array()) {
            return 0;
        }

        $updated = 0;
        foreach ($items as $item) {
            if (!$item instanceof WP_Post) {
                continue;
            }
            if ((int) $item->menu_item_parent !== 0) {
                continue;
            }
            $normalized = self::normalize_title((string) $item->title);
            if ($normalized === '') {
                continue;
            }

            foreach ($compiled as $section) {
                if (!isset($section['needles'][$normalized])) {
                    continue;
                }
                $touched = false;
                if (is_array($section['featured'])) {
                    update_post_meta((int) $item->ID, '_rigpa_mega_menu_featured', $section['featured']);
                    $touched = true;
                }
                if (is_array($section['centres'])) {
                    update_post_meta((int) $item->ID, '_rigpa_mega_menu_featured_centres', $section['centres']);
                    $touched = true;
                }
                if ($touched) {
                    $updated++;
                }
                break;
            }
        }

        return $updated;
    }

    /**
     * Lowercase, strip leading emoji / status glyphs and punctuation, collapse
     * whitespace. Used for matching section heading variants.
     *
     * @param string $title
     * @return string
     */
    private static function normalize_title($title) {
        $title = wp_strip_all_tags($title);
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remove common leading emoji/status markers used in the source menu
        // (e.g. ☑️, ⏳, 🔤) and any surrounding whitespace/punctuation.
        $title = preg_replace('/^[^\p{L}\p{N}]+/u', '', $title) ?? $title;
        $title = preg_replace('/[^\p{L}\p{N}]+$/u', '', $title) ?? $title;
        $title = preg_replace('/\s+/u', ' ', $title) ?? $title;
        return function_exists('mb_strtolower') ? mb_strtolower(trim($title), 'UTF-8') : strtolower(trim($title));
    }

    /**
     * Strip emoji, pictographs, dingbats, variation selectors and zero-width
     * joiners from a string, then collapse whitespace and trim. Used so menu
     * item titles like "☑️ Donate" or "🔤 Compassion" land in the target as
     * "Donate" / "Compassion".
     *
     * @param string $text
     * @return string
     */
    private static function strip_emoji($text) {
        if ($text === '') {
            return $text;
        }

        // Unicode ranges covering the bulk of emoji + pictographs + dingbats
        // + regional indicators, plus the ZWJ and variation selectors used to
        // compose multi-codepoint emoji.
        $pattern = '/['
            . '\x{1F300}-\x{1F5FF}'   // Misc Symbols & Pictographs
            . '\x{1F600}-\x{1F64F}'   // Emoticons
            . '\x{1F680}-\x{1F6FF}'   // Transport & Map
            . '\x{1F700}-\x{1F77F}'   // Alchemical
            . '\x{1F780}-\x{1F7FF}'   // Geometric Shapes Extended
            . '\x{1F800}-\x{1F8FF}'   // Supplemental Arrows-C
            . '\x{1F900}-\x{1F9FF}'   // Supplemental Symbols & Pictographs
            . '\x{1FA00}-\x{1FA6F}'   // Chess Symbols / Symbols & Pictographs Ext-A
            . '\x{1FA70}-\x{1FAFF}'   // Symbols & Pictographs Ext-A
            . '\x{2600}-\x{26FF}'     // Misc Symbols (☀, ☑, ⏳…)
            . '\x{2700}-\x{27BF}'     // Dingbats (✅, ✨, ✉…)
            . '\x{1F1E6}-\x{1F1FF}'   // Regional Indicators (flags)
            . '\x{FE00}-\x{FE0F}'     // Variation Selectors
            . '\x{200D}'              // Zero-width joiner
            . '\x{20E3}'              // Combining enclosing keycap
            . '\x{2B00}-\x{2BFF}'     // Misc Symbols & Arrows
            . '\x{2300}-\x{23FF}'     // Misc Technical (⏰, ⌛…)
            . ']/u';

        $cleaned = preg_replace($pattern, '', $text);
        if ($cleaned === null) {
            // Regex failed (e.g. invalid UTF-8); fall back to the original.
            return trim($text);
        }

        $cleaned = preg_replace('/\s+/u', ' ', $cleaned) ?? $cleaned;
        return trim($cleaned);
    }

    /**
     * @param int $from_id
     * @param int $to_id
     */
    private static function copy_item_meta($from_id, $to_id) {
        $featured = get_post_meta($from_id, '_rigpa_mega_menu_featured', true);
        if (is_array($featured)) {
            $clean = Rigpa_Mega_Menu_Sanitize::featured($featured);
            if ($clean !== null) {
                update_post_meta($to_id, '_rigpa_mega_menu_featured', $clean);
            }
        }

        $centres = get_post_meta($from_id, '_rigpa_mega_menu_featured_centres', true);
        if (is_array($centres) && $centres !== array()) {
            $clean_centres = Rigpa_Mega_Menu_Sanitize::featured_centres($centres);
            if ($clean_centres !== array()) {
                update_post_meta($to_id, '_rigpa_mega_menu_featured_centres', $clean_centres);
            }
        }
    }
}
