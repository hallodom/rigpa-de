<?php
/**
 * Location data for the Rigpa.de map (mirrors Replicate Design/src/data/locations.ts).
 *
 * @return array{left: array<int, array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}>, right: array<int, array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}>}
 */
function rigpa_de_map_get_locations() {
    return array(
        'left'  => array(
            array(
                'id'     => 'aachen',
                'name'   => 'Aachen',
                'region' => 'Group',
                'url'    => '',
                'coords' => array('x' => 162, 'y' => 217),
            ),
            array(
                'id'     => 'bad-saarow',
                'name'   => 'Bad Saarow',
                'region' => 'Group',
                'url'    => '',
                'coords' => array('x' => 406, 'y' => 142),
            ),
            array(
                'id'     => 'berlin',
                'name'   => 'Berlin',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 386, 'y' => 132),
            ),
            array(
                'id'     => 'bielefeld',
                'name'   => 'Bielefeld',
                'region' => 'Group',
                'url'    => '',
                'coords' => array('x' => 237, 'y' => 156),
            ),
            array(
                'id'     => 'bremen',
                'name'   => 'Bremen',
                'region' => 'Group',
                'url'    => '',
                'coords' => array('x' => 245, 'y' => 104),
            ),
            array(
                'id'     => 'duesseldorf',
                'name'   => 'Düsseldorf',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 184, 'y' => 195),
            ),
            array(
                'id'     => 'frankfurt',
                'name'   => 'Frankfurt',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 242, 'y' => 250),
            ),
            array(
                'id'     => 'freiburg',
                'name'   => 'Freiburg',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 216, 'y' => 354),
            ),
        ),
        'right' => array(
            array(
                'id'     => 'fuerth',
                'name'   => 'Fürth',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 313, 'y' => 281),
            ),
            array(
                'id'     => 'hamburg',
                'name'   => 'Hamburg',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 282, 'y' => 81),
            ),
            array(
                'id'     => 'heidelberg',
                'name'   => 'Heidelberg',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 242, 'y' => 285),
            ),
            array(
                'id'     => 'kassel',
                'name'   => 'Kassel',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 267, 'y' => 191),
            ),
            array(
                'id'     => 'koeln',
                'name'   => 'Köln',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 189, 'y' => 209),
            ),
            array(
                'id'     => 'muenchen',
                'name'   => 'München',
                'region' => 'Centre',
                'url'    => '',
                'coords' => array('x' => 330, 'y' => 346),
            ),
            array(
                'id'     => 'stuttgart',
                'name'   => 'Stuttgart',
                'region' => 'Group',
                'url'    => '',
                'coords' => array('x' => 257, 'y' => 314),
            ),
            array(
                'id'     => 'wiesbaden',
                'name'   => 'Wiesbaden',
                'region' => '',
                'url'    => '',
                'coords' => array('x' => 228, 'y' => 251),
            ),
        ),
    );
}
