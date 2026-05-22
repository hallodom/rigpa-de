<?php
/**
 * WordPress admin UI for Rigpa.de Map (Tools → Map).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_De_Map_Admin {

    const MENU_SLUG = 'rigpa-de-map';
    const OPTION_KEY = RIGPA_DE_MAP_URLS_OPTION;

    public static function init() {
        if (!is_admin()) {
            return;
        }

        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_init', array(__CLASS__, 'handle_save'));
    }

    public static function register_menu() {
        add_management_page(
            __('Map', 'rigpa-de-map'),
            __('Map', 'rigpa-de-map'),
            'manage_options',
            self::MENU_SLUG,
            array(__CLASS__, 'render_page')
        );
    }

    public static function handle_save() {
        if (!isset($_POST['rigpa_de_map_save'])) {
            return;
        }

        if (
            !isset($_POST['_wpnonce'])
            || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'rigpa_de_map_save')
        ) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        $urls = array();
        if (isset($_POST['location_url']) && is_array($_POST['location_url'])) {
            foreach ($_POST['location_url'] as $id => $url) {
                $id = sanitize_key($id);
                if ($id === '') {
                    continue;
                }
                $urls[$id] = rigpa_de_map_sanitize_location_url($url);
            }
        }

        update_option(self::OPTION_KEY, $urls, false);

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'    => self::MENU_SLUG,
                    'updated' => '1',
                ),
                admin_url('tools.php')
            )
        );
        exit;
    }

    /**
     * @return array<string, string>
     */
    public static function get_saved_urls() {
        $urls = get_option(self::OPTION_KEY, array());
        return is_array($urls) ? $urls : array();
    }

    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'rigpa-de-map'));
        }

        $locations = rigpa_de_map_get_locations();
        $saved_urls = self::get_saved_urls();
        $updated = isset($_GET['updated']) && $_GET['updated'] === '1';

        $css_path = RIGPA_DE_MAP_PATH . 'assets/css/rigpa-de-map.css';
        $js_path  = RIGPA_DE_MAP_PATH . 'assets/js/rigpa-de-map.js';
        $assets_ok = file_exists($css_path) && file_exists($js_path);

        ?>
        <div class="wrap rigpa-de-map-admin">
            <h1><?php esc_html_e('Map', 'rigpa-de-map'); ?></h1>

            <?php if ($updated) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Settings saved.', 'rigpa-de-map'); ?></p>
                </div>
            <?php endif; ?>

            <div class="rigpa-de-map-admin__grid">
                <div class="rigpa-de-map-admin__panel">
                    <h2><?php esc_html_e('Shortcode', 'rigpa-de-map'); ?></h2>
                    <p><?php esc_html_e('Add the map to any page or post:', 'rigpa-de-map'); ?></p>
                    <code class="rigpa-de-map-admin__code">[rigpa_de_map]</code>
                    <p class="description">
                        <?php esc_html_e('Alias:', 'rigpa-de-map'); ?>
                        <code>[rigpa-de-map]</code>
                    </p>
                </div>

                <div class="rigpa-de-map-admin__panel">
                    <h2><?php esc_html_e('Assets', 'rigpa-de-map'); ?></h2>
                    <?php if ($assets_ok) : ?>
                        <p class="rigpa-de-map-admin__status rigpa-de-map-admin__status--ok">
                            <?php esc_html_e('Map assets are built and ready.', 'rigpa-de-map'); ?>
                        </p>
                    <?php else : ?>
                        <p class="rigpa-de-map-admin__status rigpa-de-map-admin__status--warn">
                            <?php esc_html_e('Map assets are missing. Run make build-map from the project root.', 'rigpa-de-map'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('rigpa_de_map_save'); ?>
                <input type="hidden" name="rigpa_de_map_save" value="1" />

                <h2><?php esc_html_e('Locations', 'rigpa-de-map'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Set the “View details” link for each location. Use a full URL (https://…) or a site-relative path (/centres/berlin). Leave blank to show the label without a link.', 'rigpa-de-map'); ?>
                </p>

                <?php self::render_location_table(__('Left column', 'rigpa-de-map'), $locations['left'] ?? array(), $saved_urls); ?>
                <?php self::render_location_table(__('Right column', 'rigpa-de-map'), $locations['right'] ?? array(), $saved_urls); ?>

                <?php submit_button(__('Save URLs', 'rigpa-de-map')); ?>
            </form>
        </div>
        <?php
        self::print_styles();
    }

    /**
     * @param string $title
     * @param array<int, array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}> $items
     * @param array<string, string> $saved_urls
     */
    private static function render_location_table($title, $items, $saved_urls) {
        ?>
        <h3><?php echo esc_html($title); ?></h3>
        <table class="widefat striped rigpa-de-map-admin__table">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Name', 'rigpa-de-map'); ?></th>
                    <th scope="col"><?php esc_html_e('Type', 'rigpa-de-map'); ?></th>
                    <th scope="col"><?php esc_html_e('View details URL', 'rigpa-de-map'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item) : ?>
                    <?php
                    $id = $item['id'];
                    $url = isset($saved_urls[$id]) ? $saved_urls[$id] : ($item['url'] ?? '');
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($item['name']); ?></strong>
                            <br />
                            <span class="description"><?php echo esc_html($id); ?></span>
                        </td>
                        <td><?php echo esc_html($item['region'] !== '' ? $item['region'] : '—'); ?></td>
                        <td>
                            <input
                                type="text"
                                name="location_url[<?php echo esc_attr($id); ?>]"
                                value="<?php echo esc_attr($url); ?>"
                                class="large-text"
                                placeholder="/centres/berlin"
                            />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private static function print_styles() {
        ?>
        <style>
            .rigpa-de-map-admin__grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 16px;
                margin: 16px 0 24px;
            }
            .rigpa-de-map-admin__panel {
                background: #fff;
                border: 1px solid #c3c4c7;
                padding: 16px 20px;
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
            }
            .rigpa-de-map-admin__panel h2 {
                margin-top: 0;
                font-size: 14px;
            }
            .rigpa-de-map-admin__code {
                display: inline-block;
                background: #f0f0f1;
                padding: 6px 10px;
                border-radius: 4px;
                font-size: 13px;
            }
            .rigpa-de-map-admin__status--ok {
                color: #00a32a;
            }
            .rigpa-de-map-admin__status--warn {
                color: #b32d2e;
            }
            .rigpa-de-map-admin__table {
                margin-bottom: 24px;
            }
            .rigpa-de-map-admin__table td {
                vertical-align: middle;
            }
        </style>
        <?php
    }
}
