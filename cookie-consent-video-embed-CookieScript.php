<?php
/**
 * Plugin Name: Bonsai Cookie Consent
 * Plugin URI:  https://thebonsaidigitalcollective.co.uk
 * Description: Replaces YouTube embeds with a consent-safe thumbnail overlay until marketing consent is granted via CookieScript or Cookiebot.
 * Version:     2.3.2
 * Author:      Ben Ervine / The Bonsai Digital Collective
 * Author URI:  https://thebonsaidigitalcollective.co.uk
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ccve-cookiescript
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Update URI:  https://github.com/Bonsai-Systems/bonsai-cookie-consent
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CCVE_COOKIESCRIPT_VERSION', '2.3.2' );
define( 'CCVE_COOKIESCRIPT_OPTION_KEY', 'ccve_cookiescript_options' );
define( 'CCVE_COOKIESCRIPT_GITHUB_REPOSITORY', 'https://github.com/Bonsai-Systems/bonsai-cookie-consent' );
define( 'CCVE_COOKIESCRIPT_GITHUB_BRANCH', 'main' );

/*
|--------------------------------------------------------------------------
| Plugin Update Checker (via Composer)
|--------------------------------------------------------------------------
*/

$ccve_autoload = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

if ( file_exists( $ccve_autoload ) ) {
    require_once $ccve_autoload;

    if ( class_exists( '\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory' ) ) {
        $ccve_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            CCVE_COOKIESCRIPT_GITHUB_REPOSITORY,
            __FILE__,
            'cookie-consent-video-embed-cookiescript'
        );

        $ccve_update_checker->setBranch( CCVE_COOKIESCRIPT_GITHUB_BRANCH );
        $ccve_update_checker->scheduler->checkPeriod = 6;
        $ccve_update_checker->getVcsApi()->enableReleaseAssets();
    }
}

/**
 * Return the list of supported consent manager keys.
 *
 * @return array<string, string>
 */
function ccve_cookiescript_get_consent_managers() {
    return array(
        'cookiescript' => __( 'CookieScript', 'ccve-cookiescript' ),
        'cookiebot'    => __( 'Cookiebot', 'ccve-cookiescript' ),
    );
}

/**
 * Return plugin default settings.
 *
 * @return array<string, string>
 */
function ccve_cookiescript_get_default_options() {
    return array(
        'consent_manager'      => 'cookiescript',
        'cookie_category'      => 'marketing',
        'default_bg_image'     => '',
        'consent_text'         => 'Please accept marketing cookies to view this video.',
        'consent_link_url'     => '',
        'consent_link_label'   => 'Update cookie preferences',
    );
}

/**
 * Return merged settings.
 *
 * @return array<string, string>
 */
function ccve_cookiescript_get_options() {
    $options = get_option( CCVE_COOKIESCRIPT_OPTION_KEY, array() );

    if ( ! is_array( $options ) ) {
        $options = array();
    }

    return wp_parse_args( $options, ccve_cookiescript_get_default_options() );
}

/**
 * Sanitize plugin settings before save.
 *
 * @param array<string, mixed> $input Raw settings input.
 * @return array<string, string>
 */
function ccve_cookiescript_sanitize_options( $input ) {
    $defaults = ccve_cookiescript_get_default_options();

    if ( ! is_array( $input ) ) {
        return $defaults;
    }

    $consent_manager = isset( $input['consent_manager'] ) ? sanitize_key( $input['consent_manager'] ) : $defaults['consent_manager'];
    if ( ! array_key_exists( $consent_manager, ccve_cookiescript_get_consent_managers() ) ) {
        $consent_manager = $defaults['consent_manager'];
    }

    $cookie_category = isset( $input['cookie_category'] ) ? sanitize_key( $input['cookie_category'] ) : $defaults['cookie_category'];
    if ( '' === $cookie_category ) {
        $cookie_category = $defaults['cookie_category'];
    }

    $default_bg_image = isset( $input['default_bg_image'] ) ? esc_url_raw( wp_unslash( $input['default_bg_image'] ) ) : '';
    $consent_text     = isset( $input['consent_text'] ) ? sanitize_textarea_field( wp_unslash( $input['consent_text'] ) ) : '';
    $consent_link_url = isset( $input['consent_link_url'] ) ? esc_url_raw( wp_unslash( $input['consent_link_url'] ) ) : '';
    $consent_label    = isset( $input['consent_link_label'] ) ? sanitize_text_field( wp_unslash( $input['consent_link_label'] ) ) : '';

    if ( '' === $consent_text ) {
        $consent_text = $defaults['consent_text'];
    }

    if ( '' === $consent_label ) {
        $consent_label = $defaults['consent_link_label'];
    }

    return array(
        'consent_manager'    => $consent_manager,
        'cookie_category'    => $cookie_category,
        'default_bg_image'   => $default_bg_image,
        'consent_text'       => $consent_text,
        'consent_link_url'   => $consent_link_url,
        'consent_link_label' => $consent_label,
    );
}

/**
 * Register plugin settings and fields.
 *
 * @return void
 */
function ccve_cookiescript_register_settings() {
    register_setting(
        'ccve_cookiescript_settings_group',
        CCVE_COOKIESCRIPT_OPTION_KEY,
        array(
            'type'              => 'array',
            'sanitize_callback' => 'ccve_cookiescript_sanitize_options',
            'default'           => ccve_cookiescript_get_default_options(),
        )
    );

    add_settings_section(
        'ccve_cookiescript_main_section',
        __( 'Display Settings', 'ccve-cookiescript' ),
        '__return_false',
        'ccve-cookiescript'
    );

    add_settings_field(
        'ccve_consent_manager',
        __( 'Consent manager', 'ccve-cookiescript' ),
        'ccve_cookiescript_render_consent_manager_field',
        'ccve-cookiescript',
        'ccve_cookiescript_main_section'
    );

    add_settings_field(
        'ccve_cookie_category',
        __( 'Cookie category key', 'ccve-cookiescript' ),
        'ccve_cookiescript_render_cookie_category_field',
        'ccve-cookiescript',
        'ccve_cookiescript_main_section'
    );

    add_settings_field(
        'ccve_default_bg_image',
        __( 'Default video background image URL', 'ccve-cookiescript' ),
        'ccve_cookiescript_render_default_bg_image_field',
        'ccve-cookiescript',
        'ccve_cookiescript_main_section'
    );

    add_settings_field(
        'ccve_consent_text',
        __( 'Consent text', 'ccve-cookiescript' ),
        'ccve_cookiescript_render_consent_text_field',
        'ccve-cookiescript',
        'ccve_cookiescript_main_section'
    );

    add_settings_field(
        'ccve_consent_link_url',
        __( 'Consent link URL (optional)', 'ccve-cookiescript' ),
        'ccve_cookiescript_render_consent_link_url_field',
        'ccve-cookiescript',
        'ccve_cookiescript_main_section'
    );

    add_settings_field(
        'ccve_consent_link_label',
        __( 'Consent link label', 'ccve-cookiescript' ),
        'ccve_cookiescript_render_consent_link_label_field',
        'ccve-cookiescript',
        'ccve_cookiescript_main_section'
    );
}
add_action( 'admin_init', 'ccve_cookiescript_register_settings' );

/**
 * Add settings page under Settings.
 *
 * @return void
 */
function ccve_cookiescript_add_admin_menu() {
    add_options_page(
        __( 'Cookie Video Consent', 'ccve-cookiescript' ),
        __( 'Cookie Video Consent', 'ccve-cookiescript' ),
        'manage_options',
        'ccve-cookiescript',
        'ccve_cookiescript_render_settings_page'
    );
}
add_action( 'admin_menu', 'ccve_cookiescript_add_admin_menu' );

/**
 * Enqueue inline admin styles scoped to the plugin settings page.
 *
 * @param string $hook Current admin page hook suffix.
 * @return void
 */
function ccve_cookiescript_enqueue_admin_styles( $hook ) {
    if ( 'settings_page_ccve-cookiescript' !== $hook ) {
        return;
    }

    wp_add_inline_style(
        'wp-admin',
        '.ccve-admin-header{display:flex;align-items:center;gap:10px;padding:10px 14px;background:#ee4367;border-radius:3px;margin:0 0 16px;}'
        . '.ccve-admin-header__brand{color:#fff;font-weight:700;font-size:13px;letter-spacing:.02em;}'
        . '.ccve-admin-header__plugin{color:rgba(255,255,255,.75);font-size:12px;}'
    );
}
add_action( 'admin_enqueue_scripts', 'ccve_cookiescript_enqueue_admin_styles' );

/**
 * Render consent manager field.
 *
 * @return void
 */
function ccve_cookiescript_render_consent_manager_field() {
    $options  = ccve_cookiescript_get_options();
    $managers = ccve_cookiescript_get_consent_managers();
    ?>
    <fieldset>
        <?php foreach ( $managers as $key => $label ) : ?>
            <label style="display:block;margin-bottom:4px;">
                <input type="radio" name="<?php echo esc_attr( CCVE_COOKIESCRIPT_OPTION_KEY ); ?>[consent_manager]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $options['consent_manager'], $key ); ?> />
                <?php echo esc_html( $label ); ?>
            </label>
        <?php endforeach; ?>
    </fieldset>
    <p class="description"><?php esc_html_e( 'Which cookie consent management platform is active on this site. Controls the blocking attributes written to the iframe and which API is called to reopen the preferences dialog.', 'ccve-cookiescript' ); ?></p>
    <?php
}

/**
 * Render cookie category field.
 *
 * @return void
 */
function ccve_cookiescript_render_cookie_category_field() {
    $options = ccve_cookiescript_get_options();
    ?>
    <input type="text" name="<?php echo esc_attr( CCVE_COOKIESCRIPT_OPTION_KEY ); ?>[cookie_category]" value="<?php echo esc_attr( $options['cookie_category'] ); ?>" class="regular-text" />
    <p class="description"><?php esc_html_e( 'The consent category key used on blocked iframes for both CookieScript and Cookiebot (default: marketing).', 'ccve-cookiescript' ); ?></p>
    <?php
}

/**
 * Render default background image URL field.
 *
 * @return void
 */
function ccve_cookiescript_render_default_bg_image_field() {
    $options = ccve_cookiescript_get_options();
    ?>
    <input type="url" name="<?php echo esc_attr( CCVE_COOKIESCRIPT_OPTION_KEY ); ?>[default_bg_image]" value="<?php echo esc_url( $options['default_bg_image'] ); ?>" class="regular-text" placeholder="https://example.com/video-placeholder.jpg" />
    <p class="description"><?php esc_html_e( 'If set, this image overrides YouTube thumbnails for all blocked videos.', 'ccve-cookiescript' ); ?></p>
    <?php
}

/**
 * Render consent text field.
 *
 * @return void
 */
function ccve_cookiescript_render_consent_text_field() {
    $options  = ccve_cookiescript_get_options();
    $defaults = ccve_cookiescript_get_default_options();
    ?>
    <textarea name="<?php echo esc_attr( CCVE_COOKIESCRIPT_OPTION_KEY ); ?>[consent_text]" rows="4" class="large-text"><?php echo esc_textarea( $options['consent_text'] ); ?></textarea>
    <p class="description"><?php echo esc_html( sprintf( 'Shown over blocked videos. Default fallback: %s', $defaults['consent_text'] ) ); ?></p>
    <?php
}

/**
 * Render consent link URL field.
 *
 * @return void
 */
function ccve_cookiescript_render_consent_link_url_field() {
    $options = ccve_cookiescript_get_options();
    ?>
    <input type="url" name="<?php echo esc_attr( CCVE_COOKIESCRIPT_OPTION_KEY ); ?>[consent_link_url]" value="<?php echo esc_url( $options['consent_link_url'] ); ?>" class="regular-text" placeholder="https://example.com/privacy-policy" />
    <p class="description"><?php esc_html_e( 'Optional. Leave empty to trigger the active consent manager\'s preferences popup on click.', 'ccve-cookiescript' ); ?></p>
    <?php
}

/**
 * Render consent link label field.
 *
 * @return void
 */
function ccve_cookiescript_render_consent_link_label_field() {
    $options = ccve_cookiescript_get_options();
    ?>
    <input type="text" name="<?php echo esc_attr( CCVE_COOKIESCRIPT_OPTION_KEY ); ?>[consent_link_label]" value="<?php echo esc_attr( $options['consent_link_label'] ); ?>" class="regular-text" />
    <?php
}

/**
 * Render settings page markup.
 *
 * @return void
 */
function ccve_cookiescript_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Insufficient permissions.', 'ccve-cookiescript' ) );
    }
    ?>
    <div class="wrap">
        <div class="ccve-admin-header">
            <span class="ccve-admin-header__brand"><?php esc_html_e( 'The Bonsai Digital Collective', 'ccve-cookiescript' ); ?></span>
            <span class="ccve-admin-header__plugin"><?php esc_html_e( 'Cookie Video Consent', 'ccve-cookiescript' ); ?></span>
        </div>
        <h1><?php esc_html_e( 'Cookie Video Consent', 'ccve-cookiescript' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'ccve_cookiescript_settings_group' );
            do_settings_sections( 'ccve-cookiescript' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Enqueue frontend assets and pass plugin settings.
 *
 * @return void
 */
function ccve_cookiescript_enqueue_assets() {
    $options = ccve_cookiescript_get_options();

    wp_enqueue_style(
        'ccve-cookiescript-style',
        plugin_dir_url( __FILE__ ) . 'assets/css/ccve-cookiescript.css',
        array(),
        CCVE_COOKIESCRIPT_VERSION
    );

    wp_enqueue_script(
        'ccve-cookiescript-script',
        plugin_dir_url( __FILE__ ) . 'assets/js/ccve-cookiescript.js',
        array(),
        CCVE_COOKIESCRIPT_VERSION,
        true
    );

    wp_localize_script(
        'ccve-cookiescript-script',
        'ccveCookieScriptSettings',
        array(
            'consentManager'  => $options['consent_manager'],
            'cookieCategory'  => $options['cookie_category'],
            'defaultBgImage'  => $options['default_bg_image'],
            'consentText'     => $options['consent_text'],
            'consentLinkUrl'  => $options['consent_link_url'],
            'consentLinkText' => $options['consent_link_label'],
        )
    );
}
add_action( 'wp_enqueue_scripts', 'ccve_cookiescript_enqueue_assets' );

/**
 * Remove plugin options on uninstall.
 *
 * @return void
 */
function ccve_cookiescript_uninstall() {
    delete_option( CCVE_COOKIESCRIPT_OPTION_KEY );
}
register_uninstall_hook( __FILE__, 'ccve_cookiescript_uninstall' );