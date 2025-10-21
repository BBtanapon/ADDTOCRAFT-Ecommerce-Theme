/**
 * Loop Grid Layout Fix - FULLY RESPONSIVE WITH ELEMENTOR BREAKPOINTS
 * Desktop: >1024px | Tablet: 768-1024px | Mobile: ≤767px
 */

(function ($) {
	"use strict";

	// Elementor breakpoints
	const BREAKPOINTS = {
		mobile: 767,
		tablet: 1024,
	};

	function getDeviceType() {
		const width = window.innerWidth;
		if (width <= BREAKPOINTS.mobile) return "mobile";
		if (width <= BREAKPOINTS.tablet) return "tablet";
		return "desktop";
	}

	function getColumnsForDevice(container) {
		const deviceType = getDeviceType();

		// Try to get columns from data attributes
		let columns = 4; // default

		if (deviceType === "mobile") {
			columns = parseInt(container.dataset.columnsMobile) || 1;
		} else if (deviceType === "tablet") {
			columns = parseInt(container.dataset.columnsTablet) || 2;
		} else {
			columns = parseInt(container.dataset.columns) || 4;
		}

		return columns;
	}

	function fixLoopGridLayout() {
		console.log(
			"%c🔧 Fixing Loop Grid Layout...",
			"color: #FF9800; font-weight: bold;",
		);

		const loopContainers = document.querySelectorAll(
			".elementor-loop-container, .custom-product-loop-grid",
		);

		if (loopContainers.length === 0) {
			console.warn("⚠️ No loop containers found");
			return;
		}

		const deviceType = getDeviceType();
		const width = window.innerWidth;

		console.log(`   Device: ${deviceType} (${width}px)`);
		console.log(`   Found ${loopContainers.length} loop container(s)`);

		loopContainers.forEach((container, index) => {
			const columns = getColumnsForDevice(container);

			console.log(`   Container ${index + 1}: ${columns} columns`);

			// Force grid display
			container.style.display = "grid";
			container.style.gridTemplateColumns = `repeat(${columns}, 1fr)`;
			container.style.width = "100%";
			container.style.maxWidth = "100%";
			container.style.justifyItems = "stretch";
			container.style.alignItems = "start";
			container.style.justifyContent = "start";
			container.style.alignContent = "start";
			container.style.boxSizing = "border-box";

			// Set gap based on device
			let gap = "30px";
			if (deviceType === "mobile") {
				gap = "15px";
			} else if (deviceType === "tablet") {
				gap = "20px";
			}
			container.style.gap = gap;

			// Fix all children
			const items = container.querySelectorAll(
				".e-loop-item, .product-loop-item, .elementor-post",
			);

			items.forEach((item) => {
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

	// Run on document ready
	$(document).ready(function () {
		console.log(
			"%c🚀 Loop Grid Layout Fixer Loaded",
			"color: #2196F3; font-weight: bold;",
		);
		fixLoopGridLayout();
	});

	// Run on window load
	$(window).on("load", function () {
		setTimeout(function () {
			fixLoopGridLayout();
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

	// Handle window resize with debounce
	let resizeTimer;
	$(window).on("resize", function () {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(function () {
			fixLoopGridLayout();
		}, 250);
	});

	// Watch for DOM mutations (if products are added dynamically)
	const observer = new MutationObserver(function (mutations) {
		let shouldFix = false;

		mutations.forEach(function (mutation) {
			if (mutation.addedNodes.length > 0) {
				mutation.addedNodes.forEach(function (node) {
					if (node.nodeType === 1) {
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
