/**
 * ✅ ENHANCED TRASH PRODUCT CLEANUP SCRIPT
 * Aggressively removes draft/trash products from DOM
 * Only published products remain visible
 *
 * @package HelloElementorChild
 */

(function ($) {
	"use strict";

	console.log(
		"%c🗑️ Enhanced Trash Product Cleanup: Starting...",
		"color: #FF5722; font-weight: bold; font-size: 14px;",
	);

	/**
	 * ✅ MAIN CLEANUP FUNCTION
	 */
	function cleanupTrashProducts() {
		// Wait for products data to be available
		if (!window.loopGridProductsData) {
			console.warn("⏳ Products data not ready yet, retrying...");
			setTimeout(cleanupTrashProducts, 200);
			return;
		}

		console.log(
			"%c📊 Products Data Ready",
			"color: #4CAF50; font-weight: bold;",
		);

		const publishedProductIds = Object.keys(window.loopGridProductsData);
		console.log(
			`   ✅ Published products in data: ${publishedProductIds.length}`,
		);

		// Find ALL possible product elements (cast a wide net)
		const allProductElements = $(
			'.e-loop-item, .product-loop-item, [class*="product-id-"], [class*="post-"], [data-product-id], .elementor-post, .product, .type-product',
		);

		console.log(
			`   🔍 Found ${allProductElements.length} product elements in DOM`,
		);

		let removedCount = 0;
		let keptCount = 0;
		const removedIds = [];

		console.group(
			"%c🔄 Checking Each Product",
			"color: #2196F3; font-weight: bold;",
		);

		allProductElements.each(function () {
			const $element = $(this);
			const productId = extractProductId($element);

			if (!productId) {
				console.log("   ⚠️ Element has no product ID, skipping...");
				return; // Skip if we can't find ID
			}

			// ✅ CRITICAL CHECK: Is this product in published data?
			if (!publishedProductIds.includes(String(productId))) {
				console.log(
					`%c   🗑️ REMOVING: Product ${productId} (not published)`,
					"color: #FF5722;",
				);

				// Remove the element completely
				$element.remove();

				removedCount++;
				removedIds.push(productId);
			} else {
				console.log(
					`%c   ✅ KEEPING: Product ${productId} (published)`,
					"color: #4CAF50;",
				);
				keptCount++;
			}
		});

		console.groupEnd();

		// Summary
		console.log(
			"%c─────────────────────────────────────",
			"color: #9E9E9E;",
		);
		console.log(
			"%c✅ Cleanup Complete!",
			"color: #4CAF50; font-weight: bold; font-size: 16px;",
		);
		console.log(
			`%c   ✓ Kept: ${keptCount} published products`,
			"color: #4CAF50;",
		);
		console.log(
			`%c   🗑️ Removed: ${removedCount} trash/draft products`,
			"color: #FF5722;",
		);

		if (removedIds.length > 0) {
			console.log(
				"%c   Removed Product IDs:",
				"color: #FF5722; font-weight: bold;",
			);
			console.log("   ", removedIds);
		}

		console.log(
			"%c─────────────────────────────────────",
			"color: #9E9E9E;",
		);

		// Trigger event for other scripts
		$(document).trigger("trash-products-cleaned");
	}

	/**
	 * ✅ EXTRACT PRODUCT ID FROM ELEMENT
	 * Tries multiple methods to find the product ID
	 */
	function extractProductId($element) {
		// Method 1: data-product-id attribute
		let id =
			$element.data("product-id") ||
			$element.attr("data-product-id") ||
			$element.attr("data-product_id");

		if (id && id !== "{{ post.id }}" && id !== "") {
			return String(id);
		}

		// Method 2: Check classes for product-id-XXX
		const classes = $element.attr("class") || "";
		const patterns = [
			/product-id-(\d+)/,
			/post-(\d+)/,
			/elementor-post-(\d+)/,
			/type-product-(\d+)/,
		];

		for (const pattern of patterns) {
			const match = classes.match(pattern);
			if (match) {
				return String(match[1]);
			}
		}

		// Method 3: Check for add to cart button
		const addToCartBtn = $element.find(
			"[data-product_id], [data-product-id]",
		);
		if (addToCartBtn.length) {
			id =
				addToCartBtn.data("product_id") ||
				addToCartBtn.data("product-id");
			if (id) {
				return String(id);
			}
		}

		// Method 4: Check for product link
		const productLink = $element.find('a[href*="/product/"]');
		if (productLink.length) {
			const href = productLink.attr("href");
			const match = href.match(/product\/([^\/]+)/);
			if (match) {
				const slug = match[1];

				// Try to find product ID by slug in our data
				for (const [id, data] of Object.entries(
					window.loopGridProductsData || {},
				)) {
					if (
						data.title &&
						data.title.toLowerCase().replace(/\s+/g, "-") === slug
					) {
						return String(id);
					}
				}
			}
		}

		return null;
	}

	/**
	 * ✅ RUN CLEANUP AT MULTIPLE POINTS
	 * Ensures cleanup runs whenever products might be loaded
	 */

	// Run immediately if DOM is ready
	$(document).ready(function () {
		console.log("📄 DOM Ready - Starting cleanup in 500ms");
		setTimeout(cleanupTrashProducts, 500);
	});

	// Run after window load
	$(window).on("load", function () {
		console.log("🌐 Window Loaded - Running cleanup");
		setTimeout(cleanupTrashProducts, 500);
	});

	// Run when attributes are ready
	$(document).on("loop-grid-attributes-ready", function () {
		console.log("🎯 Attributes Ready - Running cleanup");
		setTimeout(cleanupTrashProducts, 100);
	});

	// Run on Elementor frontend init
	if (typeof elementorFrontend !== "undefined") {
		$(window).on("elementor/frontend/init", function () {
			console.log("🎨 Elementor Init - Running cleanup");
			setTimeout(cleanupTrashProducts, 1000);

			// Hook into loop grid specifically
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/loop-grid.default",
				function ($scope) {
					console.log("🔄 Loop Grid Ready - Running cleanup");
					setTimeout(cleanupTrashProducts, 500);
				},
			);

			// Hook into custom product loop grid
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/custom_product_loop_grid.default",
				function ($scope) {
					console.log("🔄 Custom Loop Grid Ready - Running cleanup");
					setTimeout(cleanupTrashProducts, 500);
				},
			);
		});
	}

	/**
	 * ✅ EXPOSE GLOBAL FUNCTIONS FOR MANUAL USE
	 */

	// Manual cleanup function
	window.cleanupTrashProducts = cleanupTrashProducts;

	// Debug function to find ghost products
	window.debugTrashProducts = function () {
		console.group(
			"%c🔍 Trash Products Debug",
			"color: #FF5722; font-weight: bold; font-size: 16px;",
		);

		if (!window.loopGridProductsData) {
			console.error("❌ window.loopGridProductsData is not available!");
			console.groupEnd();
			return;
		}

		const publishedIds = Object.keys(window.loopGridProductsData);
		console.log(
			"%c📊 Published Products in Data:",
			"color: #4CAF50; font-weight: bold;",
		);
		console.log("   Count:", publishedIds.length);
		console.log("   IDs:", publishedIds);

		const allElements = $(
			'.e-loop-item, .product-loop-item, [class*="product-id-"], .elementor-post, .product, .type-product',
		);
		console.log(
			`\n%c🔍 Total Elements in DOM: ${allElements.length}`,
			"color: #2196F3; font-weight: bold;",
		);

		const ghostProducts = [];
		const publishedProducts = [];

		allElements.each(function () {
			const $el = $(this);
			const id = extractProductId($el);

			if (!id) {
				return;
			}

			if (!publishedIds.includes(String(id))) {
				ghostProducts.push({
					id: id,
					element: this,
					classes: $el.attr("class"),
					title:
						$el.find("h2, h3, .product-title").text() || "No title",
				});
			} else {
				publishedProducts.push({
					id: id,
					title:
						$el.find("h2, h3, .product-title").text() || "No title",
				});
			}
		});

		console.log(
			`\n%c✅ Published Products in DOM: ${publishedProducts.length}`,
			"color: #4CAF50; font-weight: bold;",
		);
		console.table(publishedProducts);

		if (ghostProducts.length > 0) {
			console.log(
				`\n%c⚠️ GHOST Products Found: ${ghostProducts.length}`,
				"color: #FF5722; font-weight: bold; font-size: 14px;",
			);
			console.log(
				"%cThese products are in DOM but NOT in published data (should be removed):",
				"color: #FF5722;",
			);
			console.table(ghostProducts);

			console.log(
				"\n%c💡 To remove these ghost products, run:",
				"color: #00BCD4; font-weight: bold;",
			);
			console.log("   cleanupTrashProducts()");
		} else {
			console.log(
				"\n%c✅ No ghost products found! All clean!",
				"color: #4CAF50; font-weight: bold; font-size: 14px;",
			);
		}

		console.groupEnd();
	};

	// List all published products
	window.listPublishedProducts = function () {
		if (!window.loopGridProductsData) {
			console.error("❌ window.loopGridProductsData is not available!");
			return;
		}

		console.group(
			"%c📦 Published Products List",
			"color: #4CAF50; font-weight: bold; font-size: 16px;",
		);

		const products = Object.entries(window.loopGridProductsData).map(
			([id, data]) => ({
				ID: id,
				Title: data.title,
				Price: data.price,
				Categories: data.categories?.join(", ") || "None",
				Tags: data.tags?.join(", ") || "None",
			}),
		);

		console.table(products);
		console.log(`Total: ${products.length} published products`);
		console.groupEnd();
	};

	console.log(
		"%c💡 Debug Commands Available!",
		"color: #00BCD4; font-weight: bold; font-size: 14px;",
	);
	console.log(
		"   %ccleanupTrashProducts()%c - Manually remove trash products",
		"background: #333; color: #0f0; padding: 2px 5px;",
		"",
	);
	console.log(
		"   %cdebugTrashProducts()%c - Show ghost products analysis",
		"background: #333; color: #0f0; padding: 2px 5px;",
		"",
	);
	console.log(
		"   %clistPublishedProducts()%c - List all published products",
		"background: #333; color: #0f0; padding: 2px 5px;",
		"",
	);
})(jQuery);
