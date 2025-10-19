<?php
/**
 * Product Filter AJAX Handlers
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX handler for filtering products
 */
add_action('wp_ajax_filter_products', 'ajax_filter_products');
add_action('wp_ajax_nopriv_filter_products', 'ajax_filter_products');

function ajax_filter_products() {
    // Verify nonce
    check_ajax_referer('filter_nonce', 'nonce');
    
    // Sanitize and prepare args
    $args = [
        'per_page' => -1,
        'categories' => isset($_POST['categories']) ? sanitize_text_field($_POST['categories']) : '',
        'orderby' => isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date',
        'order' => isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC',
        'min_price' => isset($_POST['min_price']) ? intval($_POST['min_price']) : '',
        'max_price' => isset($_POST['max_price']) ? intval($_POST['max_price']) : '',
        'attributes' => isset($_POST['attributes']) ? sanitize_text_field($_POST['attributes']) : '',
        'custom_attributes' => isset($_POST['custom_attributes']) ? sanitize_text_field($_POST['custom_attributes']) : '',
        'tags' => isset($_POST['tags']) ? sanitize_text_field($_POST['tags']) : '',
        'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
    ];
    
    // Get products HTML
    require_once get_stylesheet_directory() . '/includes/product-filter-query.php';
    echo get_filtered_products_html($args);
    
    wp_die();
}