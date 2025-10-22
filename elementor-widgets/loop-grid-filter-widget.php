<?php
/**
 * Enhanced Elementor Loop Grid Filter Widget - WITH DETAILED LOGGING
 * ✅ Logs categories, attributes, price range
 * ✅ Price range from PUBLISHED products only
 *
 * @package HelloElementorChild
 */

if (!defined("ABSPATH")) {
	exit();
}

class Elementor_Loop_Grid_Filter_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return "loop_grid_filter";
	}

	public function get_title()
	{
		return __("Loop Grid Filter - Customizable", "hello-elementor-child");
	}

	public function get_icon()
	{
		return "eicon-filter";
	}

	public function get_categories()
	{
		return ["custom-widgets", "general"];
	}

	protected function register_controls()
	{
		// ============ GENERAL SETTINGS ============
		$this->start_controls_section("section_general", [
			"label" => __("General Settings", "hello-elementor-child"),
		]);

		$this->add_control("target_loop_grid_id", [
			"label" => __("Target Loop Grid CSS ID", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"placeholder" => "my-loop-grid",
		]);

		$this->add_control("filter_title", [
			"label" => __("Filter Title", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Filters", "hello-elementor-child"),
		]);

		$this->add_control("sidebar_width", [
			"label" => __("Sidebar Width (px)", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::NUMBER,
			"default" => 260,
			"min" => 200,
			"max" => 500,
		]);

		$this->end_controls_section();

		// ============ SHOW/HIDE SECTIONS ============
		$this->start_controls_section("section_toggles", [
			"label" => __("Show/Hide Sections", "hello-elementor-child"),
		]);

		$this->add_control("show_search", [
			"label" => __("Show Search", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"default" => "yes",
		]);

		$this->add_control("show_sort", [
			"label" => __("Show Sort", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"default" => "yes",
		]);

		$this->add_control("show_categories", [
			"label" => __("Show Categories", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"default" => "yes",
		]);

		$this->add_control("show_attributes", [
			"label" => __("Show Attributes", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"default" => "yes",
		]);

		$this->add_control("show_tags", [
			"label" => __("Show Tags", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"default" => "yes",
		]);

		$this->add_control("show_price", [
			"label" => __("Show Price Range", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"default" => "yes",
		]);

		$this->add_control("show_reset", [
			"label" => __("Show Reset Button", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"default" => "yes",
		]);

		$this->add_control("expandable_sections", [
			"label" => __("Collapsible Sections", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"default" => "yes",
		]);

		$this->end_controls_section();

		// ============ CUSTOM LABELS ============
		$this->start_controls_section("section_labels", [
			"label" => __("Custom Labels", "hello-elementor-child"),
		]);

		$this->add_control("search_label", [
			"label" => __("Search Label", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Search", "hello-elementor-child"),
		]);

		$this->add_control("sort_label", [
			"label" => __("Sort Label", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Sort By", "hello-elementor-child"),
		]);

		$this->add_control("category_label", [
			"label" => __("Category Label", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Categories", "hello-elementor-child"),
		]);

		$this->add_control("attribute_label", [
			"label" => __("Attribute Label", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Attributes", "hello-elementor-child"),
		]);

		$this->add_control("tag_label", [
			"label" => __("Tag Label", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Tags", "hello-elementor-child"),
		]);

		$this->add_control("price_label", [
			"label" => __("Price Label", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Price Range", "hello-elementor-child"),
		]);

		$this->add_control("reset_label", [
			"label" => __("Reset Label", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Reset Filters", "hello-elementor-child"),
		]);

		$this->end_controls_section();

		// ============ TITLE STYLE ============
		$this->start_controls_section("section_title_style", [
			"label" => __("Title Style", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				"name" => "title_typography",
				"selector" => "{{WRAPPER}} .loop-filter-sidebar h3",
			],
		);

		$this->add_control("title_color", [
			"label" => __("Title Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#1e1e1e",
			"selectors" => [
				"{{WRAPPER}} .loop-filter-sidebar h3" => "color: {{VALUE}};",
			],
		]);

		$this->add_control("title_bg", [
			"label" => __("Title Background", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "transparent",
			"selectors" => [
				"{{WRAPPER}} .loop-filter-sidebar h3" =>
					"background-color: {{VALUE}};",
			],
		]);

		$this->add_responsive_control("title_padding", [
			"label" => __("Title Padding", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::DIMENSIONS,
			"size_units" => ["px"],
			"selectors" => [
				"{{WRAPPER}} .loop-filter-sidebar h3" =>
					"padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
			],
		]);

		$this->end_controls_section();

		// ============ FILTER LABEL STYLE ============
		$this->start_controls_section("section_label_style", [
			"label" => __("Filter Label Style", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				"name" => "label_typography",
				"selector" => "{{WRAPPER}} .filter-group > label",
			],
		);

		$this->add_control("label_color", [
			"label" => __("Label Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#1e1e1e",
			"selectors" => [
				"{{WRAPPER}} .filter-group > label" => "color: {{VALUE}};",
			],
		]);

		$this->add_control("label_bg", [
			"label" => __("Label Background", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "transparent",
			"selectors" => [
				"{{WRAPPER}} .filter-group > label" =>
					"background-color: {{VALUE}};",
			],
		]);

		$this->add_responsive_control("label_padding", [
			"label" => __("Label Padding", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::DIMENSIONS,
			"size_units" => ["px"],
			"selectors" => [
				"{{WRAPPER}} .filter-group > label" =>
					"padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
			],
		]);

		$this->end_controls_section();

		// ============ CHECKBOX/ITEM STYLE ============
		$this->start_controls_section("section_item_style", [
			"label" => __("Checkbox/Item Style", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				"name" => "item_typography",
				"selector" =>
					"{{WRAPPER}} .filter-checkbox-item label, {{WRAPPER}} .loop-filter-custom-attribute ~ label",
			],
		);

		$this->add_control("item_color", [
			"label" => __("Item Text Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#555",
			"selectors" => [
				"{{WRAPPER}} .filter-checkbox-item label" =>
					"color: {{VALUE}};",
				"{{WRAPPER}} .loop-filter-custom-attribute ~ label" =>
					"color: {{VALUE}};",
			],
		]);

		$this->add_control("item_hover_color", [
			"label" => __("Item Hover Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#1e1e1e",
			"selectors" => [
				"{{WRAPPER}} .filter-checkbox-item:hover label" =>
					"color: {{VALUE}};",
				"{{WRAPPER}} .loop-filter-custom-attribute:hover ~ label" =>
					"color: {{VALUE}};",
			],
		]);

		$this->add_control("checkbox_accent", [
			"label" => __("Checkbox Accent Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#1e1e1e",
			"selectors" => [
				"{{WRAPPER}} input[type='checkbox']" =>
					"accent-color: {{VALUE}};",
			],
		]);

		$this->add_responsive_control("item_spacing", [
			"label" => __("Item Spacing", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SLIDER,
			"size_units" => ["px"],
			"range" => ["px" => ["min" => 0, "max" => 30]],
			"default" => ["size" => 10],
			"selectors" => [
				"{{WRAPPER}} .filter-checkbox-item" =>
					"margin-bottom: {{SIZE}}{{UNIT}};",
			],
		]);

		$this->end_controls_section();

		// ============ INPUT/SELECT STYLE ============
		$this->start_controls_section("section_input_style", [
			"label" => __("Input/Select Style", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				"name" => "input_typography",
				"selector" => "{{WRAPPER}} input, {{WRAPPER}} select",
			],
		);

		$this->add_control("input_bg", [
			"label" => __("Background Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#ffffff",
			"selectors" => [
				"{{WRAPPER}} input, {{WRAPPER}} select" =>
					"background-color: {{VALUE}};",
			],
		]);

		$this->add_control("input_text_color", [
			"label" => __("Text Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#1e1e1e",
			"selectors" => [
				"{{WRAPPER}} input, {{WRAPPER}} select" => "color: {{VALUE}};",
			],
		]);

		$this->add_control("input_border_color", [
			"label" => __("Border Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#e0e0e0",
			"selectors" => [
				"{{WRAPPER}} input, {{WRAPPER}} select" =>
					"border-color: {{VALUE}};",
			],
		]);

		$this->add_control("input_border_width", [
			"label" => __("Border Width (px)", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::NUMBER,
			"default" => 1,
			"min" => 0,
			"max" => 5,
			"selectors" => [
				"{{WRAPPER}} input, {{WRAPPER}} select" =>
					"border-width: {{VALUE}}px;",
			],
		]);

		$this->add_control("input_radius", [
			"label" => __("Border Radius (px)", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::NUMBER,
			"default" => 4,
			"min" => 0,
			"max" => 50,
			"selectors" => [
				"{{WRAPPER}} input, {{WRAPPER}} select" =>
					"border-radius: {{VALUE}}px;",
			],
		]);

		$this->add_responsive_control("input_padding", [
			"label" => __("Padding", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::DIMENSIONS,
			"size_units" => ["px"],
			"selectors" => [
				"{{WRAPPER}} input, {{WRAPPER}} select" =>
					"padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
			],
		]);

		$this->end_controls_section();

		// ============ BUTTON STYLE ============
		$this->start_controls_section("section_button_style", [
			"label" => __("Button Style", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				"name" => "button_typography",
				"selector" => "{{WRAPPER}} .loop-filter-reset",
			],
		);

		$this->add_control("button_bg", [
			"label" => __("Background Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#1e1e1e",
			"selectors" => [
				"{{WRAPPER}} .loop-filter-reset" =>
					"background-color: {{VALUE}};",
			],
		]);

		$this->add_control("button_text_color", [
			"label" => __("Text Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#ffffff",
			"selectors" => [
				"{{WRAPPER}} .loop-filter-reset" => "color: {{VALUE}};",
			],
		]);

		$this->add_control("button_hover_bg", [
			"label" => __("Hover Background", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#333333",
			"selectors" => [
				"{{WRAPPER}} .loop-filter-reset:hover" =>
					"background-color: {{VALUE}};",
			],
		]);

		$this->add_control("button_radius", [
			"label" => __("Border Radius (px)", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::NUMBER,
			"default" => 4,
			"min" => 0,
			"max" => 50,
			"selectors" => [
				"{{WRAPPER}} .loop-filter-reset" =>
					"border-radius: {{VALUE}}px;",
			],
		]);

		$this->add_responsive_control("button_padding", [
			"label" => __("Padding", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::DIMENSIONS,
			"size_units" => ["px"],
			"selectors" => [
				"{{WRAPPER}} .loop-filter-reset" =>
					"padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
			],
		]);

		$this->end_controls_section();

		// ============ SIDEBAR STYLE ============
		$this->start_controls_section("section_sidebar_style", [
			"label" => __("Sidebar Style", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_control("sidebar_bg", [
			"label" => __("Background Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#ffffff",
			"selectors" => [
				"{{WRAPPER}} .loop-filter-sidebar" =>
					"background-color: {{VALUE}};",
			],
		]);

		$this->add_control("sidebar_border", [
			"label" => __("Border Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#f0f0f0",
			"selectors" => [
				"{{WRAPPER}} .loop-filter-sidebar" =>
					"border-color: {{VALUE}};",
			],
		]);

		$this->add_responsive_control("sidebar_padding", [
			"label" => __("Padding", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::DIMENSIONS,
			"size_units" => ["px"],
			"selectors" => [
				"{{WRAPPER}} .loop-filter-sidebar" =>
					"padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
			],
		]);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$widget_id = $this->get_id();

		// ✅ GET DATA WITH LOGGING
		$categories = $this->get_product_categories();
		$tags = $this->get_product_tags();
		$wc_attributes = $this->get_woocommerce_attributes_only();
		$max_price = $this->get_max_price();

		// ✅ LOG EVERYTHING TO CONSOLE
		$this->log_filter_data($categories, $tags, $wc_attributes, $max_price);
		?>

		<style>
			.elementor-widget-loop_grid_filter .loop-filter-sidebar {
				width: <?php echo esc_attr($settings["sidebar_width"]); ?>px !important;
				position: relative;
			}

			.elementor-widget-loop_grid_filter .filter-close-btn {
				position: absolute;
				top: 15px;
				right: 15px;
				width: 32px;
				height: 32px;
				background: #f5f5f5;
				border: none;
				border-radius: 50%;
				cursor: pointer;
				z-index: 10;
				transition: all 0.2s ease;
			}

			.elementor-widget-loop_grid_filter .filter-close-btn:hover {
				background: #e0e0e0;
				transform: rotate(90deg);
			}

			.elementor-widget-loop_grid_filter .filter-close-btn::before,
			.elementor-widget-loop_grid_filter .filter-close-btn::after {
				content: "";
				position: absolute;
				top: 50%;
				left: 50%;
				width: 16px;
				height: 2px;
				background: #1e1e1e;
			}

			.elementor-widget-loop_grid_filter .filter-close-btn::before {
				transform: translate(-50%, -50%) rotate(45deg);
			}

			.elementor-widget-loop_grid_filter .filter-close-btn::after {
				transform: translate(-50%, -50%) rotate(-45deg);
			}

			@media (max-width: 1024px) {
				.elementor-widget-loop_grid_filter .filter-close-btn {
					display: block !important;
				}
			}

			.elementor-widget-loop_grid_filter .filter-toggle-label {
				cursor: pointer;
				display: flex;
				justify-content: space-between;
				align-items: center;
				user-select: none;
			}

			.elementor-widget-loop_grid_filter .filter-toggle-label.no-toggle {
				cursor: default;
			}

			.elementor-widget-loop_grid_filter .filter-toggle-label.no-toggle::after {
				display: none;
			}

			.elementor-widget-loop_grid_filter .filter-toggle-label::after {
				content: "▼";
				transition: transform 0.3s ease;
				font-size: 0.75em;
				display: inline-block;
			}

			.elementor-widget-loop_grid_filter .filter-toggle-label.collapsed::after {
				transform: rotate(-90deg);
			}

			.elementor-widget-loop_grid_filter .filter-content {
				transition: max-height 0.3s ease, opacity 0.3s ease, margin 0.3s ease;
				max-height: 1000px;
				opacity: 1;
				overflow: hidden;
				margin-top: 12px;
			}

			.elementor-widget-loop_grid_filter .filter-content.hidden {
				max-height: 0;
				opacity: 0;
				margin-top: 0;
			}

			.elementor-widget-loop_grid_filter .filter-content.always-show {
				max-height: 10000px !important;
				opacity: 1 !important;
				margin-top: 12px !important;
			}

			/* ✅ FIXED: Price Slider - Both Min and Max Draggable */
			.elementor-widget-loop_grid_filter .price-slider-container {
				position: relative;
				padding: 10px 0;
			}

			.elementor-widget-loop_grid_filter .price-slider-wrapper {
				position: relative;
				height: 30px;
				margin: 15px 0 20px 0;
				display: flex;
				align-items: center;
			}

			/* Background track */
			.elementor-widget-loop_grid_filter .price-slider-track-bg {
				position: absolute;
				width: 100%;
				height: 5px;
				background: #e0e0e0;
				border-radius: 3px;
				top: 50%;
				transform: translateY(-50%);
			}

			/* Active track */
			.elementor-widget-loop_grid_filter .price-slider-track {
				position: absolute;
				height: 5px;
				background: #1e1e1e;
				border-radius: 3px;
				top: 50%;
				transform: translateY(-50%);
				pointer-events: none;
			}

			.elementor-widget-loop_grid_filter .price-slider-wrapper input[type="range"] {
				position: absolute;
				width: 100%;
				height: 30px;
				background: transparent;
				pointer-events: none;
				appearance: none;
				-webkit-appearance: none;
				margin: 0;
				padding: 0;
				margin-top: 5px;
			}

			/* ✅ Make thumbs interactive */
			.elementor-widget-loop_grid_filter .price-slider-wrapper input[type="range"]::-webkit-slider-thumb {
				-webkit-appearance: none;
				appearance: none;
				width: 20px;
				height: 20px;
				background: #1e1e1e;
				border-radius: 50%;
				cursor: grab;
				border: 3px solid white;
				box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
				pointer-events: all;
				transition: transform 0.2s ease;
			}

			.elementor-widget-loop_grid_filter .price-slider-wrapper input[type="range"]:active::-webkit-slider-thumb {
				cursor: grabbing;
				transform: scale(1.2);
				box-shadow: 0 3px 10px rgba(0, 0, 0, 0.4);
			}

			.elementor-widget-loop_grid_filter .price-slider-wrapper input[type="range"]::-moz-range-thumb {
				width: 20px;
				height: 20px;
				background: #1e1e1e;
				border-radius: 50%;
				cursor: grab;
				border: 3px solid white;
				box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
				pointer-events: all;
				transition: transform 0.2s ease;
			}

			.elementor-widget-loop_grid_filter .price-slider-wrapper input[type="range"]:active::-moz-range-thumb {
				cursor: grabbing;
				transform: scale(1.2);
				box-shadow: 0 3px 10px rgba(0, 0, 0, 0.4);
			}

			/* Hide default track */
			.elementor-widget-loop_grid_filter .price-slider-wrapper input[type="range"]::-webkit-slider-runnable-track {
				width: 100%;
				height: 5px;
				background: transparent;
				border: none;
			}

			.elementor-widget-loop_grid_filter .price-slider-wrapper input[type="range"]::-moz-range-track {
				width: 100%;
				height: 5px;
				background: transparent;
				border: none;
			}

			/* Z-index management */
			.elementor-widget-loop_grid_filter .loop-price-min-slider {
				z-index: 3;
			}

			.elementor-widget-loop_grid_filter .loop-price-max-slider {
				z-index: 4;
			}

			.elementor-widget-loop_grid_filter .loop-price-min-slider:active {
				z-index: 5 !important;
			}

			/* Price values display */
			.elementor-widget-loop_grid_filter .price-values {
				display: flex;
				justify-content: space-between;
				margin-top: 10px;
				font-weight: 600;
				color: #1e1e1e;
			}

			/* Price inputs */
			.elementor-widget-loop_grid_filter .price-inputs {
				display: flex;
				gap: 10px;
				margin-top: 10px;
			}

			.elementor-widget-loop_grid_filter .price-input {
				flex: 1;
				padding: 8px;
				border: 1px solid #e0e0e0;
				border-radius: 4px;
				font-size: 14px;
			}
		</style>

		<div class="loop-grid-filter-widget"
		     data-widget-id="<?php echo esc_attr($widget_id); ?>"
		     data-target="<?php echo esc_attr($settings["target_loop_grid_id"]); ?>"
		     data-expandable="<?php echo esc_attr(
       	$settings["expandable_sections"],
       ); ?>">

			<div class="loop-filter-sidebar">
				<button class="filter-close-btn" aria-label="Close Filters"></button>

				<h3><?php echo esc_html($settings["filter_title"]); ?></h3>

				<?php if ($settings["show_search"] === "yes"): ?>
					<div class="filter-group">
						<label><?php echo esc_html($settings["search_label"]); ?></label>
						<div class="filter-content always-show">
							<input
								type="text"
								class="loop-filter-search filter-select"
								placeholder="<?php echo esc_attr($settings["search_label"]); ?>..."
							/>
						</div>
					</div>
				<?php endif; ?>

				<?php if ($settings["show_sort"] === "yes"): ?>
					<div class="filter-group">
						<label><?php echo esc_html($settings["sort_label"]); ?></label>
						<div class="filter-content always-show">
							<select class="loop-filter-sort filter-select">
								<option value="date"><?php _e("Newest", "hello-elementor-child"); ?></option>
								<option value="title"><?php _e("Title", "hello-elementor-child"); ?></option>
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
					</div>
				<?php endif; ?>

				<?php if ($settings["show_categories"] === "yes" && !empty($categories)): ?>
					<div class="filter-group">
						<label class="filter-toggle-label" data-toggle="categories">
							<?php echo esc_html($settings["category_label"]); ?>
						</label>
						<div class="filter-content">
							<div class="filter-checkboxes">
								<?php foreach ($categories as $cat): ?>
									<div class="filter-checkbox-item">
										<input
											type="checkbox"
											class="loop-filter-category"
											value="<?php echo esc_attr($cat->term_id); ?>"
											id="cat-<?php echo esc_attr($widget_id . "-" . $cat->term_id); ?>"
										/>
										<label for="cat-<?php echo esc_attr($widget_id . "-" . $cat->term_id); ?>">
											<?php echo esc_html($cat->name); ?>
										</label>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ($settings["show_attributes"] === "yes" && !empty($wc_attributes)): ?>
					<?php foreach ($wc_attributes as $attribute): ?>
						<div class="filter-group">
							<label class="filter-toggle-label" data-toggle="attributes">
								<?php echo esc_html($attribute["label"]); ?>
							</label>
							<div class="filter-content">
								<div class="filter-checkboxes">
									<?php foreach ($attribute["terms"] as $term): ?>
										<div class="filter-checkbox-item">
											<input
												type="checkbox"
												class="loop-filter-custom-attribute"
												value="<?php echo esc_attr($attribute["taxonomy"] . ":" . $term->slug); ?>"
												id="attr-<?php echo esc_attr(
            	$widget_id . "-" . $attribute["taxonomy"] . "-" . $term->slug,
            ); ?>"
											/>
											<label for="attr-<?php echo esc_attr(
           	$widget_id . "-" . $attribute["taxonomy"] . "-" . $term->slug,
           ); ?>">
												<?php echo esc_html($term->name); ?>
											</label>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if ($settings["show_tags"] === "yes" && !empty($tags)): ?>
					<div class="filter-group">
						<label class="filter-toggle-label" data-toggle="tags">
							<?php echo esc_html($settings["tag_label"]); ?>
						</label>
						<div class="filter-content">
							<div class="filter-checkboxes">
								<?php foreach ($tags as $tag): ?>
									<div class="filter-checkbox-item">
										<input
											type="checkbox"
											class="loop-filter-tag"
											value="<?php echo esc_attr($tag->term_id); ?>"
											id="tag-<?php echo esc_attr($widget_id . "-" . $tag->term_id); ?>"
										/>
										<label for="tag-<?php echo esc_attr($widget_id . "-" . $tag->term_id); ?>">
											<?php echo esc_html($tag->name); ?>
										</label>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ($settings["show_price"] === "yes"): ?>
					<div class="filter-group">
						<label><?php echo esc_html($settings["price_label"]); ?></label>
						<div class="filter-content always-show">
							<div class="price-slider-container">
								<div class="price-slider-wrapper">
									<div class="price-slider-track-bg"></div>
									<div class="price-slider-track"></div>
									<input
										type="range"
										class="loop-price-min-slider"
										min="0"
										max="<?php echo esc_attr($max_price); ?>"
										value="0"
										step="100"
									/>
									<input
										type="range"
										class="loop-price-max-slider"
										min="0"
										max="<?php echo esc_attr($max_price); ?>"
										value="<?php echo esc_attr($max_price); ?>"
										step="100"
									/>
								</div>
								<div class="price-values">
									<span class="price-min-value">฿0</span>
									<span class="price-max-value">฿<?php echo number_format($max_price); ?></span>
								</div>
								<div class="price-inputs">
									<input type="number" class="price-input loop-price-min-input" placeholder="Min" value="0" step="100" />
									<input type="number" class="price-input loop-price-max-input" placeholder="Max" value="<?php echo esc_attr(
         	$max_price,
         ); ?>" step="100" />
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ($settings["show_reset"] === "yes"): ?>
					<button class="reset-btn loop-filter-reset">
						<?php echo esc_html($settings["reset_label"]); ?>
					</button>
				<?php endif; ?>
			</div>
		</div>

		<script>
			(function($) {
				$(document).ready(function() {
					const widgetId = '<?php echo esc_js($widget_id); ?>';
					const widget = $('[data-widget-id="' + widgetId + '"]');
					const isExpandable = widget.data('expandable') === 'yes' || widget.data('expandable') === '1';
					const sidebar = widget.find('.loop-filter-sidebar');

					sidebar.removeAttr('style');

					// Close button
					widget.on('click', '.filter-close-btn', function(e) {
						e.preventDefault();
						sidebar.slideUp(300);
					});

					// Toggle sections
					if (isExpandable) {
						widget.on('click', '.filter-toggle-label', function(e) {
							e.preventDefault();
							const toggle = $(this);
							const content = toggle.siblings('.filter-content');

							if (content.hasClass('always-show')) {
								return;
							}

							toggle.toggleClass('collapsed');
							content.toggleClass('hidden');
						});

						widget.find('.filter-toggle-label').each(function() {
							const content = $(this).siblings('.filter-content');
							if (!content.hasClass('always-show')) {
								$(this).removeClass('collapsed');
								content.removeClass('hidden');
							}
						});
					} else {
						widget.find('.filter-toggle-label').addClass('no-toggle');
						widget.find('.filter-content').addClass('always-show');
					}

					// Price range
					const minSlider = widget.find('.loop-price-min-slider');
					const maxSlider = widget.find('.loop-price-max-slider');
					const minInput = widget.find('.loop-price-min-input');
					const maxInput = widget.find('.loop-price-max-input');

					// ✅ Improve z-index handling when dragging
					minSlider.on('mousedown touchstart', function() {
						$(this).css('z-index', '5');
						maxSlider.css('z-index', '4');
					});

					maxSlider.on('mousedown touchstart', function() {
						$(this).css('z-index', '5');
						minSlider.css('z-index', '3');
					});

					// ✅ Reset z-index after dragging
					$(document).on('mouseup touchend', function() {
						// Default: max slider slightly on top
						minSlider.css('z-index', '3');
						maxSlider.css('z-index', '4');
					});

					function updatePriceDisplay() {
						let minVal = parseInt(minSlider.val()) || 0;
						let maxVal = parseInt(maxSlider.val()) || <?php echo esc_js($max_price); ?>;

						// ✅ Prevent sliders from crossing
						if (minVal > maxVal - 100) {
							minSlider.val(maxVal - 100);
							minVal = maxVal - 100;
						}
						if (maxVal < minVal + 100) {
							maxSlider.val(minVal + 100);
							maxVal = minVal + 100;
						}

						widget.find('.price-min-value').text('฿' + minVal.toLocaleString());
						widget.find('.price-max-value').text('฿' + maxVal.toLocaleString());
						minInput.val(minVal);
						maxInput.val(maxVal);

						const maxPrice = <?php echo esc_js($max_price); ?>;
						const minPercent = (minVal / maxPrice) * 100;
						const maxPercent = (maxVal / maxPrice) * 100;

						widget.find('.price-slider-track').css({
							left: minPercent + '%',
							width: maxPercent - minPercent + '%',
						});
					}

					minSlider.on('input change', updatePriceDisplay);
					maxSlider.on('input change', updatePriceDisplay);

					minInput.on('change', function() {
						let val = parseInt($(this).val()) || 0;
						minSlider.val(val);
						updatePriceDisplay();
					});

					maxInput.on('change', function() {
						let val = parseInt($(this).val()) || <?php echo esc_js($max_price); ?>;
						maxSlider.val(val);
						updatePriceDisplay();
					});

					updatePriceDisplay();
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * ✅ LOG FILTER DATA TO CONSOLE
	 */
	private function log_filter_data(
		$categories,
		$tags,
		$wc_attributes,
		$max_price,
	) {
		?>
		<script>
		console.group('%c📊 FILTER DATA LOADED', 'color: #FF5722; font-weight: bold; font-size: 16px;');

		// Categories
		console.group('%c📁 Categories', 'color: #2196F3; font-weight: bold;');
		console.log('Total Categories:', <?php echo count($categories); ?>);
		<?php if (!empty($categories)): ?>
			<?php foreach ($categories as $cat): ?>
				console.log('  • <?php echo esc_js($cat->name); ?> (ID: <?php echo esc_js(
 	$cat->term_id,
 ); ?>, Count: <?php echo esc_js($cat->count); ?>)');
			<?php endforeach; ?>
		<?php else: ?>
			console.warn('No categories found');
		<?php endif; ?>
		console.groupEnd();

		// Tags
		console.group('%c🏷️ Tags', 'color: #9C27B0; font-weight: bold;');
		console.log('Total Tags:', <?php echo count($tags); ?>);
		<?php if (!empty($tags)): ?>
			<?php foreach ($tags as $tag): ?>
				console.log('  • <?php echo esc_js($tag->name); ?> (ID: <?php echo esc_js(
 	$tag->term_id,
 ); ?>, Count: <?php echo esc_js($tag->count); ?>)');
			<?php endforeach; ?>
		<?php else: ?>
			console.warn('No tags found');
		<?php endif; ?>
		console.groupEnd();

		// Attributes
		console.group('%c🎨 WooCommerce Attributes', 'color: #FF9800; font-weight: bold;');
		console.log('Total Attributes:', <?php echo count($wc_attributes); ?>);
		<?php if (!empty($wc_attributes)): ?>
			<?php foreach ($wc_attributes as $attribute): ?>
				console.group('  📌 <?php echo esc_js(
    	$attribute["label"],
    ); ?> (<?php echo esc_js($attribute["taxonomy"]); ?>)');
				console.log('    Terms Count:', <?php echo count($attribute["terms"]); ?>);
				<?php foreach ($attribute["terms"] as $term): ?>
					console.log('      • <?php echo esc_js(
     	$term->name,
     ); ?> (Slug: <?php echo esc_js($term->slug); ?>, Count: <?php echo esc_js(
	$term->count,
); ?>)');
				<?php endforeach; ?>
				console.groupEnd();
			<?php endforeach; ?>
		<?php else: ?>
			console.warn('No WooCommerce attributes found');
		<?php endif; ?>
		console.groupEnd();

		// Price Range
		console.group('%c💰 Price Range (PUBLISHED PRODUCTS ONLY)', 'color: #4CAF50; font-weight: bold;');
		console.log('Max Price:', '฿<?php echo number_format($max_price); ?>');
		console.log('Min Price:', '฿0');
		console.log('Range:', '฿0 - ฿<?php echo number_format($max_price); ?>');
		console.groupEnd();

		console.groupEnd();
		</script>
		<?php
	}

	/**
	 * ✅ FIXED: Get ONLY WooCommerce Attributes (pa_*)
	 */
	private function get_woocommerce_attributes_only()
	{
		if (!class_exists("WooCommerce")) {
			error_log("❌ WooCommerce not active");
			return [];
		}

		global $wpdb;
		$wc_attributes = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies",
		);

		if (empty($wc_attributes)) {
			error_log("⚠️ No WooCommerce attributes found in database");
			return [];
		}

		error_log(
			"📊 Found " . count($wc_attributes) . " WC attributes in database",
		);

		$attributes = [];

		foreach ($wc_attributes as $attribute) {
			$taxonomy = "pa_" . $attribute->attribute_name;

			if (!taxonomy_exists($taxonomy)) {
				error_log("⚠️ Taxonomy does not exist: " . $taxonomy);
				continue;
			}

			$terms = get_terms([
				"taxonomy" => $taxonomy,
				"hide_empty" => true,
				"orderby" => "name",
				"order" => "ASC",
			]);

			if (empty($terms) || is_wp_error($terms)) {
				error_log("⚠️ No terms found for: " . $taxonomy);
				continue;
			}

			error_log(
				"✅ " .
					$attribute->attribute_label .
					" (" .
					$taxonomy .
					"): " .
					count($terms) .
					" terms",
			);

			$attributes[] = [
				"name" => $attribute->attribute_name,
				"label" => $attribute->attribute_label,
				"taxonomy" => $taxonomy,
				"terms" => $terms,
			];
		}

		error_log("✅ Total WC attributes with terms: " . count($attributes));

		return $attributes;
	}

	/**
	 * ✅ Get Product Categories
	 */
	private function get_product_categories()
	{
		$categories = get_terms([
			"taxonomy" => "product_cat",
			"hide_empty" => true,
		]);

		if (is_wp_error($categories)) {
			error_log(
				"❌ Error getting categories: " .
					$categories->get_error_message(),
			);
			return [];
		}

		error_log("✅ Found " . count($categories) . " product categories");
		return $categories;
	}

	/**
	 * ✅ Get Product Tags
	 */
	private function get_product_tags()
	{
		$tags = get_terms([
			"taxonomy" => "product_tag",
			"hide_empty" => true,
		]);

		if (is_wp_error($tags)) {
			error_log("❌ Error getting tags: " . $tags->get_error_message());
			return [];
		}

		error_log("✅ Found " . count($tags) . " product tags");
		return $tags;
	}

	/**
	 * ✅ FIXED: Get Max Price from PUBLISHED PRODUCTS ONLY
	 */
	private function get_max_price()
	{
		if (!class_exists("WooCommerce")) {
			error_log("❌ WooCommerce not active for price check");
			return 10000;
		}

		global $wpdb;

		// ✅ CRITICAL: Only get prices from PUBLISHED products
		$max_price = $wpdb->get_var(
			"SELECT MAX(CAST(pm.meta_value AS UNSIGNED))
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE pm.meta_key = '_price'
			AND pm.meta_value != ''
			AND p.post_type = 'product'
			AND p.post_status = 'publish'",
		);

		if (!$max_price) {
			error_log("⚠️ No max price found, using default 10000");
			return 10000;
		}

		$max_price = ceil($max_price / 100) * 100;

		error_log(
			"✅ Max Price (Published Products Only): ฿" .
				number_format($max_price),
		);

		return $max_price;
	}
}
