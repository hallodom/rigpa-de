<?php
/**
 * WordPress admin UI for Rigpa Centre Maps (Tools → Maps).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_De_Map_Admin {

    const MENU_SLUG = 'rigpa-de-map';
    const OPTION_KEY = RIGPA_DE_MAP_URLS_OPTION;
    const IMAGES_OPTION_KEY = RIGPA_DE_MAP_IMAGES_OPTION;
    const COPY_OPTION_KEY = RIGPA_DE_MAP_COPY_OPTION;
    const LOCATION_TEXTS_OPTION_KEY = RIGPA_DE_MAP_LOCATION_TEXTS_OPTION;
    const COUNTRY_OPTION_KEY = RIGPA_DE_MAP_DEFAULT_COUNTRY_OPTION;

    public static function init() {
        if (!is_admin()) {
            return;
        }

        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_init', array(__CLASS__, 'handle_save'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }

    public static function register_menu() {
        add_management_page(
            __('Maps', 'rigpa-de-map'),
            __('Maps', 'rigpa-de-map'),
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

        $default_country = isset($_POST['default_country'])
            ? rigpa_de_map_sanitize_country(wp_unslash($_POST['default_country']))
            : rigpa_de_map_get_default_country();
        $editing_country = isset($_POST['editing_country'])
            ? rigpa_de_map_sanitize_country(wp_unslash($_POST['editing_country']))
            : rigpa_de_map_get_default_country();

        update_option(self::COUNTRY_OPTION_KEY, $default_country, false);

        $all_urls = get_option(self::OPTION_KEY, array());
        $all_urls = is_array($all_urls) ? $all_urls : array();
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

        $all_urls[$editing_country] = $urls;
        update_option(self::OPTION_KEY, $all_urls, false);

        $all_copy = get_option(self::COPY_OPTION_KEY, array());
        $all_copy = is_array($all_copy) ? $all_copy : array();
        $copy = rigpa_de_map_default_copy($editing_country);
        if (isset($_POST['map_copy']) && is_array($_POST['map_copy'])) {
            foreach ($copy as $key => $default) {
                $copy[$key] = isset($_POST['map_copy'][$key])
                    ? rigpa_de_map_sanitize_copy_value($key, $_POST['map_copy'][$key])
                    : $default;
            }
        }

        $all_copy[$editing_country] = $copy;
        update_option(self::COPY_OPTION_KEY, $all_copy, false);

        $all_location_texts = get_option(self::LOCATION_TEXTS_OPTION_KEY, array());
        $all_location_texts = is_array($all_location_texts) ? $all_location_texts : array();
        $location_texts = array();
        if (isset($_POST['location_text']) && is_array($_POST['location_text'])) {
            foreach ($_POST['location_text'] as $id => $fields) {
                $id = sanitize_key($id);
                if ($id === '' || !is_array($fields)) {
                    continue;
                }

                $location_texts[$id] = array(
                    'name'   => isset($fields['name']) ? rigpa_de_map_sanitize_location_text($fields['name']) : '',
                    'region' => isset($fields['region']) ? rigpa_de_map_sanitize_location_text($fields['region']) : '',
                );
            }
        }

        $all_location_texts[$editing_country] = $location_texts;
        update_option(self::LOCATION_TEXTS_OPTION_KEY, $all_location_texts, false);

        $all_images = get_option(self::IMAGES_OPTION_KEY, array());
        $all_images = is_array($all_images) ? $all_images : array();
        $images = array();
        if (isset($_POST['location_image_state']) && is_array($_POST['location_image_state'])) {
            foreach ($_POST['location_image_state'] as $id => $state) {
                $id = sanitize_key($id);
                if ($id === '') {
                    continue;
                }

                $state = sanitize_key($state);
                if ($state === 'default') {
                    continue;
                }

                if ($state === 'none') {
                    $images[$id] = '';
                    continue;
                }

                if ($state === 'custom') {
                    $url = isset($_POST['location_image'][$id])
                        ? rigpa_de_map_sanitize_location_image($_POST['location_image'][$id])
                        : '';
                    $images[$id] = $url;
                }
            }
        }

        $all_images[$editing_country] = $images;
        update_option(self::IMAGES_OPTION_KEY, $all_images, false);

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'    => self::MENU_SLUG,
                    'updated' => '1',
                    'country' => $editing_country,
                ),
                admin_url('tools.php')
            )
        );
        exit;
    }

    /**
     * @return array<string, string>
     */
    public static function get_saved_urls($country = null) {
        return rigpa_de_map_get_saved_urls($country);
    }

    /**
     * @return array<string, string>
     */
    public static function get_saved_images($country = null) {
        return rigpa_de_map_get_saved_images($country);
    }

    /**
     * @return array<string, string>
     */
    public static function get_saved_copy($country = null) {
        return rigpa_de_map_get_copy($country);
    }

    /**
     * Load the media library frame + picker script on our settings page only.
     *
     * @param string $hook Current admin page hook suffix.
     */
    public static function enqueue_assets($hook) {
        if ($hook !== 'tools_page_' . self::MENU_SLUG) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'rigpa-de-map-admin-media',
            RIGPA_DE_MAP_URL . 'assets/js/admin-media.js',
            array('jquery'),
            RIGPA_DE_MAP_VERSION,
            true
        );
    }

    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'rigpa-de-map'));
        }

        $default_country = rigpa_de_map_get_default_country();
        $editing_country = isset($_GET['country'])
            ? rigpa_de_map_sanitize_country(wp_unslash($_GET['country']))
            : $default_country;
        $countries = rigpa_de_map_get_country_registry();
        $locations = rigpa_de_map_get_locations($editing_country);
        $saved_urls = self::get_saved_urls($editing_country);
        $saved_images = self::get_saved_images($editing_country);
        $saved_copy = self::get_saved_copy($editing_country);
        $updated = isset($_GET['updated']) && $_GET['updated'] === '1';
        $country_slugs = array_keys($countries);

        ?>
        <div class="wrap rigpa-de-map-admin">
            <h1><?php esc_html_e('Maps', 'rigpa-de-map'); ?></h1>

            <?php if ($updated) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Settings saved.', 'rigpa-de-map'); ?></p>
                </div>
            <?php endif; ?>

            <div class="rigpa-de-map-admin__grid">
                <div class="rigpa-de-map-admin__panel">
                    <h2><?php esc_html_e('Shortcode', 'rigpa-de-map'); ?></h2>
                    <p><?php esc_html_e('Default country (site setting):', 'rigpa-de-map'); ?></p>
                    <code class="rigpa-de-map-admin__code">[rigpa_de_map]</code>
                    <p><?php esc_html_e('Specific country:', 'rigpa-de-map'); ?></p>
                    <code class="rigpa-de-map-admin__code">[rigpa_de_map country="uk"]</code>
                    <p><?php esc_html_e('All countries on one page:', 'rigpa-de-map'); ?></p>
                    <code class="rigpa-de-map-admin__code">[rigpa_de_map_all_countries]</code>
                    <p class="description">
                        <?php esc_html_e('Alias:', 'rigpa-de-map'); ?>
                        <code>[rigpa-de-map]</code>
                    </p>
                    <p class="description">
                        <?php
                        echo esc_html(
                            sprintf(
                                /* translators: %s: comma-separated country slugs */
                                __('Country slugs: %s', 'rigpa-de-map'),
                                implode(', ', $country_slugs)
                            )
                        );
                        ?>
                    </p>
                </div>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('rigpa_de_map_save'); ?>
                <input type="hidden" name="rigpa_de_map_save" value="1" />
                <input type="hidden" name="editing_country" value="<?php echo esc_attr($editing_country); ?>" />

                <?php self::render_country_field($editing_country, $default_country, $countries); ?>
                <?php self::render_copy_fields($saved_copy); ?>

                <h2>
                    <?php
                    echo esc_html(
                        sprintf(
                            /* translators: %s: country label */
                            __('Locations (%s)', 'rigpa-de-map'),
                            $countries[$editing_country]['label'] ?? $editing_country
                        )
                    );
                    ?>
                </h2>
                <p class="description">
                    <?php esc_html_e('Set the visible text, “View details” link and hover-card image for each location. URLs may be full (https://…) or site-relative (/centres/berlin). Leave the URL blank to show the label without a link.', 'rigpa-de-map'); ?>
                </p>

                <?php self::render_location_table(__('Left column', 'rigpa-de-map'), $locations['left'] ?? array(), $saved_urls, $saved_images, $editing_country); ?>
                <?php self::render_location_table(__('Right column', 'rigpa-de-map'), $locations['right'] ?? array(), $saved_urls, $saved_images, $editing_country); ?>

                <?php submit_button(__('Save settings', 'rigpa-de-map')); ?>
            </form>
        </div>
        <?php
        self::print_styles();
    }

    /**
     * @param string $editing_country
     * @param string $default_country
     * @param array<string, array{label: string}> $countries
     */
    private static function render_country_field($editing_country, $default_country, $countries) {
        $base_url = admin_url('tools.php?page=' . self::MENU_SLUG);
        ?>
        <h2><?php esc_html_e('Country', 'rigpa-de-map'); ?></h2>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="rigpa-de-map-edit-country"><?php esc_html_e('Country to edit', 'rigpa-de-map'); ?></label>
                    </th>
                    <td>
                        <select
                            id="rigpa-de-map-edit-country"
                            onchange="if (this.value) { window.location.href = this.value; }"
                        >
                            <?php foreach ($countries as $slug => $definition) : ?>
                                <?php
                                $url = add_query_arg('country', $slug, $base_url);
                                ?>
                                <option value="<?php echo esc_attr($url); ?>" <?php selected($editing_country, $slug); ?>>
                                    <?php echo esc_html($definition['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Switch countries to edit that country’s map text, images and links. Does not change the site default until you update Default country and save.', 'rigpa-de-map'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="rigpa-de-map-default-country"><?php esc_html_e('Default country', 'rigpa-de-map'); ?></label>
                    </th>
                    <td>
                        <select id="rigpa-de-map-default-country" name="default_country">
                            <?php foreach ($countries as $slug => $definition) : ?>
                                <option value="<?php echo esc_attr($slug); ?>" <?php selected($default_country, $slug); ?>>
                                    <?php echo esc_html($definition['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Used by [rigpa_de_map] when no country attribute is set. Override per page with [rigpa_de_map country="uk"].', 'rigpa-de-map'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * @param array<string, string> $copy
     */
    private static function render_copy_fields($copy) {
        $fields = array(
            'title'                => __('Heading', 'rigpa-de-map'),
            'subtitle'             => __('Subheading', 'rigpa-de-map'),
            'country_label'        => __('Hover-card country label', 'rigpa-de-map'),
            'view_details_label'   => __('Hover-card link label', 'rigpa-de-map'),
            'international_prefix' => __('International centres text before link', 'rigpa-de-map'),
            'international_link'   => __('International centres link text', 'rigpa-de-map'),
            'international_url'    => __('International centres URL', 'rigpa-de-map'),
        );
        ?>
        <h2><?php esc_html_e('Map text', 'rigpa-de-map'); ?></h2>
        <table class="form-table rigpa-de-map-admin__copy-table" role="presentation">
            <tbody>
                <?php foreach ($fields as $key => $label) : ?>
                    <tr>
                        <th scope="row">
                            <label for="rigpa-de-map-copy-<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label>
                        </th>
                        <td>
                            <input
                                type="<?php echo $key === 'international_url' ? 'url' : 'text'; ?>"
                                id="rigpa-de-map-copy-<?php echo esc_attr($key); ?>"
                                name="map_copy[<?php echo esc_attr($key); ?>]"
                                value="<?php echo esc_attr($copy[$key] ?? ''); ?>"
                                class="regular-text"
                            />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * @param string $title
     * @param array<int, array{id: string, name: string, region: string, url: string, coords: array{x: int, y: int}}> $items
     * @param array<string, string> $saved_urls
     * @param array<string, string> $saved_images
     * @param string $country
     */
    private static function render_location_table($title, $items, $saved_urls, $saved_images, $country) {
        ?>
        <h3><?php echo esc_html($title); ?></h3>
        <table class="widefat striped rigpa-de-map-admin__table">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Name', 'rigpa-de-map'); ?></th>
                    <th scope="col"><?php esc_html_e('Type', 'rigpa-de-map'); ?></th>
                    <th scope="col"><?php esc_html_e('Image', 'rigpa-de-map'); ?></th>
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
                            <input
                                type="text"
                                name="location_text[<?php echo esc_attr($id); ?>][name]"
                                value="<?php echo esc_attr($item['name']); ?>"
                                class="regular-text"
                            />
                            <br />
                            <span class="description"><?php echo esc_html($id); ?></span>
                        </td>
                        <td>
                            <input
                                type="text"
                                name="location_text[<?php echo esc_attr($id); ?>][region]"
                                value="<?php echo esc_attr($item['region']); ?>"
                                class="regular-text"
                                placeholder="<?php esc_attr_e('Centre, Group, etc.', 'rigpa-de-map'); ?>"
                            />
                        </td>
                        <td class="rigpa-de-map-admin__image-cell">
                            <?php self::render_location_image_field($id, $saved_images, $country); ?>
                        </td>
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

    /**
     * @param string               $id           Location slug.
     * @param array<string, string> $saved_images Saved image overrides.
     * @param string               $country      Country slug.
     */
    private static function render_location_image_field($id, $saved_images, $country) {
        $state       = rigpa_de_map_get_location_image_state($id, $saved_images);
        $default_url = rigpa_de_map_default_image_url($id, $country);
        $custom_url  = ($state === 'custom') ? (string) $saved_images[$id] : '';

        if ($state === 'default') {
            $preview_url = $default_url;
        } elseif ($state === 'custom') {
            $preview_url = $custom_url;
        } else {
            $preview_url = '';
        }

        $show_preview = $preview_url !== '';
        $show_remove  = $state === 'custom' || ($state === 'default' && $default_url !== '');
        ?>
        <div
            class="rigpa-de-map-image-field"
            data-default-url="<?php echo esc_attr($default_url); ?>"
        >
            <input
                type="hidden"
                class="rigpa-de-map-image-state"
                name="location_image_state[<?php echo esc_attr($id); ?>]"
                value="<?php echo esc_attr($state); ?>"
            />
            <input
                type="text"
                class="rigpa-de-map-image-input"
                name="location_image[<?php echo esc_attr($id); ?>]"
                value="<?php echo esc_attr($custom_url); ?>"
                style="display:none;"
            />
            <img
                class="rigpa-de-map-image-preview"
                src="<?php echo esc_url($preview_url); ?>"
                alt=""
                style="<?php echo $show_preview ? '' : 'display:none;'; ?>"
            />
            <span class="rigpa-de-map-image-buttons">
                <button
                    type="button"
                    class="button button-small rigpa-de-map-image-select"
                    data-title="<?php esc_attr_e('Select or upload image', 'rigpa-de-map'); ?>"
                    data-button="<?php esc_attr_e('Use this image', 'rigpa-de-map'); ?>"
                >
                    <?php esc_html_e('Select image', 'rigpa-de-map'); ?>
                </button>
                <button
                    type="button"
                    class="button button-small rigpa-de-map-image-remove"
                    style="<?php echo $show_remove ? '' : 'display:none;'; ?>"
                >
                    <?php esc_html_e('Remove', 'rigpa-de-map'); ?>
                </button>
            </span>
        </div>
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
            .rigpa-de-map-admin__table {
                margin-bottom: 24px;
            }
            .rigpa-de-map-admin__table td {
                vertical-align: middle;
            }
            .rigpa-de-map-admin__image-cell {
                min-width: 120px;
            }
            .rigpa-de-map-image-field {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }
            .rigpa-de-map-image-preview {
                max-width: 80px;
                max-height: 48px;
                width: auto;
                height: auto;
                object-fit: cover;
                border-radius: 4px;
                border: 1px solid #c3c4c7;
            }
            .rigpa-de-map-image-buttons {
                display: inline-flex;
                flex-wrap: wrap;
                gap: 6px;
            }
        </style>
        <?php
    }
}
