<?php
/**
 * Copy default menu item descriptions from static data onto WordPress nav menus.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_Mega_Menu_Description_Sync {

    /**
     * Relative paths to description lookup data files.
     */
    const SOURCE_FILES = 'includes/menu-descriptions.php, includes/menus.php';

    /**
     * Minimum similarity score (0–1) required for an approximate title match.
     */
    const MATCH_THRESHOLD = 0.72;

    /**
     * Apply default descriptions to both mega menu locations.
     *
     * @return array<string, array{menu_name: string, updated: int, skipped: int, unchanged: int}>
     */
    public static function add_all() {
        return array(
            'english' => self::add_lang('english'),
            'german'  => self::add_lang('german'),
        );
    }

    /**
     * @deprecated Use add_all().
     * @return array<string, array{menu_name: string, updated: int, skipped: int, unchanged: int}|\WP_Error>
     */
    public static function sync_all() {
        return self::add_all();
    }

    /**
     * Clear descriptions from matched items on both mega menu locations.
     *
     * @return array<string, array{menu_name: string, updated: int, skipped: int, unchanged: int}>
     */
    public static function clear_all() {
        return array(
            'english' => self::clear_lang('english'),
            'german'  => self::clear_lang('german'),
        );
    }

    /**
     * @param string $lang english|german
     * @return array{menu_name: string, updated: int, skipped: int, unchanged: int}|\WP_Error
     */
    public static function add_lang($lang) {
        return self::apply_lang($lang, 'add');
    }

    /**
     * @deprecated Use add_lang().
     * @param string $lang english|german
     * @return array{menu_name: string, updated: int, skipped: int, unchanged: int}|\WP_Error
     */
    public static function sync_lang($lang) {
        return self::add_lang($lang);
    }

    /**
     * @param string $lang english|german
     * @return array{menu_name: string, updated: int, skipped: int, unchanged: int}|\WP_Error
     */
    public static function clear_lang($lang) {
        return self::apply_lang($lang, 'clear');
    }

    /**
     * @param string $lang english|german
     * @param string $mode add|clear
     * @return array{menu_name: string, updated: int, skipped: int, unchanged: int}|\WP_Error
     */
    private static function apply_lang($lang, $mode) {
        if ($lang !== 'english' && $lang !== 'german') {
            return new WP_Error('rigpa_mega_menu_invalid_lang', __('Invalid language.', 'rigpa-mega-menu'));
        }

        $menu_name = $lang === 'german' ? 'Mega Menu (German)' : 'Mega Menu (English)';
        $menu      = wp_get_nav_menu_object($menu_name);
        if (!$menu instanceof WP_Term) {
            return new WP_Error(
                'rigpa_mega_menu_missing_menu',
                sprintf(__('Menu "%s" was not found.', 'rigpa-mega-menu'), $menu_name)
            );
        }

        $entries = self::build_entries($lang);
        $items   = wp_get_nav_menu_items((int) $menu->term_id, array('update_post_term_cache' => false));
        if (!is_array($items)) {
            return new WP_Error(
                'rigpa_mega_menu_empty_menu',
                sprintf(__('Menu "%s" has no items.', 'rigpa-mega-menu'), $menu_name)
            );
        }

        $updated   = 0;
        $skipped   = 0;
        $unchanged = 0;

        foreach ($items as $item) {
            if (!$item instanceof WP_Post) {
                continue;
            }

            // Only nested links use the subtitle line in the mega menu UI.
            if ((int) $item->menu_item_parent === 0) {
                continue;
            }

            $match = self::match_entry((string) $item->title, $entries);
            if ($match === null) {
                $skipped++;
                continue;
            }

            $target = $mode === 'clear' ? '' : $match['description'];

            $current = Rigpa_Mega_Menu_Sanitize::text((string) $item->description);
            if ($current === $target) {
                $unchanged++;
                continue;
            }

            if (!self::update_item_description((int) $menu->term_id, $item, $target)) {
                $skipped++;
                continue;
            }

            $updated++;
        }

        return array(
            'menu_name' => $menu_name,
            'updated'   => $updated,
            'skipped'   => $skipped,
            'unchanged' => $unchanged,
        );
    }

    /**
     * @param int $menu_id
     * @param WP_Post $item
     * @param string $description
     */
    private static function update_item_description($menu_id, WP_Post $item, $description) {
        $result = wp_update_nav_menu_item(
            $menu_id,
            (int) $item->ID,
            array(
                'menu-item-title'       => Rigpa_Mega_Menu_Sanitize::text((string) $item->title),
                'menu-item-url'         => (string) $item->url,
                'menu-item-description' => $description,
                'menu-item-type'        => (string) $item->type,
                'menu-item-object'      => (string) $item->object,
                'menu-item-object-id'   => (int) $item->object_id,
                'menu-item-parent-id'   => (int) $item->menu_item_parent,
                'menu-item-status'      => 'publish',
                'menu-item-classes'     => '',
            )
        );

        return !is_wp_error($result);
    }

    /**
     * @param string $lang
     * @return array<int, array{title: string, normalized: string, description: string, tokens: array<int, string>}>
     */
    private static function build_entries($lang) {
        $entries = array();

        foreach (self::entries_from_items(rigpa_mega_menu_get_description_entries($lang)) as $entry) {
            $entries[] = $entry;
        }

        foreach (rigpa_mega_menu_get_static_menus($lang) as $section) {
            foreach (self::entries_from_items($section['items'] ?? array()) as $entry) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array{title: string, normalized: string, description: string, tokens: array<int, string>}>
     */
    private static function entries_from_items(array $items) {
        $entries = array();

        foreach ($items as $item) {
            $description = Rigpa_Mega_Menu_Sanitize::text((string) ($item['description'] ?? ''));
            $title       = Rigpa_Mega_Menu_Sanitize::text((string) ($item['title'] ?? ''));
            if ($description === '' || $title === '') {
                continue;
            }

            $normalized = self::normalize_title($title);
            if ($normalized === '') {
                continue;
            }

            $entries[] = array(
                'title'       => $title,
                'normalized'  => $normalized,
                'description' => $description,
                'tokens'      => self::significant_tokens($normalized),
            );
        }

        return $entries;
    }

    /**
     * @param string $nav_title
     * @param array<int, array{title: string, normalized: string, description: string, tokens: array<int, string>}> $entries
     * @return array{title: string, normalized: string, description: string, tokens: array<int, string>}|null
     */
    private static function match_entry($nav_title, array $entries) {
        $normalized = self::normalize_title($nav_title);
        if ($normalized === '') {
            return null;
        }

        $tokens = self::significant_tokens($normalized);
        $best   = null;
        $score  = 0.0;

        foreach ($entries as $entry) {
            $candidate_score = self::title_similarity($normalized, $tokens, $entry);
            if ($candidate_score > $score) {
                $score = $candidate_score;
                $best  = $entry;
            }
        }

        if ($best === null || $score < self::MATCH_THRESHOLD) {
            return null;
        }

        return $best;
    }

    /**
     * Score how closely two menu titles match in meaning/spelling.
     *
     * @param string $nav_normalized
     * @param array<int, string> $nav_tokens
     * @param array{title: string, normalized: string, description: string, tokens: array<int, string>} $entry
     */
    private static function title_similarity($nav_normalized, array $nav_tokens, array $entry) {
        $static_normalized = $entry['normalized'];

        if ($nav_normalized === $static_normalized) {
            return 1.0;
        }

        $contains_score = self::contains_similarity($nav_normalized, $static_normalized);
        if ($contains_score > 0) {
            return $contains_score;
        }

        $token_score = self::token_similarity($nav_tokens, $entry['tokens']);
        $text_score  = self::text_similarity($nav_normalized, $static_normalized);

        // Weight shared words more heavily than raw character similarity.
        return max($text_score, ($token_score * 0.75) + ($text_score * 0.25));
    }

    /**
     * @param string $a
     * @param string $b
     */
    private static function contains_similarity($a, $b) {
        if ($a === $b) {
            return 1.0;
        }

        $shorter = strlen($a) <= strlen($b) ? $a : $b;
        $longer  = strlen($a) > strlen($b) ? $a : $b;

        if ($shorter === '' || !str_contains($longer, $shorter)) {
            return 0.0;
        }

        $ratio = strlen($shorter) / max(strlen($longer), 1);
        if ($ratio < 0.45) {
            return 0.0;
        }

        return min(0.96, 0.78 + ($ratio * 0.18));
    }

    /**
     * @param array<int, string> $nav_tokens
     * @param array<int, string> $static_tokens
     */
    private static function token_similarity(array $nav_tokens, array $static_tokens) {
        if ($nav_tokens === array() || $static_tokens === array()) {
            return 0.0;
        }

        $intersection = array_intersect($nav_tokens, $static_tokens);
        $union        = array_unique(array_merge($nav_tokens, $static_tokens));
        if ($union === array()) {
            return 0.0;
        }

        $jaccard = count($intersection) / count($union);

        $static_coverage = count($intersection) / count($static_tokens);
        $nav_coverage    = count($intersection) / count($nav_tokens);

        return max($jaccard, $static_coverage * 0.9, $nav_coverage * 0.85);
    }

    /**
     * @param string $a
     * @param string $b
     */
    private static function text_similarity($a, $b) {
        similar_text($a, $b, $percent);

        $max_len = max(strlen($a), strlen($b));
        if ($max_len === 0) {
            return 1.0;
        }

        $lev = levenshtein($a, $b);
        $lev_ratio = 1 - ($lev / $max_len);

        return max($percent / 100, $lev_ratio);
    }

    /**
     * @param string $normalized
     * @return array<int, string>
     */
    private static function significant_tokens($normalized) {
        $parts = preg_split('/\s+/u', $normalized) ?: array();
        $stop_words = array(
            'a', 'an', 'and', 'for', 'in', 'of', 'on', 'the', 'to', 'with',
            'and', 'der', 'die', 'das', 'und', 'in', 'im', 'zu', 'zum', 'zur', 'von', 'mit', 'bei',
        );

        $tokens = array();
        foreach ($parts as $part) {
            $part = trim($part, "-_");
            if ($part === '' || strlen($part) < 3) {
                continue;
            }
            if (in_array($part, $stop_words, true)) {
                continue;
            }
            $tokens[] = $part;
        }

        return array_values(array_unique($tokens));
    }

    /**
     * @param string $title
     */
    private static function normalize_title($title) {
        $title = Rigpa_Mega_Menu_Sanitize::text($title);
        $title = remove_accents($title);
        $title = strtolower($title);
        $title = preg_replace('/[\x{2010}\x{2011}\x{2012}\x{2013}\x{2014}\x{2212}]/u', '-', $title) ?? $title;
        $title = preg_replace('/[^\p{L}\p{N}\s-]+/u', ' ', $title) ?? $title;
        $title = preg_replace('/\s+/u', ' ', $title) ?? $title;
        $title = preg_replace('/-+/u', '-', $title) ?? $title;

        return trim($title);
    }

    // -------------------------------------------------------------------------
    // Featured panel sync
    // -------------------------------------------------------------------------

    /**
     * Apply default featured panels to both mega menu locations.
     *
     * @return array<string, array{menu_name: string, updated: int, skipped: int, unchanged: int}|\WP_Error>
     */
    public static function apply_featured_all() {
        return array(
            'english' => self::apply_featured_lang('english'),
            'german'  => self::apply_featured_lang('german'),
        );
    }

    /**
     * Clear featured panels from matched sections on both mega menu locations.
     *
     * @return array<string, array{menu_name: string, updated: int, skipped: int, unchanged: int}|\WP_Error>
     */
    public static function clear_featured_all() {
        return array(
            'english' => self::clear_featured_lang('english'),
            'german'  => self::clear_featured_lang('german'),
        );
    }

    /**
     * @param string $lang english|german
     * @return array{menu_name: string, updated: int, skipped: int, unchanged: int}|\WP_Error
     */
    public static function apply_featured_lang($lang) {
        return self::do_featured($lang, 'add');
    }

    /**
     * @param string $lang english|german
     * @return array{menu_name: string, updated: int, skipped: int, unchanged: int}|\WP_Error
     */
    public static function clear_featured_lang($lang) {
        return self::do_featured($lang, 'clear');
    }

    /**
     * @param string $lang english|german
     * @param string $mode add|clear
     * @return array{menu_name: string, updated: int, skipped: int, unchanged: int}|\WP_Error
     */
    private static function do_featured($lang, $mode) {
        if ($lang !== 'english' && $lang !== 'german') {
            return new WP_Error('rigpa_mega_menu_invalid_lang', __('Invalid language.', 'rigpa-mega-menu'));
        }

        $menu_name = $lang === 'german' ? 'Mega Menu (German)' : 'Mega Menu (English)';
        $menu      = wp_get_nav_menu_object($menu_name);
        if (!$menu instanceof WP_Term) {
            return new WP_Error(
                'rigpa_mega_menu_missing_menu',
                sprintf(__('Menu "%s" was not found.', 'rigpa-mega-menu'), $menu_name)
            );
        }

        $featured_lookup = self::build_featured_lookup($lang);
        $items = wp_get_nav_menu_items((int) $menu->term_id, array('update_post_term_cache' => false));
        if (!is_array($items)) {
            return new WP_Error(
                'rigpa_mega_menu_empty_menu',
                sprintf(__('Menu "%s" has no items.', 'rigpa-mega-menu'), $menu_name)
            );
        }

        $updated   = 0;
        $skipped   = 0;
        $unchanged = 0;

        foreach ($items as $item) {
            if (!$item instanceof WP_Post) {
                continue;
            }

            // Featured panels belong to top-level section headings only.
            if ((int) $item->menu_item_parent !== 0) {
                continue;
            }

            $matched_featured = self::match_featured((string) $item->title, $featured_lookup);
            if ($matched_featured === null) {
                $skipped++;
                continue;
            }

            if ($mode === 'clear') {
                $existing = get_post_meta((int) $item->ID, '_rigpa_mega_menu_featured', true);
                if (!is_array($existing) || empty($existing)) {
                    $unchanged++;
                    continue;
                }
                delete_post_meta((int) $item->ID, '_rigpa_mega_menu_featured');
                $updated++;
                continue;
            }

            $existing = get_post_meta((int) $item->ID, '_rigpa_mega_menu_featured', true);
            if (is_array($existing) && !empty($existing['title'])) {
                $existing_clean = Rigpa_Mega_Menu_Sanitize::featured($existing);
                if ($existing_clean === $matched_featured) {
                    $unchanged++;
                    continue;
                }
            }

            update_post_meta((int) $item->ID, '_rigpa_mega_menu_featured', $matched_featured);
            $updated++;
        }

        return array(
            'menu_name' => $menu_name,
            'updated'   => $updated,
            'skipped'   => $skipped,
            'unchanged' => $unchanged,
        );
    }

    /**
     * @param string $lang
     * @return array<int, array{label_normalized: string, label_tokens: array<int, string>, featured: array{title: string, description: string, image: string, url: string}}>
     */
    private static function build_featured_lookup($lang) {
        $lookup = array();

        foreach (rigpa_mega_menu_get_static_menus($lang) as $section) {
            if (empty($section['featured']) || !is_array($section['featured'])) {
                continue;
            }

            $label = Rigpa_Mega_Menu_Sanitize::text((string) ($section['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $normalized = self::normalize_title($label);
            if ($normalized === '') {
                continue;
            }

            $featured = array(
                'title'       => Rigpa_Mega_Menu_Sanitize::text((string) ($section['featured']['title'] ?? '')),
                'description' => Rigpa_Mega_Menu_Sanitize::text((string) ($section['featured']['description'] ?? '')),
                'image'       => (string) ($section['featured']['image'] ?? ''),
                'url'         => (string) ($section['featured']['url'] ?? ''),
            );

            if ($featured['title'] === '') {
                continue;
            }

            $lookup[] = array(
                'label_normalized' => $normalized,
                'label_tokens'     => self::significant_tokens($normalized),
                'featured'         => $featured,
            );
        }

        return $lookup;
    }

    /**
     * @param string $nav_title
     * @param array<int, array{label_normalized: string, label_tokens: array<int, string>, featured: array{title: string, description: string, image: string, url: string}}> $lookup
     * @return array{title: string, description: string, image: string, url: string}|null
     */
    private static function match_featured($nav_title, array $lookup) {
        $normalized = self::normalize_title($nav_title);
        if ($normalized === '') {
            return null;
        }

        $tokens = self::significant_tokens($normalized);
        $best   = null;
        $score  = 0.0;

        foreach ($lookup as $entry) {
            $candidate_score = self::title_similarity(
                $normalized,
                $tokens,
                array(
                    'normalized' => $entry['label_normalized'],
                    'tokens'     => $entry['label_tokens'],
                )
            );
            if ($candidate_score > $score) {
                $score = $candidate_score;
                $best  = $entry['featured'];
            }
        }

        if ($best === null || $score < self::MATCH_THRESHOLD) {
            return null;
        }

        return $best;
    }
}
