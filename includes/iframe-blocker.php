<?php
/**
 * Server-side iframe blocking.
 *
 * Rewrites YouTube and Vimeo iframes in the rendered page before it is sent,
 * so the browser never sees a live `src` and nothing loads before consent.
 * The front-end script then adds the placeholder; it still handles any
 * iframes injected after page load (modals, load-more, etc.) on its own.
 *
 * @package CCVE_CookieScript
 */

defined( 'ABSPATH' ) || exit;

/**
 * Detect the video provider from an embed URL.
 *
 * Mirrors getProvider() in assets/js/ccve-cookiescript.js.
 *
 * @param string $url Embed URL.
 * @return string 'youtube', 'vimeo' or '' when unsupported.
 */
function ccve_cookiescript_get_provider( $url ) {
    if ( '' === $url ) {
        return '';
    }

    if ( preg_match( '#(youtube(?:-nocookie)?\.com|youtu\.be)/#i', $url ) ) {
        return 'youtube';
    }

    if ( preg_match( '#player\.vimeo\.com/video/#i', $url ) ) {
        return 'vimeo';
    }

    return '';
}

/**
 * Convert an embed URL to the provider's privacy-enhanced form.
 *
 * YouTube: swap to youtube-nocookie.com. Vimeo: force `dnt=1`, keeping all
 * other params (including the `h=` hash unlisted videos need).
 * Mirrors toPrivacyUrl() in assets/js/ccve-cookiescript.js.
 *
 * @param string $url      Embed URL.
 * @param string $provider Provider key.
 * @return string
 */
function ccve_cookiescript_to_privacy_url( $url, $provider ) {
    if ( 'vimeo' === $provider ) {
        if ( preg_match( '/[?&]dnt=/i', $url ) ) {
            return preg_replace( '/([?&])dnt=[^&#]*/i', '${1}dnt=1', $url );
        }

        $hash_pos = strpos( $url, '#' );
        $fragment = false !== $hash_pos ? substr( $url, $hash_pos ) : '';
        $base     = false !== $hash_pos ? substr( $url, 0, $hash_pos ) : $url;

        return $base . ( false !== strpos( $base, '?' ) ? '&' : '?' ) . 'dnt=1' . $fragment;
    }

    return str_replace(
        array( 'https://www.youtube.com/', 'https://youtube.com/' ),
        'https://www.youtube-nocookie.com/',
        $url
    );
}

/**
 * Return the src/category attribute names the active consent manager scans for.
 *
 * Mirrors DATA_SRC_ATTR / DATA_CATEGORY_ATTR in assets/js/ccve-cookiescript.js.
 *
 * @param string $consent_manager Consent manager key.
 * @return array{src: string, category: string}
 */
function ccve_cookiescript_get_blocking_attributes( $consent_manager ) {
    if ( 'cookiebot' === $consent_manager ) {
        return array(
            'src'      => 'data-cookieblock-src',
            'category' => 'data-cookieconsent',
        );
    }

    return array(
        'src'      => 'data-src',
        'category' => 'data-cookiecategory',
    );
}

/**
 * Rewrite supported video iframes in an HTML string so they are blocked until consent.
 *
 * @param string $html HTML to process.
 * @return string Rewritten HTML, or the original when nothing needed changing.
 */
function ccve_cookiescript_block_iframes_in_html( $html ) {
    if ( ! is_string( $html ) || false === stripos( $html, '<iframe' ) ) {
        return $html;
    }

    // WP_HTML_Tag_Processor (WP 6.2+) edits attributes safely without regex-parsing HTML.
    if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
        return $html;
    }

    $options    = ccve_cookiescript_get_options();
    $attributes = ccve_cookiescript_get_blocking_attributes( $options['consent_manager'] );
    $processor  = new WP_HTML_Tag_Processor( $html );
    $changed    = false;

    // Uppercase query: WP < 6.5 only uppercases the HTML side, so 'iframe' would miss `<IFRAME>`.
    while ( $processor->next_tag( 'IFRAME' ) ) {
        // Already handled (e.g. content filtered twice).
        if ( null !== $processor->get_attribute( 'data-ccve-provider' ) ) {
            continue;
        }

        $src      = $processor->get_attribute( 'src' );
        $data_src = $processor->get_attribute( 'data-src' );
        $original = is_string( $src ) && '' !== $src ? $src : ( is_string( $data_src ) ? $data_src : '' );
        $provider = ccve_cookiescript_get_provider( $original );

        if ( '' === $provider ) {
            continue;
        }

        $processor->remove_attribute( 'src' );

        // A pre-blocked `data-src` is only meaningful to CookieScript; drop it when writing Cookiebot's attribute instead.
        if ( 'data-src' !== $attributes['src'] ) {
            $processor->remove_attribute( 'data-src' );
        }

        $processor->set_attribute( $attributes['src'], ccve_cookiescript_to_privacy_url( $original, $provider ) );
        $processor->set_attribute( $attributes['category'], $options['cookie_category'] );
        $processor->set_attribute( 'data-ccve-provider', $provider );
        $changed = true;
    }

    return $changed ? $processor->get_updated_html() : $html;
}

/**
 * Whether the current request should have its output rewritten.
 *
 * @return bool
 */
function ccve_cookiescript_should_buffer_output() {
    if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || wp_is_json_request() ) {
        return false;
    }

    if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_feed() || is_robots() || is_trackback() ) {
        return false;
    }

    // Page builder editors/previews need the live iframe to render and edit it.
    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only presence checks.
    $builder_params = array( 'elementor-preview', 'et_fb', 'fl_builder', 'bricks', 'ct_builder', 'vc_editable' );
    foreach ( $builder_params as $param ) {
        if ( isset( $_GET[ $param ] ) ) {
            return false;
        }
    }
    // phpcs:enable

    /**
     * Filter whether video iframes are rewritten server-side on this request.
     *
     * Return false to fall back to the front-end script only.
     *
     * @param bool $enabled Default true.
     */
    return (bool) apply_filters( 'ccve_cookiescript_block_iframes', true );
}

/**
 * Start buffering front-end output so iframes can be rewritten before sending.
 *
 * @return void
 */
function ccve_cookiescript_start_output_buffer() {
    if ( ! ccve_cookiescript_should_buffer_output() ) {
        return;
    }

    ob_start( 'ccve_cookiescript_output_buffer_callback' );
}
add_action( 'template_redirect', 'ccve_cookiescript_start_output_buffer', 0 );

/**
 * Output buffer callback. Never lets a failure blank the page.
 *
 * @param string $buffer Page output.
 * @return string
 */
function ccve_cookiescript_output_buffer_callback( $buffer ) {
    if ( ! is_string( $buffer ) || '' === $buffer ) {
        return $buffer;
    }

    // Leave non-HTML responses (JSON, XML, files) untouched.
    foreach ( headers_list() as $header ) {
        if ( 0 === stripos( $header, 'content-type:' ) && false === stripos( $header, 'html' ) ) {
            return $buffer;
        }
    }

    try {
        $rewritten = ccve_cookiescript_block_iframes_in_html( $buffer );
        return is_string( $rewritten ) && '' !== $rewritten ? $rewritten : $buffer;
    } catch ( Throwable $e ) {
        error_log( 'CCVE iframe blocking failed: ' . $e->getMessage() );
        return $buffer;
    }
}
