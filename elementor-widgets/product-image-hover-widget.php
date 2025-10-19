<?php
/**
 * Elementor Product Image Hover Gallery Widget
 *
 * @package HelloElementorChild
 */

if (!defined("ABSPATH")) {
	exit(); // Exit if accessed directly.
}

class Elementor_Product_Image_Hover_Widget extends \Elementor\Widget_Base
{
	/**
	 * Get widget name
	 */
	public function get_name()
	{
		return "product_image_hover_gallery";
	}

	/**
	 * Get widget title
	 */
	public function get_title()
	{
		return __("Product Image Hover Gallery", "hello-elementor-child");
	}

	/**
	 * Get widget icon
	 */
	public function get_icon()
	{
		return "eicon-image-rollover";
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
		return ["product", "image", "gallery", "hover", "woocommerce"];
	}

	/**
	 * Register widget controls
	 */
	protected function register_controls()
	{
		// Content Section
		$this->start_controls_section("content_section", [
			"label" => __("Settings", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control("hover_effect", [
			"label" => __("Hover Effect", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "fade",
			"options" => [
				"fade" => __("Fade", "hello-elementor-child"),
				"slide" => __("Slide", "hello-elementor-child"),
				"zoom" => __("Zoom", "hello-elementor-child"),
			],
		]);

		$this->add_control("transition_speed", [
			"label" => __("Transition Speed (ms)", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::NUMBER,
			"default" => 500,
			"min" => 100,
			"max" => 2000,
			"step" => 100,
		]);

		$this->add_control("auto_cycle", [
			"label" => __("Auto Cycle Images", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"label_on" => __("Yes", "hello-elementor-child"),
			"label_off" => __("No", "hello-elementor-child"),
			"return_value" => "yes",
			"default" => "yes",
		]);

		$this->add_control("cycle_speed", [
			"label" => __("Cycle Speed (ms)", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::NUMBER,
			"default" => 800,
			"min" => 300,
			"max" => 3000,
			"step" => 100,
			"condition" => [
				"auto_cycle" => "yes",
			],
		]);

		$this->add_control("show_indicators", [
			"label" => __(
				"Show Pagination Indicators",
				"hello-elementor-child",
			),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"label_on" => __("Show", "hello-elementor-child"),
			"label_off" => __("Hide", "hello-elementor-child"),
			"return_value" => "yes",
			"default" => "yes",
			"description" => __(
				"Display dots to show which image is currently visible",
				"hello-elementor-child",
			),
		]);

		$this->add_control("indicators_position", [
			"label" => __("Indicators Position", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "bottom",
			"options" => [
				"bottom" => __("Bottom", "hello-elementor-child"),
				"top" => __("Top", "hello-elementor-child"),
			],
			"condition" => [
				"show_indicators" => "yes",
			],
		]);

		$this->add_control("image_size", [
			"label" => __("Image Size", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "woocommerce_thumbnail",
			"options" => [
				"woocommerce_thumbnail" => __(
					"Thumbnail",
					"hello-elementor-child",
				),
				"woocommerce_single" => __(
					"Single Product",
					"hello-elementor-child",
				),
				"medium" => __("Medium", "hello-elementor-child"),
				"large" => __("Large", "hello-elementor-child"),
				"full" => __("Full", "hello-elementor-child"),
			],
		]);

		$this->add_control("enable_link", [
			"label" => __("Enable Link", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"label_on" => __("Yes", "hello-elementor-child"),
			"label_off" => __("No", "hello-elementor-child"),
			"return_value" => "yes",
			"default" => "no",
			"description" => __(
				"Make the entire gallery clickable",
				"hello-elementor-child",
			),
		]);

		$this->add_control("link_type", [
			"label" => __("Link Type", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SELECT,
			"default" => "product",
			"options" => [
				"product" => __("Product Page", "hello-elementor-child"),
				"custom" => __("Custom URL", "hello-elementor-child"),
			],
			"condition" => [
				"enable_link" => "yes",
			],
		]);

		$this->add_control("custom_link", [
			"label" => __("Custom URL", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::URL,
			"placeholder" => __(
				"https://your-link.com",
				"hello-elementor-child",
			),
			"default" => [
				"url" => "",
				"is_external" => false,
				"nofollow" => false,
			],
			"condition" => [
				"enable_link" => "yes",
				"link_type" => "custom",
			],
		]);

		$this->add_control("link_target", [
			"label" => __("Open in New Tab", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"label_on" => __("Yes", "hello-elementor-child"),
			"label_off" => __("No", "hello-elementor-child"),
			"return_value" => "yes",
			"default" => "no",
			"condition" => [
				"enable_link" => "yes",
			],
		]);

		$this->end_controls_section();

		// Style Section
		$this->start_controls_section("style_section", [
			"label" => __("Style", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_responsive_control("image_border_radius", [
			"label" => __("Border Radius", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::DIMENSIONS,
			"size_units" => ["px", "%"],
			"selectors" => [
				"{{WRAPPER}} .product-hover-gallery" =>
					"border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
				"{{WRAPPER}} .product-hover-gallery img" =>
					"border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
			],
		]);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				"name" => "image_box_shadow",
				"selector" => "{{WRAPPER}} .product-hover-gallery",
			],
		);

		$this->add_control("indicator_color", [
			"label" => __("Indicator Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#cccccc",
			"selectors" => [
				"{{WRAPPER}} .gallery-indicator" =>
					"background-color: {{VALUE}};",
			],
			"condition" => [
				"show_indicators" => "yes",
			],
		]);

		$this->add_control("indicator_active_color", [
			"label" => __("Active Indicator Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"default" => "#1e1e1e",
			"selectors" => [
				"{{WRAPPER}} .gallery-indicator.active" =>
					"background-color: {{VALUE}};",
			],
			"condition" => [
				"show_indicators" => "yes",
			],
		]);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend
	 */
	protected function render()
	{
		$settings = $this->get_settings_for_display();

		// Check if we're on a product page or have a product context
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

		$attachment_ids = $product->get_gallery_image_ids();
		$featured_image_id = $product->get_image_id();

		// Combine featured image with gallery images
		$all_images = [];
		if ($featured_image_id) {
			$all_images[] = $featured_image_id;
		}
		if (!empty($attachment_ids)) {
			$all_images = array_merge($all_images, $attachment_ids);
		}

		if (empty($all_images)) {
			echo "<p>" .
				__("No product images found.", "hello-elementor-child") .
				"</p>";
			return;
		}

		$widget_id = "product-gallery-" . $this->get_id() . "-" . get_the_ID();
		$hover_effect = $settings["hover_effect"];
		$transition_speed = $settings["transition_speed"];
		$auto_cycle = $settings["auto_cycle"] === "yes";
		$cycle_speed = $settings["cycle_speed"];
		$show_indicators = $settings["show_indicators"] === "yes";
		$image_size = $settings["image_size"];

		// Link settings
		$enable_link = $settings["enable_link"] === "yes";
		$link_url = "";
		$link_target = $settings["link_target"] === "yes" ? "_blank" : "_self";
		$link_nofollow = "";

		if ($enable_link) {
			if ($settings["link_type"] === "product") {
				$link_url = get_permalink($product->get_id());
			} elseif (
				$settings["link_type"] === "custom" &&
				!empty($settings["custom_link"]["url"])
			) {
				$link_url = $settings["custom_link"]["url"];
				$link_target = $settings["custom_link"]["is_external"]
					? "_blank"
					: "_self";
				$link_nofollow = $settings["custom_link"]["nofollow"]
					? "nofollow"
					: "";
			}
		}
		?>
        <div class="product-hover-gallery-wrapper" id="<?php echo esc_attr(
        	$widget_id,
        ); ?>">
            <?php if ($enable_link && $link_url): ?>
                <a href="<?php echo esc_url($link_url); ?>"
                   target="<?php echo esc_attr($link_target); ?>"
                   <?php echo $link_nofollow ? 'rel="nofollow"' : ""; ?>
                   class="product-gallery-link">
            <?php endif; ?>

            <div class="product-hover-gallery" data-effect="<?php echo esc_attr(
            	$hover_effect,
            ); ?>" data-auto-cycle="<?php echo $auto_cycle
	? "true"
	: "false"; ?>" data-cycle-speed="<?php echo esc_attr($cycle_speed); ?>">
                <?php foreach ($all_images as $index => $image_id): ?>
                    <img
                        src="<?php echo esc_url(
                        	wp_get_attachment_image_url($image_id, $image_size),
                        ); ?>"
                        alt="<?php echo esc_attr(
                        	get_post_meta(
                        		$image_id,
                        		"_wp_attachment_image_alt",
                        		true,
                        	),
                        ); ?>"
                        class="gallery-image <?php echo $index === 0
                        	? "active"
                        	: ""; ?>"
                        data-index="<?php echo esc_attr($index); ?>"
                    >
                <?php endforeach; ?>
            </div>

            <?php if ($show_indicators && count($all_images) > 1): ?>
                <div class="gallery-indicators">
                    <?php foreach ($all_images as $index => $image_id): ?>
                        <span class="gallery-indicator <?php echo $index === 0
                        	? "active"
                        	: ""; ?>" data-index="<?php echo esc_attr(
	$index,
); ?>"></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($enable_link && $link_url): ?>
                </a>
            <?php endif; ?>
        </div>

        <style>
            #<?php echo esc_attr(
            	$widget_id,
            ); ?> .product-hover-gallery-wrapper {
                position: relative;
                overflow: hidden;
            }

            #<?php echo esc_attr($widget_id); ?> .product-gallery-link {
                display: block;
                text-decoration: none;
                cursor: pointer;
            }

            #<?php echo esc_attr($widget_id); ?> .product-hover-gallery {
                position: relative;
                width: 100%;
                aspect-ratio: 1 / 1;
                overflow: hidden;
            }

            #<?php echo esc_attr($widget_id); ?> .product-hover-gallery img {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                opacity: 0;
                transition: opacity <?php echo esc_attr(
                	$transition_speed,
                ); ?>ms ease, transform <?php echo esc_attr(
	$transition_speed,
); ?>ms ease;
            }

            #<?php echo esc_attr(
            	$widget_id,
            ); ?> .product-hover-gallery img.active {
                opacity: 1;
            }

            #<?php echo esc_attr(
            	$widget_id,
            ); ?> .product-hover-gallery[data-effect="zoom"] img.active {
                transform: scale(1);
            }

            #<?php echo esc_attr(
            	$widget_id,
            ); ?> .product-hover-gallery[data-effect="zoom"]:hover img.active,
            #<?php echo esc_attr(
            	$widget_id,
            ); ?> .product-gallery-link:hover .product-hover-gallery[data-effect="zoom"] img.active {
                transform: scale(1.05);
            }

            #<?php echo esc_attr(
            	$widget_id,
            ); ?> .product-hover-gallery[data-effect="slide"] img {
                transform: translateX(100%);
            }

            #<?php echo esc_attr(
            	$widget_id,
            ); ?> .product-hover-gallery[data-effect="slide"] img.active {
                transform: translateX(0);
            }

            #<?php echo esc_attr($widget_id); ?> .gallery-indicators {
                position: absolute;
                <?php echo $settings["indicators_position"] === "top"
                	? "top"
                	: "bottom"; ?>: 15px;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                gap: 8px;
                z-index: 10;
                padding: 5px 10px;
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(5px);
                border-radius: 20px;
                pointer-events: none;
            }

            #<?php echo esc_attr($widget_id); ?> .gallery-indicator {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                cursor: pointer;
                transition: all 0.3s ease;
                pointer-events: all;
            }

            #<?php echo esc_attr($widget_id); ?> .gallery-indicator:hover {
                transform: scale(1.2);
            }

            #<?php echo esc_attr($widget_id); ?> .gallery-indicator.active {
                width: 24px;
                border-radius: 4px;
            }

            /* Mobile Adjustments */
            @media (max-width: 768px) {
                #<?php echo esc_attr($widget_id); ?> .product-hover-gallery {
                    position: relative;
                }

                #<?php echo esc_attr(
                	$widget_id,
                ); ?> .product-hover-gallery img {
                    position: relative;
                    display: none;
                }

                #<?php echo esc_attr(
                	$widget_id,
                ); ?> .product-hover-gallery img.active {
                    display: block;
                    position: relative;
                }

                #<?php echo esc_attr($widget_id); ?> .gallery-indicators {
                    position: relative;
                    left: auto;
                    transform: none;
                    justify-content: center;
                    margin-top: 10px;
                    <?php echo $settings["indicators_position"] === "top"
                    	? "top"
                    	: "bottom"; ?>: auto;
                }
            }
        </style>

        <script>
        (function($) {
            $(document).ready(function() {
                var widgetId = '<?php echo esc_js($widget_id); ?>';
                var gallery = $('#' + widgetId + ' .product-hover-gallery');
                var images = gallery.find('.gallery-image');
                var indicators = $('#' + widgetId + ' .gallery-indicator');
                var currentIndex = 0;
                var totalImages = images.length;
                var isHovering = false;
                var cycleInterval;
                var autoCycle = gallery.data('auto-cycle');
                var cycleSpeed = gallery.data('cycle-speed');

                if (totalImages <= 1) return;

                function showImage(index) {
                    images.removeClass('active');
                    indicators.removeClass('active');

                    images.eq(index).addClass('active');
                    indicators.eq(index).addClass('active');

                    currentIndex = index;
                }

                function nextImage() {
                    var nextIndex = (currentIndex + 1) % totalImages;
                    showImage(nextIndex);
                }

                function startCycle() {
                    if (isHovering) {
                        // Show next image immediately on hover
                        if (autoCycle) {
                            nextImage();
                            cycleInterval = setInterval(nextImage, cycleSpeed);
                        } else {
                            // If auto-cycle is off, just show the second image
                            if (totalImages > 1) {
                                showImage(1);
                            }
                        }
                    }
                }

                function stopCycle() {
                    if (cycleInterval) {
                        clearInterval(cycleInterval);
                        cycleInterval = null;
                    }
                }

                // Hover events - support both direct hover and link wrapper hover
                var galleryWrapper = gallery.closest('.product-hover-gallery-wrapper');

                galleryWrapper.on('mouseenter', function() {
                    isHovering = true;
                    startCycle();
                });

                galleryWrapper.on('mouseleave', function() {
                    isHovering = false;
                    stopCycle();
                    showImage(0); // Return to first image
                });

                // Indicator click events
                indicators.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var index = $(this).data('index');
                    stopCycle();
                    showImage(index);
                    if (isHovering && autoCycle) {
                        // Restart cycle from clicked image
                        setTimeout(function() {
                            cycleInterval = setInterval(nextImage, cycleSpeed);
                        }, cycleSpeed);
                    }
                });

                // Clean up on window unload
                $(window).on('beforeunload', function() {
                    stopCycle();
                });
            });
        })(jQuery);
        </script>
        <?php
	}

	/**
	 * Render widget output in the editor
	 */
	protected function content_template()
	{
		?>
        <div class="product-hover-gallery-wrapper">
            <div class="product-hover-gallery">
                <img src="https://via.placeholder.com/400x400?text=Product+Image+1" class="gallery-image active" alt="Product Image">
                <img src="https://via.placeholder.com/400x400?text=Product+Image+2" class="gallery-image" alt="Product Image">
                <img src="https://via.placeholder.com/400x400?text=Product+Image+3" class="gallery-image" alt="Product Image">
            </div>

            <# if (settings.show_indicators === 'yes') { #>
                <div class="gallery-indicators">
                    <span class="gallery-indicator active"></span>
                    <span class="gallery-indicator"></span>
                    <span class="gallery-indicator"></span>
                </div>
            <# } #>
        </div>
        <p style="text-align: center; margin-top: 10px; font-size: 12px; color: #999;">
            Preview: Hover to see gallery effect on frontend
        </p>
        <?php
	}
}
