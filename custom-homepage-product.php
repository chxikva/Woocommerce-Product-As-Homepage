<?php
/*
Plugin Name: Custom Homepage Product
Plugin URI: https://example.com/custom-homepage-product
Description: Dynamically set a WooCommerce product as the homepage via WordPress settings.
Version: 1.1
Author: Kanaleto
Author URI: https://example.com
License: GPL2
Text Domain: custom-homepage-product
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1️⃣ Register Setting and Add Admin Dropdown
 */
function chp_register_settings() {
    register_setting('reading', 'chp_homepage_product'); // Save in 'reading' settings

    add_settings_section(
        'chp_settings_section',
        'Custom Homepage Product',
        '__return_null',
        'reading'
    );

    add_settings_field(
        'chp_homepage_product_field',
        'Select Homepage Product',
        'chp_product_dropdown_callback',
        'reading',
        'chp_settings_section'
    );
}
add_action('admin_init', 'chp_register_settings');

/**
 * 2️⃣ Display Dropdown of Products in Settings > Reading
 */
function chp_product_dropdown_callback() {
    $selected_product = get_option('chp_homepage_product');
    $products = wc_get_products(['status' => 'publish', 'limit' => -1]);

    echo '<select name="chp_homepage_product">';
    echo '<option value="">-- Select a Product --</option>';
    foreach ($products as $product) {
        $selected = ($selected_product == $product->get_id()) ? 'selected' : '';
        echo '<option value="' . esc_attr($product->get_id()) . '" ' . $selected . '>';
        echo esc_html($product->get_name());
        echo '</option>';
    }
    echo '</select>';
}

/**
 * 3️⃣ Redirect Homepage to Selected Product
 */
function chp_set_product_as_homepage() {
    if (is_front_page() && !is_admin()) {
        $product_id = get_option('chp_homepage_product');
        if ($product_id && get_post_status($product_id) === 'publish') {
            $product_url = get_permalink($product_id);
            if ($product_url) {
                wp_redirect($product_url);
                exit;
            }
        }
    }
}
add_action('template_redirect', 'chp_set_product_as_homepage');

/**
 * 4️⃣ Plugin Activation Hook - Ensure Default Option
 */
function chp_activate_plugin() {
    if (get_option('chp_homepage_product') === false) {
        add_option('chp_homepage_product', '');
    }
}
register_activation_hook(__FILE__, 'chp_activate_plugin');
