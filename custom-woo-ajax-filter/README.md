# Custom Woo AJAX Product Filter

## Installation
1. Upload `custom-woo-ajax-filter` to `/wp-content/plugins/`.
2. Activate plugin.
3. Create a **Filter Set** in wp-admin -> Filter Sets.
4. Configure layout (sidebar/topbar/accordion), AND/OR relation, and filter rows with taxonomy + type.
5. Add shortcode `[custom_product_filter id="123"]` or Elementor widget.

## Supported Filter Types
- Checkbox
- Radio
- Dropdown
- Text search
- Price min/max fields
- Sorting (latest, price, A-Z, rating)

## AJAX + URL behavior
- Product list updates with no page refresh
- Query parameters update dynamically for SEO/shareable URLs


## Hooks
- `wp_ajax_cwaf_filter_products`
- `wp_ajax_nopriv_cwaf_filter_products`
