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
                <h2><?php esc_html_e('Elementor header setup', 'rigpa-mega-menu'); ?></h2>
                <ol>
                    <li><?php esc_html_e('Go to Templates → Theme Builder → Header.', 'rigpa-mega-menu'); ?></li>
                    <li><?php esc_html_e('Create or edit a header template and set display conditions (e.g. Entire Site).', 'rigpa-mega-menu'); ?></li>
                    <li><?php esc_html_e('Add a Shortcode widget with [rigpa_mega_menu].', 'rigpa-mega-menu'); ?></li>
                    <li><?php esc_html_e('Disable the theme’s default header if it conflicts with your Elementor header.', 'rigpa-mega-menu'); ?></li>
                </ol>
            </div>

            <div class="rigpa-mega-menu-admin__panel">
                <h2><?php esc_html_e('Menu content', 'rigpa-mega-menu'); ?></h2>
                <p>
                    <?php esc_html_e('Menu labels, descriptions, and featured cards are defined in:', 'rigpa-mega-menu'); ?>
                    <code>wp-content/plugins/rigpa-mega-menu/includes/menus.php</code>
                </p>
                <p class="description">
                    <?php esc_html_e('Edit that file and redeploy to change menu content. URLs default to # until updated in code.', 'rigpa-mega-menu'); ?>
                </p>
            </div>
        </div>
        <?php
        self::print_styles();
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
        </style>
        <?php
    }
}
