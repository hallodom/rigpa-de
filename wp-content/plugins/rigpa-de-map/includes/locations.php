<?php
/**
 * Location data for the Rigpa.de map (mirrors Replicate Design/src/data/locations.ts).
 *
 * @return array{left: array<int, array{name: string, region: string, url: string}>, right: array<int, array{name: string, region: string, url: string}>}
 */
function rigpa_de_map_get_locations() {
    return array(
        'left'  => array(
            array(
                'name'   => 'Aachen',
                'region' => 'Group',
                'url'    => '',
            ),
            array(
                'name'   => 'Bad Saarow',
                'region' => 'Group',
                'url'    => '',
            ),
            array(
                'name'   => 'Berlin',
                'region' => 'Centre',
                'url'    => '',
            ),
            array(
                'name'   => 'Bielefeld',
                'region' => 'Group',
                'url'    => '',
            ),
            array(
                'name'   => 'Bremen',
                'region' => 'Group',
                'url'    => '',
            ),
            array(
                'name'   => 'Düsseldorf',
                'region' => 'Centre',
                'url'    => '',
            ),
            array(
                'name'   => 'Frankfurt',
                'region' => 'Centre',
                'url'    => '',
            ),
            array(
                'name'   => 'Freiburg',
                'region' => 'Centre',
                'url'    => '',
            ),
        ),
        'right' => array(
            array(
                'name'   => 'Fürth',
                'region' => 'Centre',
                'url'    => '',
            ),
            array(
                'name'   => 'Hamburg',
                'region' => 'Centre',
                'url'    => '',
            ),
            array(
                'name'   => 'Heidelberg',
                'region' => 'Centre',
                'url'    => '',
            ),
            array(
                'name'   => 'Kassel',
                'region' => 'Centre',
                'url'    => '',
            ),
            array(
                'name'   => 'Köln',
                'region' => 'Centre',
                'url'    => '',
            ),
            array(
                'name'   => 'München',
                'region' => 'Centre',
                'url'    => '',
            ),
            array(
                'name'   => 'Stuttgart',
                'region' => 'Group',
                'url'    => '',
            ),
            array(
                'name'   => 'Wiesbaden',
                'region' => '',
                'url'    => '',
            ),
        ),
    );
}
