<?php
/**
 * Plugin Name: SeggWat Feedback
 * Plugin URI: https://seggwat.com
 * Description: Collect user feedback directly from your WordPress site with a beautiful, customizable widget. Features: customizable button color & position, multi-language support (EN/DE/SV), per-page control, and white-label options. Requires a free SeggWat account and Project Key from seggwat.com to display the widget.
 * Version: 1.5.0
 * Author: Hauke Jung
 * Author URI: https://seggwat.com
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: seggwat-feedback
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) { exit; }

final class SeggWat_Feedback_Plugin {
    // Option keys
    const OPT_PROJECT_KEY      = 'seggwat_project_key';
    const OPT_COLOR            = 'seggwat_button_color';
    const OPT_POSITION         = 'seggwat_button_position';
    const OPT_DEFAULT_BEHAVIOR = 'seggwat_default_behavior'; // 'inject' | 'skip'
    const OPT_LANGUAGE         = 'seggwat_language';
    const OPT_SHOW_POWERED_BY  = 'seggwat_show_powered_by';

    // Post meta (new + legacy)
    const META_BEHAVIOR        = '_seggwat_widget_behavior'; // 'inherit' | 'enable' | 'disable' (absent/empty = inherit)
    const META_DISABLE_LEGACY  = '_seggwat_disable_widget';  // bool-like "1"/"" (pre v1.3.0)

    // Misc
    const SLUG    = 'seggwat-feedback';
    const HANDLE  = 'seggwat-widget';

    // Defaults
    const DEFAULT_SCRIPT_SRC   = 'https://seggwat.com/static/widgets/v1/seggwat-feedback.js';
    const DEFAULT_BUTTON_COLOR = '#10b981';
    const DEFAULT_BUTTON_POS   = 'right-side'; // allowed: right-side | bottom-right | icon-only
    const DEFAULT_BEHAVIOR     = 'inject';     // site-wide default
    const DEFAULT_LANGUAGE     = 'auto';       // allowed: auto | en | de | sv
    const SCRIPT_ID            = 'widget-script';

    public function __construct() {
        add_action('plugins_loaded', [$this, 'i18n']);

        // Settings UI
        add_action('admin_init',     [$this, 'register_settings']);
        add_action('admin_menu',     [$this, 'add_settings_page']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_settings_link']);
        add_action('admin_notices',  [$this, 'maybe_admin_notice']);

        // Meta box + save
        add_action('init',           [$this, 'register_post_meta']);
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post',      [$this, 'save_meta_box']);

        // Front-end injection
        add_action('wp_enqueue_scripts', [$this, 'enqueue_widget']);
    }

    public function i18n() {
        load_plugin_textdomain('seggwat-feedback', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /** SETTINGS */
    public function register_settings() {
        register_setting(self::SLUG . '_settings', self::OPT_PROJECT_KEY, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);

        register_setting(self::SLUG . '_settings', self::OPT_COLOR, [
            'type'              => 'string',
            'sanitize_callback' => [$this, 'sanitize_color'],
            'default'           => self::DEFAULT_BUTTON_COLOR,
        ]);

        register_setting(self::SLUG . '_settings', self::OPT_POSITION, [
            'type'              => 'string',
            'sanitize_callback' => [$this, 'sanitize_position'],
            'default'           => self::DEFAULT_BUTTON_POS,
        ]);

        register_setting(self::SLUG . '_settings', self::OPT_DEFAULT_BEHAVIOR, [
            'type'              => 'string',
            'sanitize_callback' => [$this, 'sanitize_default_behavior'],
            'default'           => self::DEFAULT_BEHAVIOR,
        ]);

        register_setting(self::SLUG . '_settings', self::OPT_LANGUAGE, [
            'type'              => 'string',
            'sanitize_callback' => [$this, 'sanitize_language'],
            'default'           => self::DEFAULT_LANGUAGE,
        ]);

        register_setting(self::SLUG . '_settings', self::OPT_SHOW_POWERED_BY, [
            'type'              => 'boolean',
            'sanitize_callback' => [$this, 'sanitize_boolean'],
            'default'           => true,
        ]);

        add_settings_section(
            self::SLUG . '_main',
            __('SeggWat Widget', 'seggwat-feedback'),
            fn() => print '<p>' . esc_html__('Configure your SeggWat widget. You can override behavior per post/page.', 'seggwat-feedback') . '</p>',
            self::SLUG
        );

        // Project Key
        add_settings_field(
            self::OPT_PROJECT_KEY,
            __('Project Key', 'seggwat-feedback') . ' <span style="color:#dc2626;">*</span>',
            function () {
                $val = get_option(self::OPT_PROJECT_KEY, '');
                printf(
                    '<input type="text" name="%1$s" value="%2$s" class="regular-text" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" />',
                    esc_attr(self::OPT_PROJECT_KEY), esc_attr($val)
                );
                echo '<p class="description">';
                printf(
                    wp_kses(
                        __('Required. Get your Project Key from the <a href="%s" target="_blank">SeggWat Dashboard</a>. Create a free account if you don\'t have one yet.', 'seggwat-feedback'),
                        ['a' => ['href' => [], 'target' => []]]
                    ),
                    'https://seggwat.com'
                );
                echo '</p>';
            },
            self::SLUG, self::SLUG . '_main'
        );

        // Button Color
        add_settings_field(
            self::OPT_COLOR,
            __('Button Color', 'seggwat-feedback'),
            function () {
                $val = get_option(self::OPT_COLOR, self::DEFAULT_BUTTON_COLOR);
                printf(
                    '<input type="text" name="%1$s" value="%2$s" class="regular-text" placeholder="#10b981" />',
                    esc_attr(self::OPT_COLOR), esc_attr($val)
                );
                echo '<p class="description">' . esc_html__('Hex color like #10b981.', 'seggwat-feedback') . '</p>';
            },
            self::SLUG, self::SLUG . '_main'
        );

        // Button Position
        add_settings_field(
            self::OPT_POSITION,
            __('Button Position', 'seggwat-feedback'),
            function () {
                $val = get_option(self::OPT_POSITION, self::DEFAULT_BUTTON_POS);
                $options = [
                    'right-side'   => __('Right side', 'seggwat-feedback'),
                    'bottom-right' => __('Bottom right', 'seggwat-feedback'),
                    'icon-only'    => __('Icon only (compact)', 'seggwat-feedback'),
                ];
                echo '<select name="' . esc_attr(self::OPT_POSITION) . '">';
                foreach ($options as $k => $label) {
                    printf('<option value="%1$s" %2$s>%3$s</option>', esc_attr($k), selected($val, $k, false), esc_html($label));
                }
                echo '</select>';
            },
            self::SLUG, self::SLUG . '_main'
        );

        // Default site behavior
        add_settings_field(
            self::OPT_DEFAULT_BEHAVIOR,
            __('Default Behavior', 'seggwat-feedback'),
            function () {
                $val = get_option(self::OPT_DEFAULT_BEHAVIOR, self::DEFAULT_BEHAVIOR);
                ?>
                <fieldset>
                    <label>
                        <input type="radio" name="<?php echo esc_attr(self::OPT_DEFAULT_BEHAVIOR); ?>" value="inject" <?php checked($val, 'inject'); ?> />
                        <?php echo esc_html__('Inject on all pages by default', 'seggwat-feedback'); ?>
                    </label><br/>
                    <label>
                        <input type="radio" name="<?php echo esc_attr(self::OPT_DEFAULT_BEHAVIOR); ?>" value="skip" <?php checked($val, 'skip'); ?> />
                        <?php echo esc_html__('Do not inject unless forced per post/page', 'seggwat-feedback'); ?>
                    </label>
                </fieldset>
                <p class="description"><?php echo esc_html__('Per post/page you can choose: Default, Force enable, or Disable.', 'seggwat-feedback'); ?></p>
                <?php
            },
            self::SLUG, self::SLUG . '_main'
        );

        // Widget Language
        add_settings_field(
            self::OPT_LANGUAGE,
            __('Widget Language', 'seggwat-feedback'),
            function () {
                $val = get_option(self::OPT_LANGUAGE, self::DEFAULT_LANGUAGE);
                $options = [
                    'auto' => __('Auto-detect from browser', 'seggwat-feedback'),
                    'en'   => __('English', 'seggwat-feedback'),
                    'de'   => __('German (Deutsch)', 'seggwat-feedback'),
                    'sv'   => __('Swedish (Svenska)', 'seggwat-feedback'),
                ];
                echo '<select name="' . esc_attr(self::OPT_LANGUAGE) . '">';
                foreach ($options as $k => $label) {
                    printf('<option value="%1$s" %2$s>%3$s</option>', esc_attr($k), selected($val, $k, false), esc_html($label));
                }
                echo '</select>';
                echo '<p class="description">' . esc_html__('Choose the language for the feedback widget interface.', 'seggwat-feedback') . '</p>';
            },
            self::SLUG, self::SLUG . '_main'
        );

        // Show Powered By
        add_settings_field(
            self::OPT_SHOW_POWERED_BY,
            __('Show Branding', 'seggwat-feedback'),
            function () {
                $val = (bool) get_option(self::OPT_SHOW_POWERED_BY, true);
                ?>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr(self::OPT_SHOW_POWERED_BY); ?>" value="1" <?php checked($val, true); ?> />
                    <?php echo esc_html__('Show "Powered by SeggWat" in widget footer', 'seggwat-feedback'); ?>
                </label>
                <p class="description">
                    <?php echo esc_html__('Uncheck to hide the branding for white-label implementations or strict brand requirements.', 'seggwat-feedback'); ?>
                </p>
                <?php
            },
            self::SLUG, self::SLUG . '_main'
        );
    }

    // Sanitizers
    public function sanitize_color($value) {
        $san = sanitize_hex_color($value);
        return $san ?: self::DEFAULT_BUTTON_COLOR;
    }
    public function sanitize_position($value) {
        $allowed = ['right-side', 'bottom-right', 'icon-only'];
        return in_array($value, $allowed, true) ? $value : self::DEFAULT_BUTTON_POS;
    }
    public function sanitize_default_behavior($value) {
        $allowed = ['inject', 'skip'];
        return in_array($value, $allowed, true) ? $value : self::DEFAULT_BEHAVIOR;
    }
    public function sanitize_language($value) {
        $allowed = ['auto', 'en', 'de', 'sv'];
        return in_array($value, $allowed, true) ? $value : self::DEFAULT_LANGUAGE;
    }
    public function sanitize_boolean($value) {
        return (bool) $value;
    }

    public function add_settings_page() {
        add_options_page(
            __('SeggWat Feedback', 'seggwat-feedback'),
            __('SeggWat Feedback', 'seggwat-feedback'),
            'manage_options',
            self::SLUG,
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) { return; } ?>
        <div class="wrap">
            <h1><?php echo esc_html__('SeggWat Feedback', 'seggwat-feedback'); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::SLUG . '_settings');
                do_settings_sections(self::SLUG);
                submit_button(__('Save Changes', 'seggwat-feedback'));
                ?>
            </form>
        </div>
    <?php }

    public function add_settings_link(array $links) : array {
        $url = admin_url('options-general.php?page=' . self::SLUG);
        array_unshift($links, '<a href="'.esc_url($url).'">'.esc_html__('Settings','seggwat-feedback').'</a>');
        return $links;
    }

    public function maybe_admin_notice() {
        if (!current_user_can('manage_options')) { return; }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $on_plugins_or_settings = $screen && in_array($screen->id, ['plugins', 'settings_page_' . self::SLUG], true);

        if ($on_plugins_or_settings && !get_option(self::OPT_PROJECT_KEY)) {
            $settings_url = esc_url(admin_url('options-general.php?page=' . self::SLUG));
            echo '<div class="notice notice-warning"><p>'
               . sprintf(
                   wp_kses(__('SeggWat Feedback: please <a href="%1$s">set your project key</a> to enable the widget.', 'seggwat-feedback'), ['a'=>['href'=>[]]]),
                   $settings_url
               )
               . '</p></div>';
        }
    }

    /** POST META (3-state control) */
    public function register_post_meta() {
        $post_types = apply_filters('seggwat/post_types', ['post', 'page']);
        foreach ($post_types as $pt) {
            register_post_meta($pt, self::META_BEHAVIOR, [
                'type'          => 'string',
                'single'        => true,
                'default'       => '',
                'show_in_rest'  => true,
                'auth_callback' => function() { return current_user_can('edit_posts'); },
            ]);
        }
    }

    public function add_meta_box() {
        $post_types = apply_filters('seggwat/post_types', ['post', 'page']);
        foreach ($post_types as $pt) {
            add_meta_box(
                'seggwat_meta',
                __('SeggWat Feedback', 'seggwat-feedback'),
                [$this, 'render_meta_box'],
                $pt,
                'side',
                'default'
            );
        }
    }

    public function render_meta_box($post) {
        wp_nonce_field('seggwat_meta_box', 'seggwat_meta_nonce');

        // Current value
        $value = (string) get_post_meta($post->ID, self::META_BEHAVIOR, true);
        if ($value === '' && get_post_meta($post->ID, self::META_DISABLE_LEGACY, true)) {
            $value = 'disable'; // legacy compatibility
        }

        $opt_default = get_option(self::OPT_DEFAULT_BEHAVIOR, self::DEFAULT_BEHAVIOR);
        ?>
        <p><strong><?php echo esc_html__('Widget behavior', 'seggwat-feedback'); ?></strong></p>
        <label style="display:block;margin-bottom:4px;">
            <input type="radio" name="<?php echo esc_attr(self::META_BEHAVIOR); ?>" value="" <?php checked($value, ''); ?> />
            <?php
            printf(
                /* translators: %s is "Inject" or "Do not inject" */
                esc_html__('Default (site setting: %s)', 'seggwat-feedback'),
                $opt_default === 'inject' ? esc_html__('Inject', 'seggwat-feedback') : esc_html__('Do not inject', 'seggwat-feedback')
            );
            ?>
        </label>
        <label style="display:block;margin-bottom:4px;">
            <input type="radio" name="<?php echo esc_attr(self::META_BEHAVIOR); ?>" value="enable" <?php checked($value, 'enable'); ?> />
            <?php echo esc_html__('Force enable', 'seggwat-feedback'); ?>
        </label>
        <label style="display:block;">
            <input type="radio" name="<?php echo esc_attr(self::META_BEHAVIOR); ?>" value="disable" <?php checked($value, 'disable'); ?> />
            <?php echo esc_html__('Disable', 'seggwat-feedback'); ?>
        </label>
        <p class="description"><?php echo esc_html__('Choose how the widget behaves on this post/page.', 'seggwat-feedback'); ?></p>
        <?php
    }

    public function save_meta_box($post_id) {
        if (!isset($_POST['seggwat_meta_nonce']) || !wp_verify_nonce($_POST['seggwat_meta_nonce'], 'seggwat_meta_box')) { return; }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
        if (wp_is_post_revision($post_id)) { return; }
        if (!current_user_can('edit_post', $post_id)) { return; }

        $raw = isset($_POST[self::META_BEHAVIOR]) ? (string) $_POST[self::META_BEHAVIOR] : '';
        $val = in_array($raw, ['', 'enable', 'disable'], true) ? $raw : '';

        if ($val === '') {
            delete_post_meta($post_id, self::META_BEHAVIOR);
        } else {
            update_post_meta($post_id, self::META_BEHAVIOR, $val);
        }

        // Clean legacy key if present (we store behavior now)
        delete_post_meta($post_id, self::META_DISABLE_LEGACY);
    }

    /** FRONT-END INJECTION */
    public function enqueue_widget() {
        if (is_admin()) { return; }

        $project_key = trim((string) get_option(self::OPT_PROJECT_KEY, ''));
        if ($project_key === '') { return; } // don’t inject until configured

        // Compute effective behavior
        $default_behavior = $this->sanitize_default_behavior(get_option(self::OPT_DEFAULT_BEHAVIOR, self::DEFAULT_BEHAVIOR));
        $should_inject = ($default_behavior === 'inject');

        if (is_singular()) {
            $post = get_post();
            if ($post) {
                $behavior = (string) get_post_meta($post->ID, self::META_BEHAVIOR, true);

                // Legacy: respect old checkbox if new behavior not set
                if ($behavior === '' && get_post_meta($post->ID, self::META_DISABLE_LEGACY, true)) {
                    $behavior = 'disable';
                }

                if ($behavior === 'enable') {
                    $should_inject = true;
                } elseif ($behavior === 'disable') {
                    $should_inject = false;
                } else {
                    // inherit → use site default already in $should_inject
                }
            }
        } else {
            // Non-singular (archives, home, etc.) respect only site default (no per-post override here)
        }

        if (!$should_inject) { return; }

        // Gather settings
        $script_src       = apply_filters('seggwat/script_src', self::DEFAULT_SCRIPT_SRC);
        $button_color     = apply_filters('seggwat/button_color', $this->sanitize_color(get_option(self::OPT_COLOR, self::DEFAULT_BUTTON_COLOR)));
        $button_pos       = apply_filters('seggwat/button_position', $this->sanitize_position(get_option(self::OPT_POSITION, self::DEFAULT_BUTTON_POS)));
        $language         = apply_filters('seggwat/language', $this->sanitize_language(get_option(self::OPT_LANGUAGE, self::DEFAULT_LANGUAGE)));
        $show_powered_by  = apply_filters('seggwat/show_powered_by', $this->sanitize_boolean(get_option(self::OPT_SHOW_POWERED_BY, true)));

        // Enqueue and rewrite tag to include data-* attrs
        wp_register_script(self::HANDLE, $script_src, [], null, true);
        wp_enqueue_script(self::HANDLE);
        if (function_exists('wp_script_add_data')) {
            wp_script_add_data(self::HANDLE, 'defer', true);
        }

        add_filter('script_loader_tag', function ($tag, $handle, $src) use ($project_key, $button_color, $button_pos, $language, $show_powered_by) {
            if ($handle !== SeggWat_Feedback_Plugin::HANDLE) { return $tag; }

            $attrs = [
                'id'                   => SeggWat_Feedback_Plugin::SCRIPT_ID,
                'src'                  => esc_url($src),
                'defer'                => true,
                'data-project-key'     => $project_key,
                'data-button-color'    => $button_color,
                'data-button-position' => $button_pos,
            ];

            // Only add data-language if not 'auto' (let widget auto-detect)
            if ($language !== 'auto') {
                $attrs['data-language'] = $language;
            }

            // Add data-show-powered-by attribute
            $attrs['data-show-powered-by'] = $show_powered_by ? 'true' : 'false';

            $parts = [];
            foreach ($attrs as $k => $v) {
                $parts[] = $v === true ? $k : sprintf('%s="%s"', esc_attr($k), esc_attr($v));
            }
            return '<script ' . implode(' ', $parts) . '></script>';
        }, 10, 3);
    }
}

new SeggWat_Feedback_Plugin();
