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
 * Build the "Groups" section: all German locations + two featured centre cards.
 *
 * Location list mirrors the Rigpa.de map data (rigpa-de-map plugin) but is kept
 * here so the mega menu has no hard dependency on the map plugin.
 *
 * @param string $lang english|german
 * @return array<string, mixed>
 */
function rigpa_mega_menu_get_groups_section($lang) {
    $location_names = array(
        'Aachen',
        'Bad Saarow',
        'Dharma Mati Berlin',
        'Bielefeld',
        'Bremen',
        'Düsseldorf',
        'Frankfurt',
        'Freiburg',
        'Hannover',
        'Fürth',
        'Hamburg',
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
        return array(
            'label'           => 'Gruppen',
            'items'           => $items,
            'featuredCentres' => array(
                array(
                    'title'       => 'Dzogchen Beara',
                    'description' => 'Retreatzentrum · Irland',
                    'image'       => rigpa_mega_menu_asset_url('dzogchen-beara.jpg'),
                    'url'         => 'https://www.dzogchenbeara.org/',
                ),
                array(
                    'title'       => 'Lerab Ling',
                    'description' => 'Retreatzentrum · Frankreich',
                    'image'       => rigpa_mega_menu_asset_url('lerab-ling.jpg'),
                    'url'         => 'https://www.lerabling.org/',
                ),
            ),
        );
    }

    return array(
        'label'           => 'Groups',
        'items'           => $items,
        'featuredCentres' => array(
            array(
                'title'       => 'Dzogchen Beara',
                'description' => 'Retreat centre · Ireland',
                'image'       => rigpa_mega_menu_asset_url('dzogchen-beara.jpg'),
                'url'         => 'https://www.dzogchenbeara.org/',
            ),
            array(
                'title'       => 'Lerab Ling',
                'description' => 'Retreat centre · France',
                'image'       => rigpa_mega_menu_asset_url('lerab-ling.jpg'),
                'url'         => 'https://www.lerabling.org/',
            ),
        ),
    );
}

/**
 * @return array<int, array<string, mixed>>
 */
function rigpa_mega_menu_get_english_menus() {
    $menus = array(
        array(
            'label' => 'Meditate',
            'items' => array(
                array('title' => 'Introduction to Meditation', 'description' => 'Learn the fundamentals of meditation practice', 'url' => '/introduction-to-meditation/'),
                array('title' => 'Daily Practice', 'description' => 'Join our daily meditation sessions', 'url' => '/daily-practice/'),
                array('title' => 'Guided Sessions', 'description' => 'Audio and video guided meditations', 'url' => '/guided-sessions/'),
                array('title' => 'Meditation Times', 'description' => 'View our weekly schedule', 'url' => '/meditation-times/'),
                array('title' => 'Online Meditation', 'description' => 'Practice with us from anywhere', 'url' => '/online-meditation/'),
                array('title' => 'One-on-One Guidance', 'description' => 'Personal instruction with experienced teachers', 'url' => '/one-on-one-guidance/'),
            ),
            'featured' => array(
                'title'       => 'New to Meditation?',
                'description' => 'Start your journey with our beginner-friendly introduction course',
                'image'       => rigpa_mega_menu_asset_url('featured-meditate.jpg'),
                'url'         => '/new-to-meditation/',
            ),
        ),
        array(
            'label' => 'What We Offer',
            'items' => array(
                array('title' => 'Courses & Programs', 'description' => 'Multi-week programs and study courses', 'url' => '/courses-programs/'),
                array('title' => 'Retreats', 'description' => 'Residential and day retreats throughout the year', 'url' => '/retreats/'),
                array('title' => 'Workshops', 'description' => 'Single-day and weekend intensive workshops', 'url' => '/workshops/'),
                array('title' => 'Teacher Training', 'description' => 'Comprehensive programs for aspiring teachers', 'url' => '/teacher-training/'),
                array('title' => 'Online Offerings', 'description' => 'Live-streamed and recorded teachings', 'url' => '/online-offerings/'),
                array('title' => 'Private Instruction', 'description' => 'Personalized guidance and mentorship', 'url' => '/private-instruction/'),
            ),
            'featured' => array(
                'title'       => 'Upcoming Retreats',
                'description' => 'Explore our spring and summer retreat schedule',
                'image'       => rigpa_mega_menu_asset_url('featured-retreats.jpg'),
                'url'         => '/upcoming-retreats/',
            ),
        ),
        array(
            'label' => 'Community',
            'items' => array(
                array('title' => 'Our Sangha', 'description' => 'Meet our vibrant practice community', 'url' => '/our-sangha/'),
                array('title' => 'Join Us', 'description' => 'Become a member of our community', 'url' => '/join-us/'),
                array('title' => 'Events Calendar', 'description' => 'View all upcoming events and activities', 'url' => '/events-calendar/'),
                array('title' => 'Volunteer', 'description' => 'Support our community through service', 'url' => '/volunteer/'),
                array('title' => 'Connect', 'description' => 'Practice groups and study circles', 'url' => '/connect/'),
                array('title' => 'Newsletter', 'description' => 'Stay informed with monthly updates', 'url' => '/newsletter/'),
            ),
        ),
        array(
            'label' => 'Resources',
            'items' => array(
                array('title' => 'Teachings Library', 'description' => 'Archive of dharma talks and teachings', 'url' => '/teachings-library/'),
                array('title' => 'Articles & Essays', 'description' => 'Written teachings and contemplations', 'url' => '/articles-essays/'),
                array('title' => 'Audio & Video', 'description' => 'Recorded talks and guided practices', 'url' => '/audio-video/'),
                array('title' => 'Recommended Books', 'description' => 'Curated reading lists for study', 'url' => '/recommended-books/'),
                array('title' => 'Practice Guides', 'description' => 'Downloadable meditation instructions', 'url' => '/practice-guides/'),
                array('title' => 'FAQs', 'description' => 'Common questions about practice and programs', 'url' => '/faqs/'),
            ),
        ),
        array(
            'label' => 'About',
            'items' => array(
                array('title' => 'Our History', 'description' => 'The story of our center and lineage', 'url' => '/our-history/'),
                array('title' => 'Teachers', 'description' => 'Meet our resident and visiting teachers', 'url' => '/teachers/'),
                array('title' => 'Philosophy', 'description' => 'Our approach to Buddhist practice', 'url' => '/philosophy/'),
                array('title' => 'Location & Facilities', 'description' => 'Visit our meditation center', 'url' => '/location-facilities/'),
                array('title' => 'Contact', 'description' => 'Get in touch with our team', 'url' => '/contact/'),
                array('title' => 'Careers', 'description' => 'Join our staff and teaching team', 'url' => '/careers/'),
            ),
        ),
        array(
            'label' => 'New Here?',
            'items' => array(
                array('title' => 'Discover Buddhism', 'description' => 'Introduction to Buddhist philosophy and practice', 'url' => '/discover-buddhism/'),
                array('title' => 'Meditate Now', 'description' => 'Start your meditation practice today', 'url' => '/meditate-now/'),
                array('title' => 'Spiritual Path in Rigpa', 'description' => 'Learn about our approach to the path', 'url' => '/spiritual-path-in-rigpa/'),
                array('title' => 'Find a Group', 'description' => 'Connect with practitioners near you', 'url' => '/find-a-group/'),
                array('title' => 'Personal Guidance', 'description' => 'One-on-one support for your journey', 'url' => '/personal-guidance/'),
                array('title' => 'Get Involved', 'description' => 'Join our community and volunteer', 'url' => '/get-involved/'),
                array('title' => 'Subscribe to Newsletter', 'description' => 'Stay updated with monthly insights', 'url' => '/subscribe-newsletter/'),
                array('title' => 'FAQ', 'description' => 'Common questions about practice and programs', 'url' => '/faq/'),
            ),
            'featured' => array(
                'title'       => 'Welcome to Rigpa',
                'description' => 'Begin your journey with meditation and discover the wisdom of Tibetan Buddhism',
                'image'       => rigpa_mega_menu_asset_url('featured-welcome.jpg'),
                'url'         => '/welcome-to-rigpa/',
            ),
        ),
    );

    $menus[] = rigpa_mega_menu_get_groups_section('english');

    return $menus;
}

/**
 * @return array<int, array<string, mixed>>
 */
function rigpa_mega_menu_get_german_menus() {
    $menus = array(
        array(
            'label' => 'Angebote',
            'items' => array(
                array('title' => 'Meditieren Lernen', 'description' => 'Grundlagen der Meditationspraxis erlernen', 'url' => '/de-meditieren-lernen/'),
                array('title' => 'Buddhismus entdecken', 'description' => 'Die buddhistische Lehre kennenlernen', 'url' => '/de-buddhismus-entdecken/'),
                array('title' => 'Retreats & Highlights', 'description' => 'Besondere Veranstaltungen und Rückzugsorte', 'url' => '/de-retreats-highlights/'),
                array('title' => 'Online Programm', 'description' => 'Digitale Kurse und Live-Übertragungen', 'url' => '/de-online-programm/'),
                array('title' => 'Heilung, Krankheit, Verlust & Sterben', 'description' => 'Unterstützung in schwierigen Lebensphasen', 'url' => '/de-heilung-krankheit-verlust-sterben/'),
                array('title' => 'Familien & Jugendliche', 'description' => 'Programme für junge Menschen und Familien', 'url' => '/de-familien-jugendliche/'),
            ),
            'featured' => array(
                'title'       => 'Frühjahrsretreat 2026',
                'description' => 'Entdecken Sie unser besonderes Retreat-Programm für Frühling und Sommer',
                'image'       => rigpa_mega_menu_asset_url('featured-retreats.jpg'),
                'url'         => '/de-fruehjahrsretreat-2026/',
            ),
        ),
        array(
            'label' => 'Schnellzugriff',
            'items' => array(
                array('title' => 'Programm Kalender - Übersicht', 'description' => 'Alle Veranstaltungen auf einen Blick', 'url' => '/de-programm-kalender/'),
                array('title' => 'Heute', 'description' => 'Heutige Termine und Aktivitäten', 'url' => '/de-heute/'),
                array('title' => 'International', 'description' => 'Globale Veranstaltungen und Zentren', 'url' => '/de-international/'),
                array('title' => 'Newsletter abonnieren', 'description' => 'Bleiben Sie informiert mit monatlichen Updates', 'url' => '/de-newsletter-abonnieren/'),
            ),
        ),
        array(
            'label' => 'In Deiner Nähe',
            'items' => array(
                array('title' => 'Berlin Dharma Mali', 'description' => 'Unser Zentrum in Berlin', 'url' => '/de-berlin-dharma-mali/'),
                array('title' => 'Bremen', 'description' => 'Meditationsgruppe in Bremen', 'url' => '/de-bremen/'),
                array('title' => 'Hamburg', 'description' => 'Praxisgruppe in Hamburg', 'url' => '/de-hamburg/'),
                array('title' => 'München', 'description' => 'Zentrum in München', 'url' => '/de-muenchen/'),
                array('title' => 'Köln', 'description' => 'Gemeinschaft in Köln', 'url' => '/de-koeln/'),
                array('title' => 'Online', 'description' => 'Virtuelle Sangha weltweit', 'url' => '/de-online/'),
            ),
            'featured' => array(
                'title'       => 'Besuchen Sie uns',
                'description' => 'Finden Sie ein Zentrum in Ihrer Nähe und werden Sie Teil unserer Gemeinschaft',
                'image'       => rigpa_mega_menu_asset_url('featured-visit.jpg'),
                'url'         => '/de-besuchen-sie-uns/',
            ),
        ),
        array(
            'label' => 'Ressourcen',
            'items' => array(
                array('title' => 'Jetzt meditieren', 'description' => 'Sofort mit geführten Meditationen beginnen', 'url' => '/de-jetzt-meditieren/'),
                array('title' => 'Blog', 'description' => 'Artikel und Lehren', 'url' => '/de-blog/'),
                array('title' => 'Gebetswünsche', 'description' => 'Teilen Sie Ihre Anliegen', 'url' => '/de-gebetswuensche/'),
                array('title' => 'Begleitung bei Tod und Verlust', 'description' => 'Unterstützung in schwierigen Zeiten', 'url' => '/de-begleitung-tod-verlust/'),
                array('title' => 'Persönliche Beratung', 'description' => 'Individuelle spirituelle Führung', 'url' => '/de-persoenliche-beratung/'),
                array('title' => 'Belehrungen online', 'description' => 'Archiv von Dharma-Vorträgen', 'url' => '/de-belehrungen-online/'),
                array('title' => 'Tibetisches Buch vom Leben und Sterben', 'description' => 'Studienressourcen zum klassischen Text', 'url' => '/de-tibetisches-buch-leben-sterben/'),
                array('title' => 'Rigpa Wiki', 'description' => 'Umfassendes Wissensarchiv', 'url' => '/de-rigpa-wiki/'),
                array('title' => 'Shop', 'description' => 'Bücher, Meditationshilfen und mehr', 'url' => '/de-shop/'),
            ),
        ),
        array(
            'label' => 'Wer wir sind',
            'items' => array(
                array('title' => 'Über Rigpa', 'description' => 'Geschichte und Mission unserer Organisation', 'url' => '/de-ueber-rigpa/'),
                array('title' => 'Spiritueller Pfad in Rigpa', 'description' => 'Unser Ansatz zur buddhistischen Praxis', 'url' => '/de-spiritueller-pfad/'),
                array('title' => 'Linie & Lehrende', 'description' => 'Unsere Lehrer und spirituelle Abstammung', 'url' => '/de-linie-lehrende/'),
                array('title' => 'Sangha & Community', 'description' => 'Unsere lebendige Praxisgemeinschaft', 'url' => '/de-sangha-community/'),
                array('title' => 'Kinder, Familien, Junge Menschen', 'description' => 'Programme für die nächste Generation', 'url' => '/de-kinder-familien/'),
                array('title' => 'Ethik & Diversität', 'description' => 'Unsere Werte und Verpflichtungen', 'url' => '/de-ethik-diversitaet/'),
                array('title' => 'Team', 'description' => 'Mitarbeiter und Organisationsstruktur', 'url' => '/de-team/'),
                array('title' => 'Jobs & Ehrenamt', 'description' => 'Möglichkeiten zur Mitarbeit', 'url' => '/de-jobs-ehrenamt/'),
                array('title' => 'Mitglied werden', 'description' => 'Teil unserer Gemeinschaft werden', 'url' => '/de-mitglied-werden/'),
                array('title' => 'Internationale Zentren', 'description' => 'Rigpa-Zentren weltweit', 'url' => '/de-internationale-zentren/'),
                array('title' => 'Kontakt & Presse', 'description' => 'Nehmen Sie Kontakt mit uns auf', 'url' => '/de-kontakt-presse/'),
            ),
        ),
        array(
            'label' => 'Neu hier?',
            'items' => array(
                array('title' => 'Buddhismus entdecken', 'description' => 'Einführung in buddhistische Philosophie und Praxis', 'url' => '/de-buddhismus-entdecken/'),
                array('title' => 'Jetzt meditieren', 'description' => 'Beginnen Sie noch heute mit der Meditation', 'url' => '/de-jetzt-meditieren/'),
                array('title' => 'Spiritueller Pfad in Rigpa', 'description' => 'Erfahren Sie mehr über unseren Ansatz zum Pfad', 'url' => '/de-spiritueller-pfad/'),
                array('title' => 'Gruppe finden', 'description' => 'Verbinden Sie sich mit Praktizierenden in Ihrer Nähe', 'url' => '/de-gruppe-finden/'),
                array('title' => 'Persönliche Beratung', 'description' => 'Individuelle Unterstützung für Ihre Reise', 'url' => '/de-persoenliche-beratung/'),
                array('title' => 'Mitmachen', 'description' => 'Werden Sie Teil unserer Gemeinschaft', 'url' => '/de-mitmachen/'),
                array('title' => 'Newsletter abonnieren', 'description' => 'Bleiben Sie mit monatlichen Updates informiert', 'url' => '/de-newsletter-abonnieren/'),
                array('title' => 'FAQ', 'description' => 'Häufig gestellte Fragen zu Praxis und Programmen', 'url' => '/faq/'),
            ),
            'featured' => array(
                'title'       => 'Willkommen bei Rigpa',
                'description' => 'Beginnen Sie Ihre Reise mit Meditation und entdecken Sie die Weisheit des tibetischen Buddhismus',
                'image'       => rigpa_mega_menu_asset_url('featured-welcome.jpg'),
                'url'         => '/de-willkommen-bei-rigpa/',
            ),
        ),
    );

    $menus[] = rigpa_mega_menu_get_groups_section('german');

    return $menus;
}
