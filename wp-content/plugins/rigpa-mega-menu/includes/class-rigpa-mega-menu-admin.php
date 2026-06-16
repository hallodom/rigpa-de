<?php
/**
 * WordPress admin UI for Rigpa Mega Menu (Tools → Mega Menu).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_Mega_Menu_Admin {

    const MENU_SLUG          = 'rigpa-mega-menu';
    const ACTION_SEED          = 'rigpa_mega_menu_seed';
    const ACTION_SAVE_SETTINGS = 'rigpa_mega_menu_save_settings';
    const ACTION_COPY_MAIN     = 'rigpa_mega_menu_copy_main';
    const ACTION_COPY_MAIN_DE  = 'rigpa_mega_menu_copy_main_de';
    const ACTION_ADD_DESCRIPTIONS    = 'rigpa_mega_menu_add_descriptions';
    const ACTION_CLEAR_DESCRIPTIONS  = 'rigpa_mega_menu_clear_descriptions';
    const ACTION_SAVE_FEATURED       = 'rigpa_mega_menu_save_featured';
    const ACTION_CLEAR         = 'rigpa_mega_menu_clear';

    public static function init() {
        if (!is_admin()) {
            return;
        }

        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
        add_action('admin_post_' . self::ACTION_SEED, array(__CLASS__, 'handle_seed'));
        add_action('admin_post_' . self::ACTION_SAVE_SETTINGS, array(__CLASS__, 'handle_save_settings'));
        add_action('admin_post_' . self::ACTION_COPY_MAIN, array(__CLASS__, 'handle_copy_main'));
        add_action('admin_post_' . self::ACTION_COPY_MAIN_DE, array(__CLASS__, 'handle_copy_main_de'));
        add_action('admin_post_' . self::ACTION_ADD_DESCRIPTIONS, array(__CLASS__, 'handle_add_descriptions'));
        add_action('admin_post_' . self::ACTION_CLEAR_DESCRIPTIONS, array(__CLASS__, 'handle_clear_descriptions'));
        add_action('admin_post_' . self::ACTION_SAVE_FEATURED, array(__CLASS__, 'handle_save_featured'));
        add_action('admin_post_' . self::ACTION_CLEAR, array(__CLASS__, 'handle_clear'));
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
            'rigpa-mega-menu-admin-media',
            RIGPA_MEGA_MENU_URL . 'assets/js/admin-media.js',
            array('jquery'),
            RIGPA_MEGA_MENU_VERSION,
            true
        );
    }

    /**
     * Render an image field: text input + media-library picker + preview.
     *
     * @param string $name  Form field name.
     * @param string $value Current image URL/path.
     */
    private static function render_image_field($name, $value) {
        $value = (string) $value;
        ?>
        <div class="rigpa-mega-menu-image-field">
            <input type="text" class="regular-text rigpa-mm-image-input"
                name="<?php echo esc_attr($name); ?>"
                value="<?php echo esc_attr($value); ?>"
                placeholder="https://... or /path/to/image.jpg"
                style="width: 100%;">
            <span class="rigpa-mm-image-buttons" style="display:inline-flex; gap:6px; margin-top:4px;">
                <button type="button" class="button button-small rigpa-mm-image-select"
                    data-title="<?php esc_attr_e('Select or upload image', 'rigpa-mega-menu'); ?>"
                    data-button="<?php esc_attr_e('Use this image', 'rigpa-mega-menu'); ?>">
                    <?php esc_html_e('Select image', 'rigpa-mega-menu'); ?>
                </button>
                <button type="button" class="button button-small rigpa-mm-image-remove"
                    style="<?php echo $value === '' ? 'display:none;' : ''; ?>">
                    <?php esc_html_e('Remove', 'rigpa-mega-menu'); ?>
                </button>
            </span>
            <img class="rigpa-mm-image-preview" src="<?php echo esc_url($value); ?>" alt=""
                style="max-width: 120px; max-height: 60px; margin-top: 6px; border-radius: 4px; display: <?php echo $value === '' ? 'none' : 'block'; ?>;">
        </div>
        <?php
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

    /**
     * Save appearance settings from the admin form.
     */
    public static function handle_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', 'rigpa-mega-menu'));
        }

        check_admin_referer(self::ACTION_SAVE_SETTINGS);

        $transparent = isset($_POST['rigpa_mega_menu_transparent'])
            && sanitize_text_field(wp_unslash($_POST['rigpa_mega_menu_transparent'])) === '1';

        Rigpa_Mega_Menu_Settings::set_transparent($transparent);

        if (isset($_POST['rigpa_mega_menu_text_color'])) {
            Rigpa_Mega_Menu_Settings::set_menu_text_color(
                sanitize_text_field(wp_unslash($_POST['rigpa_mega_menu_text_color']))
            );
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'             => self::MENU_SLUG,
                    'settings_updated' => '1',
                ),
                admin_url('tools.php')
            )
        );
        exit;
    }

    /**
     * Copy the site main menu into Mega Menu (English).
     */
    public static function handle_copy_main() {
        self::do_copy_main('english', self::ACTION_COPY_MAIN);
    }

    /**
     * Copy the site main menu into Mega Menu (German).
     */
    public static function handle_copy_main_de() {
        self::do_copy_main('german', self::ACTION_COPY_MAIN_DE);
    }

    /**
     * @param string $lang english|german
     * @param string $action nonce action name
     */
    private static function do_copy_main($lang, $action) {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', 'rigpa-mega-menu'));
        }

        check_admin_referer($action);

        $result = $lang === 'german'
            ? Rigpa_Mega_Menu_Duplicator::copy_main_to_german()
            : Rigpa_Mega_Menu_Duplicator::copy_main_to_english();

        if (is_wp_error($result)) {
            wp_safe_redirect(
                add_query_arg(
                    array(
                        'page'       => self::MENU_SLUG,
                        'copy_error' => rawurlencode($result->get_error_message()),
                    ),
                    admin_url('tools.php')
                )
            );
            exit;
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'         => self::MENU_SLUG,
                    'copy_success' => '1',
                    'copy_detail'  => rawurlencode(
                        sprintf(
                            '%s → %s (%d sections, %d links, %d descriptions, %d featured)',
                            $result['source_menu_name'],
                            $result['target_menu_name'],
                            (int) $result['sections'],
                            (int) $result['links'],
                            (int) ($result['descriptions_updated'] ?? 0),
                            (int) ($result['featured_updated'] ?? 0)
                        )
                    ),
                ),
                admin_url('tools.php')
            )
        );
        exit;
    }

    /**
     * Clear a mega menu location (remove items and unassign).
     */
    public static function handle_clear() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', 'rigpa-mega-menu'));
        }

        check_admin_referer(self::ACTION_CLEAR);

        $lang = isset($_POST['rigpa_mega_menu_clear_lang'])
            ? sanitize_key(wp_unslash($_POST['rigpa_mega_menu_clear_lang']))
            : '';

        $result = Rigpa_Mega_Menu_Seeder::clear_lang($lang);

        if (is_wp_error($result)) {
            wp_safe_redirect(
                add_query_arg(
                    array(
                        'page'        => self::MENU_SLUG,
                        'clear_error' => rawurlencode($result->get_error_message()),
                    ),
                    admin_url('tools.php')
                )
            );
            exit;
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'          => self::MENU_SLUG,
                    'clear_success' => '1',
                    'clear_detail'  => rawurlencode(
                        sprintf(
                            '%s: %d items removed',
                            $result['menu_name'],
                            (int) $result['items_removed']
                        )
                    ),
                ),
                admin_url('tools.php')
            )
        );
        exit;
    }

    /**
     * Apply default descriptions from includes/menus.php onto mega menu nav items.
     */
    public static function handle_add_descriptions() {
        self::handle_description_action('add');
    }

    /**
     * Clear descriptions from mega menu nav items matched to includes/menus.php.
     */
    public static function handle_clear_descriptions() {
        self::handle_description_action('clear');
    }

    /**
     * @param string $mode add|clear
     */
    private static function handle_description_action($mode) {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', 'rigpa-mega-menu'));
        }

        $action = $mode === 'clear' ? self::ACTION_CLEAR_DESCRIPTIONS : self::ACTION_ADD_DESCRIPTIONS;
        check_admin_referer($action);

        $results = $mode === 'clear'
            ? Rigpa_Mega_Menu_Description_Sync::clear_all()
            : Rigpa_Mega_Menu_Description_Sync::add_all();

        $featured_results = $mode === 'clear'
            ? Rigpa_Mega_Menu_Description_Sync::clear_featured_all()
            : Rigpa_Mega_Menu_Description_Sync::apply_featured_all();

        $messages = array();

        foreach ($results as $lang => $result) {
            if (is_wp_error($result)) {
                $messages[] = $lang . ': ' . $result->get_error_message();
                continue;
            }

            $featured_count = 0;
            if (isset($featured_results[$lang]) && !is_wp_error($featured_results[$lang])) {
                $featured_count = (int) $featured_results[$lang]['updated'];
            }

            $messages[] = sprintf(
                '%s: %d %s, %d unchanged, %d unmatched, %d featured %s',
                $result['menu_name'],
                (int) $result['updated'],
                $mode === 'clear' ? 'cleared' : 'added',
                (int) $result['unchanged'],
                (int) $result['skipped'],
                $featured_count,
                $mode === 'clear' ? 'removed' : 'applied'
            );
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'                => self::MENU_SLUG,
                    'descriptions_action' => $mode,
                    'descriptions_detail' => rawurlencode(implode(' | ', $messages)),
                ),
                admin_url('tools.php')
            )
        );
        exit;
    }


    /**
     * Save featured panel data from the admin editor.
     */
    public static function handle_save_featured() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', 'rigpa-mega-menu'));
        }

        check_admin_referer(self::ACTION_SAVE_FEATURED);

        $updated = 0;

        $has_featured = isset($_POST['rigpa_featured']) && is_array($_POST['rigpa_featured']);
        $has_centres  = isset($_POST['rigpa_centres']) && is_array($_POST['rigpa_centres']);

        if (!$has_featured && !$has_centres) {
            wp_safe_redirect(add_query_arg(array('page' => self::MENU_SLUG, 'featured_saved' => '0'), admin_url('tools.php')));
            exit;
        }

        if ($has_centres) {
            $updated += self::save_centres_from_post(wp_unslash($_POST['rigpa_centres']));
        }

        if (!$has_featured) {
            wp_safe_redirect(
                add_query_arg(
                    array('page' => self::MENU_SLUG, 'featured_saved' => (string) $updated),
                    admin_url('tools.php')
                )
            );
            exit;
        }

        foreach ($_POST['rigpa_featured'] as $item_id => $data) {
            $item_id = (int) $item_id;
            if ($item_id <= 0) {
                continue;
            }

            $title = isset($data['title']) ? Rigpa_Mega_Menu_Sanitize::text(wp_unslash($data['title'])) : '';
            $description = isset($data['description']) ? Rigpa_Mega_Menu_Sanitize::text(wp_unslash($data['description'])) : '';
            $image = isset($data['image']) ? esc_url_raw(wp_unslash($data['image'])) : '';
            $url = isset($data['url']) ? esc_url_raw(wp_unslash($data['url'])) : '';

            if ($title === '' && $description === '' && $image === '' && $url === '') {
                delete_post_meta($item_id, '_rigpa_mega_menu_featured');
                $updated++;
                continue;
            }

            if ($title === '') {
                continue;
            }

            $featured = array(
                'title'       => $title,
                'description' => $description,
                'image'       => $image,
                'url'         => $url,
            );

            update_post_meta($item_id, '_rigpa_mega_menu_featured', $featured);
            $updated++;
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'           => self::MENU_SLUG,
                    'featured_saved' => (string) $updated,
                ),
                admin_url('tools.php')
            )
        );
        exit;
    }

    /**
     * Persist featured-centre cards from the admin form.
     *
     * @param array<int, array<int, array<string, mixed>>> $centres_post Already wp_unslash()'d.
     * @return int Number of sections updated.
     */
    private static function save_centres_from_post(array $centres_post) {
        $updated = 0;

        foreach ($centres_post as $item_id => $cards) {
            $item_id = (int) $item_id;
            if ($item_id <= 0 || !is_array($cards)) {
                continue;
            }

            ksort($cards);
            $clean = Rigpa_Mega_Menu_Sanitize::featured_centres(array_values($cards));

            if ($clean === array()) {
                delete_post_meta($item_id, '_rigpa_mega_menu_featured_centres');
            } else {
                update_post_meta($item_id, '_rigpa_mega_menu_featured_centres', $clean);
            }

            $updated++;
        }

        return $updated;
    }

    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'rigpa-mega-menu'));
        }

        $css_path  = RIGPA_MEGA_MENU_PATH . 'assets/css/rigpa-mega-menu.css';
        $js_path   = RIGPA_MEGA_MENU_PATH . 'assets/js/rigpa-mega-menu.js';
        $assets_ok = file_exists($css_path) && file_exists($js_path);

        $menu_status       = self::get_menu_location_status();
        $transparent       = Rigpa_Mega_Menu_Settings::is_transparent();
        $menu_text_color   = Rigpa_Mega_Menu_Settings::get_menu_text_color($transparent);
        $main_menu         = Rigpa_Mega_Menu_Duplicator::resolve_main_menu();
        $seed_success      = isset($_GET['seed_success']) && $_GET['seed_success'] === '1';
        $copy_success      = isset($_GET['copy_success']) && $_GET['copy_success'] === '1';
        $descriptions_action = isset($_GET['descriptions_action'])
            ? sanitize_key(wp_unslash($_GET['descriptions_action']))
            : '';
        $clear_success     = isset($_GET['clear_success']) && $_GET['clear_success'] === '1';
        $copy_error        = isset($_GET['copy_error'])
            ? sanitize_text_field(rawurldecode(wp_unslash($_GET['copy_error'])))
            : '';
        $clear_error       = isset($_GET['clear_error'])
            ? sanitize_text_field(rawurldecode(wp_unslash($_GET['clear_error'])))
            : '';
        $settings_updated  = isset($_GET['settings_updated']) && $_GET['settings_updated'] === '1';
        $featured_saved    = isset($_GET['featured_saved']) ? (int) $_GET['featured_saved'] : -1;
        $seed_detail       = $seed_success && isset($_GET['seed_detail'])
            ? rawurldecode(sanitize_text_field(wp_unslash($_GET['seed_detail'])))
            : '';
        $copy_detail       = $copy_success && isset($_GET['copy_detail'])
            ? rawurldecode(sanitize_text_field(wp_unslash($_GET['copy_detail'])))
            : '';
        $descriptions_detail = in_array($descriptions_action, array('add', 'clear'), true) && isset($_GET['descriptions_detail'])
            ? rawurldecode(sanitize_text_field(wp_unslash($_GET['descriptions_detail'])))
            : '';
        $clear_detail      = $clear_success && isset($_GET['clear_detail'])
            ? rawurldecode(sanitize_text_field(wp_unslash($_GET['clear_detail'])))
            : '';

        ?>
        <div class="wrap rigpa-mega-menu-admin">
            <h1><?php esc_html_e('Mega Menu', 'rigpa-mega-menu'); ?></h1>

            <?php if ($settings_updated) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Settings saved.', 'rigpa-mega-menu'); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($featured_saved >= 0) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Featured panels saved.', 'rigpa-mega-menu'); ?></strong>
                        — <?php echo esc_html(sprintf(__('%d sections updated.', 'rigpa-mega-menu'), $featured_saved)); ?>
                    </p>
                </div>
            <?php endif; ?>

                        <?php if ($descriptions_action === 'add') : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Menu item descriptions added.', 'rigpa-mega-menu'); ?></strong>
                        <?php if ($descriptions_detail) : ?>
                            — <?php echo esc_html($descriptions_detail); ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($descriptions_action === 'clear') : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Menu item descriptions cleared.', 'rigpa-mega-menu'); ?></strong>
                        <?php if ($descriptions_detail) : ?>
                            — <?php echo esc_html($descriptions_detail); ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($clear_error) : ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html($clear_error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($clear_success) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Mega menu cleared.', 'rigpa-mega-menu'); ?></strong>
                        <?php if ($clear_detail) : ?>
                            — <?php echo esc_html($clear_detail); ?>
                        <?php endif; ?>
                        <?php esc_html_e('The plugin will use built-in fallback defaults until you copy, seed, or assign a menu again.', 'rigpa-mega-menu'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($copy_error) : ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html($copy_error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($copy_success) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Main menu copied to Mega Menu (English).', 'rigpa-mega-menu'); ?></strong>
                        <?php if ($copy_detail) : ?>
                            — <?php echo esc_html($copy_detail); ?>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>">
                            <?php esc_html_e('Edit in Appearance → Menus →', 'rigpa-mega-menu'); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>

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
                    <p class="description">
                        <?php esc_html_e('Per-page appearance override (for interior pages with light backgrounds):', 'rigpa-mega-menu'); ?>
                        <code>[rigpa_mega_menu transparent="false" color="#171717"]</code>
                    </p>
                    <p class="description">
                        <?php
                        esc_html_e(
                            'When the menu is mounted from a theme template part instead of the shortcode, set the override on the page itself via the "Mega Menu Header" metabox in the Page editor (sidebar).',
                            'rigpa-mega-menu'
                        );
                        ?>
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
                <h2><?php esc_html_e('Appearance', 'rigpa-mega-menu'); ?></h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="<?php echo esc_attr(self::ACTION_SAVE_SETTINGS); ?>">
                    <?php wp_nonce_field(self::ACTION_SAVE_SETTINGS); ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><?php esc_html_e('Transparent header', 'rigpa-mega-menu'); ?></th>
                            <td>
                                <label class="rigpa-mega-menu-admin__switch" for="rigpa-mega-menu-transparent">
                                    <input
                                        type="checkbox"
                                        id="rigpa-mega-menu-transparent"
                                        name="rigpa_mega_menu_transparent"
                                        value="1"
                                        <?php checked($transparent); ?>
                                    >
                                    <span class="rigpa-mega-menu-admin__switch-slider" aria-hidden="true"></span>
                                    <span class="rigpa-mega-menu-admin__switch-label">
                                        <?php echo $transparent
                                            ? esc_html__('On', 'rigpa-mega-menu')
                                            : esc_html__('Off', 'rigpa-mega-menu'); ?>
                                    </span>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('Default is off (solid white background, dark text) so interior pages over a light background work out of the box. Turn on for sites whose homepage / landing page sits over a hero image or video — or leave off globally and enable it per page via the Mega Menu Header metabox.', 'rigpa-mega-menu'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="rigpa-mega-menu-text-color">
                                    <?php esc_html_e('Menu item text colour', 'rigpa-mega-menu'); ?>
                                </label>
                            </th>
                            <td>
                                <input
                                    type="color"
                                    id="rigpa-mega-menu-text-color"
                                    name="rigpa_mega_menu_text_color"
                                    value="<?php echo esc_attr($menu_text_color); ?>"
                                >
                                <code class="rigpa-mega-menu-admin__code"><?php echo esc_html($menu_text_color); ?></code>
                                <p class="description">
                                    <?php esc_html_e('Colour for top-level items in the menu bar (default: white). Hover does not change the background.', 'rigpa-mega-menu'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(__('Save appearance', 'rigpa-mega-menu')); ?>
                </form>
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
                    <p>
                        <strong><?php esc_html_e('Copy main menu', 'rigpa-mega-menu'); ?></strong><br>
                        <?php if ($main_menu) : ?>
                            <span class="description">
                                <?php
                                echo esc_html(
                                    sprintf(
                                        /* translators: 1: menu name, 2: theme location slug or empty */
                                        __('Copies “%1$s”%2$s into the selected Mega Menu location and assigns it. Replaces any existing mega menu items. Menu text is stripped to plain text; theme CSS classes and HTML from the source menu are not copied.', 'rigpa-mega-menu'),
                                        $main_menu['name'],
                                        $main_menu['location'] !== ''
                                            ? ' (' . sprintf(__('theme location: %s', 'rigpa-mega-menu'), $main_menu['location']) . ')'
                                            : ''
                                    )
                                );
                                ?>
                            </span>
                        <?php else : ?>
                            <span class="description rigpa-mega-menu-admin__status rigpa-mega-menu-admin__status--warn">
                                <?php esc_html_e('No main menu detected. Assign a menu to Primary/Main under Appearance → Menus, or name one “Main Menu”.', 'rigpa-mega-menu'); ?>
                            </span>
                        <?php endif; ?>
                    </p>
                    <div class="rigpa-mega-menu-admin__button-row" style="margin-bottom: 1.25rem;">
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="<?php echo esc_attr(self::ACTION_COPY_MAIN); ?>">
                            <?php wp_nonce_field(self::ACTION_COPY_MAIN); ?>
                            <?php
                            submit_button(
                                __('Copy Main Menu → English', 'rigpa-mega-menu'),
                                'secondary',
                                'submit',
                                false,
                                $main_menu ? array() : array('disabled' => 'disabled')
                            );
                            ?>
                        </form>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="<?php echo esc_attr(self::ACTION_COPY_MAIN_DE); ?>">
                            <?php wp_nonce_field(self::ACTION_COPY_MAIN_DE); ?>
                            <?php
                            submit_button(
                                __('Copy Main Menu → German', 'rigpa-mega-menu'),
                                'secondary',
                                'submit',
                                false,
                                $main_menu ? array() : array('disabled' => 'disabled')
                            );
                            ?>
                        </form>
                    </div>

                    <div style="margin-top: 1rem;">
                        <p>
                            <strong><?php esc_html_e('Menu item descriptions', 'rigpa-mega-menu'); ?></strong><br>
                            <span class="description">
                                <?php
                                echo esc_html(
                                    sprintf(
                                        /* translators: %s: plugin-relative path to the static menu data file */
                                        __('Add or clear the grey subtitle line on dropdown links using the default text in %s. Items are matched to the closest menu item name (punctuation, wording, and shared words).', 'rigpa-mega-menu'),
                                        Rigpa_Mega_Menu_Description_Sync::SOURCE_FILES
                                    )
                                );
                                ?>
                            </span>
                        </p>
                        <div class="rigpa-mega-menu-admin__button-row">
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="<?php echo esc_attr(self::ACTION_ADD_DESCRIPTIONS); ?>">
                                <?php wp_nonce_field(self::ACTION_ADD_DESCRIPTIONS); ?>
                                <?php submit_button(__('Add Descriptions', 'rigpa-mega-menu'), 'secondary', 'submit', false); ?>
                            </form>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="<?php echo esc_attr(self::ACTION_CLEAR_DESCRIPTIONS); ?>">
                                <?php wp_nonce_field(self::ACTION_CLEAR_DESCRIPTIONS); ?>
                                <?php submit_button(__('Clear Descriptions', 'rigpa-mega-menu'), 'delete', 'submit', false); ?>
                            </form>
                        </div>
                    </div>

                    <p style="margin-top: 1.5rem;">
                        <strong><?php esc_html_e('Clear mega menus', 'rigpa-mega-menu'); ?></strong><br>
                        <span class="description">
                            <?php esc_html_e('Removes all items from the selected mega menu and unassigns it from the plugin location. The frontend falls back to built-in defaults.', 'rigpa-mega-menu'); ?>
                        </span>
                    </p>
                    <div class="rigpa-mega-menu-admin__button-row">
                        <?php foreach (array('english' => __('Clear English', 'rigpa-mega-menu'), 'german' => __('Clear German', 'rigpa-mega-menu')) as $lang_key => $label) : ?>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="<?php echo esc_attr(self::ACTION_CLEAR); ?>">
                                <input type="hidden" name="rigpa_mega_menu_clear_lang" value="<?php echo esc_attr($lang_key); ?>">
                                <?php wp_nonce_field(self::ACTION_CLEAR); ?>
                                <?php submit_button($label, 'delete', 'submit', false); ?>
                            </form>
                        <?php endforeach; ?>
                    </div>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #f0f0f1;">
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
                <h2><?php esc_html_e('Featured Panels', 'rigpa-mega-menu'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Edit the image sidebar that appears in mega menu dropdowns. Each top-level section heading can have a featured panel with an image, title, description, and link. Leave all fields blank to remove.', 'rigpa-mega-menu'); ?>
                </p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="<?php echo esc_attr(self::ACTION_SAVE_FEATURED); ?>">
                    <?php wp_nonce_field(self::ACTION_SAVE_FEATURED); ?>
                    <?php
                    $featured_sections = self::get_featured_sections();
                    if (empty($featured_sections)) :
                    ?>
                        <p class="rigpa-mega-menu-admin__status rigpa-mega-menu-admin__status--warn">
                            <?php esc_html_e('No mega menus assigned yet. Copy or seed menus first.', 'rigpa-mega-menu'); ?>
                        </p>
                    <?php else : ?>
                        <?php foreach ($featured_sections as $group) : ?>
                            <h3 style="margin: 1.5rem 0 0.5rem; font-size: 13px; text-transform: uppercase; color: #646970;">
                                <?php echo esc_html($group['menu_name']); ?>
                            </h3>
                            <table class="widefat striped rigpa-mega-menu-admin__table" style="margin-bottom: 1rem;">
                                <thead>
                                    <tr>
                                        <th style="width: 140px;"><?php esc_html_e('Section', 'rigpa-mega-menu'); ?></th>
                                        <th><?php esc_html_e('Title', 'rigpa-mega-menu'); ?></th>
                                        <th><?php esc_html_e('Description', 'rigpa-mega-menu'); ?></th>
                                        <th><?php esc_html_e('Image URL', 'rigpa-mega-menu'); ?></th>
                                        <th><?php esc_html_e('Link URL', 'rigpa-mega-menu'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($group['sections'] as $section) : ?>
                                        <tr>
                                            <td><strong><?php echo esc_html($section['label']); ?></strong></td>
                                            <td>
                                                <input type="text" class="regular-text"
                                                    name="rigpa_featured[<?php echo esc_attr($section['item_id']); ?>][title]"
                                                    value="<?php echo esc_attr($section['featured']['title'] ?? ''); ?>"
                                                    placeholder="<?php esc_attr_e('Featured title', 'rigpa-mega-menu'); ?>"
                                                    style="width: 100%;">
                                            </td>
                                            <td>
                                                <input type="text" class="regular-text"
                                                    name="rigpa_featured[<?php echo esc_attr($section['item_id']); ?>][description]"
                                                    value="<?php echo esc_attr($section['featured']['description'] ?? ''); ?>"
                                                    placeholder="<?php esc_attr_e('Short description', 'rigpa-mega-menu'); ?>"
                                                    style="width: 100%;">
                                            </td>
                                            <td>
                                                <?php
                                                self::render_image_field(
                                                    'rigpa_featured[' . (int) $section['item_id'] . '][image]',
                                                    (string) ($section['featured']['image'] ?? '')
                                                );
                                                ?>
                                            </td>
                                            <td>
                                                <input type="text" class="regular-text"
                                                    name="rigpa_featured[<?php echo esc_attr($section['item_id']); ?>][url]"
                                                    value="<?php echo esc_attr($section['featured']['url'] ?? ''); ?>"
                                                    placeholder="/page-slug/"
                                                    style="width: 100%;">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php foreach ($group['sections'] as $section) :
                                $show_centres = !empty($section['centres'])
                                    || in_array($section['label'], array('Groups', 'Gruppen'), true);
                                if (!$show_centres) {
                                    continue;
                                }
                                $cards = $section['centres'];
                            ?>
                                <h4 style="margin: 1.25rem 0 0.5rem; font-size: 12px; text-transform: uppercase; color: #646970;">
                                    <?php echo esc_html(sprintf(__('Featured centres — %s', 'rigpa-mega-menu'), $section['label'])); ?>
                                </h4>
                                <p class="description" style="margin-top:0;">
                                    <?php esc_html_e('Two compact cards shown in the right column of this dropdown. Leave a card’s title blank to remove it.', 'rigpa-mega-menu'); ?>
                                </p>
                                <table class="widefat striped rigpa-mega-menu-admin__table" style="margin-bottom: 1rem;">
                                    <thead>
                                        <tr>
                                            <th style="width: 160px;"><?php esc_html_e('Image', 'rigpa-mega-menu'); ?></th>
                                            <th><?php esc_html_e('Title', 'rigpa-mega-menu'); ?></th>
                                            <th><?php esc_html_e('Description', 'rigpa-mega-menu'); ?></th>
                                            <th><?php esc_html_e('Link URL', 'rigpa-mega-menu'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($i = 0; $i < 2; $i++) :
                                            $card = isset($cards[$i]) && is_array($cards[$i])
                                                ? $cards[$i]
                                                : array('title' => '', 'description' => '', 'image' => '', 'url' => '');
                                            $base = 'rigpa_centres[' . (int) $section['item_id'] . '][' . $i . ']';
                                        ?>
                                            <tr>
                                                <td>
                                                    <?php self::render_image_field($base . '[image]', (string) ($card['image'] ?? '')); ?>
                                                </td>
                                                <td>
                                                    <input type="text" class="regular-text"
                                                        name="<?php echo esc_attr($base); ?>[title]"
                                                        value="<?php echo esc_attr($card['title'] ?? ''); ?>"
                                                        placeholder="<?php esc_attr_e('Centre name', 'rigpa-mega-menu'); ?>"
                                                        style="width: 100%;">
                                                </td>
                                                <td>
                                                    <input type="text" class="regular-text"
                                                        name="<?php echo esc_attr($base); ?>[description]"
                                                        value="<?php echo esc_attr($card['description'] ?? ''); ?>"
                                                        placeholder="<?php esc_attr_e('Short description', 'rigpa-mega-menu'); ?>"
                                                        style="width: 100%;">
                                                </td>
                                                <td>
                                                    <input type="text" class="regular-text"
                                                        name="<?php echo esc_attr($base); ?>[url]"
                                                        value="<?php echo esc_attr($card['url'] ?? ''); ?>"
                                                        placeholder="https://..."
                                                        style="width: 100%;">
                                                </td>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <?php submit_button(__('Save Featured Panels', 'rigpa-mega-menu')); ?>
                    <?php endif; ?>
                </form>
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
     * Get top-level section items with their current featured meta for the admin editor.
     *
     * @return array<int, array{menu_name: string, sections: array<int, array{item_id: int, label: string, featured: array{title: string, description: string, image: string, url: string}}>}>
     */
    private static function get_featured_sections() {
        $groups = array();
        $locations = get_nav_menu_locations();

        foreach (array('rigpa-mega-menu-en' => 'english', 'rigpa-mega-menu-de' => 'german') as $location => $lang) {
            $menu_id = isset($locations[$location]) ? (int) $locations[$location] : 0;
            $menu = $menu_id > 0 ? wp_get_nav_menu_object($menu_id) : false;
            if (!$menu instanceof WP_Term) {
                continue;
            }

            $items = wp_get_nav_menu_items((int) $menu->term_id, array('update_post_term_cache' => false));
            if (!is_array($items)) {
                continue;
            }

            $sections = array();
            foreach ($items as $item) {
                if (!$item instanceof WP_Post) {
                    continue;
                }
                if ((int) $item->menu_item_parent !== 0) {
                    continue;
                }

                $featured = get_post_meta((int) $item->ID, '_rigpa_mega_menu_featured', true);
                if (!is_array($featured)) {
                    $featured = array('title' => '', 'description' => '', 'image' => '', 'url' => '');
                }

                $centres_meta = get_post_meta((int) $item->ID, '_rigpa_mega_menu_featured_centres', true);
                $centres = array();
                if (is_array($centres_meta)) {
                    foreach ($centres_meta as $centre) {
                        if (!is_array($centre)) {
                            continue;
                        }
                        $centres[] = array(
                            'title'       => (string) ($centre['title'] ?? ''),
                            'description' => (string) ($centre['description'] ?? ''),
                            'image'       => (string) ($centre['image'] ?? ''),
                            'url'         => (string) ($centre['url'] ?? ''),
                        );
                    }
                }

                $sections[] = array(
                    'item_id'  => (int) $item->ID,
                    'label'    => Rigpa_Mega_Menu_Sanitize::text((string) $item->title),
                    'featured' => array(
                        'title'       => (string) ($featured['title'] ?? ''),
                        'description' => (string) ($featured['description'] ?? ''),
                        'image'       => (string) ($featured['image'] ?? ''),
                        'url'         => (string) ($featured['url'] ?? ''),
                    ),
                    'centres'  => $centres,
                );
            }

            if (!empty($sections)) {
                $groups[] = array(
                    'menu_name' => (string) $menu->name,
                    'sections'  => $sections,
                );
            }
        }

        return $groups;
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
            .rigpa-mega-menu-admin__switch {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
                user-select: none;
            }
            .rigpa-mega-menu-admin__switch input {
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
            }
            .rigpa-mega-menu-admin__switch-slider {
                position: relative;
                display: inline-block;
                width: 44px;
                height: 24px;
                background: #c3c4c7;
                border-radius: 24px;
                transition: background 0.2s;
            }
            .rigpa-mega-menu-admin__switch-slider::before {
                content: "";
                position: absolute;
                width: 18px;
                height: 18px;
                left: 3px;
                top: 3px;
                background: #fff;
                border-radius: 50%;
                transition: transform 0.2s;
            }
            .rigpa-mega-menu-admin__switch input:checked + .rigpa-mega-menu-admin__switch-slider {
                background: #2271b1;
            }
            .rigpa-mega-menu-admin__switch input:checked + .rigpa-mega-menu-admin__switch-slider::before {
                transform: translateX(20px);
            }
            .rigpa-mega-menu-admin__switch-label {
                font-weight: 500;
            }
            .rigpa-mega-menu-admin__button-row {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 8px;
            }
        </style>
        <?php
    }
}
