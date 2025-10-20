/**
 * Force Load Elementor Template CSS - COMPLETE FIX
 */

(function ($) {
	"use strict";

	console.log(
		"%c🎨 Template CSS Loader initialized",
		"color: #9C27B0; font-weight: bold;",
	);

	// Function to force CSS reload
	function forceReloadTemplateCSS() {
		// Find all loop templates
		var $templates = $(".e-loop-item, .product-loop-item");

		if ($templates.length > 0) {
			console.log(
				"%c✅ Found " + $templates.length + " loop items",
				"color: #4CAF50;",
			);

			// Force browser to recalculate styles
			$templates.each(function (index) {
				var $item = $(this);

				// Force style recalculation
				$item.hide().show(0);

				// Trigger reflow
				void $item[0].offsetHeight;

				// Add loaded class
				$item.addClass("template-css-loaded");
			});

			console.log(
				"%c✅ Template CSS applied to all items",
				"color: #4CAF50;",
			);
		}
	}

	// Wait for Elementor to be ready
	if (typeof elementorFrontend !== "undefined") {
		$(window).on("elementor/frontend/init", function () {
			console.log(
				"%c🎨 Elementor frontend initialized",
				"color: #2196F3;",
			);

			// Handle Loop Grid
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/loop-grid.default",
				function ($scope) {
					setTimeout(forceReloadTemplateCSS, 100);
				},
			);

			// Handle Custom Product Loop Grid
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/custom_product_loop_grid.default",
				function ($scope) {
					console.log(
						"%c🎨 Custom product loop grid detected",
						"color: #FF9800;",
					);
					setTimeout(forceReloadTemplateCSS, 100);
				},
			);
		});
	}

	// Also run on document ready
	$(document).ready(function () {
		setTimeout(forceReloadTemplateCSS, 500);
	});

	// Run on window load as final fallback
	$(window).on("load", function () {
		setTimeout(forceReloadTemplateCSS, 1000);
	});
})(jQuery);
