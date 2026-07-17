<?php
/**
 * Country registry and seeded location data for the Rigpa map.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param string $country_slug
 * @param string $country_label
 * @return array<string, string>
 */
function rigpa_de_map_country_copy($country_slug, $country_label) {
    if ($country_slug === 'germany') {
        return array(
            'title'                => 'Rigpa Standorte Deutschland',
            'subtitle'             => 'Meditation und tibetischer Buddhismus in Ihrer Nähe',
            'country_label'        => 'Germany',
            'view_details_label'   => 'View details',
            'international_prefix' => 'To view our international centres worldwide, visit',
            'international_link'   => 'rigpa.org',
            'international_url'    => 'https://rigpa.org/',
        );
    }

    return array(
        'title'                => 'Rigpa centres and groups in ' . $country_label,
        'subtitle'             => 'Meditation and Tibetan Buddhism near you',
        'country_label'        => $country_label,
        'view_details_label'   => 'View details',
        'international_prefix' => 'To view our international centres worldwide, visit',
        'international_link'   => 'rigpa.org',
        'international_url'    => 'https://rigpa.org/',
    );
}

/**
 * @param string $id
 * @param string $name
 * @param string $region
 * @param string $url
 * @param int    $x
 * @param int    $y
 * @return array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}
 */
function rigpa_de_map_location($id, $name, $region, $url, $x, $y) {
    return array(
        'id'     => $id,
        'name'   => $name,
        'region' => $region,
        'url'    => $url,
        'coords' => array('x' => $x, 'y' => $y),
    );
}

/**
 * @param array<int, array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}> $items
 * @return array{left: array<int, array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}>, right: array<int, array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}>}
 */
function rigpa_de_map_split_locations($items) {
    $midpoint = (int) ceil(count($items) / 2);

    return array(
        'left'  => array_slice($items, 0, $midpoint),
        'right' => array_slice($items, $midpoint),
    );
}

/**
 * @return array<string, array{label: string, map: array{svg: string, width: int, height: int}, copy: array<string, string>, locations: array{left: array<int, array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}>, right: array<int, array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}>}}>
 */
function rigpa_de_map_get_country_registry() {
    $countries = array(
        'germany' => array(
            'label'     => 'Germany',
            'map'       => array('svg' => 'germany.svg', 'width' => 592, 'height' => 395),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('aachen', 'Aachen', 'Group', 'https://rigpa.org/centres/aachen/', 162, 217),
                rigpa_de_map_location('bad-saarow', 'Bad Saarow', 'Group', 'https://rigpa.org/centres/bad-saarow/', 406, 142),
                rigpa_de_map_location('dharma-mati-berlin', 'Dharma Mati Berlin', 'Centre', 'https://rigpa.org/centres/dharma-mati-berlin/', 386, 132),
                rigpa_de_map_location('bielefeld', 'Bielefeld', 'Group', 'https://rigpa.org/centres/bielefeld/', 237, 156),
                rigpa_de_map_location('bremen', 'Bremen', 'Group', 'https://rigpa.org/centres/bremen/', 245, 104),
                rigpa_de_map_location('duesseldorf', 'Düsseldorf', 'Centre', 'https://rigpa.org/centres/dusseldorf/', 184, 195),
                rigpa_de_map_location('frankfurt', 'Frankfurt', 'Centre', 'https://rigpa.org/centres/frankfurt/', 242, 250),
                rigpa_de_map_location('freiburg', 'Freiburg', 'Centre', 'https://rigpa.org/centres/freiburg/', 216, 354),
                rigpa_de_map_location('hannover', 'Hannover', 'Group', 'https://rigpa.org/centres/hannover/', 263, 125),
                rigpa_de_map_location('fuerth', 'Fürth', 'Centre', 'https://rigpa.org/centres/furth/', 313, 281),
                rigpa_de_map_location('hamburg', 'Hamburg', 'Centre', 'https://rigpa.org/centres/hamburg/', 282, 81),
                rigpa_de_map_location('heidelberg', 'Heidelberg', 'Centre', 'https://rigpa.org/centres/heidelberg/', 242, 285),
                rigpa_de_map_location('kassel', 'Kassel', 'Centre', 'https://rigpa.org/centres/kassel/', 267, 191),
                rigpa_de_map_location('koeln', 'Köln', 'Centre', 'https://rigpa.org/centres/cologne/', 189, 209),
                rigpa_de_map_location('muenchen', 'München', 'Centre', 'https://rigpa.org/centres/munich/', 330, 346),
                rigpa_de_map_location('stuttgart', 'Stuttgart', 'Group', 'https://rigpa.org/centres/stuttgart/', 257, 314),
                rigpa_de_map_location('wiesbaden', 'Wiesbaden', 'Centre', 'https://rigpa.org/centres/wiesbaden/', 228, 251),
            )),
        ),
        'netherlands' => array(
            'label'     => 'Netherlands',
            'map'       => array('svg' => 'netherlands.svg', 'width' => 420, 'height' => 520),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('amsterdam', 'Amsterdam', 'Centre', 'https://rigpa.org/centres/amsterdam/', 177, 237),
                rigpa_de_map_location('groningen', 'Groningen', 'Centre', 'https://rigpa.org/centres/groningen/', 323, 115),
                rigpa_de_map_location('schoorl', 'Schoorl', 'Group', 'https://rigpa.org/centres/schoorl/', 159, 190),
            )),
        ),
        'uk' => array(
            'label'     => 'UK',
            'map'       => array('svg' => 'uk.svg', 'width' => 430, 'height' => 620),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('birmingham', 'Birmingham', 'Group', 'https://rigpa.org/centres/birmingham/', 253, 470),
                rigpa_de_map_location('london', 'London', 'Centre', 'https://rigpa.org/centres/london/', 302, 515),
                rigpa_de_map_location('london-cornwall', 'London – Cornwall', 'Group', 'https://rigpa.org/centres/london-cornwall/', 175, 563),
                rigpa_de_map_location('london-norwich', 'London – Norwich', 'Group', 'https://rigpa.org/centres/london-norwich/', 342, 464),
                rigpa_de_map_location('london-south-west', 'London – South-West', 'Group', 'https://rigpa.org/centres/london-south-west/', 294, 517),
                rigpa_de_map_location('manchester', 'Manchester', 'Group', 'https://rigpa.org/centres/manchester/', 242, 425),
                rigpa_de_map_location('south-downs', 'South Downs', 'Centre', 'https://rigpa.org/centres/south-downs/', 281, 540),
                rigpa_de_map_location('tarset', 'Tarset', 'Group', 'https://rigpa.org/centres/tarset/', 239, 339),
            )),
        ),
        'australia' => array(
            'label'     => 'Australia',
            'map'       => array('svg' => 'australia.svg', 'width' => 620, 'height' => 430),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('adelaide', 'Adelaide', 'Centre', 'https://rigpa.org/centres/adelaide/', 367, 301),
                rigpa_de_map_location('australia', 'Australia', 'Centre', 'https://rigpa.org/centres/australia/', 326, 175),
                rigpa_de_map_location('brisbane', 'Brisbane', 'Centre', 'https://rigpa.org/centres/brisbane/', 523, 207),
                rigpa_de_map_location('bush-telegraph-distance-learning', 'Bush Telegraph (Distance Learning)', 'Group', 'https://rigpa.org/centres/bush-telegraph-distance-learning/', 517, 267),
                rigpa_de_map_location('garab-ling-blueys', 'Garab Ling Blueys', 'Centre · Retreat Centre', 'https://rigpa.org/centres/garab-ling-blueys/', 517, 267),
                rigpa_de_map_location('melbourne', 'Melbourne', 'Centre', 'https://rigpa.org/centres/melbourne/', 436, 339),
                rigpa_de_map_location('newcastle', 'Newcastle', 'Centre', 'https://rigpa.org/centres/newcastle/', 509, 275),
                rigpa_de_map_location('rigpa-australia-bush-telegraph', 'Rigpa Australia Bush Telegraph', 'Group', 'https://rigpa.org/centres/rigpa-australia-bush-telegraph/', 517, 267),
                rigpa_de_map_location('sydney', 'Sydney', 'Centre', 'https://rigpa.org/centres/sydney/', 503, 287),
            )),
        ),
        'canada' => array(
            'label'     => 'Canada',
            'map'       => array('svg' => 'canada.svg', 'width' => 650, 'height' => 420),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('montreal', 'Montréal', 'Centre', 'https://rigpa.org/centres/montreal/', 407, 393),
                rigpa_de_map_location('quebec', 'Québec', 'Group', 'https://rigpa.org/centres/quebec/', 413, 356),
            )),
        ),
        'ireland' => array(
            'label'     => 'Ireland',
            'map'       => array('svg' => 'ireland.svg', 'width' => 520, 'height' => 460),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('athlone', 'Athlone', 'Centre', 'https://rigpa.org/centres/athlone/', 278, 235),
                rigpa_de_map_location('cork', 'Cork', 'Group', 'https://rigpa.org/centres/cork/', 242, 405),
                rigpa_de_map_location('dublin', 'Dublin', 'Centre', 'https://rigpa.org/centres/dublin/', 392, 243),
                rigpa_de_map_location('dzogchen-beara', 'Dzogchen Beara', 'Centre · Retreat Centre', 'https://rigpa.org/centres/dzogchen-beara/', 139, 436),
                rigpa_de_map_location('ireland', 'Ireland', 'Centre', 'https://rigpa.org/centres/ireland/', 276, 298),
                rigpa_de_map_location('limerick', 'Limerick', 'Group', 'https://rigpa.org/centres/limerick/', 231, 321),
            )),
        ),
        'usa' => array(
            'label'     => 'USA',
            'map'       => array('svg' => 'usa.svg', 'width' => 650, 'height' => 420),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('boston-ma', 'Boston, MA', 'Group', 'https://rigpa.org/centres/boston-ma/', 597, 147),
                rigpa_de_map_location('boulder-co', 'Boulder, CO', 'Group', 'https://rigpa.org/centres/boulder-co/', 222, 181),
                rigpa_de_map_location('cape-cod-ma', 'Cape Cod, MA', 'Group', 'https://rigpa.org/centres/cape-cod-ma/', 609, 156),
                rigpa_de_map_location('maine', 'Maine', 'Group', 'https://rigpa.org/centres/maine/', 621, 96),
                rigpa_de_map_location('new-york-ny', 'New York, NY', 'Group', 'https://rigpa.org/centres/new-york-ny/', 565, 172),
                rigpa_de_map_location('oxnard-ca', 'Oxnard, CA', 'Group', 'https://rigpa.org/centres/oxnard-ca/', 69, 262),
                rigpa_de_map_location('portland-or', 'Portland, OR', 'Group', 'https://rigpa.org/centres/portland-or/', 30, 99),
                rigpa_de_map_location('san-francisco-bay-area-ca', 'San Francisco Bay Area, CA', 'Group', 'https://rigpa.org/centres/san-francisco-bay-area-ca/', 34, 213),
                rigpa_de_map_location('santa-cruz-ca', 'Santa Cruz, CA', 'Group', 'https://rigpa.org/centres/santa-cruz-ca/', 38, 223),
                rigpa_de_map_location('seattle-wa', 'Seattle, WA', 'Group', 'https://rigpa.org/centres/seattle-wa/', 34, 66),
                rigpa_de_map_location('rigpa-usa', 'USA', 'Centre', 'https://rigpa.org/centres/rigpa-usa/', 275, 185),
                rigpa_de_map_location('usa-distance-sangha-online-group', 'USA Distance Sangha (online group)', 'Group', 'https://rigpa.org/centres/usa-distance-sangha-online-group/', 275, 185),
                rigpa_de_map_location('washington-dc', 'Washington, DC', 'Group', 'https://rigpa.org/centres/washington-dc/', 532, 197),
            )),
        ),
        'belgium' => array(
            'label'     => 'Belgium',
            'map'       => array('svg' => 'belgium.svg', 'width' => 520, 'height' => 400),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('bruxelles', 'Brussels', 'Centre', 'https://rigpa.org/centres/bruxelles/', 252, 154),
            )),
        ),
        'italy' => array(
            'label'     => 'Italy',
            'map'       => array('svg' => 'italy.svg', 'width' => 520, 'height' => 520),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('bologna', 'Bologna', 'Group', 'https://rigpa.org/centres/bologna/', 216, 142),
                rigpa_de_map_location('bolzano', 'Bolzano', 'Group', 'https://rigpa.org/centres/bolzano/', 212, 30),
                rigpa_de_map_location('isola-delba', 'Isola d’Elba', 'Group', 'https://rigpa.org/centres/isola-delba/', 180, 226),
                rigpa_de_map_location('roma', 'Roma', 'Group', 'https://rigpa.org/centres/roma/', 257, 270),
                rigpa_de_map_location('torino', 'Torino', 'Centre', 'https://rigpa.org/centres/torino/', 85, 112),
            )),
        ),
        'switzerland' => array(
            'label'     => 'Switzerland',
            'map'       => array('svg' => 'switzerland.svg', 'width' => 460, 'height' => 350),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('basel', 'Basel', 'Group', 'https://rigpa.org/centres/basel/', 182, 90),
                rigpa_de_map_location('geneva', 'Geneva', 'Centre', 'https://rigpa.org/centres/geneva/', 70, 244),
                rigpa_de_map_location('neuchatel', 'Neuchâtel', 'Centre', 'https://rigpa.org/centres/neuchatel/', 130, 155),
                rigpa_de_map_location('zurich', 'Zürich', 'Centre', 'https://rigpa.org/centres/zurich/', 256, 111),
            )),
        ),
        'france' => array(
            'label'     => 'France',
            'map'       => array('svg' => 'france.svg', 'width' => 520, 'height' => 520),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('aix-en-provence', 'Aix-en-Provence', 'Group', 'https://rigpa.org/centres/aix-en-provence/', 367, 406),
                rigpa_de_map_location('ales', 'Alès', 'Group', 'https://rigpa.org/centres/ales/', 319, 377),
                rigpa_de_map_location('angouleme-cognac', 'Angoulême-Cognac', 'Group', 'https://rigpa.org/centres/angouleme-cognac/', 164, 299),
                rigpa_de_map_location('chatellerault', 'Châtellerault', 'Group', 'https://rigpa.org/centres/chatellerault/', 195, 241),
                rigpa_de_map_location('grenoble', 'Grenoble', 'Group', 'https://rigpa.org/centres/grenoble/', 378, 324),
                rigpa_de_map_location('lerab-ling', 'Lerab Ling', 'Centre · Retreat Centre', 'https://rigpa.org/centres/lerab-ling/', 289, 392),
                rigpa_de_map_location('lyon', 'Lyon', 'Centre', 'https://rigpa.org/centres/lyon/', 346, 295),
                rigpa_de_map_location('montpellier', 'Montpellier', 'Group', 'https://rigpa.org/centres/montpellier/', 312, 402),
                rigpa_de_map_location('nice', 'Nice', 'Group', 'https://rigpa.org/centres/nice/', 431, 397),
                rigpa_de_map_location('paris', 'Paris', 'Centre', 'https://rigpa.org/centres/paris/', 258, 135),
                rigpa_de_map_location('perpignan', 'Perpignan', 'Group', 'https://rigpa.org/centres/perpignan/', 278, 446),
                rigpa_de_map_location('rennes', 'Rennes', 'Group', 'https://rigpa.org/centres/rennes/', 116, 174),
                rigpa_de_map_location('toulon', 'Toulon', 'Group', 'https://rigpa.org/centres/toulon/', 384, 425),
                rigpa_de_map_location('toulouse', 'Toulouse', 'Centre', 'https://rigpa.org/centres/toulouse/', 226, 402),
            )),
        ),
        'spain' => array(
            'label'     => 'Spain',
            'map'       => array('svg' => 'spain.svg', 'width' => 520, 'height' => 420),
            'locations' => rigpa_de_map_split_locations(array(
                rigpa_de_map_location('alicante', 'Alicante', 'Centre', 'https://rigpa.org/centres/alicante/', 390, 153),
                rigpa_de_map_location('barcelona', 'Barcelona', 'Centre', 'https://rigpa.org/centres/barcelona/', 444, 73),
                rigpa_de_map_location('madrid', 'Madrid', 'Centre', 'https://rigpa.org/centres/madrid/', 325, 99),
                rigpa_de_map_location('mallorca', 'Mallorca', 'Group', 'https://rigpa.org/centres/mallorca/', 458, 120),
                rigpa_de_map_location('pontevedra', 'Pontevedra', 'Centre', 'https://rigpa.org/centres/pontevedra/', 226, 45),
                rigpa_de_map_location('santiago-de-compostela', 'Santiago de Compostela', 'Centre', 'https://rigpa.org/centres/santiago-de-compostela/', 228, 32),
                rigpa_de_map_location('tenerife', 'Tenerife', 'Centre', 'https://rigpa.org/centres/tenerife/', 64, 397),
                rigpa_de_map_location('valencia', 'Valencia', 'Group', 'https://rigpa.org/centres/valencia/', 392, 124),
            )),
        ),
    );

    foreach ($countries as $slug => $country) {
        $countries[$slug]['copy'] = rigpa_de_map_country_copy($slug, $country['label']);
    }

    return $countries;
}

/**
 * @param string $country
 * @return string
 */
function rigpa_de_map_sanitize_country($country) {
    $country = sanitize_key($country);
    $countries = rigpa_de_map_get_country_registry();

    return isset($countries[$country]) ? $country : 'germany';
}

/**
 * @return string
 */
function rigpa_de_map_get_default_country() {
    return rigpa_de_map_sanitize_country(get_option(RIGPA_DE_MAP_DEFAULT_COUNTRY_OPTION, 'germany'));
}

/**
 * @param string|null $country
 * @return array{label: string, map: array{svg: string, width: int, height: int}, copy: array<string, string>, locations: array{left: array<int, array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}>, right: array<int, array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}>}}
 */
function rigpa_de_map_get_country($country = null) {
    $countries = rigpa_de_map_get_country_registry();
    $country = $country === null ? rigpa_de_map_get_default_country() : rigpa_de_map_sanitize_country($country);

    return $countries[$country];
}
