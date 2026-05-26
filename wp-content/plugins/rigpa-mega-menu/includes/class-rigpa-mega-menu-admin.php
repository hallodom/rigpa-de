<?php
/**
 * WordPress admin UI for Rigpa Mega Menu (Tools → Mega Menu).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_Mega_Menu_Admin {

    const MENU_SLUG = 'rigpa-mega-menu';

    public static function init() {
        if (!is_admin()) {
            return;
        }

        add_action('admin_menu', array(__CLASS__, 'register_menu'));
    }

    public static function register_menu() {
        add_management_page(
            __('Mega Menu', 'rigpa-mega-menu'),
            __('Mega Menu', 'rigpa-mega-menu'),
            'manage_options',
            self::MENU_SLUG,
            array(__CLASS__, 'render_page')
        );
    }

    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'rigpa-mega-menu'));
        }

        $css_path = RIGPA_MEGA_MENU_PATH . 'assets/css/rigpa-mega-menu.css';
        $js_path  = RIGPA_MEGA_MENU_PATH . 'assets/js/rigpa-mega-menu.js';
        $assets_ok = file_exists($css_path) && file_exists($js_path);
        $menu_status = self::get_menu_location_status();

        ?>
        <div class="wrap rigpa-mega-menu-admin">
            <h1><?php esc_html_e('Mega Menu', 'rigpa-mega-menu'); ?></h1>

            <div class="rigpa-mega-menu-admin__grid">
                <div class="rigpa-mega-menu-admin__panel">
                    <h2><?php esc_html_e('Shortcode', 'rigpa-mega-menu'); ?></h2>
                    <p><?php esc_html_e('Add the mega menu to an Elementor header or any page:', 'rigpa-mega-menu'); ?></p>
                    <code class="rigpa-mega-menu-admin__code">[rigpa_mega_menu]</code>
                    <p class="description">
                        <?php esc_html_e('Alias:', 'rigpa-mega-menu'); ?>
                        <code>[rigpa-mega-menu]</code>
                    </p>
                    <p class="description">
                        <?php esc_html_e('Language override:', 'rigpa-mega-menu'); ?>
                        <code>[rigpa_mega_menu lang="german"]</code>,
                        <code>[rigpa_mega_menu lang="english"]</code>,
                        <code>[rigpa_mega_menu lang="auto"]</code>
                    </p>
                </div>

                <div class="rigpa-mega-menu-admin__panel">
                    <h2><?php esc_html_e('Assets', 'rigpa-mega-menu'); ?></h2>
                    <?php if ($assets_ok) : ?>
                        <p class="rigpa-mega-menu-admin__status rigpa-mega-menu-admin__status--ok">
                            <?php esc_html_e('Mega menu assets are built and ready.', 'rigpa-mega-menu'); ?>
                        </p>
                    <?php else : ?>
                        <p class="rigpa-mega-menu-admin__status rigpa-mega-menu-admin__status--warn">
                            <?php esc_html_e('Mega menu assets are missing. Run make build-mega-menu from the project root.', 'rigpa-mega-menu'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rigpa-mega-menu-admin__panel">
                <h2><?php esc_html_e('How it works', 'rigpa-mega-menu'); ?></h2>
                <p><?php esc_html_e('The mega menu reads its structure from WordPress navigation menus in the database (Appearance → Menus), not from theme header menus.', 'rigpa-mega-menu'); ?></p>
                <ol>
                    <li><?php esc_html_e('This plugin registers two menu locations: Mega Menu (English) and Mega Menu (German).', 'rigpa-mega-menu'); ?></li>
                    <li><?php esc_html_e('You create or seed a nav menu and assign it to the matching location under Menu Settings.', 'rigpa-mega-menu'); ?></li>
                    <li><?php esc_html_e('Top-level menu items become section labels (e.g. Meditate). Nested items become links inside each dropdown panel.', 'rigpa-mega-menu'); ?></li>
                    <li><?php esc_html_e('The shortcode loads the assigned menu, passes it to the React frontend, and renders the interactive mega menu.', 'rigpa-mega-menu'); ?></li>
                </ol>
                <p class="description">
                    <?php esc_html_e('If no menu is assigned to a location, the plugin falls back to built-in defaults in includes/menus.php.', 'rigpa-mega-menu'); ?>
                </p>
            </div>

            <div class="rigpa-mega-menu-admin__panel">
                <h2><?php esc_html_e('Languages', 'rigpa-mega-menu'); ?></h2>
                <p><?php esc_html_e('The plugin supports two menu languages — English and German — each with its own nav menu location and Appearance → Menus entry.', 'rigpa-mega-menu'); ?></p>
                <table class="widefat striped rigpa-mega-menu-admin__table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Shortcode lang', 'rigpa-mega-menu'); ?></th>
                            <th><?php esc_html_e('Menu loaded', 'rigpa-mega-menu'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>lang="auto"</code> <?php esc_html_e('(default)', 'rigpa-mega-menu'); ?></td>
                            <td><?php esc_html_e('German menu if WordPress locale starts with de (e.g. de_DE); otherwise English.', 'rigpa-mega-menu'); ?></td>
                        </tr>
                        <tr>
                            <td><code>lang="english"</code> / <code>lang="en"</code></td>
                            <td><?php esc_html_e('Always Mega Menu (English).', 'rigpa-mega-menu'); ?></td>
                        </tr>
                        <tr>
                            <td><code>lang="german"</code> / <code>lang="de"</code></td>
                            <td><?php esc_html_e('Always Mega Menu (German).', 'rigpa-mega-menu'); ?></td>
                        </tr>
                    </tbody>
                </table>
                <p class="description" style="margin-top: 12px;">
                    <?php esc_html_e('Menu item text comes from the assigned WordPress nav menu. Small UI labels in the header (e.g. “Menu”, “Learn more →”) follow the resolved language.', 'rigpa-mega-menu'); ?>
                </p>
                <p><strong><?php esc_html_e('Other WordPress locales', 'rigpa-mega-menu'); ?></strong></p>
                <p class="description">
                    <?php esc_html_e('Locales other than German (e.g. fr_FR, es_ES) use the English menu location when lang="auto". To show German content on a German site, set the site language to Deutsch or use lang="german" on the shortcode.', 'rigpa-mega-menu'); ?>
                </p>
                <p><strong><?php esc_html_e('Multilingual plugins (WPML, Polylang, etc.)', 'rigpa-mega-menu'); ?></strong></p>
                <p class="description">
                    <?php esc_html_e('The plugin does not auto-detect multilingual plugin context. Use separate Elementor headers per language with the appropriate lang attribute, or one header with lang="auto" on sites where WordPress locale matches the desired menu.', 'rigpa-mega-menu'); ?>
                </p>
            </div>

            <div class="rigpa-mega-menu-admin__panel">
                <h2><?php esc_html_e('Menu locations', 'rigpa-mega-menu'); ?></h2>
                <p><?php esc_html_e('Assign menus under Appearance → Menus → Menu Settings (checkboxes at the bottom of the page).', 'rigpa-mega-menu'); ?></p>
                <table class="widefat striped rigpa-mega-menu-admin__table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Location', 'rigpa-mega-menu'); ?></th>
                            <th><?php esc_html_e('Assigned menu', 'rigpa-mega-menu'); ?></th>
                            <th><?php esc_html_e('Status', 'rigpa-mega-menu'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menu_status as $row) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($row['label']); ?></strong><br><code><?php echo esc_html($row['location']); ?></code></td>
                                <td><?php echo esc_html($row['menu_name']); ?></td>
                                <td>
                                    <?php if ($row['assigned']) : ?>
                                        <span class="rigpa-mega-menu-admin__status rigpa-mega-menu-admin__status--ok"><?php esc_html_e('Assigned', 'rigpa-mega-menu'); ?></span>
                                    <?php else : ?>
                                        <span class="rigpa-mega-menu-admin__status rigpa-mega-menu-admin__status--warn"><?php esc_html_e('Not assigned — using fallback defaults', 'rigpa-mega-menu'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="rigpa-mega-menu-admin__panel">
                <h2><?php esc_html_e('Editing menu content', 'rigpa-mega-menu'); ?></h2>
                <p><?php esc_html_e('Go to Appearance → Menus and select Mega Menu (English) or Mega Menu (German).', 'rigpa-mega-menu'); ?></p>
                <ul>
                    <li><?php esc_html_e('Top-level items = section headings shown in the header bar.', 'rigpa-mega-menu'); ?></li>
                    <li><?php esc_html_e('Nested items = links inside each dropdown panel.', 'rigpa-mega-menu'); ?></li>
                    <li><?php esc_html_e('Enable “Description” under Screen Options to edit the subtitle shown under each link.', 'rigpa-mega-menu'); ?></li>
                </ul>
                <p><strong><?php esc_html_e('First-time setup (local dev)', 'rigpa-mega-menu'); ?></strong></p>
                <p class="description">
                    <?php esc_html_e('Menus are not created automatically when the plugin is activated. Run the seed command from the project root to create demo pages, build both nav menus, and assign them to the plugin locations:', 'rigpa-mega-menu'); ?>
                </p>
                <code class="rigpa-mega-menu-admin__code">make seed-mega-menu-pages</code>
                <p class="description">
                    <?php esc_html_e('Menus only (no pages):', 'rigpa-mega-menu'); ?>
                    <code>make seed-mega-menu-nav</code>
                </p>
                <p class="description">
                    <?php esc_html_e('Re-running the seed replaces menu items with the default structure from includes/menus.php. Featured card images on section panels are stored as menu item meta and are only set by the seed script.', 'rigpa-mega-menu'); ?>
                </p>
            </div>
        </div>
        <?php
        self::print_styles();
    }

    /**
     * @return array<int, array{location: string, label: string, menu_name: string, assigned: bool}>
     */
    private static function get_menu_location_status() {
        $locations = get_nav_menu_locations();
        $rows = array(
            array(
                'location' => 'rigpa-mega-menu-en',
                'label'    => __('Mega Menu (English)', 'rigpa-mega-menu'),
            ),
            array(
                'location' => 'rigpa-mega-menu-de',
                'label'    => __('Mega Menu (German)', 'rigpa-mega-menu'),
            ),
        );

        foreach ($rows as $index => $row) {
            $menu_id = isset($locations[$row['location']]) ? (int) $locations[$row['location']] : 0;
            $menu = $menu_id > 0 ? wp_get_nav_menu_object($menu_id) : false;

            $rows[$index]['menu_name'] = ($menu instanceof WP_Term) ? (string) $menu->name : '—';
            $rows[$index]['assigned']  = $menu instanceof WP_Term;
        }

        return $rows;
    }

    private static function print_styles() {
        ?>
        <style>
            .rigpa-mega-menu-admin__grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 16px;
                margin: 16px 0 24px;
            }
            .rigpa-mega-menu-admin__panel {
                background: #fff;
                border: 1px solid #c3c4c7;
                padding: 16px 20px;
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
                margin-bottom: 16px;
            }
            .rigpa-mega-menu-admin__panel h2 {
                margin-top: 0;
                font-size: 14px;
            }
            .rigpa-mega-menu-admin__code {
                display: inline-block;
                background: #f0f0f1;
                padding: 6px 10px;
                border-radius: 4px;
                font-size: 13px;
            }
            .rigpa-mega-menu-admin__status--ok {
                color: #00a32a;
            }
            .rigpa-mega-menu-admin__status--warn {
                color: #b32d2e;
            }
            .rigpa-mega-menu-admin__table {
                margin-top: 12px;
            }
            .rigpa-mega-menu-admin__table code {
                font-size: 12px;
            }
        </style>
        <?php
    }
}
