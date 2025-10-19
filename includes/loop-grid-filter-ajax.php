<?php
/**
 * Loop Grid Filter AJAX Handler
 * Handles AJAX requests for filtering Elementor Loop Grid
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

class Loop_Grid_Filter_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_filter_loop_grid', [$this, 'filter_loop_grid']);
        add_action('wp_ajax_nopriv_filter_loop_grid', [$this, 'filter_loop_grid']);
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    /**
     * Enqueue filter scripts and styles
     */
    public function enqueue_scripts() {
        // Check if Elementor is active and we're on a page with Loop Grid
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        // Enqueue the filter script
        wp_enqueue_script(
            'loop-grid-filter',
            get_stylesheet_directory_uri() . '/assets/js/loop-grid-filter.js',
            ['jquery'],
            HELLO_ELEMENTOR_CHILD_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('loop-grid-filter', 'loopGridFilterAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('loop_grid_filter_nonce'),
        ]);
        
        // Enqueue styles (reuse your existing styles)
        wp_enqueue_style(
            'loop-grid-filter-styles',
            get_stylesheet_directory_uri() . '/assets/css/product-filter.css',
            [],
            HELLO_ELEMENTOR_CHILD_VERSION
        );
    }
    
    /**
     * Handle AJAX filter request
     */
    public function filter_loop_grid() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'loop_grid_filter_nonce')) {
            wp_die('Security check failed');
        }
        
        // Get filters
        $filters = $_POST['filters'] ?? [];
        $widget_id = sanitize_text_field($_POST['widget_id'] ?? '');
        
        // Build query args based on filters
        $query_args = $this->build_query_args($filters);
        
        // Get the query
        $query = new WP_Query($query_args);
        
        // Generate HTML
        ob_start();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                // Try to use Elementor's loop item template if available
                if ($this->render_loop_item()) {
                    continue;
                }
                
                // Fallback to basic product card
                $this->render_fallback_item();
            }
        } else {
            echo '<div class="no-results-message">No products found matching your criteria.</div>';
        }
        
        wp_reset_postdata();
        
        $html = ob_get_clean();
        
        wp_send_json_success([
            'html' => $html,
            'found_posts' => $query->found_posts,
            'max_pages' => $query->max_num_pages,
        ]);
    }
    
    /**
     * Build WP_Query args from filters
     */
    private function build_query_args($filters) {
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12, // Default, can be customized
        ];
        
        // Search
        if (!empty($filters['search'])) {
            $args['s'] = sanitize_text_field($filters['search']);
        }
        
        // Sorting
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'title':
                    $args['orderby'] = 'title';
                    $args['order'] = 'ASC';
                    break;
                case 'price':
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                    $args['meta_key'] = '_price';
                    break;
                case 'price-desc':
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    $args['meta_key'] = '_price';
                    break;
                case 'popularity':
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    $args['meta_key'] = 'total_sales';
                    break;
                case 'rating':
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    $args['meta_key'] = '_wc_average_rating';
                    break;
                default:
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
            }
        }
        
        // Build tax query
        $tax_query = ['relation' => 'AND'];
        
        // Categories
        if (!empty($filters['categories']) && is_array($filters['categories'])) {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => array_map('intval', $filters['categories']),
            ];
        }
        
        // Tags
        if (!empty($filters['tags']) && is_array($filters['tags'])) {
            $tax_query[] = [
                'taxonomy' => 'product_tag',
                'field' => 'term_id',
                'terms' => array_map('intval', $filters['tags']),
            ];
        }
        
        // WooCommerce Attributes
        if (!empty($filters['attributes']) && is_array($filters['attributes'])) {
            foreach ($filters['attributes'] as $attr) {
                $parts = explode(':', $attr);
                if (count($parts) === 2) {
                    $tax_query[] = [
                        'taxonomy' => sanitize_text_field($parts[0]),
                        'field' => 'term_id',
                        'terms' => intval($parts[1]),
                    ];
                }
            }
        }
        
        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }
        
        // Price filter
        $meta_query = [];
        
        if (!empty($filters['minPrice']) || !empty($filters['maxPrice'])) {
            $min = !empty($filters['minPrice']) ? floatval($filters['minPrice']) : 0;
            $max = !empty($filters['maxPrice']) ? floatval($filters['maxPrice']) : 999999;
            
            $meta_query[] = [
                'key' => '_price',
                'value' => [$min, $max],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ];
        }
        
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        return $args;
    }
    
    /**
     * Try to render using Elementor's loop item template
     */
    private function render_loop_item() {
        // Check if Elementor Pro is active and has loop templates
        if (!function_exists('elementor_pro')) {
            return false;
        }
        
        global $post;
        
        // Try to find and render the loop template
        // This would need to be customized based on your specific setup
        $template_id = get_option('elementor_loop_template_id');
        
        if ($template_id && class_exists('\ElementorPro\Plugin')) {
            echo \ElementorPro\Plugin::elementor()->frontend->get_builder_content($template_id, true);
            return true;
        }
        
        return false;
    }
    
    /**
     * Render fallback item when Elementor template not available
     */
    private function render_fallback_item() {
        global $product;
        
        if (!$product) {
            $product = wc_get_product(get_the_ID());
        }
        
        if (!$product) {
            return;
        }
        
        $categories = get_the_terms(get_the_ID(), 'product_cat');
        $tags = get_the_terms(get_the_ID(), 'product_tag');
        
        $cat_ids = $categories ? implode(',', wp_list_pluck($categories, 'term_id')) : '';
        $tag_ids = $tags ? implode(',', wp_list_pluck($tags, 'term_id')) : '';
        ?>
        <article class="e-loop-item" 
                 data-product-id="<?php echo esc_attr(get_the_ID()); ?>"
                 data-categories="<?php echo esc_attr($cat_ids); ?>"
                 data-tags="<?php echo esc_attr($tag_ids); ?>">
            
            <div class="loop-item-inner">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="loop-item-image">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('medium'); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="loop-item-content">
                    <h3 class="loop-item-title">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h3>
                    
                    <?php if ($product) : ?>
                        <div class="loop-item-price">
                            <?php echo $product->get_price_html(); ?>
                        </div>
                        
                        <div class="loop-item-button">
                            <?php woocommerce_template_loop_add_to_cart(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php
    }
}

// Initialize the AJAX handler
new Loop_Grid_Filter_Ajax();