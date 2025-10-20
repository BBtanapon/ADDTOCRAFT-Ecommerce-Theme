<?php
/**
 * Custom Query Sources for Elementor Loop Grid - FIXED
 * No output echoing, clean code
 *
 * @package HelloElementorChild
 */

if (!defined("ABSPATH")) {
	exit(); // Prevent direct access
}

class Custom_Elementor_Loop_Query_Sources
{
	public function __construct()
	{
		// Hook into Loop Grid widget registration
		add_action("elementor/widgets/register", [$this, "init_hooks"]);
	}

	/**
	 * Initialize hooks after widgets are registered
	 */
	public function init_hooks()
	{
		// Add custom controls to Loop Grid
		add_action(
			"elementor/element/loop-grid/section_query/before_section_end",
			[$this, "add_query_id_control"],
			10,
			2,
		);

		// Modify query results
		add_action(
			"elementor/query/query_results",
			[$this, "modify_query_results"],
			10,
			2,
		);
	}

	/**
	 * Add Query ID control to Loop Grid
	 */
	public function add_query_id_control($element, $args)
	{
		$element->add_control("custom_query_divider", [
			"type" => \Elementor\Controls_Manager::DIVIDER,
		]);

		$element->add_control("custom_query_heading", [
			"label" => __("🎯 Custom Product Queries", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::HEADING,
		]);

		$element->add_control("query_id_custom", [
			"label" => __("Custom Query", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "",
			"options" => [
				"" => __("None (Use Default)", "hello-elementor-child"),
				"popular_products" => __(
					"🔥 Popular Products",
					"hello-elementor-child",
				),
				"best_sellers" => __(
					"⭐ Best Selling Products",
					"hello-elementor-child",
				),
				"on_sale_products" => __(
					"💰 On Sale Products",
					"hello-elementor-child",
				),
				"featured_products" => __(
					"✨ Featured Products",
					"hello-elementor-child",
				),
				"top_rated_products" => __(
					"⭐⭐⭐ Top Rated Products",
					"hello-elementor-child",
				),
				"recent_products" => __(
					"🆕 Recent Products",
					"hello-elementor-child",
				),
				"low_stock_products" => __(
					"⚠️ Low Stock Products",
					"hello-elementor-child",
				),
			],
			"description" => __(
				"Select a custom product query or leave empty to use default query settings",
				"hello-elementor-child",
			),
		]);

		// Time period control
		$element->add_control("custom_time_period", [
			"label" => __("Time Period", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "all_time",
			"options" => [
				"all_time" => __("All Time", "hello-elementor-child"),
				"30_days" => __("Last 30 Days", "hello-elementor-child"),
				"7_days" => __("Last 7 Days", "hello-elementor-child"),
				"24_hours" => __("Last 24 Hours", "hello-elementor-child"),
			],
			"condition" => [
				"query_id_custom" => ["popular_products", "best_sellers"],
			],
		]);

		// Minimum stock level
		$element->add_control("low_stock_threshold", [
			"label" => __("Low Stock Threshold", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::NUMBER,
			"default" => 5,
			"min" => 1,
			"max" => 100,
			"condition" => [
				"query_id_custom" => "low_stock_products",
			],
		]);

		// Exclude out of stock
		$element->add_control("exclude_out_of_stock_custom", [
			"label" => __("Exclude Out of Stock", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"label_on" => __("Yes", "hello-elementor-child"),
			"label_off" => __("No", "hello-elementor-child"),
			"return_value" => "yes",
			"default" => "no",
			"condition" => [
				"query_id_custom!" => "",
			],
		]);
	}

	/**
	 * Modify query results based on custom query ID
	 */
	public function modify_query_results($query, $widget)
	{
		$settings = $widget->get_settings();

		if (empty($settings["query_id_custom"])) {
			return;
		}

		$query_id = $settings["query_id_custom"];
		$time_period = !empty($settings["custom_time_period"])
			? $settings["custom_time_period"]
			: "all_time";
		$exclude_out_of_stock =
			!empty($settings["exclude_out_of_stock_custom"]) &&
			$settings["exclude_out_of_stock_custom"] === "yes";
		$low_stock_threshold = !empty($settings["low_stock_threshold"])
			? intval($settings["low_stock_threshold"])
			: 5;

		// Base query args
		$args = [
			"post_type" => "product",
			"post_status" => "publish",
			"posts_per_page" => !empty($settings["posts_per_page"])
				? intval($settings["posts_per_page"])
				: 8,
			"paged" => get_query_var("paged") ? get_query_var("paged") : 1,
		];

		// Exclude out of stock
		if ($exclude_out_of_stock) {
			if (!isset($args["meta_query"])) {
				$args["meta_query"] = [];
			}
			$args["meta_query"][] = [
				"key" => "_stock_status",
				"value" => "instock",
			];
		}

		// Apply specific query
		switch ($query_id) {
			case "popular_products":
				$args = $this->get_popular_products($args, $time_period);
				break;

			case "best_sellers":
				$args = $this->get_best_sellers($args, $time_period);
				break;

			case "on_sale_products":
				$args = $this->get_on_sale_products($args);
				break;

			case "featured_products":
				$args = $this->get_featured_products($args);
				break;

			case "top_rated_products":
				$args = $this->get_top_rated_products($args);
				break;

			case "recent_products":
				$args = $this->get_recent_products($args);
				break;

			case "low_stock_products":
				$args = $this->get_low_stock_products(
					$args,
					$low_stock_threshold,
				);
				break;
		}

		// Create new WP_Query
		$new_query = new \WP_Query($args);

		// Replace the query properties
		$query->query = $new_query->query;
		$query->query_vars = $new_query->query_vars;
		$query->posts = $new_query->posts;
		$query->post_count = $new_query->post_count;
		$query->found_posts = $new_query->found_posts;
		$query->max_num_pages = $new_query->max_num_pages;
	}

	/**
	 * Popular Products Query (by total sales)
	 */
	private function get_popular_products($args, $time_period)
	{
		$args["orderby"] = "meta_value_num";
		$args["order"] = "DESC";
		$args["meta_key"] = "total_sales";

		if ($time_period !== "all_time") {
			$days_map = [
				"24_hours" => 1,
				"7_days" => 7,
				"30_days" => 30,
			];
			$days = isset($days_map[$time_period])
				? $days_map[$time_period]
				: 30;

			$args["date_query"] = [
				[
					"after" => $days . " days ago",
					"inclusive" => true,
				],
			];
		}

		return $args;
	}

	/**
	 * Best Sellers Query (by actual sales data)
	 */
	private function get_best_sellers($args, $time_period)
	{
		global $wpdb;

		$days_map = [
			"all_time" => 3650,
			"24_hours" => 1,
			"7_days" => 7,
			"30_days" => 30,
		];
		$days = isset($days_map[$time_period]) ? $days_map[$time_period] : 30;
		$date_filter = date("Y-m-d H:i:s", strtotime("-{$days} days"));

		// Get best selling product IDs
		$product_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT p.ID
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = 'product'
				AND p.post_status = 'publish'
				AND p.post_date >= %s
				AND pm.meta_key = 'total_sales'
				AND pm.meta_value > 0
				ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC
				LIMIT %d",
				$date_filter,
				$args["posts_per_page"],
			),
		);

		if (!empty($product_ids)) {
			$args["post__in"] = $product_ids;
			$args["orderby"] = "post__in";
		} else {
			// Fallback to total sales
			$args["orderby"] = "meta_value_num";
			$args["meta_key"] = "total_sales";
			$args["order"] = "DESC";
		}

		return $args;
	}

	/**
	 * On Sale Products Query
	 */
	private function get_on_sale_products($args)
	{
		$sale_product_ids = wc_get_product_ids_on_sale();

		if (!empty($sale_product_ids)) {
			$args["post__in"] = $sale_product_ids;
		} else {
			$args["post__in"] = [0]; // No products
		}

		$args["orderby"] = "date";
		$args["order"] = "DESC";

		return $args;
	}

	/**
	 * Featured Products Query
	 */
	private function get_featured_products($args)
	{
		$args["tax_query"] = [
			[
				"taxonomy" => "product_visibility",
				"field" => "name",
				"terms" => "featured",
			],
		];
		$args["orderby"] = "date";
		$args["order"] = "DESC";

		return $args;
	}

	/**
	 * Top Rated Products Query
	 */
	private function get_top_rated_products($args)
	{
		$args["orderby"] = "meta_value_num";
		$args["order"] = "DESC";
		$args["meta_key"] = "_wc_average_rating";

		if (!isset($args["meta_query"])) {
			$args["meta_query"] = [];
		}

		$args["meta_query"][] = [
			"key" => "_wc_average_rating",
			"value" => 0,
			"compare" => ">",
			"type" => "DECIMAL",
		];

		return $args;
	}

	/**
	 * Recent Products Query
	 */
	private function get_recent_products($args)
	{
		$args["orderby"] = "date";
		$args["order"] = "DESC";

		return $args;
	}

	/**
	 * Low Stock Products Query
	 */
	private function get_low_stock_products($args, $threshold)
	{
		$args["orderby"] = "meta_value_num";
		$args["order"] = "ASC";
		$args["meta_key"] = "_stock";

		if (!isset($args["meta_query"])) {
			$args["meta_query"] = [];
		}

		$args["meta_query"][] = [
			"key" => "_stock",
			"value" => $threshold,
			"compare" => "<=",
			"type" => "NUMERIC",
		];

		$args["meta_query"][] = [
			"key" => "_stock",
			"value" => 0,
			"compare" => ">",
			"type" => "NUMERIC",
		];

		return $args;
	}
}

// NO OUTPUT AFTER THIS LINE - CRITICAL!
