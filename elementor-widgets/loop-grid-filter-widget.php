<?php

/**
 * Enhanced Elementor Loop Grid Filter Widget - FIXED (No Redundant Attributes)
 * Uses ONLY WooCommerce registered attributes, not product-level attributes
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Loop_Grid_Filter_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'loop_grid_filter';
    }

    public function get_title()
    {
        return __('Loop Grid Filter', 'hello-elementor-child');
    }

    public function get_icon()
    {
        return 'eicon-filter';
    }

    public function get_categories()
    {
        return ['custom-widgets', 'general'];
    }

    protected function register_controls()
    {
        // ============ GENERAL SETTINGS ============
        $this->start_controls_section(
            'section_general',
            [
                'label' => __('General Settings', 'hello-elementor-child'),
            ]
        );

        $this->add_control(
            'target_loop_grid_id',
            [
                'label' => __('Target Loop Grid CSS ID', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'my-loop-grid',
                'description' => __('Enter the CSS ID of the Loop Grid widget (Advanced > CSS ID)', 'hello-elementor-child'),
            ]
        );

        $this->add_control(
            'filter_method',
            [
                'label' => __('Filter Method', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'client_side',
                'options' => [
                    'client_side' => __('Client-Side (Fast, No Page Reload)', 'hello-elementor-child'),
                    'ajax' => __('AJAX (Server-Side, More Accurate)', 'hello-elementor-child'),
                ],
                'description' => __('Client-side is faster but works on visible items only', 'hello-elementor-child'),
            ]
        );

        $this->end_controls_section();

        // ============ FILTER OPTIONS ============
        $this->start_controls_section(
            'section_filters',
            [
                'label' => __('Filter Options', 'hello-elementor-child'),
            ]
        );

        $this->add_control(
            'show_search',
            [
                'label' => __('Show Search', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_sort',
            [
                'label' => __('Show Sort Options', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_categories',
            [
                'label' => __('Show Categories', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_tags',
            [
                'label' => __('Show Tags', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_attributes',
            [
                'label' => __('Show WooCommerce Attributes', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_price_filter',
            [
                'label' => __('Show Price Filter', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_reset_button',
            [
                'label' => __('Show Reset Button', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // ============ MOBILE SETTINGS ============
        $this->start_controls_section(
            'section_mobile',
            [
                'label' => __('Mobile Settings', 'hello-elementor-child'),
            ]
        );

        $this->add_control(
            'mobile_toggle',
            [
                'label' => __('Mobile Toggle Button', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // ============ STYLE SECTION ============
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Style', 'hello-elementor-child'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'filter_background',
            [
                'label' => __('Background Color', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .loop-filter-sidebar' => 'background-color: {{VALUE}};',
                ],
                'default' => '#FFFFFF',
            ]
        );

        $this->add_control(
            'filter_text_color',
            [
                'label' => __('Text Color', 'hello-elementor-child'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .loop-filter-sidebar' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .loop-filter-sidebar label' => 'color: {{VALUE}};',
                ],
                'default' => '#1e1e1e',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $widget_id = $this->get_id();

        // FIXED: Get only WooCommerce registered attributes (no redundancy)
        $categories = $this->get_product_categories();
        $tags = $this->get_product_tags();
        $wc_attributes = $this->get_woocommerce_attributes_only(); // NEW METHOD
        $max_price = $this->get_max_price();

?>
        <div class="loop-grid-filter-widget"
            data-widget-id="<?php echo esc_attr($widget_id); ?>"
            data-target="<?php echo esc_attr($settings['target_loop_grid_id']); ?>"
            data-filter-method="<?php echo esc_attr($settings['filter_method']); ?>">

            <?php if ($settings['mobile_toggle'] === 'yes') : ?>
                <div class="filter-overlay"></div>
                <button class="filter-toggle-btn" aria-label="Open Filters">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z" />
                    </svg>
                </button>
            <?php endif; ?>

            <div class="loop-filter-sidebar">
                <?php if ($settings['mobile_toggle'] === 'yes') : ?>
                    <button class="filter-close-btn" aria-label="Close Filters"></button>
                <?php endif; ?>

                <h3><?php _e('Filters', 'hello-elementor-child'); ?></h3>

                <?php if ($settings['show_search'] === 'yes') : ?>
                    <div class="filter-group">
                        <label><?php _e('Search', 'hello-elementor-child'); ?></label>
                        <input type="text" class="loop-filter-search filter-select" placeholder="<?php _e('Search...', 'hello-elementor-child'); ?>">
                    </div>
                <?php endif; ?>

                <?php if ($settings['show_sort'] === 'yes') : ?>
                    <div class="filter-group">
                        <label><?php _e('Sort By', 'hello-elementor-child'); ?></label>
                        <select class="loop-filter-sort filter-select">
                            <option value="date"><?php _e('Newest', 'hello-elementor-child'); ?></option>
                            <option value="title"><?php _e('Title', 'hello-elementor-child'); ?></option>
                            <option value="price"><?php _e('Price: Low to High', 'hello-elementor-child'); ?></option>
                            <option value="price-desc"><?php _e('Price: High to Low', 'hello-elementor-child'); ?></option>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ($settings['show_categories'] === 'yes' && !empty($categories)) : ?>
                    <div class="filter-group">
                        <label><?php _e('Categories', 'hello-elementor-child'); ?></label>
                        <div class="filter-checkboxes">
                            <?php foreach ($categories as $category) : ?>
                                <div class="filter-checkbox-item">
                                    <input type="checkbox" class="loop-filter-category" value="<?php echo esc_attr($category->term_id); ?>" id="cat-<?php echo esc_attr($widget_id . '-' . $category->term_id); ?>">
                                    <label for="cat-<?php echo esc_attr($widget_id . '-' . $category->term_id); ?>"><?php echo esc_html($category->name); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php // FIXED: Display ONLY WooCommerce registered attributes (no duplicates) 
                ?>
                <?php if ($settings['show_attributes'] === 'yes' && !empty($wc_attributes)) : ?>
                    <?php foreach ($wc_attributes as $attribute) :
                        $taxonomy = $attribute['taxonomy'];
                        $terms = $attribute['terms'];

                        if (empty($terms)) continue;
                    ?>
                        <div class="filter-group">
                            <label><?php echo esc_html($attribute['label']); ?></label>
                            <div class="filter-checkboxes">
                                <?php foreach ($terms as $term) : ?>
                                    <div class="filter-checkbox-item">
                                        <input type="checkbox" class="loop-filter-custom-attribute"
                                            value="<?php echo esc_attr($taxonomy . ':' . $term->slug); ?>"
                                            id="attr-<?php echo esc_attr($widget_id . '-' . $taxonomy . '-' . $term->slug); ?>">
                                        <label for="attr-<?php echo esc_attr($widget_id . '-' . $taxonomy . '-' . $term->slug); ?>">
                                            <?php echo esc_html($term->name); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($settings['show_tags'] === 'yes' && !empty($tags)) : ?>
                    <div class="filter-group">
                        <label><?php _e('Tags', 'hello-elementor-child'); ?></label>
                        <div class="filter-checkboxes">
                            <?php foreach ($tags as $tag) : ?>
                                <div class="filter-checkbox-item">
                                    <input type="checkbox" class="loop-filter-tag" value="<?php echo esc_attr($tag->term_id); ?>" id="tag-<?php echo esc_attr($widget_id . '-' . $tag->term_id); ?>">
                                    <label for="tag-<?php echo esc_attr($widget_id . '-' . $tag->term_id); ?>"><?php echo esc_html($tag->name); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($settings['show_price_filter'] === 'yes' && class_exists('WooCommerce')) : ?>
                    <div class="filter-group">
                        <label><?php _e('Price Range', 'hello-elementor-child'); ?></label>
                        <div class="price-slider-container">
                            <div class="price-slider-wrapper">
                                <div class="price-slider-track-bg"></div>
                                <div class="price-slider-track"></div>
                                <input type="range" class="loop-price-min-slider" min="0" max="<?php echo esc_attr($max_price); ?>" value="0" step="100">
                                <input type="range" class="loop-price-max-slider" min="0" max="<?php echo esc_attr($max_price); ?>" value="<?php echo esc_attr($max_price); ?>" step="100">
                            </div>
                            <div class="price-values">
                                <span class="price-min-value">฿0</span>
                                <span class="price-max-value" data-max="<?php echo esc_attr($max_price); ?>">฿<?php echo number_format($max_price); ?></span>
                            </div>
                            <div class="price-inputs">
                                <input type="number" class="price-input loop-price-min-input" placeholder="Min" value="0" step="100">
                                <input type="number" class="price-input loop-price-max-input" placeholder="Max" value="<?php echo esc_attr($max_price); ?>" step="100">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($settings['show_reset_button'] === 'yes') : ?>
                    <button class="reset-btn loop-filter-reset"><?php _e('Reset Filters', 'hello-elementor-child'); ?></button>
                <?php endif; ?>
            </div>
        </div>
<?php
    }

    // ============ FIXED METHODS - NO REDUNDANT ATTRIBUTES ============

    /**
     * FIXED: Get ONLY WooCommerce registered attributes (global taxonomies)
     * Does NOT extract from individual products - prevents redundancy
     * 
     * This method:
     * 1. Gets attributes from WooCommerce's attribute_taxonomies table
     * 2. Only includes attributes that have terms assigned to products
     * 3. Does NOT look at product-level custom attributes
     * 4. Ensures no duplicate attribute listings
     */
    private function get_woocommerce_attributes_only()
    {
        if (!class_exists('WooCommerce')) {
            return [];
        }

        global $wpdb;

        // Get all registered WooCommerce attributes from the attributes table
        $wc_attributes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies");

        $attributes = [];

        foreach ($wc_attributes as $attribute) {
            $taxonomy = 'pa_' . $attribute->attribute_name;

            // Check if taxonomy exists
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }

            // Get all terms for this attribute (with products)
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => true, // Only show attributes that are actually used
                'orderby' => 'name',
                'order' => 'ASC',
            ]);

            // Skip if no terms or error
            if (empty($terms) || is_wp_error($terms)) {
                continue;
            }

            // Add to attributes array with full taxonomy info
            $attributes[] = [
                'name' => $attribute->attribute_name,
                'label' => $attribute->attribute_label,
                'taxonomy' => $taxonomy,
                'terms' => $terms,
            ];
        }

        return $attributes;
    }

    /**
     * Get maximum product price
     */
    private function get_max_price()
    {
        if (!class_exists('WooCommerce')) {
            return 10000;
        }

        global $wpdb;
        $max_price = $wpdb->get_var("
            SELECT MAX(CAST(meta_value AS UNSIGNED)) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_price' AND meta_value != ''
        ");
        return $max_price ? ceil($max_price / 100) * 100 : 10000;
    }

    /**
     * Get product categories
     */
    private function get_product_categories()
    {
        return get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
        ]);
    }

    /**
     * Get product tags
     */
    private function get_product_tags()
    {
        return get_terms([
            'taxonomy' => 'product_tag',
            'hide_empty' => true,
        ]);
    }
}
