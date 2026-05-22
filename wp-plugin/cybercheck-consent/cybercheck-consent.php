<?php
/**
 * Plugin Name: CyberCheck Review Consent
 * Plugin URI:  https://cybercheck-links.vercel.app
 * Description: Adds a SMS review-consent widget to any page via shortcode. Drop on booking confirmation pages.
 * Version:     1.0.0
 * Author:      CyberCheck
 * Author URI:  https://cybercheckinc.com
 * License:     GPL-2.0+
 * Text Domain: cybercheck-consent
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Settings page ────────────────────────────────────────────────────────────

add_action( 'admin_menu', function () {
    add_options_page(
        'CyberCheck Consent',
        'CyberCheck Consent',
        'manage_options',
        'cybercheck-consent',
        'cc_settings_page'
    );
} );

add_action( 'admin_init', function () {
    register_setting( 'cc_settings_group', 'cc_site_id', [ 'sanitize_callback' => 'sanitize_text_field' ] );
} );

function cc_settings_page() {
    ?>
    <div class="wrap">
        <h1>CyberCheck Review Consent</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'cc_settings_group' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="cc_site_id">Site ID</label></th>
                    <td>
                        <input type="text" id="cc_site_id" name="cc_site_id"
                               value="<?php echo esc_attr( get_option( 'cc_site_id', '' ) ); ?>"
                               class="regular-text" placeholder="Your CyberCheck Site ID" />
                        <p class="description">Found in your CyberCheck admin dashboard under Settings → Review Platform.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <hr>
        <h2>Shortcode Usage</h2>
        <p>Place this shortcode on your booking confirmation page:</p>
        <pre style="background:#f1f1f1;padding:12px;border-radius:6px;">[cybercheck_consent]</pre>

        <p>You can also pass booking details dynamically (e.g. via a booking plugin hook):</p>
        <pre style="background:#f1f1f1;padding:12px;border-radius:6px;">[cybercheck_consent
  name="Maria"
  booking_ref="BK-1234"
  visit_date="2025-08-10"
  items="Half Day Kayak Tour, Snorkel Gear"]</pre>

        <h2>Programmatic Usage (PHP)</h2>
        <p>Call from any theme or plugin template:</p>
        <pre style="background:#f1f1f1;padding:12px;border-radius:6px;">&lt;?php echo do_shortcode('[cybercheck_consent name="' . esc_attr($customer_name) . '" booking_ref="' . esc_attr($order_id) . '" visit_date="' . esc_attr($date) . '"]'); ?&gt;</pre>
    </div>
    <?php
}

// ── Enqueue widget script ─────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', function () {
    wp_register_script(
        'cybercheck-consent-widget',
        'https://cybercheck-links.vercel.app/js/review-consent-widget.js',
        [],
        '1.0.0',
        true
    );
} );

// ── Shortcode ─────────────────────────────────────────────────────────────────

add_shortcode( 'cybercheck_consent', function ( $atts ) {
    $atts = shortcode_atts( [
        'site_id'     => get_option( 'cc_site_id', '' ),
        'name'        => '',
        'email'       => '',
        'phone'       => '',
        'booking_ref' => '',
        'visit_date'  => '',
        'items'       => '',   // comma-separated string  OR  JSON array string
        'source'      => 'wordpress',
        'container'   => '',   // optional custom CSS selector
    ], $atts, 'cybercheck_consent' );

    if ( empty( $atts['site_id'] ) ) {
        return '<!-- CyberCheck: site_id is required. Set it in Settings → CyberCheck Consent or pass it as a shortcode attribute. -->';
    }

    // Normalise items → JSON array
    $items_raw = trim( $atts['items'] );
    if ( empty( $items_raw ) ) {
        $items_json = '[]';
    } elseif ( $items_raw[0] === '[' ) {
        // Already JSON — validate and pass through
        $decoded = json_decode( $items_raw, true );
        $items_json = $decoded !== null ? wp_json_encode( $decoded ) : '[]';
    } else {
        // Comma-separated string → array of {name}
        $names = array_filter( array_map( 'trim', explode( ',', $items_raw ) ) );
        $items_json = wp_json_encode( array_values( array_map( function( $n ) {
            return [ 'name' => $n ];
        }, $names ) ) );
    }

    // Unique container id so multiple widgets on the same page work
    static $instance = 0;
    $instance++;
    $container_id = 'cybercheck-consent-' . $instance;

    wp_enqueue_script( 'cybercheck-consent-widget' );

    // Build inline init call — all values are escaped through wp_json_encode
    $config = [
        'siteId'     => $atts['site_id'],
        'name'       => $atts['name'],
        'email'      => $atts['email'],
        'phone'      => $atts['phone'],
        'bookingRef' => $atts['booking_ref'],
        'visitDate'  => $atts['visit_date'],
        'items'      => json_decode( $items_json, true ),
        'source'     => $atts['source'],
        'container'  => '#' . $container_id,
    ];

    $config_json = wp_json_encode( $config );

    $inline = <<<JS
(function(){
  function tryInit(){
    if(typeof CyberCheckConsent !== 'undefined'){
      CyberCheckConsent.init({$config_json});
    } else {
      setTimeout(tryInit, 50);
    }
  }
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', tryInit);
  } else {
    tryInit();
  }
})();
JS;

    return '<div id="' . esc_attr( $container_id ) . '"></div>'
         . '<script>' . $inline . '</script>';
} );

// ── WooCommerce order-confirmation auto-inject (optional) ─────────────────────
// Fires on the WooCommerce "Thank You" page if WooCommerce is active.

add_action( 'woocommerce_thankyou', function ( $order_id ) {
    $site_id = get_option( 'cc_site_id', '' );
    if ( empty( $site_id ) ) return;

    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    $name  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $email = $order->get_billing_email();
    $phone = $order->get_billing_phone();
    $date  = $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d' ) : '';

    $items = [];
    foreach ( $order->get_items() as $item ) {
        $items[] = [ 'name' => $item->get_name() ];
    }

    echo do_shortcode(
        '[cybercheck_consent'
        . ' site_id="' . esc_attr( $site_id ) . '"'
        . ' name="' . esc_attr( trim( $name ) ) . '"'
        . ' email="' . esc_attr( $email ) . '"'
        . ' phone="' . esc_attr( $phone ) . '"'
        . ' booking_ref="' . esc_attr( (string) $order_id ) . '"'
        . ' visit_date="' . esc_attr( $date ) . '"'
        . ' items=\'' . esc_attr( wp_json_encode( $items ) ) . '\''
        . ' source="woocommerce"]'
    );
}, 20 );
