/**
 * Loop Grid Layout Fix - COMPLETE VERSION
 * Ensures proper grid layout after Elementor loads
 */

(function ($) {
	"use strict";

	function fixLoopGridLayout() {
		console.log(
			"%c🔧 Fixing Loop Grid Layout...",
			"color: #FF9800; font-weight: bold;",
		);

		// Find all loop containers
		const loopContainers = document.querySelectorAll(
			".elementor-loop-container, .custom-product-loop-grid",
		);

		if (loopContainers.length === 0) {
			console.warn("⚠️ No loop containers found");
			return;
		}

		console.log(`   Found ${loopContainers.length} loop container(s)`);

		loopContainers.forEach((container, index) => {
			// Force grid display
			container.style.display = "grid";
			container.style.gridTemplateColumns = "repeat(4, 1fr)";
			container.style.gap = "30px";
			container.style.width = "100%";
			container.style.maxWidth = "100%";
			container.style.justifyItems = "stretch";
			container.style.alignItems = "start";
			container.style.justifyContent = "start";
			container.style.alignContent = "start";
			container.style.boxSizing = "border-box";

			// Fix all children
			const items = container.querySelectorAll(
				".e-loop-item, .product-loop-item, .elementor-post",
			);

			console.log(`   Container ${index + 1}: ${items.length} items`);

			items.forEach((item, itemIndex) => {
				// Force proper sizing
				item.style.width = "100%";
				item.style.maxWidth = "100%";
				item.style.minWidth = "0";
				item.style.overflow = "hidden";
				item.style.boxSizing = "border-box";
				item.style.justifySelf = "stretch";
				item.style.alignSelf = "start";
				item.style.margin = "0";
				item.style.display = "flex";
				item.style.flexDirection = "column";

				// Fix inner content
				const innerElements = item.querySelectorAll("*");
				innerElements.forEach((el) => {
					if (el.tagName === "IMG") {
						el.style.width = "100%";
						el.style.height = "auto";
						el.style.maxWidth = "100%";
						el.style.display = "block";
					}
				});

				// Fix Elementor sections and columns
				const sections = item.querySelectorAll(".elementor-section");
				sections.forEach((section) => {
					section.style.width = "100%";
					section.style.maxWidth = "100%";
				});

				const columns = item.querySelectorAll(".elementor-column");
				columns.forEach((column) => {
					column.style.width = "100%";
					column.style.maxWidth = "100%";
				});

				const wraps = item.querySelectorAll(".elementor-widget-wrap");
				wraps.forEach((wrap) => {
					wrap.style.width = "100%";
					wrap.style.maxWidth = "100%";
				});
			});
		});

		console.log(
			"%c✅ Loop grid layout fixed successfully!",
			"color: #4CAF50; font-weight: bold;",
		);
	}

	// Responsive layout fix
	function applyResponsiveLayout() {
		const width = window.innerWidth;
		const loopContainers = document.querySelectorAll(
			".elementor-loop-container, .custom-product-loop-grid",
		);

		loopContainers.forEach((container) => {
			if (width <= 768) {
				// Mobile: 1 column
				container.style.gridTemplateColumns = "repeat(1, 1fr)";
			} else if (width <= 1024) {
				// Tablet: 2 columns
				container.style.gridTemplateColumns = "repeat(2, 1fr)";
			} else {
				// Desktop: 4 columns
				container.style.gridTemplateColumns = "repeat(4, 1fr)";
			}
		});
	}

	// Run on document ready
	$(document).ready(function () {
		console.log(
			"%c🚀 Loop Grid Layout Fixer Loaded",
			"color: #2196F3; font-weight: bold;",
		);
		fixLoopGridLayout();
		applyResponsiveLayout();
	});

	// Run on window load
	$(window).on("load", function () {
		setTimeout(function () {
			fixLoopGridLayout();
			applyResponsiveLayout();
		}, 500);
	});

	// Run on Elementor init
	if (typeof elementorFrontend !== "undefined") {
		$(window).on("elementor/frontend/init", function () {
			console.log(
				"%c🎨 Elementor Frontend Init Detected",
				"color: #9C27B0;",
			);

			setTimeout(function () {
				fixLoopGridLayout();
				applyResponsiveLayout();
			}, 1000);

			// Hook into loop grid widget
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/loop-grid.default",
				function ($scope) {
					setTimeout(fixLoopGridLayout, 200);
				},
			);

			// Hook into custom product loop grid widget
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/custom_product_loop_grid.default",
				function ($scope) {
					setTimeout(fixLoopGridLayout, 200);
				},
			);
		});
	}

	// Handle window resize
	let resizeTimer;
	$(window).on("resize", function () {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(function () {
			applyResponsiveLayout();
		}, 250);
	});

	// Watch for DOM mutations (if products are added dynamically)
	const observer = new MutationObserver(function (mutations) {
		let shouldFix = false;

		mutations.forEach(function (mutation) {
			if (mutation.addedNodes.length > 0) {
				mutation.addedNodes.forEach(function (node) {
					if (node.nodeType === 1) {
						// Element node
						if (
							node.classList &&
							(node.classList.contains("e-loop-item") ||
								node.classList.contains("product-loop-item") ||
								node.classList.contains("elementor-post"))
						) {
							shouldFix = true;
						}
					}
				});
			}
		});

		if (shouldFix) {
			console.log(
				"%c🔄 New items detected, applying fixes...",
				"color: #FF5722;",
			);
			setTimeout(fixLoopGridLayout, 100);
		}
	});

	// Start observing
	$(document).ready(function () {
		const containers = document.querySelectorAll(
			".elementor-loop-container, .custom-product-loop-grid",
		);
		containers.forEach(function (container) {
			observer.observe(container, {
				childList: true,
				subtree: true,
			});
		});
	});

	// Expose function globally for manual fixes
	window.fixLoopGridLayout = fixLoopGridLayout;

	console.log(
		"%c💡 Tip: Type fixLoopGridLayout() in console to manually fix layout",
		"color: #00BCD4;",
	);
})(jQuery);
