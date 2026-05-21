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
                'name'   => 'Dharma Mati Berlin',
                'region' => 'Centre',
                'url'    => '',
            ),
            array(
                'name'   => 'Cologne',
                'region' => 'Group',
                'url'    => '',
            ),
            array(
                'name'   => 'Munich',
                'region' => 'München',
                'url'    => '',
            ),
            array(
                'name'   => 'Bad Saarow',
                'region' => 'Rheinland',
                'url'    => '',
            ),
            array(
                'name'   => 'Bielefeld',
                'region' => 'Rheinland',
                'url'    => '',
            ),
            array(
                'name'   => 'Bremen',
                'region' => 'Hessen',
                'url'    => '',
            ),
        ),
        'right' => array(
            array(
                'name'   => 'Heidelberg',
                'region' => 'Baden-Württemberg',
                'url'    => '',
            ),
            array(
                'name'   => 'Stuttgart',
                'region' => 'Baden-Württemberg',
                'url'    => '',
            ),
            array(
                'name'   => 'München',
                'region' => 'Bayern',
                'url'    => '',
            ),
            array(
                'name'   => 'Leipzig',
                'region' => 'Sachsen',
                'url'    => '',
            ),
            array(
                'name'   => 'Dresden',
                'region' => 'Sachsen',
                'url'    => '',
            ),
        ),
    );
}
