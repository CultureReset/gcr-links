=== CyberCheck Review Consent ===
Contributors: cybercheckinc
Tags: reviews, sms, consent, booking, feedback
Requires at least: 5.8
Tested up to: 6.5
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later

Collect SMS review consent at checkout. CyberCheck sends one text after the visit with a pre-filled review link.

== Description ==

Drop the widget on any booking confirmation or WooCommerce thank-you page.
After the visit date, CyberCheck sends the customer one SMS with a link to leave a review — pre-filled with their name, date, and what they ordered.

**Features**
* Works on any WordPress site via shortcode
* Auto-integrates with WooCommerce thank-you page (zero config)
* No dashboards — everything runs through the CyberCheck platform
* TCPA-compliant — customer opts in at booking time
* Supports any booking plugin (pass data via shortcode attributes)

== Installation ==

1. Upload the `cybercheck-consent` folder to `/wp-content/plugins/`
2. Activate the plugin in the WordPress admin
3. Go to **Settings → CyberCheck Consent** and paste your Site ID
4. The widget will automatically appear on WooCommerce order confirmation pages

For non-WooCommerce bookings, add the shortcode to your confirmation page:

`[cybercheck_consent]`

Or with booking details:

`[cybercheck_consent name="Maria" booking_ref="BK-1234" visit_date="2025-08-10" items="Kayak Tour, Snorkel Gear"]`

== Shortcode Attributes ==

* `site_id` — Your CyberCheck site ID (defaults to Settings value)
* `name` — Customer full name
* `email` — Customer email
* `phone` — Customer phone (pre-fills the input)
* `booking_ref` — Booking/order ID
* `visit_date` — Date of visit (YYYY-MM-DD)
* `items` — Comma-separated list of items, or JSON array string

== Changelog ==

= 1.0.0 =
* Initial release
