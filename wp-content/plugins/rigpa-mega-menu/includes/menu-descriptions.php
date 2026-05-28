<?php
/**
 * Menu item description lookup entries for Add/Clear Descriptions.
 *
 * Matched approximately to nav menu item names. Used alongside includes/menus.php.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Flat title/description pairs used by Rigpa_Mega_Menu_Description_Sync.
 *
 * @param string $lang english|german
 * @return array<int, array{title: string, description: string}>
 */
function rigpa_mega_menu_get_description_entries($lang) {
    if ($lang === 'german') {
        return rigpa_mega_menu_get_german_description_entries();
    }

    return rigpa_mega_menu_get_english_description_entries();
}

/**
 * @return array<int, array{title: string, description: string}>
 */
function rigpa_mega_menu_get_english_description_entries() {
    return array(
        // Angebote / What We Offer
        array(
            'title'       => 'What is Buddhism?',
            'description' => 'An introduction to Buddhist teachings and the path of wisdom and compassion',
        ),
        array(
            'title'       => 'Learn to Meditate',
            'description' => 'Begin your meditation practice with guided instruction',
        ),
        array(
            'title'       => 'Places to do Retreat',
            'description' => 'Find retreat centres and places for contemplative practice',
        ),
        array(
            'title'       => 'Programme Calendar',
            'description' => 'Browse upcoming courses, events, and retreats',
        ),

        // In your area
        array(
            'title'       => 'Berlin Dharma Matti',
            'description' => 'Our Rigpa centre in Berlin',
        ),
        array(
            'title'       => 'Berlin Dharma Mali',
            'description' => 'Our Rigpa centre in Berlin',
        ),
        array(
            'title'       => 'Bremen',
            'description' => 'Meditation group in Bremen',
        ),
        array(
            'title'       => 'Dzogchen Beara',
            'description' => 'Retreat centre on the west coast of Ireland',
        ),
        array(
            'title'       => 'Find a Group',
            'description' => 'Connect with practitioners near you',
        ),

        // Community
        array(
            'title'       => 'Sangha Area',
            'description' => 'Resources and community for Rigpa sangha members',
        ),
        array(
            'title'       => 'Meditate',
            'description' => 'Start your meditation practice today',
        ),
        array(
            'title'       => 'Community',
            'description' => 'Join our sangha and practice together',
        ),
        array(
            'title'       => 'Sogyal Rinpoche',
            'description' => 'Teachings and legacy of our founder',
        ),

        // Resources
        array(
            'title'       => 'Sangha Blog',
            'description' => 'News, reflections, and stories from our community',
        ),
        array(
            'title'       => 'Facing Death and Dying',
            'description' => 'Support and guidance for life, loss, and dying',
        ),
        array(
            'title'       => 'Rigpawiki',
            'description' => 'Online encyclopedia of Tibetan Buddhism',
        ),
        array(
            'title'       => 'Prajna Online Learning',
            'description' => 'Online courses and study programmes',
        ),
        array(
            'title'       => 'Bodhi',
            'description' => 'Publications and media from Rigpa',
        ),
        array(
            'title'       => 'Prayer Requests',
            'description' => 'Submit names for dedication and prayers',
        ),
        array(
            'title'       => 'The Tibetan Book of Living and Dying',
            'description' => 'Resources on the classic text by Sogyal Rinpoche',
        ),
        array(
            'title'       => 'ZAM Rigpa Store',
            'description' => 'Books, practice materials, and gifts',
        ),
        array(
            'title'       => 'Tibetan Buddhist Calendar',
            'description' => 'Important dates and practice days in the Buddhist calendar',
        ),
    );
}

/**
 * @return array<int, array{title: string, description: string}>
 */
function rigpa_mega_menu_get_german_description_entries() {
    return array(
        // Angebote
        array(
            'title'       => 'Was ist Buddhismus?',
            'description' => 'Einführung in die buddhistische Lehre und den Weg der Weisheit und des Mitgefühls',
        ),
        array(
            'title'       => 'Meditieren lernen',
            'description' => 'Beginnen Sie Ihre Meditationspraxis mit angeleiteter Anleitung',
        ),
        array(
            'title'       => 'Orte für Retreats',
            'description' => 'Finden Sie Retreat-Zentren und Orte für kontemplative Praxis',
        ),
        array(
            'title'       => 'Programm Kalender',
            'description' => 'Entdecken Sie kommende Kurse, Veranstaltungen und Retreats',
        ),
        array(
            'title'       => 'Programm Kalender - Übersicht',
            'description' => 'Entdecken Sie kommende Kurse, Veranstaltungen und Retreats',
        ),

        // In deiner Nähe
        array(
            'title'       => 'Berlin Dharma Matti',
            'description' => 'Unser Rigpa-Zentrum in Berlin',
        ),
        array(
            'title'       => 'Berlin Dharma Mali',
            'description' => 'Unser Rigpa-Zentrum in Berlin',
        ),
        array(
            'title'       => 'Bremen',
            'description' => 'Meditationsgruppe in Bremen',
        ),
        array(
            'title'       => 'Dzogchen Beara',
            'description' => 'Retreat-Zentrum an der irischen Westküste',
        ),
        array(
            'title'       => 'Gruppe finden',
            'description' => 'Verbinden Sie sich mit Praktizierenden in Ihrer Nähe',
        ),

        // Community
        array(
            'title'       => 'Sangha-Bereich',
            'description' => 'Ressourcen und Gemeinschaft für Rigpa-Sangha-Mitglieder',
        ),
        array(
            'title'       => 'Sangha Area',
            'description' => 'Ressourcen und Gemeinschaft für Rigpa-Sangha-Mitglieder',
        ),
        array(
            'title'       => 'Meditieren',
            'description' => 'Beginnen Sie noch heute mit der Meditation',
        ),
        array(
            'title'       => 'Community',
            'description' => 'Werden Sie Teil unserer Sangha und üben Sie gemeinsam',
        ),
        array(
            'title'       => 'Sogyal Rinpoche',
            'description' => 'Lehren und Vermächtnis unseres Gründers',
        ),

        // Ressourcen
        array(
            'title'       => 'Sangha Blog',
            'description' => 'Neuigkeiten, Reflexionen und Geschichten aus der Gemeinschaft',
        ),
        array(
            'title'       => 'Begleitung bei Sterben und Tod',
            'description' => 'Unterstützung und Orientierung zu Leben, Verlust und Sterben',
        ),
        array(
            'title'       => 'Facing Death and Dying',
            'description' => 'Unterstützung und Orientierung zu Leben, Verlust und Sterben',
        ),
        array(
            'title'       => 'Rigpa Wiki',
            'description' => 'Online-Nachschlagewerk zum tibetischen Buddhismus',
        ),
        array(
            'title'       => 'Rigpawiki',
            'description' => 'Online-Nachschlagewerk zum tibetischen Buddhismus',
        ),
        array(
            'title'       => 'Prajna Online-Lernen',
            'description' => 'Online-Kurse und Studienprogramme',
        ),
        array(
            'title'       => 'Prajna Online Learning',
            'description' => 'Online-Kurse und Studienprogramme',
        ),
        array(
            'title'       => 'Bodhi',
            'description' => 'Publikationen und Medien von Rigpa',
        ),
        array(
            'title'       => 'Gebetswünsche',
            'description' => 'Namen für Widmungen und Gebete einreichen',
        ),
        array(
            'title'       => 'Prayer Requests',
            'description' => 'Namen für Widmungen und Gebete einreichen',
        ),
        array(
            'title'       => 'Tibetisches Buch vom Leben und Sterben',
            'description' => 'Ressourcen zum klassischen Text von Sogyal Rinpoche',
        ),
        array(
            'title'       => 'The Tibetan Book of Living and Dying',
            'description' => 'Ressourcen zum klassischen Text von Sogyal Rinpoche',
        ),
        array(
            'title'       => 'ZAM Rigpa Shop',
            'description' => 'Bücher, Übungsmaterialien und Geschenke',
        ),
        array(
            'title'       => 'ZAM Rigpa Store',
            'description' => 'Bücher, Übungsmaterialien und Geschenke',
        ),
        array(
            'title'       => 'Tibetisch-buddhistischer Kalender',
            'description' => 'Wichtige Termine und Übungstage im buddhistischen Kalender',
        ),
        array(
            'title'       => 'Tibetan Buddhist Calendar',
            'description' => 'Wichtige Termine und Übungstage im buddhistischen Kalender',
        ),
    );
}
