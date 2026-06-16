<?php
/**
 * Per-page header appearance overrides for Rigpa Mega Menu.
 *
 * Adds a small metabox on Pages/Posts so editors can override the
 * global header transparency and menu text colour without changing
 * the shortcode call or global plugin settings. Both the shortcode
 * and the menu-slot renderer pass through the `rigpa_mega_menu_is_transparent`
 * and `rigpa_mega_menu_text_color` filters; this class subscribes
 * to those filters to apply the per-page meta.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Rigpa_Mega_Menu_Page_Settings {

    const META_MODE  = '_rigpa_mega_menu_header_mode';
    const META_COLOR = '_rigpa_mega_menu_header_color';
    const NONCE      = 'rigpa_mega_menu_page_settings';

    const MODE_INHERIT     = 'inherit';
    const MODE_SOLID       = 'solid';
    const MODE_TRANSPARENT = 'transparent';

    public static function init() {
        add_action('add_meta_boxes', array(__CLASS__, 'register_metabox'));
        add_action('save_post', array(__CLASS__, 'save_metabox'), 10, 2);

        add_filter('rigpa_mega_menu_is_transparent', array(__CLASS__, 'filter_transparent'), 10, 2);
        add_filter('rigpa_mega_menu_text_color', array(__CLASS__, 'filter_color'), 10, 2);
    }

    public static function register_metabox() {
        foreach (array('page', 'post') as $post_type) {
            add_meta_box(
                'rigpa-mega-menu-page-settings',
                __('Mega Menu Header', 'rigpa-mega-menu'),
                array(__CLASS__, 'render_metabox'),
                $post_type,
                'side',
                'default'
            );
        }
    }

    public static function render_metabox($post) {
        $mode  = (string) get_post_meta($post->ID, self::META_MODE, true);
        $color = (string) get_post_meta($post->ID, self::META_COLOR, true);

        $allowed = array(self::MODE_INHERIT, self::MODE_SOLID, self::MODE_TRANSPARENT);
        if (!in_array($mode, $allowed, true)) {
            $mode = self::MODE_INHERIT;
        }

        wp_nonce_field(self::NONCE, self::NONCE);
        ?>
        <p>
            <label for="rigpa-mega-menu-header-mode">
                <strong><?php esc_html_e('Header style', 'rigpa-mega-menu'); ?></strong>
            </label><br>
            <select name="rigpa_mega_menu_header_mode" id="rigpa-mega-menu-header-mode" style="width:100%;">
                <option value="<?php echo esc_attr(self::MODE_INHERIT); ?>" <?php selected($mode, self::MODE_INHERIT); ?>>
                    <?php esc_html_e('Inherit global setting', 'rigpa-mega-menu'); ?>
                </option>
                <option value="<?php echo esc_attr(self::MODE_SOLID); ?>" <?php selected($mode, self::MODE_SOLID); ?>>
                    <?php esc_html_e('Solid (white background)', 'rigpa-mega-menu'); ?>
                </option>
                <option value="<?php echo esc_attr(self::MODE_TRANSPARENT); ?>" <?php selected($mode, self::MODE_TRANSPARENT); ?>>
                    <?php esc_html_e('Transparent (over hero)', 'rigpa-mega-menu'); ?>
                </option>
            </select>
        </p>
        <p>
            <label for="rigpa-mega-menu-header-color">
                <strong><?php esc_html_e('Menu text colour', 'rigpa-mega-menu'); ?></strong>
            </label><br>
            <input
                type="text"
                name="rigpa_mega_menu_header_color"
                id="rigpa-mega-menu-header-color"
                value="<?php echo esc_attr($color); ?>"
                placeholder="<?php esc_attr_e('e.g. #171717', 'rigpa-mega-menu'); ?>"
                style="width:100%;"
            >
            <span class="description">
                <?php esc_html_e('Hex colour. Leave blank to auto-derive: white when the header is transparent, dark when it is solid.', 'rigpa-mega-menu'); ?>
            </span>
        </p>
        <?php
    }

    public static function save_metabox($post_id, $post) {
        if (!isset($_POST[self::NONCE])) {
            return;
        }
        $nonce = sanitize_text_field(wp_unslash($_POST[self::NONCE]));
        if (!wp_verify_nonce($nonce, self::NONCE)) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $allowed = array(self::MODE_INHERIT, self::MODE_SOLID, self::MODE_TRANSPARENT);
        $mode    = isset($_POST['rigpa_mega_menu_header_mode'])
            ? sanitize_key(wp_unslash($_POST['rigpa_mega_menu_header_mode']))
            : self::MODE_INHERIT;
        if (!in_array($mode, $allowed, true)) {
            $mode = self::MODE_INHERIT;
        }

        if ($mode === self::MODE_INHERIT) {
            delete_post_meta($post_id, self::META_MODE);
        } else {
            update_post_meta($post_id, self::META_MODE, $mode);
        }

        $color_raw = isset($_POST['rigpa_mega_menu_header_color'])
            ? sanitize_text_field(wp_unslash($_POST['rigpa_mega_menu_header_color']))
            : '';
        $sanitized = $color_raw === '' ? '' : (string) sanitize_hex_color($color_raw);

        if ($sanitized === '') {
            delete_post_meta($post_id, self::META_COLOR);
        } else {
            update_post_meta($post_id, self::META_COLOR, $sanitized);
        }
    }

    /**
     * @param bool  $value
     * @param array $context
     * @return bool
     */
    public static function filter_transparent($value, $context = array()) {
        $post_id = self::resolve_post_id();
        if (!$post_id) {
            return $value;
        }

        $mode = (string) get_post_meta($post_id, self::META_MODE, true);
        if ($mode === self::MODE_SOLID) {
            return false;
        }
        if ($mode === self::MODE_TRANSPARENT) {
            return true;
        }

        return $value;
    }

    /**
     * @param string $value
     * @param array  $context
     * @return string
     */
    public static function filter_color($value, $context = array()) {
        $post_id = self::resolve_post_id();
        if (!$post_id) {
            return $value;
        }

        $color = (string) get_post_meta($post_id, self::META_COLOR, true);
        if ($color === '') {
            return $value;
        }

        $sanitized = sanitize_hex_color($color);

        return $sanitized ? $sanitized : $value;
    }

    private static function resolve_post_id() {
        if (!function_exists('is_singular') || !is_singular()) {
            return 0;
        }
        $id = function_exists('get_queried_object_id') ? (int) get_queried_object_id() : 0;
        return $id > 0 ? $id : 0;
    }
}
