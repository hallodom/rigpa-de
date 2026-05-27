<?php
/**
 * WordPress admin UI for Rigpa Mega Menu (Tools → Mega Menu).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_Mega_Menu_Admin {

    const MENU_SLUG   = 'rigpa-mega-menu';
    const ACTION_SEED = 'rigpa_mega_menu_seed';

    public static function init() {
        if (!is_admin()) {
            return;
        }

        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_post_' . self::ACTION_SEED, array(__CLASS__, 'handle_seed'));
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

    /**
     * Handle the seed form submission.
     */
    public static function handle_seed() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', 'rigpa-mega-menu'));
        }

        check_admin_referer(self::ACTION_SEED);

        $results = Rigpa_Mega_Menu_Seeder::seed_all();

        $messages = array();
        foreach ($results as $result) {
            $messages[] = sprintf(
                '%s: %d sections, %d links',
                esc_html($result['menu_name']),
                (int) $result['sections'],
                (int) $result['links']
            );
        }

        $redirect = add_query_arg(
            array(
                'page'         => self::MENU_SLUG,
                'seed_success' => '1',
                'seed_detail'  => rawurlencode(implode(' | ', $messages)),
            ),
            admin_url('tools.php')
        );

        wp_safe_redirect($redirect);
        exit;
    }

    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'rigpa-mega-menu'));
        }

        $css_path  = RIGPA_MEGA_MENU_PATH . 'assets/css/rigpa-mega-menu.css';
        $js_path   = RIGPA_MEGA_MENU_PATH . 'assets/js/rigpa-mega-menu.js';
        $assets_ok = file_exists($css_path) && file_exists($js_path);

        $menu_status  = self::get_menu_location_status();
        $seed_success = isset($_GET['seed_success']) && $_GET['seed_success'] === '1';
        $seed_detail  = $seed_success && isset($_GET['seed_detail'])
            ? rawurldecode(sanitize_text_field(wp_unslash($_GET['seed_detail'])))
            : '';

        ?>
        <div class="wrap rigpa-mega-menu-admin">
            <h1><?php esc_html_e('Mega Menu', 'rigpa-mega-menu'); ?></h1>

            <?php if ($seed_success) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Default menus seeded successfully.', 'rigpa-mega-menu'); ?></strong>
                        <?php if ($seed_detail) : ?>
                            — <?php echo esc_html($seed_detail); ?>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>">
                            <?php esc_html_e('Edit in Appearance → Menus →', 'rigpa-mega-menu'); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>

            <div class="rigpa-mega-menu-admin__grid">
                <div class="rigpa-mega-menu-admin__panel">
                    <h2><?php esc_html_e('Shortcode', 'rigpa-mega-menu'); ?></h2>
                    <p><?php esc_html_e('Add the mega menu to any page or header template:', 'rigpa-mega-menu'); ?></p>
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
                            <?php esc_html_e('Assets are built and ready.', 'rigpa-mega-menu'); ?>
                        </p>
                    <?php else : ?>
                        <p class="rigpa-mega-menu-admin__status rigpa-mega-menu-admin__status--warn">
                            <?php esc_html_e('Assets are missing. Run make build-mega-menu from the project root.', 'rigpa-mega-menu'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rigpa-mega-menu-admin__panel">
                <h2><?php esc_html_e('Menu locations', 'rigpa-mega-menu'); ?></h2>
                <p>
                    <?php esc_html_e('Each location must have a nav menu assigned to it. Use the button below to install the default menu structure, then edit it under Appearance → Menus.', 'rigpa-mega-menu'); ?>
                </p>
                <table class="widefat striped rigpa-mega-menu-admin__table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Location', 'rigpa-mega-menu'); ?></th>
                            <th><?php esc_html_e('Assigned menu', 'rigpa-mega-menu'); ?></th>
                            <th><?php esc_html_e('Status', 'rigpa-mega-menu'); ?></th>
                            <th><?php esc_html_e('Actions', 'rigpa-mega-menu'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menu_status as $row) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($row['label']); ?></strong><br>
                                    <code><?php echo esc_html($row['location']); ?></code>
                                </td>
                                <td><?php echo esc_html($row['menu_name']); ?></td>
                                <td>
                                    <?php if ($row['assigned']) : ?>
                                        <span class="rigpa-mega-menu-admin__status rigpa-mega-menu-admin__status--ok">
                                            <?php esc_html_e('Assigned', 'rigpa-mega-menu'); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="rigpa-mega-menu-admin__status rigpa-mega-menu-admin__status--warn">
                                            <?php esc_html_e('Not assigned — using fallback', 'rigpa-mega-menu'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['assigned']) : ?>
                                        <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="button button-small">
                                            <?php esc_html_e('Edit menu', 'rigpa-mega-menu'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="rigpa-mega-menu-admin__seed-box">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="<?php echo esc_attr(self::ACTION_SEED); ?>">
                        <?php wp_nonce_field(self::ACTION_SEED); ?>
                        <p>
                            <?php if (self::any_assigned($menu_status)) : ?>
                                <strong><?php esc_html_e('Reinstall default menus', 'rigpa-mega-menu'); ?></strong><br>
                                <span class="description"><?php esc_html_e('This will replace existing menu items with the default structure. Your current changes will be lost.', 'rigpa-mega-menu'); ?></span>
                            <?php else : ?>
                                <strong><?php esc_html_e('Install default menus', 'rigpa-mega-menu'); ?></strong><br>
                                <span class="description"><?php esc_html_e('Creates Mega Menu (English) and Mega Menu (German) with the default section and link structure, and assigns them to the plugin locations.', 'rigpa-mega-menu'); ?></span>
                            <?php endif; ?>
                        </p>
                        <?php
                        $button_label = self::any_assigned($menu_status)
                            ? __('Reinstall Default Menus', 'rigpa-mega-menu')
                            : __('Install Default Menus', 'rigpa-mega-menu');

                        submit_button(
                            $button_label,
                            self::any_assigned($menu_status) ? 'secondary' : 'primary',
                            'submit',
                            false
                        );
                        ?>
                    </form>
                </div>
            </div>

            <div class="rigpa-mega-menu-admin__panel">
                <h2><?php esc_html_e('How it works', 'rigpa-mega-menu'); ?></h2>
                <p><?php esc_html_e('The mega menu reads its structure from WordPress navigation menus (Appearance → Menus), not from theme header menus.', 'rigpa-mega-menu'); ?></p>
                <ol>
                    <li><?php esc_html_e('The plugin registers two menu locations: Mega Menu (English) and Mega Menu (German).', 'rigpa-mega-menu'); ?></li>
                    <li><?php esc_html_e('Install defaults here, or create your own menu in Appearance → Menus and assign it to a location under Menu Settings.', 'rigpa-mega-menu'); ?></li>
                    <li><?php esc_html_e('Top-level items = section headings. Nested items = dropdown links. Enable "Description" under Screen Options to edit link subtitles.', 'rigpa-mega-menu'); ?></li>
                    <li><?php esc_html_e('If no menu is assigned, the plugin uses built-in fallback defaults from includes/menus.php.', 'rigpa-mega-menu'); ?></li>
                </ol>
            </div>

            <div class="rigpa-mega-menu-admin__panel">
                <h2><?php esc_html_e('Languages', 'rigpa-mega-menu'); ?></h2>
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
                            <td><?php esc_html_e('German if WP locale starts with de (e.g. de_DE); otherwise English.', 'rigpa-mega-menu'); ?></td>
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
                <p class="description" style="margin-top:10px;">
                    <?php esc_html_e('WPML/Polylang: not auto-detected. Use separate headers with the appropriate lang attribute per language.', 'rigpa-mega-menu'); ?>
                </p>
            </div>
        </div>
        <?php
        self::print_styles();
    }

    /**
     * @param array<int, array{assigned: bool}> $menu_status
     */
    private static function any_assigned(array $menu_status) {
        foreach ($menu_status as $row) {
            if ($row['assigned']) {
                return true;
            }
        }
        return false;
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
            $menu    = $menu_id > 0 ? wp_get_nav_menu_object($menu_id) : false;

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
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
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
            .rigpa-mega-menu-admin__status--ok   { color: #00a32a; }
            .rigpa-mega-menu-admin__status--warn  { color: #b32d2e; }
            .rigpa-mega-menu-admin__table { margin-top: 12px; }
            .rigpa-mega-menu-admin__table code { font-size: 12px; }
            .rigpa-mega-menu-admin__seed-box {
                margin-top: 20px;
                padding-top: 16px;
                border-top: 1px solid #f0f0f1;
            }
        </style>
        <?php
    }
}
