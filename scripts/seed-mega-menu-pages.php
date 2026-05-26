<?php
/**
 * Seed example pages for Rigpa Mega Menu demo content.
 *
 * Usage (from project root):
 *   make wp ARGS="eval-file /var/www/html/../scripts/seed-mega-menu-pages.php"
 *
 * Or via docker compose from host:
 *   docker compose --profile tools run --rm -v "$(pwd)/scripts:/scripts" wp-cli \
 *     wp --allow-root eval-file /scripts/seed-mega-menu-pages.php
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "Run via WP-CLI inside WordPress.\n");
    exit(1);
}

/**
 * @param array<int, array{title: string, slug: string, description: string}> $pages
 * @return array<string, string> slug => path
 */
function rigpa_seed_pages(array $pages) {
    $paths = array();

    foreach ($pages as $page) {
        $slug = sanitize_title($page['slug']);
        $existing = get_page_by_path($slug, OBJECT, 'page');

        $content = sprintf(
            '<!-- wp:paragraph --><p>%s</p><!-- /wp:paragraph -->' .
            '<!-- wp:paragraph --><p><em>Example page for the Rigpa Mega Menu demo.</em></p><!-- /wp:paragraph -->',
            esc_html($page['description'])
        );

        if ($existing instanceof WP_Post) {
            $paths[$slug] = '/' . trim(get_page_uri($existing), '/') . '/';
            WP_CLI::log("Exists: {$page['title']} → {$paths[$slug]}");
            continue;
        }

        $post_id = wp_insert_post(
            array(
                'post_title'   => $page['title'],
                'post_name'    => $slug,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ),
            true
        );

        if (is_wp_error($post_id)) {
            WP_CLI::warning("Failed {$page['title']}: " . $post_id->get_error_message());
            continue;
        }

        $paths[$slug] = '/' . trim(get_page_uri($post_id), '/') . '/';
        WP_CLI::success("Created: {$page['title']} → {$paths[$slug]}");
    }

    return $paths;
}

$english_pages = array(
    array('title' => 'Introduction to Meditation', 'slug' => 'introduction-to-meditation', 'description' => 'Learn the fundamentals of meditation practice'),
    array('title' => 'Daily Practice', 'slug' => 'daily-practice', 'description' => 'Join our daily meditation sessions'),
    array('title' => 'Guided Sessions', 'slug' => 'guided-sessions', 'description' => 'Audio and video guided meditations'),
    array('title' => 'Meditation Times', 'slug' => 'meditation-times', 'description' => 'View our weekly schedule'),
    array('title' => 'Online Meditation', 'slug' => 'online-meditation', 'description' => 'Practice with us from anywhere'),
    array('title' => 'One-on-One Guidance', 'slug' => 'one-on-one-guidance', 'description' => 'Personal instruction with experienced teachers'),
    array('title' => 'New to Meditation?', 'slug' => 'new-to-meditation', 'description' => 'Start your journey with our beginner-friendly introduction course'),
    array('title' => 'Courses & Programs', 'slug' => 'courses-programs', 'description' => 'Multi-week programs and study courses'),
    array('title' => 'Retreats', 'slug' => 'retreats', 'description' => 'Residential and day retreats throughout the year'),
    array('title' => 'Workshops', 'slug' => 'workshops', 'description' => 'Single-day and weekend intensive workshops'),
    array('title' => 'Teacher Training', 'slug' => 'teacher-training', 'description' => 'Comprehensive programs for aspiring teachers'),
    array('title' => 'Online Offerings', 'slug' => 'online-offerings', 'description' => 'Live-streamed and recorded teachings'),
    array('title' => 'Private Instruction', 'slug' => 'private-instruction', 'description' => 'Personalized guidance and mentorship'),
    array('title' => 'Upcoming Retreats', 'slug' => 'upcoming-retreats', 'description' => 'Explore our spring and summer retreat schedule'),
    array('title' => 'Our Sangha', 'slug' => 'our-sangha', 'description' => 'Meet our vibrant practice community'),
    array('title' => 'Join Us', 'slug' => 'join-us', 'description' => 'Become a member of our community'),
    array('title' => 'Events Calendar', 'slug' => 'events-calendar', 'description' => 'View all upcoming events and activities'),
    array('title' => 'Volunteer', 'slug' => 'volunteer', 'description' => 'Support our community through service'),
    array('title' => 'Connect', 'slug' => 'connect', 'description' => 'Practice groups and study circles'),
    array('title' => 'Newsletter', 'slug' => 'newsletter', 'description' => 'Stay informed with monthly updates'),
    array('title' => 'Teachings Library', 'slug' => 'teachings-library', 'description' => 'Archive of dharma talks and teachings'),
    array('title' => 'Articles & Essays', 'slug' => 'articles-essays', 'description' => 'Written teachings and contemplations'),
    array('title' => 'Audio & Video', 'slug' => 'audio-video', 'description' => 'Recorded talks and guided practices'),
    array('title' => 'Recommended Books', 'slug' => 'recommended-books', 'description' => 'Curated reading lists for study'),
    array('title' => 'Practice Guides', 'slug' => 'practice-guides', 'description' => 'Downloadable meditation instructions'),
    array('title' => 'FAQs', 'slug' => 'faqs', 'description' => 'Common questions about practice and programs'),
    array('title' => 'Our History', 'slug' => 'our-history', 'description' => 'The story of our center and lineage'),
    array('title' => 'Teachers', 'slug' => 'teachers', 'description' => 'Meet our resident and visiting teachers'),
    array('title' => 'Philosophy', 'slug' => 'philosophy', 'description' => 'Our approach to Buddhist practice'),
    array('title' => 'Location & Facilities', 'slug' => 'location-facilities', 'description' => 'Visit our meditation center'),
    array('title' => 'Contact', 'slug' => 'contact', 'description' => 'Get in touch with our team'),
    array('title' => 'Careers', 'slug' => 'careers', 'description' => 'Join our staff and teaching team'),
    array('title' => 'Discover Buddhism', 'slug' => 'discover-buddhism', 'description' => 'Introduction to Buddhist philosophy and practice'),
    array('title' => 'Meditate Now', 'slug' => 'meditate-now', 'description' => 'Start your meditation practice today'),
    array('title' => 'Spiritual Path in Rigpa', 'slug' => 'spiritual-path-in-rigpa', 'description' => 'Learn about our approach to the path'),
    array('title' => 'Find a Group', 'slug' => 'find-a-group', 'description' => 'Connect with practitioners near you'),
    array('title' => 'Personal Guidance', 'slug' => 'personal-guidance', 'description' => 'One-on-one support for your journey'),
    array('title' => 'Get Involved', 'slug' => 'get-involved', 'description' => 'Join our community and volunteer'),
    array('title' => 'Subscribe to Newsletter', 'slug' => 'subscribe-newsletter', 'description' => 'Stay updated with monthly insights'),
    array('title' => 'FAQ', 'slug' => 'faq', 'description' => 'Common questions about practice and programs'),
    array('title' => 'Welcome to Rigpa', 'slug' => 'welcome-to-rigpa', 'description' => 'Begin your journey with meditation and discover the wisdom of Tibetan Buddhism'),
);

$german_pages = array(
    array('title' => 'Meditieren Lernen', 'slug' => 'de-meditieren-lernen', 'description' => 'Grundlagen der Meditationspraxis erlernen'),
    array('title' => 'Buddhismus entdecken', 'slug' => 'de-buddhismus-entdecken', 'description' => 'Die buddhistische Lehre kennenlernen'),
    array('title' => 'Retreats & Highlights', 'slug' => 'de-retreats-highlights', 'description' => 'Besondere Veranstaltungen und Rückzugsorte'),
    array('title' => 'Online Programm', 'slug' => 'de-online-programm', 'description' => 'Digitale Kurse und Live-Übertragungen'),
    array('title' => 'Heilung, Krankheit, Verlust & Sterben', 'slug' => 'de-heilung-krankheit-verlust-sterben', 'description' => 'Unterstützung in schwierigen Lebensphasen'),
    array('title' => 'Familien & Jugendliche', 'slug' => 'de-familien-jugendliche', 'description' => 'Programme für junge Menschen und Familien'),
    array('title' => 'Frühjahrsretreat 2026', 'slug' => 'de-fruehjahrsretreat-2026', 'description' => 'Entdecken Sie unser besonderes Retreat-Programm für Frühling und Sommer'),
    array('title' => 'Programm Kalender - Übersicht', 'slug' => 'de-programm-kalender', 'description' => 'Alle Veranstaltungen auf einen Blick'),
    array('title' => 'Heute', 'slug' => 'de-heute', 'description' => 'Heutige Termine und Aktivitäten'),
    array('title' => 'International', 'slug' => 'de-international', 'description' => 'Globale Veranstaltungen und Zentren'),
    array('title' => 'Newsletter abonnieren', 'slug' => 'de-newsletter-abonnieren', 'description' => 'Bleiben Sie informiert mit monatlichen Updates'),
    array('title' => 'Berlin Dharma Mali', 'slug' => 'de-berlin-dharma-mali', 'description' => 'Unser Zentrum in Berlin'),
    array('title' => 'Bremen', 'slug' => 'de-bremen', 'description' => 'Meditationsgruppe in Bremen'),
    array('title' => 'Hamburg', 'slug' => 'de-hamburg', 'description' => 'Praxisgruppe in Hamburg'),
    array('title' => 'München', 'slug' => 'de-muenchen', 'description' => 'Zentrum in München'),
    array('title' => 'Köln', 'slug' => 'de-koeln', 'description' => 'Gemeinschaft in Köln'),
    array('title' => 'Online', 'slug' => 'de-online', 'description' => 'Virtuelle Sangha weltweit'),
    array('title' => 'Besuchen Sie uns', 'slug' => 'de-besuchen-sie-uns', 'description' => 'Finden Sie ein Zentrum in Ihrer Nähe und werden Sie Teil unserer Gemeinschaft'),
    array('title' => 'Jetzt meditieren', 'slug' => 'de-jetzt-meditieren', 'description' => 'Sofort mit geführten Meditationen beginnen'),
    array('title' => 'Blog', 'slug' => 'de-blog', 'description' => 'Artikel und Lehren'),
    array('title' => 'Gebetswünsche', 'slug' => 'de-gebetswuensche', 'description' => 'Teilen Sie Ihre Anliegen'),
    array('title' => 'Begleitung bei Tod und Verlust', 'slug' => 'de-begleitung-tod-verlust', 'description' => 'Unterstützung in schwierigen Zeiten'),
    array('title' => 'Persönliche Beratung', 'slug' => 'de-persoenliche-beratung', 'description' => 'Individuelle spirituelle Führung'),
    array('title' => 'Belehrungen online', 'slug' => 'de-belehrungen-online', 'description' => 'Archiv von Dharma-Vorträgen'),
    array('title' => 'Tibetisches Buch vom Leben und Sterben', 'slug' => 'de-tibetisches-buch-leben-sterben', 'description' => 'Studienressourcen zum klassischen Text'),
    array('title' => 'Rigpa Wiki', 'slug' => 'de-rigpa-wiki', 'description' => 'Umfassendes Wissensarchiv'),
    array('title' => 'Shop', 'slug' => 'de-shop', 'description' => 'Bücher, Meditationshilfen und mehr'),
    array('title' => 'Über Rigpa', 'slug' => 'de-ueber-rigpa', 'description' => 'Geschichte und Mission unserer Organisation'),
    array('title' => 'Spiritueller Pfad in Rigpa', 'slug' => 'de-spiritueller-pfad', 'description' => 'Unser Ansatz zur buddhistischen Praxis'),
    array('title' => 'Linie & Lehrende', 'slug' => 'de-linie-lehrende', 'description' => 'Unsere Lehrer und spirituelle Abstammung'),
    array('title' => 'Sangha & Community', 'slug' => 'de-sangha-community', 'description' => 'Unsere lebendige Praxisgemeinschaft'),
    array('title' => 'Kinder, Familien, Junge Menschen', 'slug' => 'de-kinder-familien', 'description' => 'Programme für die nächste Generation'),
    array('title' => 'Ethik & Diversität', 'slug' => 'de-ethik-diversitaet', 'description' => 'Unsere Werte und Verpflichtungen'),
    array('title' => 'Team', 'slug' => 'de-team', 'description' => 'Mitarbeiter und Organisationsstruktur'),
    array('title' => 'Jobs & Ehrenamt', 'slug' => 'de-jobs-ehrenamt', 'description' => 'Möglichkeiten zur Mitarbeit'),
    array('title' => 'Mitglied werden', 'slug' => 'de-mitglied-werden', 'description' => 'Teil unserer Gemeinschaft werden'),
    array('title' => 'Internationale Zentren', 'slug' => 'de-internationale-zentren', 'description' => 'Rigpa-Zentren weltweit'),
    array('title' => 'Kontakt & Presse', 'slug' => 'de-kontakt-presse', 'description' => 'Nehmen Sie Kontakt mit uns auf'),
    array('title' => 'Gruppe finden', 'slug' => 'de-gruppe-finden', 'description' => 'Verbinden Sie sich mit Praktizierenden in Ihrer Nähe'),
    array('title' => 'Mitmachen', 'slug' => 'de-mitmachen', 'description' => 'Werden Sie Teil unserer Gemeinschaft'),
    array('title' => 'Willkommen bei Rigpa', 'slug' => 'de-willkommen-bei-rigpa', 'description' => 'Beginnen Sie Ihre Reise mit Meditation und entdecken Sie die Weisheit des tibetischen Buddhismus'),
);

WP_CLI::log('Seeding English mega menu pages…');
rigpa_seed_pages($english_pages);

WP_CLI::log('Seeding German mega menu pages…');
rigpa_seed_pages($german_pages);

$demo_slug = 'mega-menu-demo';
$demo_existing = get_page_by_path($demo_slug, OBJECT, 'page');

$demo_content = '<!-- wp:shortcode -->[rigpa_mega_menu]<!-- /wp:shortcode -->' .
    '<!-- wp:paragraph --><p>Use the navigation above to browse example content pages created for this demo.</p><!-- /wp:paragraph -->';

if ($demo_existing instanceof WP_Post) {
    WP_CLI::log('Mega Menu Demo page already exists.');
    $demo_id = $demo_existing->ID;
} else {
    $demo_id = wp_insert_post(
        array(
            'post_title'   => 'Mega Menu Demo',
            'post_name'    => $demo_slug,
            'post_content' => $demo_content,
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ),
        true
    );
    if (is_wp_error($demo_id)) {
        WP_CLI::warning('Failed to create demo page: ' . $demo_id->get_error_message());
    } else {
        WP_CLI::success('Created Mega Menu Demo → /' . trim(get_page_uri($demo_id), '/') . '/');
    }
}

WP_CLI::success('Done. Visit /mega-menu-demo/ to test the menu.');
