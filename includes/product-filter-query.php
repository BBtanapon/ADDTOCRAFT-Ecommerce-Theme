<?php
/**
 * Product Filter Query Functions
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get filtered products HTML
 */
function get_filtered_products_html($args = []) {
    $defaults = [
        'per_page' => -1,
        'categories' => '',
        'orderby' => 'date',
        'order' => 'DESC',
        'min_price' => '',
        'max_price' => '',
        'attributes' => '',
        'custom_attributes' => '',
        'tags' => '',
        'search' => '',
        'props' => '',
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // Build query args
    $query_args = build_product_query_args($args);
    
    // Execute query
    $products = new WP_Query($query_args);
    
    // Filter by custom attributes if needed
    $custom_attr_filter = parse_custom_attributes($args['custom_attributes']);
    
    // Generate HTML
    ob_start();
    
    if ($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();
            global $product;
            
            // Check custom attributes filter
            if (!empty($custom_attr_filter) && !match_custom_attributes($product, $custom_attr_filter)) {
                continue;
            }
            
            render_product_card($product);
        }
    } else {
        echo '<p class="no-products">' . __('No products found.', 'hello-elementor-child') . '</p>';
    }
    
    wp_reset_postdata();
    
    return ob_get_clean();
}

/**
 * Build WP_Query args for products
 */
function build_product_query_args($args) {
    $query_args = [
        'post_type' => 'product',
        'posts_per_page' => $args['per_page'],
        'orderby' => $args['orderby'],
        'order' => $args['order'],
        'post_status' => 'publish',
    ];
    
    // Add search query
    if (!empty($args['search'])) {
        $query_args['s'] = $args['search'];
    }
    
    // Build tax query
    $tax_query = ['relation' => 'AND'];
    
    // Categories
    if (!empty($args['categories'])) {
        $categories = explode(',', $args['categories']);
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => $categories,
            'operator' => 'IN',
        ];
    }
    
    // Attributes
    if (!empty($args['attributes'])) {
        $attributes = explode(',', $args['attributes']);
        foreach ($attributes as $attr) {
            $attr_parts = explode(':', $attr);
            if (count($attr_parts) === 2) {
                $tax_query[] = [
                    'taxonomy' => $attr_parts[0],
                    'field' => 'term_id',
                    'terms' => $attr_parts[1],
                ];
            }
        }
    }
    
    // Tags
    if (!empty($args['tags'])) {
        $tags = explode(',', $args['tags']);
        $tax_query[] = [
            'taxonomy' => 'product_tag',
            'field' => 'term_id',
            'terms' => $tags,
        ];
    }
    
    if (count($tax_query) > 1) {
        $query_args['tax_query'] = $tax_query;
    }
    
    // Price range
    if (!empty($args['min_price']) || !empty($args['max_price'])) {
        $query_args['meta_query'] = [
            [
                'key' => '_price',
                'value' => [$args['min_price'], $args['max_price']],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ],
        ];
    }
    
    return $query_args;
}

/**
 * Parse custom attributes string
 */
function parse_custom_attributes($custom_attributes_string) {
    $custom_attr_filter = [];
    
    if (empty($custom_attributes_string)) {
        return $custom_attr_filter;
    }
    
    $custom_attrs = explode(',', $custom_attributes_string);
    
    foreach ($custom_attrs as $custom_attr) {
        $parts = explode(':', $custom_attr);
        if (count($parts) === 2) {
            $attr_name = $parts[0];
            $attr_value = $parts[1];
            
            if (!isset($custom_attr_filter[$attr_name])) {
                $custom_attr_filter[$attr_name] = [];
            }
            
            $custom_attr_filter[$attr_name][] = $attr_value;
        }
    }
    
    return $custom_attr_filter;
}

/**
 * Check if product matches custom attributes
 */
function match_custom_attributes($product, $custom_attr_filter) {
    $product_attrs = $product->get_attributes();
    
    foreach ($custom_attr_filter as $filter_attr_name => $filter_values) {
        $found = false;
        
        foreach ($product_attrs as $product_attr) {
            if ($product_attr->get_name() === $filter_attr_name) {
                $product_values = $product_attr->get_options();
                
                foreach ($filter_values as $filter_value) {
                    if (in_array($filter_value, $product_values)) {
                        $found = true;
                        break 2;
                    }
                }
            }
        }
        
        if (!$found) {
            return false;
        }
    }
    
    return true;
}

/**
 * Render a single product card
 */
function render_product_card($product) {
    $is_on_sale = $product->is_on_sale();
    $tags = get_the_terms(get_the_ID(), 'product_tag');
    $main_tag = $tags && !is_wp_error($tags) ? $tags[0]->name : '';
    ?>
    <div class="product-card">
        <div class="product-badges">
            <?php if ($is_on_sale) : ?>
                <span class="badge-sale"><?php _e('Sale!', 'hello-elementor-child'); ?></span>
            <?php endif; ?>
            <?php if ($main_tag) : ?>
                <span class="badge-tag"><?php echo esc_html($main_tag); ?></span>
            <?php endif; ?>
        </div>
        
        <a href="<?php echo esc_url(get_permalink()); ?>" class="product-image-link">
            <?php echo $product->get_image('medium'); ?>
        </a>
        
        <div class="product-info">
            <h3 class="product-title">
                <a href="<?php echo esc_url(get_permalink()); ?>">
                    <?php echo esc_html(get_the_title()); ?>
                </a>
            </h3>
            
            <div class="product-price">
                <?php echo $product->get_price_html(); ?>
            </div>
            
            <div class="product-actions">
                <?php if ($product->is_type('variable')) : ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="btn-select-options">
                        <?php _e('SELECT OPTIONS', 'hello-elementor-child'); ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url('?add-to-cart=' . $product->get_id()); ?>" 
                       class="btn-add-to-cart ajax_add_to_cart add_to_cart_button" 
                       data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                       data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                       data-quantity="1"
                       aria-label="<?php echo esc_attr(sprintf(__('Add &ldquo;%s&rdquo; to your cart', 'hello-elementor-child'), $product->get_name())); ?>"
                       rel="nofollow">
                        <?php _e('ADD TO CART', 'hello-elementor-child'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}