<?php
/**
 * Product Filter Class
 *
 * @package HelloElementorChild
 */

if (!defined("ABSPATH")) {
	exit();
}

class Product_Filter
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		add_shortcode("custom_product_grid", [$this, "render_shortcode"]);
	}

	/**
	 * Get maximum product price
	 */
	private function get_max_price()
	{
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
			"taxonomy" => "product_cat",
			"hide_empty" => true,
		]);
	}

	/**
	 * Get product tags
	 */
	private function get_product_tags()
	{
		return get_terms([
			"taxonomy" => "product_tag",
			"hide_empty" => true,
		]);
	}

	/**
	 * Get WooCommerce attributes
	 */
	private function get_wc_attributes()
	{
		if (!class_exists("WooCommerce")) {
			return [];
		}

		global $wpdb;
		return $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies",
		);
	}

	/**
	 * Get product-specific custom attributes
	 */
	private function get_custom_attributes()
	{
		$custom_attributes = [];

		$products = get_posts([
			"post_type" => "product",
			"posts_per_page" => -1,
			"post_status" => "publish",
		]);

		foreach ($products as $product_post) {
			$product = wc_get_product($product_post->ID);
			if (!$product) {
				continue;
			}

			$attributes = $product->get_attributes();
			foreach ($attributes as $attribute) {
				if ($attribute->get_id()) {
					continue;
				}

				$attr_name = $attribute->get_name();
				if (!isset($custom_attributes[$attr_name])) {
					$custom_attributes[$attr_name] = [];
				}

				$options = $attribute->get_options();
				if (!empty($options)) {
					foreach ($options as $option) {
						if (
							!in_array($option, $custom_attributes[$attr_name])
						) {
							$custom_attributes[$attr_name][] = $option;
						}
					}
				}
			}
		}

		return $custom_attributes;
	}

	/**
	 * Render filter sidebar
	 */
	private function render_filter_sidebar($max_price)
	{
		$categories = $this->get_product_categories();
		$tags = $this->get_product_tags();
		$wc_attributes = $this->get_wc_attributes();
		$custom_attributes = $this->get_custom_attributes();

		ob_start();
		?>
        <div class="filter-sidebar">
            <button class="filter-close-btn" aria-label="Close Filters"></button>

            <h3><?php _e("Filters", "hello-elementor-child"); ?></h3>

            <!-- Search -->
            <div class="filter-group">
                <label><?php _e("Search", "hello-elementor-child"); ?></label>
                <input type="text" id="filter-search" class="filter-select"
                       placeholder="<?php _e(
                       	"Search products...",
                       	"hello-elementor-child",
                       ); ?>"
                       style="background-image: none; padding-right: 12px;">
            </div>

            <!-- Sort By -->
            <div class="filter-group">
                <label><?php _e("Sort By", "hello-elementor-child"); ?></label>
                <select id="filter-sort" class="filter-select">
                    <option value="date"><?php _e(
                    	"Newest",
                    	"hello-elementor-child",
                    ); ?></option>
                    <option value="popularity"><?php _e(
                    	"Popularity",
                    	"hello-elementor-child",
                    ); ?></option>
                    <option value="price"><?php _e(
                    	"Price: Low to High",
                    	"hello-elementor-child",
                    ); ?></option>
                    <option value="price-desc"><?php _e(
                    	"Price: High to Low",
                    	"hello-elementor-child",
                    ); ?></option>
                </select>
            </div>

            <!-- Categories -->
            <?php if (!empty($categories) && !is_wp_error($categories)): ?>
            <div class="filter-group">
                <label><?php _e("Category", "hello-elementor-child"); ?></label>
                <div class="filter-checkboxes">
                    <?php foreach ($categories as $cat): ?>
                    <div class="filter-checkbox-item">
                        <input type="checkbox" class="filter-category"
                               value="<?php echo esc_attr($cat->term_id); ?>"
                               id="cat-<?php echo esc_attr($cat->term_id); ?>">
                        <label for="cat-<?php echo esc_attr($cat->term_id); ?>">
                            <?php echo esc_html($cat->name); ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- WooCommerce Attributes -->
            <?php if (!empty($wc_attributes)): ?>
                <?php foreach ($wc_attributes as $attribute):

                	$taxonomy = "pa_" . $attribute->attribute_name;
                	if (!taxonomy_exists($taxonomy)) {
                		continue;
                	}

                	$terms = get_terms([
                		"taxonomy" => $taxonomy,
                		"hide_empty" => false,
                	]);

                	if (empty($terms) || is_wp_error($terms)) {
                		continue;
                	}
                	?>
                <div class="filter-group">
                    <label><?php echo esc_html(
                    	ucfirst($attribute->attribute_label),
                    ); ?></label>
                    <div class="filter-checkboxes">
                        <?php foreach ($terms as $term): ?>
                        <div class="filter-checkbox-item">
                            <input type="checkbox" class="filter-attribute"
                                   value="<?php echo esc_attr(
                                   	$taxonomy . ":" . $term->term_id,
                                   ); ?>"
                                   id="attr-<?php echo esc_attr(
                                   	$taxonomy . "-" . $term->term_id,
                                   ); ?>">
                            <label for="attr-<?php echo esc_attr(
                            	$taxonomy . "-" . $term->term_id,
                            ); ?>">
                                <?php echo esc_html($term->name); ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
                endforeach; ?>
            <?php endif; ?>

            <!-- Custom Attributes -->
            <?php if (!empty($custom_attributes)): ?>
                <?php foreach ($custom_attributes as $attr_name => $options):

                	if (empty($options)) {
                		continue;
                	}
                	sort($options);
                	?>
                <div class="filter-group">
                    <label><?php echo esc_html(
                    	ucfirst(str_replace(["-", "_"], " ", $attr_name)),
                    ); ?></label>
                    <div class="filter-checkboxes">
                        <?php foreach ($options as $option): ?>
                        <div class="filter-checkbox-item">
                            <input type="checkbox" class="filter-custom-attribute"
                                   value="<?php echo esc_attr(
                                   	$attr_name . ":" . $option,
                                   ); ?>"
                                   id="custom-attr-<?php echo esc_attr(
                                   	sanitize_title(
                                   		$attr_name . "-" . $option,
                                   	),
                                   ); ?>">
                            <label for="custom-attr-<?php echo esc_attr(
                            	sanitize_title($attr_name . "-" . $option),
                            ); ?>">
                                <?php echo esc_html($option); ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
                endforeach; ?>
            <?php endif; ?>

            <!-- Tags -->
            <?php if (!empty($tags) && !is_wp_error($tags)): ?>
            <div class="filter-group">
                <label><?php _e("Tags", "hello-elementor-child"); ?></label>
                <div class="filter-checkboxes">
                    <?php foreach ($tags as $tag): ?>
                    <div class="filter-checkbox-item">
                        <input type="checkbox" class="filter-tag"
                               value="<?php echo esc_attr($tag->term_id); ?>"
                               id="tag-<?php echo esc_attr($tag->term_id); ?>">
                        <label for="tag-<?php echo esc_attr($tag->term_id); ?>">
                            <?php echo esc_html($tag->name); ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Price Range -->
            <div class="filter-group">
                <label><?php _e(
                	"Price Range",
                	"hello-elementor-child",
                ); ?></label>
                <div class="price-slider-container">
                    <div class="price-slider-wrapper">
                        <div class="price-slider-track-bg"></div>
                        <div class="price-slider-track"></div>
                        <input type="range" id="price-min-slider" min="0" max="<?php echo esc_attr(
                        	$max_price,
                        ); ?>" value="0" step="100">
                        <input type="range" id="price-max-slider" min="0" max="<?php echo esc_attr(
                        	$max_price,
                        ); ?>" value="<?php echo esc_attr($max_price); ?>" step="100">
                    </div>
                    <div class="price-values">
                        <span id="price-min-value">฿0</span>
                        <span id="price-max-value" data-max="<?php echo esc_attr(
                        	$max_price,
                        ); ?>">฿<?php echo number_format($max_price); ?></span>
                    </div>
                    <div class="price-inputs">
                        <input type="number" id="price-min-input" class="price-input" placeholder="Min" value="0" step="100">
                        <input type="number" id="price-max-input" class="price-input" placeholder="Max" value="<?php echo esc_attr(
                        	$max_price,
                        ); ?>" step="100">
                    </div>
                </div>
            </div>

            <button id="reset-filters" class="reset-btn"><?php _e(
            	"Reset Filters",
            	"hello-elementor-child",
            ); ?></button>
        </div>
        <?php return ob_get_clean();
	}

	/**
	 * Render shortcode
	 */
	public function render_shortcode($atts)
	{
		$atts = shortcode_atts(
			[
				"per_page" => -1,
				"columns" => 3,
			],
			$atts,
		);

		$max_price = $this->get_max_price();
		$url_search = isset($_GET["s"]) ? sanitize_text_field($_GET["s"]) : "";
		$url_props = isset($_GET["e_search_props"])
			? sanitize_text_field($_GET["e_search_props"])
			: "";

		ob_start();
		?>
        <div class="custom-product-filter-wrapper"
             data-url-search="<?php echo esc_attr($url_search); ?>"
             data-url-props="<?php echo esc_attr($url_props); ?>">

            <div class="filter-overlay"></div>

            <button class="filter-toggle-btn" aria-label="Open Filters">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"/>
                </svg>
            </button>

            <?php echo $this->render_filter_sidebar($max_price); ?>

            <div class="products-area">
                <div class="loading-overlay" style="display: none;">
                    <div class="spinner"></div>
                </div>

                <div class="products-grid" data-columns="<?php echo esc_attr(
                	$atts["columns"],
                ); ?>">
                    <?php
                    $initial_args = $atts;
                    if (!empty($url_search)) {
                    	$initial_args["search"] = $url_search;
                    }
                    if (!empty($url_props)) {
                    	$initial_args["props"] = $url_props;
                    }
                    echo $this->get_products_html($initial_args);
                    ?>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
	}

	/**
	 * Get products HTML
	 */
	public function get_products_html($args = [])
	{
		require_once get_stylesheet_directory() .
			"/includes/product-filter-query.php";
		return get_filtered_products_html($args);
	}
}

// Initialize
new Product_Filter();
