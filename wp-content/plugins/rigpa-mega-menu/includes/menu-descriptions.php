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
        // New Here?
        array('title' => 'Meditate Now', 'description' => 'Start your meditation practice today'),
        array('title' => 'Discover Compassion', 'description' => 'Explore the path of compassion'),
        array('title' => 'Offerings for Beginners', 'description' => 'First steps in meditation and Buddhism'),
        array('title' => 'Overview', 'description' => 'All offerings at a glance'),
        array('title' => 'Bodhi Courses', 'description' => 'Study programmes and courses from Bodhi'),
        array('title' => 'What is Buddhism?', 'description' => 'An introduction to Buddhist teachings'),
        array('title' => 'Find a Group', 'description' => 'Connect with practitioners near you'),

        // Courses & Events
        array('title' => 'All Courses and Events', 'description' => 'Browse all upcoming events'),
        array('title' => 'Online Programme', 'description' => 'Digital courses and live streams'),
        array('title' => 'For Beginners', 'description' => 'Events for newcomers'),
        array('title' => 'Healing, Illness & Loss', 'description' => 'Support during difficult times'),
        array('title' => 'Today', 'description' => "Today's events and activities"),
        array('title' => 'Children & Families', 'description' => 'Programmes for young people and families'),
        array('title' => 'Sangha', 'description' => 'Events for sangha members'),

        // Near You
        array('title' => 'International Retreat Centres', 'description' => 'Rigpa retreat centres worldwide'),
        array('title' => 'Dharma Mati Berlin', 'description' => 'Our Rigpa centre in Berlin'),

        // Resources
        array('title' => 'Prayer Requests', 'description' => 'Submit names for dedication and prayers'),
        array('title' => 'Accompanying Illness and Loss', 'description' => 'Support and guidance for life, loss, and dying'),
        array('title' => 'Teachings Online', 'description' => 'Online courses and study programmes'),
        array('title' => 'The Tibetan Book of Living and Dying', 'description' => 'Resources on the classic text by Sogyal Rinpoche'),
        array('title' => 'Rigpawiki', 'description' => 'Online encyclopedia of Tibetan Buddhism'),
        array('title' => 'Sukhavati Hospice', 'description' => 'End-of-life care and accompaniment'),
        array('title' => 'Tibetan Buddhist Calendar', 'description' => 'Important dates and practice days'),
        array('title' => 'Shedra Studies Nepal', 'description' => 'Study college in Nepal'),
        array('title' => 'Shop', 'description' => 'Books, practice materials, and gifts'),

        // About Us
        array('title' => 'About Rigpa', 'description' => 'Our history and mission'),
        array('title' => 'A Complete Buddhist Path', 'description' => 'The path of wisdom and compassion'),
        array('title' => 'Our Teachers and Lineage', 'description' => 'Meet our teachers and spiritual lineage'),
        array('title' => 'Rigpa as a Community', 'description' => 'Our vibrant practice community'),
        array('title' => 'For Children, Families & Young People', 'description' => 'Programmes for the next generation'),
        array('title' => 'Ethics and Diversity', 'description' => 'Our values and commitments'),
        array('title' => 'Team', 'description' => 'Staff and organisational structure'),
        array('title' => 'Jobs & Volunteering', 'description' => 'Opportunities to contribute'),
        array('title' => 'Become a Member', 'description' => 'Join our community'),
        array('title' => 'Contact & Press', 'description' => 'Get in touch with us'),
    );
}

/**
 * @return array<int, array{title: string, description: string}>
 */
function rigpa_mega_menu_get_german_description_entries() {
    return array(
        // Neu hier?
        array('title' => 'Jetzt Meditieren', 'description' => 'Beginnen Sie noch heute mit der Meditation'),
        array('title' => 'Mitgefühl entdecken', 'description' => 'Den Weg des Mitgefühls kennenlernen'),
        array('title' => 'Angebote für Einsteiger*innen', 'description' => 'Erste Schritte in Meditation und Buddhismus'),
        array('title' => 'Übersicht', 'description' => 'Alle Angebote auf einen Blick'),
        array('title' => 'Bodhi - Kurse', 'description' => 'Studienprogramme und Kurse von Bodhi'),
        array('title' => 'Was ist Buddhismus?', 'description' => 'Einführung in die buddhistische Lehre'),
        array('title' => 'Gruppe finden', 'description' => 'Verbinden Sie sich mit Praktizierenden in Ihrer Nähe'),

        // Kurse & Termine
        array('title' => 'Alle Kurse und Termine', 'description' => 'Alle Veranstaltungen auf einen Blick'),
        array('title' => 'Online Programm', 'description' => 'Digitale Kurse und Live-Übertragungen'),
        array('title' => 'Für Einsteiger*innen', 'description' => 'Veranstaltungen für Neulinge'),
        array('title' => 'Heilung, Krankheit & Verlust', 'description' => 'Unterstützung in schwierigen Lebensphasen'),
        array('title' => 'Heute', 'description' => 'Heutige Termine und Aktivitäten'),
        array('title' => 'Kinder & Familien', 'description' => 'Programme für junge Menschen und Familien'),
        array('title' => 'Sangha', 'description' => 'Veranstaltungen für Sangha-Mitglieder'),

        // In Deiner Nähe
        array('title' => 'Internationale Retreatzentren', 'description' => 'Rigpa-Retreatzentren weltweit'),
        array('title' => 'Dharma Mati Berlin', 'description' => 'Unser Rigpa-Zentrum in Berlin'),

        // Ressourcen
        array('title' => 'Gebetswünsche', 'description' => 'Namen für Widmungen und Gebete einreichen'),
        array('title' => 'Begleitung bei Krankheit und Verlust', 'description' => 'Unterstützung und Orientierung zu Leben, Verlust und Sterben'),
        array('title' => 'Belehrungen Online', 'description' => 'Online-Kurse und Studienprogramme'),
        array('title' => 'Tibetisches Buch vom Leben und Sterben', 'description' => 'Ressourcen zum klassischen Text von Sogyal Rinpoche'),
        array('title' => 'Rigpawiki', 'description' => 'Online-Nachschlagewerk zum tibetischen Buddhismus'),
        array('title' => 'Hospiz Sukhavati', 'description' => 'Begleitung am Lebensende'),
        array('title' => 'Tibetisch-Buddhistischer Kalender', 'description' => 'Wichtige Termine und Übungstage im buddhistischen Kalender'),
        array('title' => 'Shedra Studium Nepal', 'description' => 'Studienkolleg in Nepal'),
        array('title' => 'Shop', 'description' => 'Bücher, Übungsmaterialien und mehr'),

        // Über uns
        array('title' => 'Über Rigpa', 'description' => 'Geschichte und Mission unserer Organisation'),
        array('title' => 'Ein vollständiger Buddhistischer Pfad', 'description' => 'Der Weg der Weisheit und des Mitgefühls'),
        array('title' => 'Linie und Lehrende', 'description' => 'Unsere Lehrer und spirituelle Abstammung'),
        array('title' => 'Rigpa als Gemeinschaft', 'description' => 'Unsere lebendige Praxisgemeinschaft'),
        array('title' => 'Für Kinder, Familien & Junge Menschen', 'description' => 'Programme für die nächste Generation'),
        array('title' => 'Ethik und Diversität', 'description' => 'Unsere Werte und Verpflichtungen'),
        array('title' => 'Team', 'description' => 'Mitarbeiter und Organisationsstruktur'),
        array('title' => 'Jobs & Ehrenamt', 'description' => 'Möglichkeiten zur Mitarbeit'),
        array('title' => 'Mitglied werden', 'description' => 'Teil unserer Gemeinschaft werden'),
        array('title' => 'Kontakt & Presse', 'description' => 'Nehmen Sie Kontakt mit uns auf'),
    );
}
