<?php
/**
 * Enhanced Elementor Loop Grid Filter Widget - FIXED
 * NO inline styles - CSS classes handle all styling
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

		$categories = $this->get_product_categories();
		$tags = $this->get_product_tags();
		$wc_attributes = $this->get_woocommerce_attributes_only();
		$max_price = $this->get_max_price();
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
				/*display: none;*/
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

			/* Price Slider Fix */
			.elementor-widget-loop_grid_filter .price-slider-wrapper {
				position: relative;
				height: 5px;
				margin: 25px 0 20px 0;
			}

			.elementor-widget-loop_grid_filter .price-slider-wrapper input[type="range"] {
				position: absolute;
				width: 100%;
				top: -8px;
				height: 20px;
				background: transparent;
				pointer-events: all !important;
				appearance: none;
				-webkit-appearance: none;
				cursor: pointer;
				z-index: 5;
			}

			.elementor-widget-loop_grid_filter .price-slider-wrapper input[type="range"]::-webkit-slider-thumb {
				-webkit-appearance: none;
				appearance: none;
				width: 18px;
				height: 18px;
				background: #1e1e1e;
				border-radius: 50%;
				cursor: grab;
				border: 3px solid white;
				box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
				margin-top: -6.5px;
				position: relative;
				z-index: 5;
			}

			.elementor-widget-loop_grid_filter .price-slider-wrapper input[type="range"]::-moz-range-thumb {
				width: 18px;
				height: 18px;
				background: #1e1e1e;
				border-radius: 50%;
				cursor: grab;
				border: 3px solid white;
				box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
				position: relative;
				z-index: 5;
			}

			.elementor-widget-loop_grid_filter .loop-price-min-slider {
				z-index: 6 !important;
			}

			.elementor-widget-loop_grid_filter .loop-price-max-slider {
				z-index: 5 !important;
			}
		</style>

		<!-- ✅ NO INLINE STYLE ATTRIBUTE HERE -->
		<div class="loop-grid-filter-widget"
		     data-widget-id="<?php echo esc_attr($widget_id); ?>"
		     data-target="<?php echo esc_attr($settings["target_loop_grid_id"]); ?>"
		     data-expandable="<?php echo esc_attr(
       	$settings["expandable_sections"],
       ); ?>">

			<!-- ✅ NO INLINE STYLE ATTRIBUTE HERE EITHER -->
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

					// ✅ CRITICAL: Remove any inline styles immediately
					sidebar.removeAttr('style');
					console.log('✅ Removed inline styles from sidebar on init');

					// Close button functionality
					widget.on('click', '.filter-close-btn', function(e) {
						e.preventDefault();
						sidebar.slideUp(300);
					});

					// Toggle sections for Categories, Tags, Attributes
					if (isExpandable) {
						widget.on('click', '.filter-toggle-label', function(e) {
							e.preventDefault();
							const toggle = $(this);
							const content = toggle.siblings('.filter-content');

							if (content.hasClass('always-show')) {
								return; // Don't toggle search, sort, price
							}

							toggle.toggleClass('collapsed');
							content.toggleClass('hidden');
						});

						// Expand all by default (except those with always-show class)
						widget.find('.filter-toggle-label').each(function() {
							const content = $(this).siblings('.filter-content');
							if (!content.hasClass('always-show')) {
								$(this).removeClass('collapsed');
								content.removeClass('hidden');
							}
						});
					} else {
						// If not expandable, hide toggle arrows
						widget.find('.filter-toggle-label').addClass('no-toggle');
						widget.find('.filter-content').addClass('always-show');
					}

					// Price range fix
					const minSlider = widget.find('.loop-price-min-slider');
					const maxSlider = widget.find('.loop-price-max-slider');
					const minInput = widget.find('.loop-price-min-input');
					const maxInput = widget.find('.loop-price-max-input');

					function updatePriceDisplay() {
						let minVal = parseInt(minSlider.val()) || 0;
						let maxVal = parseInt(maxSlider.val()) || <?php echo esc_js($max_price); ?>;

						// Prevent crossing
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

						// Update track position
						const maxPrice = <?php echo esc_js($max_price); ?>;
						const minPercent = (minVal / maxPrice) * 100;
						const maxPercent = (maxVal / maxPrice) * 100;

						widget.find('.price-slider-track').css({
							left: minPercent + '%',
							width: maxPercent - minPercent + '%',
						});
					}

					// Slider input
					minSlider.on('input change', updatePriceDisplay);
					maxSlider.on('input change', updatePriceDisplay);

					// Manual input
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

					// Initialize display
					updatePriceDisplay();
				});
			})(jQuery);
		</script>
		<?php
	}

	private function get_woocommerce_attributes_only()
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

			$attributes[] = [
				"name" => $attribute->attribute_name,
				"label" => $attribute->attribute_label,
				"taxonomy" => $taxonomy,
				"terms" => $terms,
			];
		}

		return $attributes;
	}

	private function get_product_categories()
	{
		return get_terms([
			"taxonomy" => "product_cat",
			"hide_empty" => true,
		]);
	}

	private function get_product_tags()
	{
		return get_terms([
			"taxonomy" => "product_tag",
			"hide_empty" => true,
		]);
	}

	private function get_max_price()
	{
		if (!class_exists("WooCommerce")) {
			return 10000;
		}

		global $wpdb;
		$max_price = $wpdb->get_var(
			"SELECT MAX(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = '_price' AND meta_value != ''",
		);
		return $max_price ? ceil($max_price / 100) * 100 : 10000;
	}
}
