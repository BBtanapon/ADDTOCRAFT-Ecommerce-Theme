<?php
/**
 * Theme functions and definitions - COMPLETE FIX
 *
 * @package HelloElementorChild
 */

if (!defined("ABSPATH")) {
	exit();
}

define("HELLO_ELEMENTOR_CHILD_VERSION", "2.1.0");

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
// ELEMENTOR CSS PRINTING FIX - CRITICAL
// =============================================================================

/**
 * Force print Elementor template CSS inline
 */
add_action("wp_head", "force_print_elementor_loop_css", 999);
function force_print_elementor_loop_css()
{
	if (!class_exists("\Elementor\Plugin")) {
		return;
	}

	global $post;

	if (!$post) {
		return;
	}

	// Get Elementor data
	$document = \Elementor\Plugin::$instance->documents->get($post->ID);

	if (!$document) {
		return;
	}

	$data = $document->get_elements_data();

	if (empty($data)) {
		return;
	}

	// Find all loop grids and their templates
	$template_ids = [];
	array_walk_recursive($data, function ($value, $key) use (&$template_ids) {
		if ($key === "template_id" && !empty($value)) {
			$template_ids[] = $value;
		}
	});

	// Print CSS for each template
	foreach (array_unique($template_ids) as $template_id) {
		$css_file = \Elementor\Core\Files\CSS\Post::create($template_id);

		if ($css_file) {
			echo "\n<!-- Loop Template CSS: {$template_id} -->\n";
			echo '<style id="elementor-post-' . esc_attr($template_id) . '">';
			echo $css_file->get_content();
			echo "</style>" . "\n";
		}
	}
}

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
	$css_file = \Elementor\Core\Files\CSS\Post::create($post_id);
	$css_file->update();
}

/**
 * Clear and regenerate all CSS
 */
add_action("init", "maybe_regenerate_all_elementor_css");
function maybe_regenerate_all_elementor_css()
{
	// Change version to force regeneration
	if (!get_option("elementor_css_regenerated_v4")) {
		if (class_exists("\Elementor\Plugin")) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
			update_option("elementor_css_regenerated_v4", true);
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

add_action("wp_footer", "output_products_data_json", 100);
function output_products_data_json()
{
	if (!class_exists("WooCommerce")) {
		return;
	}

	$products_data = [];
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

	wp_localize_script("loop-grid-auto-attributes", "autoAttributesData", [
		"ajaxUrl" => admin_url("admin-ajax.php"),
		"nonce" => wp_create_nonce("loop_grid_filter_nonce"),
	]);

	wp_localize_script("loop-grid-filter-script", "loopGridFilterData", [
		"ajaxUrl" => admin_url("admin-ajax.php"),
		"nonce" => wp_create_nonce("loop_grid_filter_nonce"),
	]);
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
