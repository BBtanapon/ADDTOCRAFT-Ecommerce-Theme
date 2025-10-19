<?php
/**
 * Custom Product Loop Grid Widget
 * Works perfectly with Loop Grid Filter Widget
 * Can use Elementor templates for product cards
 *
 * @package HelloElementorChild
 */

if (!defined("ABSPATH")) {
	exit();
}

class Elementor_Custom_Product_Loop_Grid extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return "custom_product_loop_grid";
	}

	public function get_title()
	{
		return __("Custom Product Loop Grid", "hello-elementor-child");
	}

	public function get_icon()
	{
		return "eicon-products";
	}

	public function get_categories()
	{
		return ["custom-widgets"];
	}

	public function get_keywords()
	{
		return ["product", "loop", "grid", "woocommerce", "shop"];
	}

	protected function register_controls()
	{
		// ============ QUERY SETTINGS ============
		$this->start_controls_section("section_query", [
			"label" => __("Query", "hello-elementor-child"),
		]);

		$this->add_control("query_type", [
			"label" => __("Query Type", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "recent",
			"options" => [
				"recent" => __("Recent Products", "hello-elementor-child"),
				"featured" => __("Featured Products", "hello-elementor-child"),
				"sale" => __("On Sale Products", "hello-elementor-child"),
				"popular" => __("Popular Products", "hello-elementor-child"),
				"top_rated" => __(
					"Top Rated Products",
					"hello-elementor-child",
				),
				"categories" => __("By Categories", "hello-elementor-child"),
				"tags" => __("By Tags", "hello-elementor-child"),
				"custom" => __("Custom Query", "hello-elementor-child"),
			],
		]);

		$this->add_control("posts_per_page", [
			"label" => __("Products Per Page", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::NUMBER,
			"default" => 12,
			"min" => 1,
			"max" => 100,
		]);

		// Category selection
		$categories = get_terms([
			"taxonomy" => "product_cat",
			"hide_empty" => false,
		]);

		$category_options = [];
		if (!empty($categories) && !is_wp_error($categories)) {
			foreach ($categories as $category) {
				$category_options[$category->term_id] = $category->name;
			}
		}

		$this->add_control("category_ids", [
			"label" => __("Select Categories", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT2,
			"multiple" => true,
			"options" => $category_options,
			"condition" => [
				"query_type" => "categories",
			],
		]);

		// Tag selection
		$tags = get_terms([
			"taxonomy" => "product_tag",
			"hide_empty" => false,
		]);

		$tag_options = [];
		if (!empty($tags) && !is_wp_error($tags)) {
			foreach ($tags as $tag) {
				$tag_options[$tag->term_id] = $tag->name;
			}
		}

		$this->add_control("tag_ids", [
			"label" => __("Select Tags", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT2,
			"multiple" => true,
			"options" => $tag_options,
			"condition" => [
				"query_type" => "tags",
			],
		]);

		$this->add_control("orderby", [
			"label" => __("Order By", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "date",
			"options" => [
				"date" => __("Date", "hello-elementor-child"),
				"title" => __("Title", "hello-elementor-child"),
				"price" => __("Price", "hello-elementor-child"),
				"popularity" => __("Popularity", "hello-elementor-child"),
				"rating" => __("Rating", "hello-elementor-child"),
				"rand" => __("Random", "hello-elementor-child"),
			],
		]);

		$this->add_control("order", [
			"label" => __("Order", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "DESC",
			"options" => [
				"ASC" => __("Ascending", "hello-elementor-child"),
				"DESC" => __("Descending", "hello-elementor-child"),
			],
		]);

		$this->add_control("exclude_out_of_stock", [
			"label" => __("Exclude Out of Stock", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"default" => "no",
		]);

		$this->end_controls_section();

		// ============ LAYOUT SETTINGS ============
		$this->start_controls_section("section_layout", [
			"label" => __("Layout", "hello-elementor-child"),
		]);

		$this->add_responsive_control("columns", [
			"label" => __("Columns", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "3",
			"tablet_default" => "2",
			"mobile_default" => "1",
			"options" => [
				"1" => "1",
				"2" => "2",
				"3" => "3",
				"4" => "4",
				"5" => "5",
				"6" => "6",
			],
			"selectors" => [
				"{{WRAPPER}} .custom-product-loop-grid" =>
					"grid-template-columns: repeat({{VALUE}}, 1fr);",
			],
		]);

		$this->add_responsive_control("column_gap", [
			"label" => __("Column Gap", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SLIDER,
			"size_units" => ["px"],
			"range" => [
				"px" => [
					"min" => 0,
					"max" => 100,
				],
			],
			"default" => [
				"size" => 30,
			],
			"selectors" => [
				"{{WRAPPER}} .custom-product-loop-grid" =>
					"column-gap: {{SIZE}}{{UNIT}};",
			],
		]);

		$this->add_responsive_control("row_gap", [
			"label" => __("Row Gap", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SLIDER,
			"size_units" => ["px"],
			"range" => [
				"px" => [
					"min" => 0,
					"max" => 100,
				],
			],
			"default" => [
				"size" => 30,
			],
			"selectors" => [
				"{{WRAPPER}} .custom-product-loop-grid" =>
					"row-gap: {{SIZE}}{{UNIT}};",
			],
		]);

		$this->end_controls_section();

		// ============ TEMPLATE SETTINGS ============
		$this->start_controls_section("section_template", [
			"label" => __("Product Card Template", "hello-elementor-child"),
		]);

		$this->add_control("use_custom_template", [
			"label" => __("Use Custom Template", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"default" => "no",
			"description" => __(
				"Use an Elementor template for product cards",
				"hello-elementor-child",
			),
		]);

		// Get all Elementor templates
		$templates = \Elementor\Plugin::instance()
			->templates_manager->get_source("local")
			->get_items();
		$template_options = [
			"" => __("Select Template", "hello-elementor-child"),
		];

		if (!empty($templates)) {
			foreach ($templates as $template) {
				$template_options[$template["template_id"]] =
					$template["title"];
			}
		}

		$this->add_control("template_id", [
			"label" => __("Select Template", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"options" => $template_options,
			"condition" => [
				"use_custom_template" => "yes",
			],
			"description" => __(
				"Create a product card template in Templates > Add New",
				"hello-elementor-child",
			),
		]);

		$this->add_control("template_note", [
			"type" => \Elementor\Controls_Manager::RAW_HTML,
			"raw" => __(
				"<strong>How to use:</strong><br>1. Go to Templates > Saved Templates > Add New<br>2. Create your product card design<br>3. Use Dynamic Tags to show product data<br>4. Select that template above",
				"hello-elementor-child",
			),
			"content_classes" =>
				"elementor-panel-alert elementor-panel-alert-info",
			"condition" => [
				"use_custom_template" => "yes",
			],
		]);

		$this->end_controls_section();

		// ============ STYLE SECTION ============
		$this->start_controls_section("section_style", [
			"label" => __("Grid Style", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_control("grid_background", [
			"label" => __("Background Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"selectors" => [
				"{{WRAPPER}} .custom-product-loop-grid" =>
					"background-color: {{VALUE}};",
			],
		]);

		$this->add_responsive_control("grid_padding", [
			"label" => __("Padding", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::DIMENSIONS,
			"size_units" => ["px", "em", "%"],
			"selectors" => [
				"{{WRAPPER}} .custom-product-loop-grid" =>
					"padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
			],
		]);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$widget_id = $this->get_id();

		// Build query
		$query_args = $this->build_query_args($settings);

		// Execute query
		$products_query = new WP_Query($query_args);

		if (!$products_query->have_posts()) {
			echo '<div class="no-products-found">' .
				__("No products found.", "hello-elementor-child") .
				"</div>";
			return;
		}

		// Get grid class for responsive columns
		$grid_class = "custom-product-loop-grid";
		$grid_class .= " elementor-grid";
		$grid_class .= " elementor-grid-" . $settings["columns"];
		$grid_class .=
			" elementor-grid-tablet-" . ($settings["columns_tablet"] ?? "2");
		$grid_class .=
			" elementor-grid-mobile-" . ($settings["columns_mobile"] ?? "1");
		?>
        <div class="custom-product-loop-wrapper" id="product-loop-<?php echo esc_attr(
        	$widget_id,
        ); ?>">
            <div class="<?php echo esc_attr($grid_class); ?>"
                 data-widget-id="<?php echo esc_attr($widget_id); ?>"
                 data-columns="<?php echo esc_attr($settings["columns"]); ?>">

                <?php
                while ($products_query->have_posts()) {

                	$products_query->the_post();
                	global $product;

                	if (!$product) {
                		continue;
                	}

                	// Get all product data for filtering
                	$product_data = $this->get_product_data_attributes(
                		$product,
                	);
                	?>
                    <article class="e-loop-item product-loop-item product-id-<?php echo esc_attr(
                    	$product->get_id(),
                    ); ?>"
                             <?php echo $this->render_data_attributes(
                             	$product_data,
                             ); ?>>

                        <?php if (
                        	$settings["use_custom_template"] === "yes" &&
                        	!empty($settings["template_id"])
                        ) {
                        	// Render Elementor template
                        	$this->render_elementor_template(
                        		$settings["template_id"],
                        	);
                        } else {
                        	// Render default product card
                        	$this->render_default_product_card($product);
                        } ?>

                    </article>
                    <?php
                }
                wp_reset_postdata();
                ?>

            </div>
        </div>

        <style>
            #product-loop-<?php echo esc_attr(
            	$widget_id,
            ); ?> .custom-product-loop-grid {
                display: grid;
                width: 100%;
            }

            #product-loop-<?php echo esc_attr($widget_id); ?> .e-loop-item {
                position: relative;
            }
        </style>
        <?php
	}

	/**
	 * Build WP_Query arguments based on settings
	 */
	private function build_query_args($settings)
	{
		$args = [
			"post_type" => "product",
			"post_status" => "publish",
			"posts_per_page" => $settings["posts_per_page"],
			"ignore_sticky_posts" => true,
		];

		// Order
		switch ($settings["orderby"]) {
			case "price":
				$args["orderby"] = "meta_value_num";
				$args["meta_key"] = "_price";
				$args["order"] = $settings["order"];
				break;
			case "popularity":
				$args["orderby"] = "meta_value_num";
				$args["meta_key"] = "total_sales";
				$args["order"] = "DESC";
				break;
			case "rating":
				$args["orderby"] = "meta_value_num";
				$args["meta_key"] = "_wc_average_rating";
				$args["order"] = "DESC";
				break;
			default:
				$args["orderby"] = $settings["orderby"];
				$args["order"] = $settings["order"];
		}

		// Query type
		switch ($settings["query_type"]) {
			case "featured":
				$args["tax_query"] = [
					[
						"taxonomy" => "product_visibility",
						"field" => "name",
						"terms" => "featured",
					],
				];
				break;

			case "sale":
				$product_ids_on_sale = wc_get_product_ids_on_sale();
				$args["post__in"] = !empty($product_ids_on_sale)
					? $product_ids_on_sale
					: [0];
				break;

			case "categories":
				if (!empty($settings["category_ids"])) {
					$args["tax_query"] = [
						[
							"taxonomy" => "product_cat",
							"field" => "term_id",
							"terms" => $settings["category_ids"],
						],
					];
				}
				break;

			case "tags":
				if (!empty($settings["tag_ids"])) {
					$args["tax_query"] = [
						[
							"taxonomy" => "product_tag",
							"field" => "term_id",
							"terms" => $settings["tag_ids"],
						],
					];
				}
				break;
		}

		// Exclude out of stock
		if ($settings["exclude_out_of_stock"] === "yes") {
			$args["meta_query"][] = [
				"key" => "_stock_status",
				"value" => "instock",
			];
		}

		return apply_filters(
			"custom_product_loop_query_args",
			$args,
			$settings,
		);
	}

	/**
	 * Get all product data for filtering
	 */
	private function get_product_data_attributes($product)
	{
		$data = [
			"product-id" => $product->get_id(),
			"title" => $product->get_name(),
			"price" => $product->get_price(),
			"regular-price" => $product->get_regular_price(),
			"sale-price" => $product->get_sale_price(),
		];

		// Variable product prices
		if ($product->is_type("variable")) {
			$variation_prices = $product->get_variation_prices(true);
			if (!empty($variation_prices["price"])) {
				$data["min-price"] = min($variation_prices["price"]);
				$data["max-price"] = max($variation_prices["price"]);
			}
		}

		// Categories (as term IDs)
		$categories = get_the_terms($product->get_id(), "product_cat");
		if ($categories && !is_wp_error($categories)) {
			$data["categories"] = implode(
				",",
				wp_list_pluck($categories, "term_id"),
			);
		}

		// Tags (as term IDs)
		$tags = get_the_terms($product->get_id(), "product_tag");
		if ($tags && !is_wp_error($tags)) {
			$data["tags"] = implode(",", wp_list_pluck($tags, "term_id"));
		}

		// WooCommerce Attributes (ONLY taxonomy attributes)
		$product_attributes = $product->get_attributes();
		foreach ($product_attributes as $attribute) {
			// ONLY process taxonomy (WooCommerce registered) attributes
			if (!$attribute->is_taxonomy()) {
				continue;
			}

			$taxonomy = $attribute->get_name();

			// Only process WooCommerce attributes (pa_*)
			if (strpos($taxonomy, "pa_") !== 0) {
				continue;
			}

			$terms = wc_get_product_terms($product->get_id(), $taxonomy, [
				"fields" => "slugs",
			]);

			if (!empty($terms) && !is_wp_error($terms)) {
				$data[$taxonomy] = implode(",", $terms);
			}
		}

		return $data;
	}

	/**
	 * Render data attributes for filtering
	 */
	private function render_data_attributes($data)
	{
		$output = "";
		foreach ($data as $key => $value) {
			$output .= sprintf(
				' data-%s="%s"',
				esc_attr($key),
				esc_attr($value),
			);
		}
		return $output;
	}

	/**
	 * Render Elementor template
	 */
	private function render_elementor_template($template_id)
	{
		if (empty($template_id)) {
			return;
		}

		echo \Elementor\Plugin::instance()->frontend->get_builder_content(
			$template_id,
			true,
		);
	}

	/**
	 * Render default product card (fallback)
	 */
	private function render_default_product_card($product)
	{
		$is_on_sale = $product->is_on_sale();
		$tags = get_the_terms($product->get_id(), "product_tag");
		$main_tag = $tags && !is_wp_error($tags) ? $tags[0]->name : "";
		?>
        <div class="default-product-card">
            <div class="product-badges">
                <?php if ($is_on_sale): ?>
                    <span class="badge-sale"><?php _e(
                    	"Sale!",
                    	"hello-elementor-child",
                    ); ?></span>
                <?php endif; ?>
                <?php if ($main_tag): ?>
                    <span class="badge-tag"><?php echo esc_html(
                    	$main_tag,
                    ); ?></span>
                <?php endif; ?>
            </div>

            <a href="<?php echo esc_url(
            	get_permalink(),
            ); ?>" class="product-image-link">
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
                        <a href="<?php echo esc_url(
                        	get_permalink(),
                        ); ?>" class="btn-select-options">
                            <?php _e(
                            	"SELECT OPTIONS",
                            	"hello-elementor-child",
                            ); ?>
                        </a>
                    <?php else: ?>
                        <?php woocommerce_template_loop_add_to_cart(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <style>
            .default-product-card {
                background: #f9f9f9;
                border-radius: 8px;
                overflow: hidden;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .default-product-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }

            .default-product-card .product-badges {
                position: absolute;
                top: 15px;
                left: 15px;
                right: 15px;
                display: flex;
                justify-content: space-between;
                z-index: 10;
            }

            .default-product-card .badge-sale,
            .default-product-card .badge-tag {
                background: #1e1e1e;
                color: white;
                padding: 6px 12px;
                font-size: 11px;
                font-weight: 700;
                border-radius: 4px;
            }

            .default-product-card .product-image-link {
                display: block;
                overflow: hidden;
                aspect-ratio: 1 / 1;
            }

            .default-product-card .product-image-link img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.5s ease;
            }

            .default-product-card:hover .product-image-link img {
                transform: scale(1.08);
            }

            .default-product-card .product-info {
                padding: 20px;
                background: white;
            }

            .default-product-card .product-title {
                margin: 0 0 8px 0;
                font-size: 14px;
                font-weight: 600;
                line-height: 1.3;
            }

            .default-product-card .product-title a {
                color: #1e1e1e;
                text-decoration: none;
            }

            .default-product-card .product-price {
                margin-bottom: 12px;
                font-size: 17px;
                font-weight: 700;
                color: #1e1e1e;
            }

            .default-product-card .btn-select-options,
            .default-product-card .button {
                width: 100%;
                padding: 10px 12px;
                background: white;
                color: #1e1e1e;
                border: 2px solid #1e1e1e;
                font-size: 11px;
                font-weight: 700;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                border-radius: 4px;
                transition: all 0.3s ease;
            }

            .default-product-card .btn-select-options:hover,
            .default-product-card .button:hover {
                background: #1e1e1e;
                color: white;
            }
        </style>
        <?php
	}
}
