<?php
/**
 * Elementor Product Badge Widget (Sale & Tags)
 *
 * @package HelloElementorChild
 */

if (!defined("ABSPATH")) {
	exit(); // Exit if accessed directly.
}

class Elementor_Product_Badge_Widget extends \Elementor\Widget_Base
{
	/**
	 * Get widget name
	 */
	public function get_name()
	{
		return "product_badge";
	}

	/**
	 * Get widget title
	 */
	public function get_title()
	{
		return __("Product Badges (Sale & Tags)", "hello-elementor-child");
	}

	/**
	 * Get widget icon
	 */
	public function get_icon()
	{
		return "eicon-tags";
	}

	/**
	 * Get widget categories
	 */
	public function get_categories()
	{
		return ["custom-widgets"];
	}

	/**
	 * Get widget keywords
	 */
	public function get_keywords()
	{
		return [
			"product",
			"badge",
			"sale",
			"tag",
			"discount",
			"woocommerce",
			"label",
		];
	}

	/**
	 * Register widget controls
	 */
	protected function register_controls()
	{
		// Sale Badge Section
		$this->start_controls_section("sale_badge_section", [
			"label" => __("Sale Badge", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control("show_sale_badge", [
			"label" => __("Show Sale Badge", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"label_on" => __("Show", "hello-elementor-child"),
			"label_off" => __("Hide", "hello-elementor-child"),
			"return_value" => "yes",
			"default" => "yes",
		]);

		$this->add_control("sale_badge_type", [
			"label" => __("Sale Badge Display", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "text",
			"options" => [
				"text" => __("Text (Sale!)", "hello-elementor-child"),
				"percentage" => __(
					"Percentage Discount",
					"hello-elementor-child",
				),
			],
			"condition" => [
				"show_sale_badge" => "yes",
			],
		]);

		$this->add_control("sale_badge_text", [
			"label" => __("Sale Badge Text", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Sale!", "hello-elementor-child"),
			"placeholder" => __("Sale!", "hello-elementor-child"),
			"condition" => [
				"show_sale_badge" => "yes",
				"sale_badge_type" => "text",
			],
		]);

		$this->add_control("percentage_format", [
			"label" => __("Percentage Format", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => "-{percentage}%",
			"placeholder" => "-{percentage}%",
			"description" => __(
				"Use {percentage} as placeholder for discount value",
				"hello-elementor-child",
			),
			"condition" => [
				"show_sale_badge" => "yes",
				"sale_badge_type" => "percentage",
			],
		]);

		$this->add_control("sale_badge_position", [
			"label" => __("Sale Badge Position", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "top-left",
			"options" => [
				"top-left" => __("Top Left", "hello-elementor-child"),
				"top-right" => __("Top Right", "hello-elementor-child"),
				"bottom-left" => __("Bottom Left", "hello-elementor-child"),
				"bottom-right" => __("Bottom Right", "hello-elementor-child"),
			],
			"condition" => [
				"show_sale_badge" => "yes",
			],
		]);

		$this->end_controls_section();

		// Product Tag Badge Section
		$this->start_controls_section("tag_badge_section", [
			"label" => __("Product Tag Badge", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control("show_tag_badge", [
			"label" => __("Show Tag Badge", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"label_on" => __("Show", "hello-elementor-child"),
			"label_off" => __("Hide", "hello-elementor-child"),
			"return_value" => "yes",
			"default" => "yes",
		]);

		$this->add_control("tag_to_display", [
			"label" => __("Which Tag to Display", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "first",
			"options" => [
				"first" => __("First Tag", "hello-elementor-child"),
				"all" => __("All Tags", "hello-elementor-child"),
			],
			"condition" => [
				"show_tag_badge" => "yes",
			],
		]);

		$this->add_control("tag_badge_position", [
			"label" => __("Tag Badge Position", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "top-right",
			"options" => [
				"top-left" => __("Top Left", "hello-elementor-child"),
				"top-right" => __("Top Right", "hello-elementor-child"),
				"bottom-left" => __("Bottom Left", "hello-elementor-child"),
				"bottom-right" => __("Bottom Right", "hello-elementor-child"),
			],
			"condition" => [
				"show_tag_badge" => "yes",
			],
		]);

		$this->end_controls_section();

		// Layout Section
		$this->start_controls_section("layout_section", [
			"label" => __("Layout", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_responsive_control("badge_spacing", [
			"label" => __("Spacing Between Badges", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SLIDER,
			"size_units" => ["px"],
			"range" => [
				"px" => [
					"min" => 0,
					"max" => 50,
					"step" => 1,
				],
			],
			"default" => [
				"unit" => "px",
				"size" => 8,
			],
			"selectors" => [
				"{{WRAPPER}} .product-badges-wrapper" =>
					"gap: {{SIZE}}{{UNIT}};",
			],
		]);

		$this->add_responsive_control("badge_offset", [
			"label" => __("Badge Offset from Edge", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SLIDER,
			"size_units" => ["px"],
			"range" => [
				"px" => [
					"min" => 0,
					"max" => 100,
					"step" => 1,
				],
			],
			"default" => [
				"unit" => "px",
				"size" => 15,
			],
			"selectors" => [
				"{{WRAPPER}} .product-badges-wrapper.position-top-left" =>
					"top: {{SIZE}}{{UNIT}}; left: {{SIZE}}{{UNIT}};",
				"{{WRAPPER}} .product-badges-wrapper.position-top-right" =>
					"top: {{SIZE}}{{UNIT}}; right: {{SIZE}}{{UNIT}};",
				"{{WRAPPER}} .product-badges-wrapper.position-bottom-left" =>
					"bottom: {{SIZE}}{{UNIT}}; left: {{SIZE}}{{UNIT}};",
				"{{WRAPPER}} .product-badges-wrapper.position-bottom-right" =>
					"bottom: {{SIZE}}{{UNIT}}; right: {{SIZE}}{{UNIT}};",
			],
		]);

		$this->end_controls_section();

		// Sale Badge Style
		$this->start_controls_section("sale_style_section", [
			"label" => __("Sale Badge Style", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				"name" => "sale_typography",
				"selector" => "{{WRAPPER}} .badge-sale",
			],
		);

		$this->add_control("sale_text_color", [
			"label" => __("Text Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#ffffff",
			"selectors" => [
				"{{WRAPPER}} .badge-sale" => "color: {{VALUE}};",
			],
		]);

		$this->add_control("sale_bg_color", [
			"label" => __("Background Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#1e1e1e",
			"selectors" => [
				"{{WRAPPER}} .badge-sale" => "background-color: {{VALUE}};",
			],
		]);

		$this->add_responsive_control("sale_padding", [
			"label" => __("Padding", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::DIMENSIONS,
			"size_units" => ["px", "em", "%"],
			"default" => [
				"top" => 6,
				"right" => 12,
				"bottom" => 6,
				"left" => 12,
				"unit" => "px",
			],
			"selectors" => [
				"{{WRAPPER}} .badge-sale" =>
					"padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
			],
		]);

		$this->add_responsive_control("sale_border_radius", [
			"label" => __("Border Radius", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::DIMENSIONS,
			"size_units" => ["px", "%"],
			"default" => [
				"top" => 4,
				"right" => 4,
				"bottom" => 4,
				"left" => 4,
				"unit" => "px",
			],
			"selectors" => [
				"{{WRAPPER}} .badge-sale" =>
					"border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
			],
		]);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				"name" => "sale_box_shadow",
				"selector" => "{{WRAPPER}} .badge-sale",
			],
		);

		$this->end_controls_section();

		// Tag Badge Style
		$this->start_controls_section("tag_style_section", [
			"label" => __("Tag Badge Style", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				"name" => "tag_typography",
				"selector" => "{{WRAPPER}} .badge-tag",
			],
		);

		$this->add_control("tag_text_color", [
			"label" => __("Text Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#ffffff",
			"selectors" => [
				"{{WRAPPER}} .badge-tag" => "color: {{VALUE}};",
			],
		]);

		$this->add_control("tag_bg_color", [
			"label" => __("Background Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#1e1e1e",
			"selectors" => [
				"{{WRAPPER}} .badge-tag" => "background-color: {{VALUE}};",
			],
		]);

		$this->add_responsive_control("tag_padding", [
			"label" => __("Padding", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::DIMENSIONS,
			"size_units" => ["px", "em", "%"],
			"default" => [
				"top" => 6,
				"right" => 12,
				"bottom" => 6,
				"left" => 12,
				"unit" => "px",
			],
			"selectors" => [
				"{{WRAPPER}} .badge-tag" =>
					"padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
			],
		]);

		$this->add_responsive_control("tag_border_radius", [
			"label" => __("Border Radius", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::DIMENSIONS,
			"size_units" => ["px", "%"],
			"default" => [
				"top" => 4,
				"right" => 4,
				"bottom" => 4,
				"left" => 4,
				"unit" => "px",
			],
			"selectors" => [
				"{{WRAPPER}} .badge-tag" =>
					"border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
			],
		]);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				"name" => "tag_box_shadow",
				"selector" => "{{WRAPPER}} .badge-tag",
			],
		);

		$this->end_controls_section();
	}

	/**
	 * Calculate percentage discount
	 */
	private function get_discount_percentage($product)
	{
		if (!$product->is_on_sale()) {
			return 0;
		}

		$regular_price = (float) $product->get_regular_price();
		$sale_price = (float) $product->get_sale_price();

		if ($regular_price <= 0) {
			return 0;
		}

		$discount = (($regular_price - $sale_price) / $regular_price) * 100;
		return round($discount);
	}

	/**
	 * Render widget output on the frontend
	 */
	protected function render()
	{
		$settings = $this->get_settings_for_display();

		global $product;

		if (!$product && is_singular("product")) {
			$product = wc_get_product(get_the_ID());
		}

		if (!$product) {
			echo "<p>" .
				__(
					"Please use this widget on a product page or within a product loop.",
					"hello-elementor-child",
				) .
				"</p>";
			return;
		}

		$show_sale = $settings["show_sale_badge"] === "yes";
		$show_tag = $settings["show_tag_badge"] === "yes";
		$is_on_sale = $product->is_on_sale();

		// Calculate discount percentage
		$discount_percentage = $is_on_sale
			? $this->get_discount_percentage($product)
			: 0;

		// Only treat as on sale if discount > 0
		if ($discount_percentage <= 0) {
			$is_on_sale = false;
		}

		// Get product tags
		$tags = get_the_terms(get_the_ID(), "product_tag");
		$has_tags = $tags && !is_wp_error($tags) && count($tags) > 0;

		$sale_position = $show_sale ? $settings["sale_badge_position"] : "";
		$tag_position = $show_tag ? $settings["tag_badge_position"] : "";

		$badges_by_position = [];

		if ($show_sale) {
			$badges_by_position[$sale_position][] = "sale";
		}

		if ($show_tag) {
			$badges_by_position[$tag_position][] = "tag";
		}
		?>

        <div class="product-badges-container">
            <?php foreach ($badges_by_position as $position => $badge_types): ?>
                <div class="product-badges-wrapper position-<?php echo esc_attr(
                	$position,
                ); ?>">
                    <?php foreach ($badge_types as $badge_type): ?>

                        <?php if ($badge_type === "sale"): ?>
                            <?php $hidden_class = !$is_on_sale
                            	? "badge-hidden"
                            	: "badge-visible"; ?>
                            <span class="product-badge badge-sale <?php echo esc_attr(
                            	$hidden_class,
                            ); ?>">
                                <?php if (
                                	$settings["sale_badge_type"] ===
                                		"percentage" &&
                                	$is_on_sale
                                ) {
                                	$format = $settings["percentage_format"];
                                	echo esc_html(
                                		str_replace(
                                			"{percentage}",
                                			$discount_percentage,
                                			$format,
                                		),
                                	);
                                } elseif ($is_on_sale) {
                                	echo esc_html($settings["sale_badge_text"]);
                                } ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($badge_type === "tag"): ?>
                            <?php $hidden_class = !$has_tags
                            	? "badge-hidden"
                            	: "badge-visible"; ?>
                            <span class="product-badge badge-tag <?php echo esc_attr(
                            	$hidden_class,
                            ); ?>">
                                <?php if ($has_tags) {
                                	if ($settings["tag_to_display"] === "all") {
                                		foreach ($tags as $tag) {
                                			echo esc_html($tag->name) . " ";
                                		}
                                	} else {
                                		echo esc_html($tags[0]->name);
                                	}
                                } ?>
                            </span>
                        <?php endif; ?>

                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
	}

	/**
	 * Render widget output in the editor
	 */
	protected function content_template()
	{
		?>
        <div class="product-badges-container">
            <#
            var showSale = settings.show_sale_badge === 'yes';
            var showTag = settings.show_tag_badge === 'yes';
            var salePosition = settings.sale_badge_position;
            var tagPosition = settings.tag_badge_position;
            #>

            <# if (showSale) { #>
                <div class="product-badges-wrapper position-{{{ salePosition }}}">
                    <span class="product-badge badge-sale">
                        <# if (settings.sale_badge_type === 'percentage') { #>
                            {{{ settings.percentage_format.replace('{percentage}', '25') }}}
                        <# } else { #>
                            {{{ settings.sale_badge_text }}}
                        <# } #>
                    </span>
                </div>
            <# } #>

            <# if (showTag) { #>
                <div class="product-badges-wrapper position-{{{ tagPosition }}}">
                    <# if (settings.tag_to_display === 'all') { #>
                        <span class="product-badge badge-tag">New</span>
                        <span class="product-badge badge-tag">Featured</span>
                    <# } else { #>
                        <span class="product-badge badge-tag">New</span>
                    <# } #>
                </div>
            <# } #>
        </div>
        <?php
	}
}
