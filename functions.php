<?php

/**
 * Theme functions and definitions.
 *
 * @package HelloElementorChild
 */

if (!defined("ABSPATH")) {
	exit(); // Exit if accessed directly.
}

define("HELLO_ELEMENTOR_CHILD_VERSION", "2.0.0");

// =============================================================================
// CORE THEME SETUP
// =============================================================================

/**
 * Enqueue child theme styles
 */
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

/**
 * Enqueue Dashicons for non-logged-in users
 */
add_action("wp_enqueue_scripts", "load_dashicons_for_non_logged_in_users");
function load_dashicons_for_non_logged_in_users()
{
	if (!is_user_logged_in()) {
		wp_enqueue_style("dashicons");
	}
}

// =============================================================================
// AUTO ATTRIBUTES SYSTEM - FIXED (NO DUPLICATES)
// =============================================================================

/**
 * Get ONLY WooCommerce registered attributes (global taxonomies)
 */
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

/**
 * Get ALL attributes from a product with proper price data
 */
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

	// Get proper price data
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

	// Get categories as term IDs
	$categories = get_the_terms($product->get_id(), "product_cat");
	if ($categories && !is_wp_error($categories)) {
		$attributes_data["categories"] = wp_list_pluck($categories, "term_id");
	}

	// Get tags as term IDs
	$tags = get_the_terms($product->get_id(), "product_tag");
	if ($tags && !is_wp_error($tags)) {
		$attributes_data["tags"] = wp_list_pluck($tags, "term_id");
	}

	// Get ONLY WooCommerce registered attributes
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
 * FIXED: Output product data as JSON ONLY (no duplicate HTML)
 */
add_action("wp_footer", "output_products_data_json", 100);
function output_products_data_json()
{
	if (!class_exists("WooCommerce")) {
		return;
	}

	$products_data = [];

	// Get all published products
	$products = wc_get_products([
		"limit" => -1,
		"status" => "publish",
	]);

	foreach ($products as $product) {
		$product_data = get_all_product_attributes($product);
		if (!empty($product_data)) {
			$products_data[$product->get_id()] = $product_data;
		}
	}

	if (empty($products_data)) {
		return;
	}
	// Output ONLY JSON data (no HTML output)
	?>
    <script id="loop-grid-products-data" type="application/json">
        <?php echo wp_json_encode($products_data); ?>
    </script>
    <script>
        window.loopGridProductsData = <?php echo wp_json_encode(
        	$products_data,
        ); ?>;
        console.log('%c📦 Products data loaded:', 'color: #4CAF50; font-weight: bold;', Object.keys(window.loopGridProductsData).length + ' products');
    </script>
<?php
}

/**
 * Get all available attributes (for filter widget)
 */
function get_all_available_attributes()
{
	return get_woocommerce_attributes_only();
}

// =============================================================================
// ENQUEUE AUTO ATTRIBUTES SCRIPT
// =============================================================================

add_action("wp_enqueue_scripts", "enqueue_auto_attribute_script");
function enqueue_auto_attribute_script()
{
	// Auto attributes script
	wp_enqueue_script(
		"loop-grid-auto-attributes",
		get_stylesheet_directory_uri() . "/assets/js/auto-attributes.js",
		["jquery"],
		HELLO_ELEMENTOR_CHILD_VERSION,
		true,
	);

	wp_localize_script("loop-grid-auto-attributes", "autoAttributesData", [
		"ajaxUrl" => admin_url("admin-ajax.php"),
		"nonce" => wp_create_nonce("loop_grid_filter_nonce"),
	]);
}

// =============================================================================
// LOOP GRID FILTER SYSTEM
// =============================================================================

/**
 * Enqueue Loop Grid Filter assets
 */
add_action("wp_enqueue_scripts", "enqueue_loop_grid_filter_assets");
function enqueue_loop_grid_filter_assets()
{
	// Only load on pages with Elementor
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

	// JavaScript
	wp_enqueue_script(
		"loop-grid-filter-script",
		get_stylesheet_directory_uri() . "/assets/js/loop-grid-filter.js",
		["jquery", "elementor-frontend"],
		HELLO_ELEMENTOR_CHILD_VERSION,
		true,
	);

	// Localize script
	wp_localize_script("loop-grid-filter-script", "loopGridFilterData", [
		"ajaxUrl" => admin_url("admin-ajax.php"),
		"nonce" => wp_create_nonce("loop_grid_filter_nonce"),
	]);
}

// =============================================================================
// WOOCOMMERCE AJAX ADD TO CART
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
// ELEMENTOR CUSTOM WIDGETS
// =============================================================================

/**
 * Register Elementor Widget Category
 */
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

/**
 * Register Custom Elementor Widgets
 */
add_action("elementor/widgets/register", "register_custom_elementor_widgets");
function register_custom_elementor_widgets($widgets_manager)
{
	$widget_files = [
		"login-logout-widget.php",
		"product-image-hover-widget.php",
		"product-badge-widget.php",
		"loop-grid-filter-widget.php",
		"product-add-to-cart.php",
		"custom-product-loop-grid.php", // NEW WIDGET
	];

	foreach ($widget_files as $file) {
		$file_path = get_stylesheet_directory() . "/elementor-widgets/" . $file;
		if (file_exists($file_path)) {
			require_once $file_path;
		}
	}

	// Register widgets
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

/**
 * Create elementor-widgets directory check
 */
add_action("init", "create_elementor_widgets_directory");
function create_elementor_widgets_directory()
{
	$widgets_dir = get_stylesheet_directory() . "/elementor-widgets";
	if (!file_exists($widgets_dir)) {
		wp_mkdir_p($widgets_dir);
	}
}
