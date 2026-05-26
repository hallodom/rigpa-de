<?php
/**
 * Replace the active block-theme header template part with a clean
 * Rigpa Mega Menu shortcode, and set the demo page as the front page.
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "Run via WP-CLI.\n");
    exit(1);
}

$theme = wp_get_theme();
$slug  = 'header';
$theme_slug = $theme->get_stylesheet();

$header_content = <<<HTML
<!-- wp:group {"tagName":"header","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}},"layout":{"type":"constrained"}} -->
<header class="wp-block-group" style="padding-top:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20)"><!-- wp:shortcode -->
[rigpa_mega_menu]
<!-- /wp:shortcode --></header>
<!-- /wp:group -->
HTML;

$existing = get_block_template($theme_slug . '//' . $slug, 'wp_template_part');

$tax_term = wp_insert_term('header', 'wp_template_part_area');
unset($tax_term);

$post_data = array(
    'post_title'   => 'Header',
    'post_name'    => $slug,
    'post_content' => $header_content,
    'post_status'  => 'publish',
    'post_type'    => 'wp_template_part',
    'tax_input'    => array(
        'wp_theme'                => $theme_slug,
        'wp_template_part_area'   => 'header',
    ),
);

if ($existing && !empty($existing->wp_id)) {
    $post_data['ID'] = $existing->wp_id;
    $post_id = wp_update_post($post_data, true);
    WP_CLI::success("Updated header template part (ID {$post_id}).");
} else {
    $post_id = wp_insert_post($post_data, true);
    if (is_wp_error($post_id)) {
        WP_CLI::error('Failed: ' . $post_id->get_error_message());
    }
    wp_set_post_terms($post_id, $theme_slug, 'wp_theme');
    wp_set_post_terms($post_id, 'header', 'wp_template_part_area');
    WP_CLI::success("Created header template part (ID {$post_id}).");
}

$demo = get_page_by_path('mega-menu-demo', OBJECT, 'page');
if ($demo instanceof WP_Post) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $demo->ID);
    WP_CLI::success("Set Mega Menu Demo as front page (ID {$demo->ID}).");
} else {
    WP_CLI::warning('Mega Menu Demo page not found.');
}
