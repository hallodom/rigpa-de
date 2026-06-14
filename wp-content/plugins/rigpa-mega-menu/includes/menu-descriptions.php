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

        // Neu hier
        array(
            'title'       => 'Compassion',
            'description' => 'Discover the transformative power of compassion in Tibetan Buddhism',
        ),
        array(
            'title'       => 'Where to begin',
            'description' => 'Your first steps into meditation and Buddhist teachings',
        ),

        // Termine & Angebot
        array(
            'title'       => 'All Courses & Events',
            'description' => 'Browse the full programme of courses, retreats and events',
        ),
        array(
            'title'       => 'Online Programme',
            'description' => 'Live-streamed and recorded teachings from anywhere in the world',
        ),
        array(
            'title'       => 'Kids and Families',
            'description' => 'Meditation and dharma programmes for children and families',
        ),

        // Ressourcen
        array(
            'title'       => 'Gebetswünsche',
            'description' => 'Submit names for dedication and prayers',
        ),
        array(
            'title'       => 'Hospiz Sukhavati',
            'description' => 'Rigpa\'s palliative care and end-of-life support service',
        ),
        array(
            'title'       => 'Shedra Study College',
            'description' => 'In-depth study of Tibetan Buddhist texts and philosophy',
        ),

        // Über Uns
        array(
            'title'       => 'About Rigpa',
            'description' => 'Our history, mission and values as a Buddhist organisation',
        ),
        array(
            'title'       => 'Complete Buddhist Path',
            'description' => 'A structured approach to the full path of Tibetan Buddhism',
        ),
        array(
            'title'       => 'Für Kinder, Familien & Junge Menschen',
            'description' => 'Programmes for children, families and young people',
        ),
        array(
            'title'       => 'Safe Environment',
            'description' => 'Our commitment to ethical conduct and safeguarding',
        ),
        array(
            'title'       => 'Grievance Procedure',
            'description' => 'How to raise concerns about conduct within the organisation',
        ),
        array(
            'title'       => 'Code of Conduct',
            'description' => 'The ethical standards all Rigpa members uphold',
        ),
        array(
            'title'       => 'Our Teams',
            'description' => 'The staff and volunteers who support Rigpa\'s work',
        ),
        array(
            'title'       => 'Jobs and Volunteering',
            'description' => 'Opportunities to work with or support our community',
        ),
        array(
            'title'       => 'Sangha and Community',
            'description' => 'Our worldwide network of Buddhist practitioners',
        ),
        array(
            'title'       => 'Mitglied Werden',
            'description' => 'Become a member of the Rigpa community',
        ),
        array(
            'title'       => 'Kontakt & Presse',
            'description' => 'Get in touch with our team or press enquiries',
        ),
        array(
            'title'       => 'Our Teachers',
            'description' => 'Meet Rigpa\'s resident and visiting teachers',
        ),

        // Other
        array(
            'title'       => 'Donate',
            'description' => 'Support Rigpa\'s work and mission',
        ),
        array(
            'title'       => 'Addressing Cristicism',
            'description' => 'How Rigpa has responded to public concerns',
        ),
        array(
            'title'       => 'Sangha Member Portal',
            'description' => 'Members-only resources and community updates',
        ),
        array(
            'title'       => 'Sangha Programmes',
            'description' => 'Programmes and events exclusively for Rigpa members',
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

        // Neu hier
        array(
            'title'       => 'Compassion',
            'description' => 'Die transformative Kraft des Mitgefühls im tibetischen Buddhismus entdecken',
        ),
        array(
            'title'       => 'Where to begin',
            'description' => 'Ihre ersten Schritte in die Meditation und die buddhistischen Lehren',
        ),

        // Termine & Angebot
        array(
            'title'       => 'All Courses & Events',
            'description' => 'Das gesamte Programm an Kursen, Retreats und Veranstaltungen',
        ),
        array(
            'title'       => 'Online Programme',
            'description' => 'Live-Übertragungen und aufgezeichnete Lehren aus aller Welt',
        ),
        array(
            'title'       => 'Kids and Families',
            'description' => 'Meditations- und Dharmaprogramme für Kinder und Familien',
        ),
        array(
            'title'       => 'Places to do Retreat',
            'description' => 'Retreat-Zentren und Orte für kontemplative Praxis finden',
        ),

        // Ressourcen
        array(
            'title'       => 'Hospiz Sukhavati',
            'description' => 'Rigpas Palliativpflege und Begleitung am Lebensende',
        ),
        array(
            'title'       => 'Shedra Study College',
            'description' => 'Vertieftes Studium tibetisch-buddhistischer Texte und Philosophie',
        ),

        // Über Uns
        array(
            'title'       => 'About Rigpa',
            'description' => 'Geschichte, Mission und Werte unserer buddhistischen Organisation',
        ),
        array(
            'title'       => 'Complete Buddhist Path',
            'description' => 'Ein strukturierter Ansatz zum vollständigen Weg des tibetischen Buddhismus',
        ),
        array(
            'title'       => 'Für Kinder, Familien & Junge Menschen',
            'description' => 'Programme für Kinder, Familien und junge Menschen',
        ),
        array(
            'title'       => 'Safe Environment',
            'description' => 'Unser Engagement für ethisches Verhalten und Schutzmaßnahmen',
        ),
        array(
            'title'       => 'Grievance Procedure',
            'description' => 'Wie Sie Bedenken innerhalb der Organisation ansprechen können',
        ),
        array(
            'title'       => 'Code of Conduct',
            'description' => 'Die ethischen Standards, denen alle Rigpa-Mitglieder verpflichtet sind',
        ),
        array(
            'title'       => 'Our Teams',
            'description' => 'Die Mitarbeiter und Freiwilligen, die Rigpas Arbeit unterstützen',
        ),
        array(
            'title'       => 'Jobs and Volunteering',
            'description' => 'Möglichkeiten, mit unserer Gemeinschaft zu arbeiten oder sie zu unterstützen',
        ),
        array(
            'title'       => 'Sangha and Community',
            'description' => 'Unser weltweites Netzwerk buddhistischer Praktizierender',
        ),
        array(
            'title'       => 'Mitglied Werden',
            'description' => 'Teil der Rigpa-Gemeinschaft werden',
        ),
        array(
            'title'       => 'Kontakt & Presse',
            'description' => 'Nehmen Sie Kontakt mit unserem Team auf oder Presseanfragen',
        ),
        array(
            'title'       => 'Our Teachers',
            'description' => 'Rigpas ansässige und Gastlehrer kennenlernen',
        ),

        // Other
        array(
            'title'       => 'Donate',
            'description' => 'Rigpas Arbeit und Mission unterstützen',
        ),
        array(
            'title'       => 'Addressing Cristicism',
            'description' => 'Wie Rigpa auf öffentliche Bedenken reagiert hat',
        ),
        array(
            'title'       => 'Sangha Member Portal',
            'description' => 'Mitgliederressourcen und Community-Updates',
        ),
        array(
            'title'       => 'Sangha Programmes',
            'description' => 'Programme und Veranstaltungen exklusiv für Rigpa-Mitglieder',
        ),
    );
}
