/**
 * ✅ TRASH PRODUCT CLEANUP SCRIPT
 * Add this to assets/js/cleanup-trash-products.js
 *
 * Removes any trash/draft products from the DOM that shouldn't be there
 */

(function ($) {
	"use strict";

	console.log(
		"%c🗑️ Trash Product Cleanup: Starting...",
		"color: #FF5722; font-weight: bold;",
	);

	/**
	 * Remove trash products from DOM
	 */
	function cleanupTrashProducts() {
		const allProductElements = $(
			'.e-loop-item, .product-loop-item, [class*="product-id-"]',
		);

		console.log(
			`🔍 Found ${allProductElements.length} product elements in DOM`,
		);

		let removedCount = 0;
		const publishedProductIds = Object.keys(
			window.loopGridProductsData || {},
		);

		console.log(
			`📊 Published products in data: ${publishedProductIds.length}`,
		);

		allProductElements.each(function () {
			const $element = $(this);
			const productId = extractProductId($element);

			if (!productId) {
				return;
			}

			// ✅ Check if product is in published products list
			if (!publishedProductIds.includes(String(productId))) {
				console.log(
					`%c🗑️ Removing trash/draft product: ${productId}`,
					"color: #FF5722;",
				);
				$element.remove();
				removedCount++;
			}
		});

		if (removedCount > 0) {
			console.log(
				`%c✅ Cleanup complete: Removed ${removedCount} trash/draft products`,
				"color: #4CAF50; font-weight: bold;",
			);
		} else {
			console.log(
				"%c✅ No trash products found - all clean!",
				"color: #4CAF50; font-weight: bold;",
			);
		}
	}

	/**
	 * Extract product ID from element
	 */
	function extractProductId($element) {
		// Try data attribute
		let id =
			$element.data("product-id") || $element.attr("data-product-id");
		if (id && id !== "{{ post.id }}") {
			return String(id);
		}

		// Try classes
		const classes = $element.attr("class") || "";
		const patterns = [
			/product-id-(\d+)/,
			/e-loop-item-(\d+)/,
			/post-(\d+)/,
			/elementor-post-(\d+)/,
		];

		for (const pattern of patterns) {
			const match = classes.match(pattern);
			if (match) {
				return String(match[1]);
			}
		}

		// Try add to cart button
		const addToCartBtn = $element.find("[data-product_id]");
		if (addToCartBtn.length) {
			return String(addToCartBtn.data("product_id"));
		}

		return null;
	}

	/**
	 * Run cleanup after products data is loaded
	 */
	function initCleanup() {
		if (window.loopGridProductsData) {
			cleanupTrashProducts();
		} else {
			console.warn(
				"⚠️ window.loopGridProductsData not available yet, waiting...",
			);
			setTimeout(initCleanup, 500);
		}
	}

	// Run on document ready
	$(document).ready(function () {
		setTimeout(initCleanup, 1000);
	});

	// Run after attributes are ready
	$(document).on("loop-grid-attributes-ready", function () {
		console.log("🔄 Attributes ready - cleaning up trash products");
		cleanupTrashProducts();
	});

	// Run on Elementor frontend init
	$(window).on("elementor/frontend/init", function () {
		setTimeout(initCleanup, 1500);
	});

	// Expose globally for manual cleanup
	window.cleanupTrashProducts = cleanupTrashProducts;

	console.log(
		"%c💡 Manual cleanup: Type cleanupTrashProducts() in console",
		"color: #00BCD4;",
	);
})(jQuery);
