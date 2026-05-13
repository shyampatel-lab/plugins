# Custom Woo AJAX Product Filter

## Installation
1. Upload `custom-woo-ajax-filter` to `/wp-content/plugins/`.
2. Activate plugin.
3. Create a **Filter Set** in wp-admin.
4. Add shortcode: `[custom_product_filter id="123"]`.
5. Or add Elementor widget **CWAF Product Filter** and set Filter Set ID.

## Architecture
- OOP + namespace-based structure
- AJAX endpoint with nonce validation
- WooCommerce template compatibility via `wc_get_template_part`
- Elementor widget integration

## Hooks
- `wp_ajax_cwaf_filter_products`
- `wp_ajax_nopriv_cwaf_filter_products`

## Notes
- Built for WooCommerce + Elementor + modern WordPress.
- HPOS compatibility declared.
- Multisite compatible (network activation supported by WP standards).
