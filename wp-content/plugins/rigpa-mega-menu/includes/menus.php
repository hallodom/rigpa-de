<?php
/**
 * Static mega menu data (English and German).
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return string
 */
function rigpa_mega_menu_asset_url($filename) {
    return RIGPA_MEGA_MENU_URL . 'assets/images/' . ltrim($filename, '/');
}

/**
 * Register nav menu locations editable under Appearance → Menus.
 */
function rigpa_mega_menu_register_nav_menus() {
    register_nav_menus(
        array(
            'rigpa-mega-menu-en' => __('Mega Menu (English)', 'rigpa-mega-menu'),
            'rigpa-mega-menu-de' => __('Mega Menu (German)', 'rigpa-mega-menu'),
        )
    );
}
add_action('after_setup_theme', 'rigpa_mega_menu_register_nav_menus');

/**
 * @param string $lang english|german
 * @return string
 */
function rigpa_mega_menu_location_for_lang($lang) {
    return $lang === 'german' ? 'rigpa-mega-menu-de' : 'rigpa-mega-menu-en';
}

/**
 * Resolve a page ID from a site-relative path such as /introduction-to-meditation/.
 *
 * @param string $url
 * @return int
 */
function rigpa_mega_menu_page_id_from_url($url) {
    $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
    if ($path === '') {
        return 0;
    }

    $page = get_page_by_path($path, OBJECT, 'page');

    return $page instanceof WP_Post ? (int) $page->ID : 0;
}

/**
 * Build mega menu JSON from a WordPress nav menu assigned to our location.
 *
 * @param string $lang english|german
 * @return array<int, array<string, mixed>>|null
 */
function rigpa_mega_menu_build_menus_from_nav($lang) {
    $location = rigpa_mega_menu_location_for_lang($lang);
    $locations = get_nav_menu_locations();

    if (empty($locations[$location])) {
        return null;
    }

    $menu = wp_get_nav_menu_object((int) $locations[$location]);
    if (!$menu instanceof WP_Term) {
        return null;
    }

    $items = wp_get_nav_menu_items((int) $menu->term_id, array('update_post_term_cache' => false));
    if (!is_array($items) || $items === array()) {
        return null;
    }

    $sections = array();
    $children_by_parent = array();

    foreach ($items as $item) {
        if (!$item instanceof WP_Post) {
            continue;
        }

        $parent_id = (int) $item->menu_item_parent;
        if ($parent_id === 0) {
            $section_url = Rigpa_Mega_Menu_Sanitize::text((string) $item->url);
            if ($section_url === '#' || $section_url === '') {
                $section_url = '';
            }

            $sections[(int) $item->ID] = array(
                'label' => Rigpa_Mega_Menu_Sanitize::text((string) $item->title),
                'url'   => $section_url,
                'items' => array(),
            );

            $featured = get_post_meta((int) $item->ID, '_rigpa_mega_menu_featured', true);
            if (is_array($featured)) {
                $clean_featured = Rigpa_Mega_Menu_Sanitize::featured($featured);
                if ($clean_featured !== null) {
                    if (!empty($clean_featured['image']) && !str_starts_with($clean_featured['image'], 'http')) {
                        $clean_featured['image'] = home_url($clean_featured['image']);
                    }
                    $sections[(int) $item->ID]['featured'] = $clean_featured;
                }
            }

            $featured_centres = get_post_meta((int) $item->ID, '_rigpa_mega_menu_featured_centres', true);
            if (is_array($featured_centres) && $featured_centres !== array()) {
                $clean_centres = Rigpa_Mega_Menu_Sanitize::featured_centres($featured_centres);
                if ($clean_centres !== array()) {
                    foreach ($clean_centres as $index => $centre) {
                        if (!empty($centre['image']) && !str_starts_with($centre['image'], 'http')) {
                            $clean_centres[$index]['image'] = home_url($centre['image']);
                        }
                    }
                    $sections[(int) $item->ID]['featuredCentres'] = $clean_centres;
                }
            }
            continue;
        }

        if (!isset($children_by_parent[$parent_id])) {
            $children_by_parent[$parent_id] = array();
        }

        $children_by_parent[$parent_id][] = $item;
    }

    if ($sections === array()) {
        return null;
    }

    foreach ($sections as $section_id => $section) {
        foreach ($children_by_parent[$section_id] ?? array() as $child) {
            $link = Rigpa_Mega_Menu_Sanitize::menu_link(
                array(
                    'title'       => (string) $child->title,
                    'description' => (string) $child->description,
                    'url'         => (string) $child->url,
                )
            );

            if ($link['title'] === '') {
                continue;
            }

            $section['items'][] = $link;
        }

        if ($section['items'] === array() && $section['url'] === '') {
            // Keep childless sections as non-interactive labels rather than dropping them.
            // They appear in the nav bar but have no dropdown.
        }

        $sections[$section_id] = $section;
    }

    return array_values($sections);
}

/**
 * @param string $lang english|german
 * @return array<int, array<string, mixed>>
 */
function rigpa_mega_menu_get_static_menus($lang) {
    if ($lang === 'german') {
        return rigpa_mega_menu_get_german_menus();
    }

    return rigpa_mega_menu_get_english_menus();
}

/**
 * Resolve language key from shortcode / locale.
 *
 * @param string $lang auto|english|german
 * @return string english|german
 */
function rigpa_mega_menu_resolve_lang($lang) {
    $lang = strtolower(trim((string) $lang));

    if ($lang === 'german' || $lang === 'de') {
        return 'german';
    }

    if ($lang === 'english' || $lang === 'en') {
        return 'english';
    }

    return str_starts_with(get_locale(), 'de') ? 'german' : 'english';
}

/**
 * @param string $lang english|german
 * @return array<int, array<string, mixed>>
 */
function rigpa_mega_menu_get_menus($lang) {
    $from_nav = rigpa_mega_menu_build_menus_from_nav($lang);
    if ($from_nav !== null) {
        return $from_nav;
    }

    return rigpa_mega_menu_get_static_menus($lang);
}

/**
 * Build the "In Deiner Nähe" / "Near You" section: all German locations
 * plus international retreat centres, with Dharma Mati Berlin as featured.
 *
 * Location list mirrors the Rigpa.de map data (rigpa-de-map plugin) but is kept
 * here so the mega menu has no hard dependency on the map plugin.
 *
 * @param string $lang english|german
 * @return array<string, mixed>
 */
function rigpa_mega_menu_get_near_you_section($lang) {
    $location_names = array(
        'Aachen',
        'Bad Saarow',
        'Bielefeld',
        'Bremen',
        'Dharma Mati Berlin',
        'Düsseldorf',
        'Frankfurt',
        'Freiburg',
        'Fürth',
        'Hamburg',
        'Hannover',
        'Heidelberg',
        'Kassel',
        'Köln',
        'München',
        'Stuttgart',
        'Wiesbaden',
    );

    $items = array();
    foreach ($location_names as $name) {
        $items[] = array('title' => $name, 'description' => '', 'url' => '#');
    }

    if ($lang === 'german') {
        $items[] = array('title' => 'Internationale Retreatzentren', 'description' => '', 'url' => '#');

        return array(
            'label'    => 'In Deiner Nähe',
            'items'    => $items,
            'featured' => array(
                'title'       => 'Dharma Mati Berlin',
                'description' => 'Unser Rigpa-Zentrum in Berlin',
                'image'       => rigpa_mega_menu_asset_url('featured-visit.jpg'),
                'url'         => '#',
            ),
        );
    }

    $items[] = array('title' => 'International Retreat Centres', 'description' => '', 'url' => '#');

    return array(
        'label'    => 'Near You',
        'items'    => $items,
        'featured' => array(
            'title'       => 'Dharma Mati Berlin',
            'description' => 'Our Rigpa centre in Berlin',
            'image'       => rigpa_mega_menu_asset_url('featured-visit.jpg'),
            'url'         => '#',
        ),
    );
}

/**
 * @return array<int, array<string, mixed>>
 */
function rigpa_mega_menu_get_english_menus() {
    $menus = array(
        array(
            'label' => 'New Here?',
            'items' => array(
                array('title' => 'Meditate Now', 'description' => 'Start your meditation practice today', 'url' => '#'),
                array('title' => 'Discover Compassion', 'description' => 'Explore the path of compassion', 'url' => '#'),
                array('title' => 'Offerings for Beginners', 'description' => 'First steps in meditation and Buddhism', 'url' => '#'),
                array('title' => 'Overview', 'description' => 'All offerings at a glance', 'url' => '#'),
                array('title' => 'Bodhi Courses', 'description' => 'Study programmes and courses from Bodhi', 'url' => '#'),
                array('title' => 'What is Buddhism?', 'description' => 'An introduction to Buddhist teachings', 'url' => '#'),
                array('title' => 'Find a Group', 'description' => 'Connect with practitioners near you', 'url' => '#'),
            ),
        ),
        array(
            'label' => 'Courses & Events',
            'items' => array(
                array('title' => 'All Courses and Events', 'description' => 'Browse all upcoming events', 'url' => '#'),
                array('title' => 'Online Programme', 'description' => 'Digital courses and live streams', 'url' => '#'),
                array('title' => 'For Beginners', 'description' => 'Events for newcomers', 'url' => '#'),
                array('title' => 'Healing, Illness & Loss', 'description' => 'Support during difficult times', 'url' => '#'),
                array('title' => 'Today', 'description' => "Today's events and activities", 'url' => '#'),
                array('title' => 'Children & Families', 'description' => 'Programmes for young people and families', 'url' => '#'),
                array('title' => 'Sangha', 'description' => 'Events for sangha members', 'url' => '#'),
            ),
            'featured' => array(
                'title'       => 'Highlights & Retreats',
                'description' => 'Special events and retreat opportunities',
                'image'       => rigpa_mega_menu_asset_url('featured-retreats.jpg'),
                'url'         => '#',
            ),
        ),
        rigpa_mega_menu_get_near_you_section('english'),
        array(
            'label' => 'Resources',
            'items' => array(
                array('title' => 'Prayer Requests', 'description' => 'Submit names for dedication and prayers', 'url' => '#'),
                array('title' => 'Accompanying Illness and Loss', 'description' => 'Support and guidance for life, loss, and dying', 'url' => '#'),
                array('title' => 'Teachings Online', 'description' => 'Online courses and study programmes', 'url' => '#'),
                array('title' => 'The Tibetan Book of Living and Dying', 'description' => 'Resources on the classic text by Sogyal Rinpoche', 'url' => '#'),
                array('title' => 'Rigpawiki', 'description' => 'Online encyclopedia of Tibetan Buddhism', 'url' => '#'),
                array('title' => 'Sukhavati Hospice', 'description' => 'End-of-life care and accompaniment', 'url' => '#'),
                array('title' => 'Tibetan Buddhist Calendar', 'description' => 'Important dates and practice days', 'url' => '#'),
                array('title' => 'Shedra Studies Nepal', 'description' => 'Study college in Nepal', 'url' => '#'),
                array('title' => 'Shop', 'description' => 'Books, practice materials, and gifts', 'url' => '#'),
            ),
        ),
        array(
            'label' => 'About Us',
            'items' => array(
                array('title' => 'About Rigpa', 'description' => 'Our history and mission', 'url' => '#'),
                array('title' => 'A Complete Buddhist Path', 'description' => 'The path of wisdom and compassion', 'url' => '#'),
                array('title' => 'Our Teachers and Lineage', 'description' => 'Meet our teachers and spiritual lineage', 'url' => '#'),
                array('title' => 'Rigpa as a Community', 'description' => 'Our vibrant practice community', 'url' => '#'),
                array('title' => 'For Children, Families & Young People', 'description' => 'Programmes for the next generation', 'url' => '#'),
                array('title' => 'Ethics and Diversity', 'description' => 'Our values and commitments', 'url' => '#'),
                array('title' => 'Team', 'description' => 'Staff and organisational structure', 'url' => '#'),
                array('title' => 'Jobs & Volunteering', 'description' => 'Opportunities to contribute', 'url' => '#'),
                array('title' => 'Become a Member', 'description' => 'Join our community', 'url' => '#'),
                array('title' => 'Contact & Press', 'description' => 'Get in touch with us', 'url' => '#'),
            ),
        ),
    );

    return $menus;
}

/**
 * @return array<int, array<string, mixed>>
 */
function rigpa_mega_menu_get_german_menus() {
    $menus = array(
        array(
            'label' => 'Neu hier?',
            'items' => array(
                array('title' => 'Jetzt Meditieren', 'description' => 'Beginnen Sie noch heute mit der Meditation', 'url' => '#'),
                array('title' => 'Mitgefühl entdecken', 'description' => 'Den Weg des Mitgefühls kennenlernen', 'url' => '#'),
                array('title' => 'Angebote für Einsteiger*innen', 'description' => 'Erste Schritte in Meditation und Buddhismus', 'url' => '#'),
                array('title' => 'Übersicht', 'description' => 'Alle Angebote auf einen Blick', 'url' => '#'),
                array('title' => 'Bodhi - Kurse', 'description' => 'Studienprogramme und Kurse von Bodhi', 'url' => '#'),
                array('title' => 'Was ist Buddhismus?', 'description' => 'Einführung in die buddhistische Lehre', 'url' => '#'),
                array('title' => 'Gruppe finden', 'description' => 'Verbinden Sie sich mit Praktizierenden in Ihrer Nähe', 'url' => '#'),
            ),
        ),
        array(
            'label' => 'Kurse & Termine',
            'items' => array(
                array('title' => 'Alle Kurse und Termine', 'description' => 'Alle Veranstaltungen auf einen Blick', 'url' => '#'),
                array('title' => 'Online Programm', 'description' => 'Digitale Kurse und Live-Übertragungen', 'url' => '#'),
                array('title' => 'Für Einsteiger*innen', 'description' => 'Veranstaltungen für Neulinge', 'url' => '#'),
                array('title' => 'Heilung, Krankheit & Verlust', 'description' => 'Unterstützung in schwierigen Lebensphasen', 'url' => '#'),
                array('title' => 'Heute', 'description' => 'Heutige Termine und Aktivitäten', 'url' => '#'),
                array('title' => 'Kinder & Familien', 'description' => 'Programme für junge Menschen und Familien', 'url' => '#'),
                array('title' => 'Sangha', 'description' => 'Veranstaltungen für Sangha-Mitglieder', 'url' => '#'),
            ),
            'featured' => array(
                'title'       => 'Highlights & Retreats',
                'description' => 'Besondere Veranstaltungen und Rückzugsorte',
                'image'       => rigpa_mega_menu_asset_url('featured-retreats.jpg'),
                'url'         => '#',
            ),
        ),
        rigpa_mega_menu_get_near_you_section('german'),
        array(
            'label' => 'Ressourcen',
            'items' => array(
                array('title' => 'Gebetswünsche', 'description' => 'Namen für Widmungen und Gebete einreichen', 'url' => '#'),
                array('title' => 'Begleitung bei Krankheit und Verlust', 'description' => 'Unterstützung und Orientierung zu Leben, Verlust und Sterben', 'url' => '#'),
                array('title' => 'Belehrungen Online', 'description' => 'Online-Kurse und Studienprogramme', 'url' => '#'),
                array('title' => 'Tibetisches Buch vom Leben und Sterben', 'description' => 'Ressourcen zum klassischen Text von Sogyal Rinpoche', 'url' => '#'),
                array('title' => 'Rigpawiki', 'description' => 'Online-Nachschlagewerk zum tibetischen Buddhismus', 'url' => '#'),
                array('title' => 'Hospiz Sukhavati', 'description' => 'Begleitung am Lebensende', 'url' => '#'),
                array('title' => 'Tibetisch-Buddhistischer Kalender', 'description' => 'Wichtige Termine und Übungstage im buddhistischen Kalender', 'url' => '#'),
                array('title' => 'Shedra Studium Nepal', 'description' => 'Studienkolleg in Nepal', 'url' => '#'),
                array('title' => 'Shop', 'description' => 'Bücher, Übungsmaterialien und mehr', 'url' => '#'),
            ),
        ),
        array(
            'label' => 'Über uns',
            'items' => array(
                array('title' => 'Über Rigpa', 'description' => 'Geschichte und Mission unserer Organisation', 'url' => '#'),
                array('title' => 'Ein vollständiger Buddhistischer Pfad', 'description' => 'Der Weg der Weisheit und des Mitgefühls', 'url' => '#'),
                array('title' => 'Linie und Lehrende', 'description' => 'Unsere Lehrer und spirituelle Abstammung', 'url' => '#'),
                array('title' => 'Rigpa als Gemeinschaft', 'description' => 'Unsere lebendige Praxisgemeinschaft', 'url' => '#'),
                array('title' => 'Für Kinder, Familien & Junge Menschen', 'description' => 'Programme für die nächste Generation', 'url' => '#'),
                array('title' => 'Ethik und Diversität', 'description' => 'Unsere Werte und Verpflichtungen', 'url' => '#'),
                array('title' => 'Team', 'description' => 'Mitarbeiter und Organisationsstruktur', 'url' => '#'),
                array('title' => 'Jobs & Ehrenamt', 'description' => 'Möglichkeiten zur Mitarbeit', 'url' => '#'),
                array('title' => 'Mitglied werden', 'description' => 'Teil unserer Gemeinschaft werden', 'url' => '#'),
                array('title' => 'Kontakt & Presse', 'description' => 'Nehmen Sie Kontakt mit uns auf', 'url' => '#'),
            ),
        ),
    );

    return $menus;
}
