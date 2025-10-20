<?php
/**
 * Theme functions and definitions - COMPLETE FIX
 * CSS Output Issue Resolved
 *
 * @package HelloElementorChild
 */

if (!defined("ABSPATH")) {
	exit();
}

define("HELLO_ELEMENTOR_CHILD_VERSION", "2.1.1");

// =============================================================================
// CORE THEME SETUP
// =============================================================================

add_action("wp_enqueue_scripts", "hello_elementor_child_scripts_styles", 20);
function hello_elementor_child_scripts_styles()
{
	wp_enqueue_style(
		"hello-elementor-child-style",
		get_stylesheet_directory_uri() . "/style.css",
		["hello-elementor-theme-style"],
		HELLO_ELEMENTOR_CHILD_VERSION,
	);
}

add_action("wp_enqueue_scripts", "load_dashicons_for_non_logged_in_users");
function load_dashicons_for_non_logged_in_users()
{
	if (!is_user_logged_in()) {
		wp_enqueue_style("dashicons");
	}
}

// =============================================================================
// ELEMENTOR CSS - SIMPLIFIED (Let Elementor Handle It)
// =============================================================================

/**
 * Ensure CSS files are generated
 */
add_action("elementor/css-file/post/enqueue", "ensure_css_file_generated");
function ensure_css_file_generated($css_file)
{
	$css_file->update();
}

/**
 * Force CSS regeneration on save
 */
add_action("elementor/editor/after_save", "force_css_regeneration", 10, 2);
function force_css_regeneration($post_id, $editor_data)
{
	if (!class_exists("\Elementor\Core\Files\CSS\Post")) {
		return;
	}

	$css_file = \Elementor\Core\Files\CSS\Post::create($post_id);
	if ($css_file) {
		$css_file->update();
	}
}

/**
 * Clear and regenerate all CSS (one-time on theme update)
 */
add_action("init", "maybe_regenerate_all_elementor_css");
function maybe_regenerate_all_elementor_css()
{
	if (!get_option("elementor_css_regenerated_v5")) {
		if (class_exists("\Elementor\Plugin")) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
			update_option("elementor_css_regenerated_v5", true);
		}
	}
}

// =============================================================================
// AUTO ATTRIBUTES SYSTEM
// =============================================================================

function get_woocommerce_attributes_only()
{
	if (!class_exists("WooCommerce")) {
		return [];
	}

	global $wpdb;
	$wc_attributes = $wpdb->get_results(
		"SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies",
	);

	$attributes = [];

	foreach ($wc_attributes as $attribute) {
		$taxonomy = "pa_" . $attribute->attribute_name;

		if (!taxonomy_exists($taxonomy)) {
			continue;
		}

		$terms = get_terms([
			"taxonomy" => $taxonomy,
			"hide_empty" => true,
			"orderby" => "name",
			"order" => "ASC",
		]);

		if (empty($terms) || is_wp_error($terms)) {
			continue;
		}

		$attributes[$taxonomy] = [
			"name" => $attribute->attribute_name,
			"label" => $attribute->attribute_label,
			"taxonomy" => $taxonomy,
			"terms" => $terms,
		];
	}

	return $attributes;
}

function get_all_product_attributes($product)
{
	if (!$product) {
		return [];
	}

	$attributes_data = [
		"id" => $product->get_id(),
		"title" => $product->get_name(),
		"price" => 0,
		"regular_price" => 0,
		"sale_price" => 0,
		"on_sale" => $product->is_on_sale(),
		"min_price" => 0,
		"max_price" => 0,
		"categories" => [],
		"tags" => [],
		"attributes" => [],
	];

	if ($product->is_type("variable")) {
		$variation_prices = $product->get_variation_prices(true);
		if (!empty($variation_prices["price"])) {
			$attributes_data["min_price"] = min($variation_prices["price"]);
			$attributes_data["max_price"] = max($variation_prices["price"]);
			$attributes_data["price"] = $attributes_data["min_price"];
		}
		if (!empty($variation_prices["regular_price"])) {
			$attributes_data["regular_price"] = min(
				$variation_prices["regular_price"],
			);
		}
		if (!empty($variation_prices["sale_price"]) && $product->is_on_sale()) {
			$attributes_data["sale_price"] = min(
				$variation_prices["sale_price"],
			);
		}
	} else {
		$attributes_data[
			"regular_price"
		] = (float) $product->get_regular_price();
		$attributes_data["sale_price"] = (float) $product->get_sale_price();

		if ($product->is_on_sale() && $attributes_data["sale_price"] > 0) {
			$attributes_data["price"] = $attributes_data["sale_price"];
		} else {
			$attributes_data["price"] = $attributes_data["regular_price"];
		}
	}

	$categories = get_the_terms($product->get_id(), "product_cat");
	if ($categories && !is_wp_error($categories)) {
		$attributes_data["categories"] = wp_list_pluck($categories, "term_id");
	}

	$tags = get_the_terms($product->get_id(), "product_tag");
	if ($tags && !is_wp_error($tags)) {
		$attributes_data["tags"] = wp_list_pluck($tags, "term_id");
	}

	$product_attributes = $product->get_attributes();

	foreach ($product_attributes as $attribute) {
		if (!$attribute->is_taxonomy()) {
			continue;
		}

		$taxonomy = $attribute->get_name();

		if (strpos($taxonomy, "pa_") !== 0) {
			continue;
		}

		$terms = wc_get_product_terms($product->get_id(), $taxonomy, [
			"fields" => "all",
		]);

		if (!empty($terms) && !is_wp_error($terms)) {
			$attributes_data["attributes"][$taxonomy] = wp_list_pluck(
				$terms,
				"slug",
			);
		}
	}

	return $attributes_data;
}

/**
 * OPTIMIZED: Output only necessary products data
 * Reduces redundancy and improves performance
 */
add_action("wp_footer", "output_products_data_json", 100);
function output_products_data_json()
{
	if (!class_exists("WooCommerce")) {
		return;
	}

	global $post;

	$products_data = [];

	// Determine which products to load based on context
	$args = [
		"limit" => -1,
		"status" => "publish",
	];

	// If we're on a specific category page, only load those products
	if (is_product_category()) {
		$category = get_queried_object();
		$args["category"] = [$category->slug];
	}
	// If we're on a tag page, only load those products
	elseif (is_product_tag()) {
		$tag = get_queried_object();
		$args["tag"] = [$tag->slug];
	}

	$products = wc_get_products($args);

	foreach ($products as $product) {
		$product_data = get_all_product_attributes($product);
		if (!empty($product_data)) {
			$products_data[$product->get_id()] = $product_data;
		}
	}

	if (empty($products_data)) {
		return;
	}
	?>
<script id="loop-grid-products-data" type="application/json">
<?php echo wp_json_encode($products_data); ?>
</script>
<script>
window.loopGridProductsData = <?php echo wp_json_encode($products_data); ?>;
console.log('%c📦 Products data loaded:', 'color: #4CAF50; font-weight: bold;', Object.keys(window.loopGridProductsData).length + ' products');
</script>
<?php
}

function get_all_available_attributes()
{
	return get_woocommerce_attributes_only();
}

// =============================================================================
// ENQUEUE SCRIPTS
// =============================================================================

add_action("wp_enqueue_scripts", "enqueue_all_filter_assets");
function enqueue_all_filter_assets()
{
	if (!did_action("elementor/loaded")) {
		return;
	}

	// CSS
	wp_enqueue_style(
		"loop-grid-filter-styles",
		get_stylesheet_directory_uri() . "/assets/css/product-filter.css",
		[],
		HELLO_ELEMENTOR_CHILD_VERSION,
	);

	// Auto attributes
	wp_enqueue_script(
		"loop-grid-auto-attributes",
		get_stylesheet_directory_uri() . "/assets/js/auto-attributes.js",
		["jquery"],
		HELLO_ELEMENTOR_CHILD_VERSION,
		true,
	);

	// Filter script
	wp_enqueue_script(
		"loop-grid-filter-script",
		get_stylesheet_directory_uri() . "/assets/js/loop-grid-filter.js",
		["jquery", "elementor-frontend"],
		HELLO_ELEMENTOR_CHILD_VERSION,
		true,
	);

	// Force CSS loader
	wp_enqueue_script(
		"force-template-css",
		get_stylesheet_directory_uri() . "/assets/js/force-template-css.js",
		["jquery", "elementor-frontend"],
		HELLO_ELEMENTOR_CHILD_VERSION,
		true,
	);

	// Pagination script
	wp_enqueue_script(
		"custom-loop-grid-pagination",
		get_stylesheet_directory_uri() . "/assets/js/loop-grid-pagination.js",
		["jquery", "elementor-frontend"],
		HELLO_ELEMENTOR_CHILD_VERSION,
		true,
	);

	// Loop Grid Layout Fix
	wp_enqueue_script(
		"fix-loop-grid-layout",
		get_stylesheet_directory_uri() . "/assets/js/fix-loop-grid-layout.js",
		["jquery", "elementor-frontend"],
		HELLO_ELEMENTOR_CHILD_VERSION,
		true,
	);

	wp_localize_script("loop-grid-auto-attributes", "autoAttributesData", [
		"ajaxUrl" => admin_url("admin-ajax.php"),
		"nonce" => wp_create_nonce("loop_grid_filter_nonce"),
	]);

	wp_localize_script("loop-grid-filter-script", "loopGridFilterData", [
		"ajaxUrl" => admin_url("admin-ajax.php"),
		"nonce" => wp_create_nonce("loop_grid_filter_nonce"),
	]);

	wp_localize_script(
		"custom-loop-grid-pagination",
		"loopGridPaginationData",
		[
			"ajaxUrl" => admin_url("admin-ajax.php"),
			"nonce" => wp_create_nonce("loop_grid_pagination_nonce"),
		],
	);
}

// =============================================================================
// AJAX PAGINATION HANDLER
// =============================================================================

add_action("wp_ajax_load_more_products", "ajax_load_more_products");
add_action("wp_ajax_nopriv_load_more_products", "ajax_load_more_products");

function ajax_load_more_products()
{
	// Verify nonce
	check_ajax_referer("loop_grid_pagination_nonce", "nonce");

	// Get parameters
	$page = isset($_POST["page"]) ? intval($_POST["page"]) : 1;
	$query_args = isset($_POST["query_args"])
		? json_decode(stripslashes($_POST["query_args"]), true)
		: [];
	$settings = isset($_POST["settings"])
		? json_decode(stripslashes($_POST["settings"]), true)
		: [];
	$widget_id = isset($_POST["widget_id"])
		? sanitize_text_field($_POST["widget_id"])
		: "";

	// Update page number
	$query_args["paged"] = $page;

	// Execute query
	$products_query = new WP_Query($query_args);

	if (!$products_query->have_posts()) {
		wp_send_json_error([
			"message" => __("No more products found.", "hello-elementor-child"),
		]);
	}

	// Generate HTML
	ob_start();

	while ($products_query->have_posts()) {

		$products_query->the_post();
		global $product;

		if (!$product) {
			continue;
		}

		// Get product data
		$product_data = [];
		$product_data["product-id"] = $product->get_id();
		$product_data["title"] = $product->get_name();
		$product_data["price"] = $product->get_price();
		$product_data["regular-price"] = $product->get_regular_price();
		$product_data["sale-price"] = $product->get_sale_price();

		// Variable product
		if ($product->is_type("variable")) {
			$variation_prices = $product->get_variation_prices(true);
			if (!empty($variation_prices["price"])) {
				$product_data["min-price"] = min($variation_prices["price"]);
				$product_data["max-price"] = max($variation_prices["price"]);
			}
		}

		// Categories
		$categories = get_the_terms($product->get_id(), "product_cat");
		if ($categories && !is_wp_error($categories)) {
			$product_data["categories"] = implode(
				",",
				wp_list_pluck($categories, "term_id"),
			);
		}

		// Tags
		$tags = get_the_terms($product->get_id(), "product_tag");
		if ($tags && !is_wp_error($tags)) {
			$product_data["tags"] = implode(
				",",
				wp_list_pluck($tags, "term_id"),
			);
		}

		// Attributes
		$product_attributes = $product->get_attributes();
		foreach ($product_attributes as $attribute) {
			if (!$attribute->is_taxonomy()) {
				continue;
			}

			$taxonomy = $attribute->get_name();
			if (strpos($taxonomy, "pa_") !== 0) {
				continue;
			}

			$terms = wc_get_product_terms($product->get_id(), $taxonomy, [
				"fields" => "slugs",
			]);

			if (!empty($terms) && !is_wp_error($terms)) {
				$product_data[$taxonomy] = implode(",", $terms);
			}
		}

		// Render data attributes
		$data_attrs = "";
		foreach ($product_data as $key => $value) {
			$data_attrs .= sprintf(
				' data-%s="%s"',
				esc_attr($key),
				esc_attr($value),
			);
		}
		?>
<article class="e-loop-item product-loop-item product-id-<?php echo esc_attr(
	$product->get_id(),
); ?>" <?php echo $data_attrs; ?>>
	<?php if (
 	!empty($settings["use_custom_template"]) &&
 	$settings["use_custom_template"] === "yes" &&
 	!empty($settings["template_id"])
 ) {
 	echo \Elementor\Plugin::instance()->frontend->get_builder_content(
 		$settings["template_id"],
 		true,
 	);
 } else {
 	render_default_product_card_ajax($product);
 } ?>
</article>
<?php
	}

	wp_reset_postdata();

	$html = ob_get_clean();

	wp_send_json_success([
		"html" => $html,
		"page" => $page,
		"max_pages" => $products_query->max_num_pages,
	]);
}

function render_default_product_card_ajax($product)
{
	$is_on_sale = $product->is_on_sale();
	$tags = get_the_terms($product->get_id(), "product_tag");
	$main_tag = $tags && !is_wp_error($tags) ? $tags[0]->name : "";
	?>
<div class="default-product-card">
	<div class="product-badges">
		<?php if ($is_on_sale): ?>
		<span class="badge-sale"><?php _e("Sale!", "hello-elementor-child"); ?></span>
		<?php endif; ?>
		<?php if ($main_tag): ?>
		<span class="badge-tag"><?php echo esc_html($main_tag); ?></span>
		<?php endif; ?>
	</div>

	<a href="<?php echo esc_url(get_permalink()); ?>" class="product-image-link">
		<?php echo $product->get_image("woocommerce_thumbnail"); ?>
	</a>

	<div class="product-info">
		<h3 class="product-title">
			<a href="<?php echo esc_url(get_permalink()); ?>">
				<?php echo esc_html($product->get_name()); ?>
			</a>
		</h3>

		<div class="product-price">
			<?php echo $product->get_price_html(); ?>
		</div>

		<div class="product-actions">
			<?php if ($product->is_type("variable")): ?>
			<a href="<?php echo esc_url(get_permalink()); ?>" class="btn-select-options">
				<?php _e("SELECT OPTIONS", "hello-elementor-child"); ?>
			</a>
			<?php else: ?>
			<?php woocommerce_template_loop_add_to_cart(); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
}

// =============================================================================
// WOOCOMMERCE AJAX
// =============================================================================

add_action(
	"wp_ajax_woocommerce_ajax_add_to_cart",
	"woocommerce_ajax_add_to_cart",
);
add_action(
	"wp_ajax_nopriv_woocommerce_ajax_add_to_cart",
	"woocommerce_ajax_add_to_cart",
);

function woocommerce_ajax_add_to_cart()
{
	$product_id = apply_filters(
		"woocommerce_add_to_cart_product_id",
		absint($_POST["product_id"]),
	);
	$quantity = empty($_POST["quantity"])
		? 1
		: wc_stock_amount($_POST["quantity"]);
	$variation_id = isset($_POST["variation_id"])
		? absint($_POST["variation_id"])
		: 0;
	$passed_validation = apply_filters(
		"woocommerce_add_to_cart_validation",
		true,
		$product_id,
		$quantity,
	);
	$product_status = get_post_status($product_id);

	if (
		$passed_validation &&
		WC()->cart->add_to_cart($product_id, $quantity, $variation_id) &&
		"publish" === $product_status
	) {
		do_action("woocommerce_ajax_added_to_cart", $product_id);

		if ("yes" === get_option("woocommerce_cart_redirect_after_add")) {
			wc_add_to_cart_message([$product_id => $quantity], true);
		}

		WC_AJAX::get_refreshed_fragments();
	} else {
		$data = [
			"error" => true,
			"product_url" => apply_filters(
				"woocommerce_cart_redirect_after_error",
				get_permalink($product_id),
				$product_id,
			),
		];

		echo wp_send_json($data);
	}

	wp_die();
}

// =============================================================================
// ELEMENTOR WIDGETS
// =============================================================================

add_action(
	"elementor/elements/categories_registered",
	"add_elementor_widget_categories",
);
function add_elementor_widget_categories($elements_manager)
{
	$elements_manager->add_category("custom-widgets", [
		"title" => __("Custom Widgets", "hello-elementor-child"),
		"icon" => "fa fa-plug",
	]);
}

add_action("elementor/widgets/register", "register_custom_elementor_widgets");
function register_custom_elementor_widgets($widgets_manager)
{
	$widget_files = [
		"login-logout-widget.php",
		"product-image-hover-widget.php",
		"product-badge-widget.php",
		"loop-grid-filter-widget.php",
		"product-add-to-cart.php",
		"custom-product-loop-grid.php",
	];

	foreach ($widget_files as $file) {
		$file_path = get_stylesheet_directory() . "/elementor-widgets/" . $file;
		if (file_exists($file_path)) {
			require_once $file_path;
		}
	}

	if (class_exists("Elementor_Login_Logout_Widget")) {
		$widgets_manager->register(new \Elementor_Login_Logout_Widget());
	}
	if (class_exists("Elementor_Product_Image_Hover_Widget")) {
		$widgets_manager->register(new \Elementor_Product_Image_Hover_Widget());
	}
	if (class_exists("Elementor_Product_Badge_Widget")) {
		$widgets_manager->register(new \Elementor_Product_Badge_Widget());
	}
	if (class_exists("Elementor_Loop_Grid_Filter_Widget")) {
		$widgets_manager->register(new \Elementor_Loop_Grid_Filter_Widget());
	}
	if (class_exists("Elementor_Product_Add_To_Cart")) {
		$widgets_manager->register(new \Elementor_Product_Add_To_Cart());
	}
	if (class_exists("Elementor_Custom_Product_Loop_Grid")) {
		$widgets_manager->register(new \Elementor_Custom_Product_Loop_Grid());
	}
}

add_action("init", "create_elementor_widgets_directory");
function create_elementor_widgets_directory()
{
	$widgets_dir = get_stylesheet_directory() . "/elementor-widgets";
	if (!file_exists($widgets_dir)) {
		wp_mkdir_p($widgets_dir);
	}
}

// =============================================================================
// CUSTOM LOOP QUERIES
// =============================================================================

$loop_queries_file =
	get_stylesheet_directory() . "/includes/class-elementor-loop-queries.php";
if (file_exists($loop_queries_file)) {
	require_once $loop_queries_file;
	new Custom_Elementor_Loop_Query_Sources();
}
