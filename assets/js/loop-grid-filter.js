/**
 * Loop Grid Filter - ULTIMATE DEDUPLICATION FIX + Widget Reinitialization
 * Filters by Product ID FIRST - guarantees zero duplicates
 * ADDED: Reinitializes all widget scripts after filtering
 *
 * @package HelloElementorChild
 */

(function ($) {
	"use strict";

	class LoopGridFilter {
		constructor(widgetElement) {
			this.widget = $(widgetElement);
			this.widgetId = this.widget.data("widget-id");
			this.targetId = this.widget.data("target");
			this.targetGrid = null;
			this.debounceTimer = null;

			// Store UNIQUE products only - Map ensures uniqueness
			this.uniqueProducts = new Map(); // productId => {element, data, index}

			// Grid properties
			this.gridClasses = "";
			this.gridStyles = "";

			this.currentFilters = {
				search: "",
				sort: "date",
				categories: [],
				tags: [],
				attributes: {},
				minPrice: 0,
				maxPrice: 99999,
			};

			this.init();
		}

		init() {
			console.log(
				"🎯 ULTIMATE FILTER: Initializing with Product ID deduplication",
			);

			this.findTargetGrid();

			if (!this.targetGrid || !this.targetGrid.length) {
				console.warn("⚠️ Target grid not found");
				return;
			}

			// Wait for attributes
			const initFilter = () => {
				if (this.uniqueProducts.size === 0) {
					console.log("📸 Capturing unique products");
					this.captureUniqueProducts();
					this.initPriceSliders();
					this.bindEvents();
					this.setupMobile();
				}
			};

			$(document).on("loop-grid-attributes-ready", initFilter);
			setTimeout(initFilter, 2000);
		}

		findTargetGrid() {
			if (this.targetId) {
				const selectors = [
					`.elementor-element-${this.targetId} .elementor-loop-container`,
					`[data-id="${this.targetId}"] .elementor-loop-container`,
					`#${this.targetId} .elementor-loop-container`,
					`#${this.targetId}`,
				];

				for (let selector of selectors) {
					this.targetGrid = $(selector);
					if (this.targetGrid.length) {
						console.log("✅ Found grid:", selector);
						break;
					}
				}
			}

			if (!this.targetGrid || !this.targetGrid.length) {
				this.targetGrid = $(".elementor-loop-container").first();
			}

			if (!this.targetGrid.hasClass("elementor-loop-container")) {
				const container = this.targetGrid
					.find(".elementor-loop-container")
					.first();
				if (container.length) {
					this.targetGrid = container;
				}
			}
		}

		/**
		 * CRITICAL: Capture UNIQUE products only - filter by Product ID FIRST
		 */
		captureUniqueProducts() {
			// Store grid properties
			this.gridClasses = this.targetGrid.attr("class") || "";
			this.gridStyles = this.targetGrid.attr("style") || "";

			console.log("📦 Capturing products...");

			// Clear map
			this.uniqueProducts.clear();

			// Find all potential product items
			const items = this.targetGrid.find(
				'.e-loop-item, .product-loop-item, [class*="product-id-"]',
			);

			console.log(`   Found ${items.length} DOM elements`);

			let skippedDuplicates = 0;

			items.each((index, element) => {
				const $element = $(element);
				const productId = this.extractProductId($element);

				if (!productId) {
					console.warn(`   ⚠️ Item ${index} has no product ID`);
					return;
				}

				// CRITICAL: Skip if product ID already exists in Map
				if (this.uniqueProducts.has(productId)) {
					console.log(
						`   🚫 Skipping duplicate product ID: ${productId} (item ${index})`,
					);
					skippedDuplicates++;
					return;
				}

				// Extract product data
				const productData = this.extractProductData($element);

				// Store in Map (Map automatically ensures uniqueness by key)
				this.uniqueProducts.set(productId, {
					id: productId,
					element: element.cloneNode(true), // Deep clone
					data: productData,
					index: this.uniqueProducts.size, // Use Map size as index
				});

				console.log(
					`   ✅ Stored product ${productId} (${productData.title || "No title"})`,
				);
			});

			console.log(
				`\n✅ Captured ${this.uniqueProducts.size} UNIQUE products`,
			);
			if (skippedDuplicates > 0) {
				console.log(
					`   🚫 Skipped ${skippedDuplicates} duplicate items`,
				);
			}

			// Log all stored products
			console.log("\n📋 Stored Product IDs:");
			Array.from(this.uniqueProducts.keys()).forEach((id, i) => {
				console.log(`   ${i + 1}. Product ID: ${id}`);
			});
		}

		extractProductId($element) {
			// Method 1: Data attributes
			let id =
				$element.data("product-id") || $element.attr("data-product-id");
			if (id && id !== "{{ post.id }}") {
				return String(id);
			}

			// Method 2: From classes
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

			return null;
		}

		extractProductData($element) {
			const data = {
				id: this.extractProductId($element),
				title: "",
				categories: [],
				tags: [],
				attributes: {},
				price: 0,
			};

			// Title - try multiple sources
			const $title = $element
				.find(
					".elementor-heading-title, h2, h3, h4, .product-title, .woocommerce-loop-product__title",
				)
				.first();
			if ($title.length) {
				data.title = $title.text().trim().toLowerCase();
			}

			// Fallback to data attribute
			if (!data.title) {
				data.title = (
					$element.data("title") ||
					$element.attr("data-title") ||
					""
				).toLowerCase();
			}

			// Categories
			const cats =
				$element.data("categories") || $element.attr("data-categories");
			if (cats) {
				data.categories = String(cats)
					.split(",")
					.map((c) => c.trim())
					.filter((c) => c);
			}

			// Tags
			const tags = $element.data("tags") || $element.attr("data-tags");
			if (tags) {
				data.tags = String(tags)
					.split(",")
					.map((t) => t.trim())
					.filter((t) => t);
			}

			// Attributes
			const dataset = $element[0].dataset || {};
			Object.keys(dataset).forEach((key) => {
				if (
					[
						"productId",
						"title",
						"price",
						"regularPrice",
						"salePrice",
						"categories",
						"tags",
						"minPrice",
						"maxPrice",
					].includes(key)
				) {
					return;
				}

				let attrName = key;
				// Convert camelCase to snake_case for pa_ attributes
				if (attrName.startsWith("pa") && attrName.length > 2) {
					if (attrName[2] === attrName[2].toUpperCase()) {
						attrName = attrName
							.replace(/([A-Z])/g, "_$1")
							.toLowerCase();
					}
				}

				const value = dataset[key];
				if (value) {
					data.attributes[attrName] = String(value)
						.split(",")
						.map((v) => v.trim().toLowerCase())
						.filter((v) => v);
				}
			});

			// Price
			const price = parseFloat(
				$element.data("price") || $element.attr("data-price") || 0,
			);
			const regularPrice = parseFloat(
				$element.data("regular-price") ||
					$element.attr("data-regular-price") ||
					0,
			);
			const salePrice = parseFloat(
				$element.data("sale-price") ||
					$element.attr("data-sale-price") ||
					0,
			);
			const minPrice = parseFloat(
				$element.data("min-price") ||
					$element.attr("data-min-price") ||
					0,
			);

			data.price = price || salePrice || regularPrice || minPrice || 0;

			return data;
		}

		initPriceSliders() {
			const minSlider = this.widget.find(".loop-price-min-slider");
			const maxSlider = this.widget.find(".loop-price-max-slider");

			if (!minSlider.length || !maxSlider.length) return;

			const maxPrice = parseInt(maxSlider.attr("max")) || 10000;
			this.currentFilters.maxPrice = maxPrice;

			minSlider.on("input", () => this.updatePriceDisplay());
			maxSlider.on("input", () => this.updatePriceDisplay());

			this.updatePriceDisplay();
		}

		updatePriceDisplay() {
			const minSlider = this.widget.find(".loop-price-min-slider");
			const maxSlider = this.widget.find(".loop-price-max-slider");
			const minVal = parseInt(minSlider.val()) || 0;
			const maxVal = parseInt(maxSlider.val()) || 10000;

			if (minVal > maxVal - 100) {
				minSlider.val(maxVal - 100);
				return;
			}

			this.widget
				.find(".price-min-value")
				.text("฿" + minVal.toLocaleString());
			this.widget
				.find(".price-max-value")
				.text("฿" + maxVal.toLocaleString());
			this.widget.find(".loop-price-min-input").val(minVal);
			this.widget.find(".loop-price-max-input").val(maxVal);

			const maxAttr = parseInt(maxSlider.attr("max")) || 10000;
			const minPercent = (minVal / maxAttr) * 100;
			const maxPercent = (maxVal / maxAttr) * 100;

			this.widget.find(".price-slider-track").css({
				left: minPercent + "%",
				width: maxPercent - minPercent + "%",
			});

			this.currentFilters.minPrice = minVal;
			this.currentFilters.maxPrice = maxVal;
		}

		bindEvents() {
			// Search
			this.widget.find(".loop-filter-search").on(
				"input",
				this.debounce(() => {
					this.currentFilters.search = this.widget
						.find(".loop-filter-search")
						.val()
						.toLowerCase()
						.trim();
					this.applyFilters();
				}, 500),
			);

			// Sort
			this.widget.find(".loop-filter-sort").on("change", () => {
				this.currentFilters.sort = this.widget
					.find(".loop-filter-sort")
					.val();
				this.applyFilters();
			});

			// Categories
			this.widget.on("change", ".loop-filter-category", () => {
				this.updateCheckboxArray("categories", ".loop-filter-category");
				this.applyFilters();
			});

			// Tags
			this.widget.on("change", ".loop-filter-tag", () => {
				this.updateCheckboxArray("tags", ".loop-filter-tag");
				this.applyFilters();
			});

			// Attributes
			this.widget.on("change", ".loop-filter-custom-attribute", () => {
				this.updateCustomAttributes();
				this.applyFilters();
			});

			// Price
			this.widget
				.find(".loop-price-min-slider, .loop-price-max-slider")
				.on("change", () => {
					this.applyFilters();
				});

			this.widget.find(".loop-price-min-input").on("change", () => {
				const val =
					parseInt(this.widget.find(".loop-price-min-input").val()) ||
					0;
				this.widget.find(".loop-price-min-slider").val(val);
				this.updatePriceDisplay();
				this.applyFilters();
			});

			this.widget.find(".loop-price-max-input").on("change", () => {
				const val =
					parseInt(this.widget.find(".loop-price-max-input").val()) ||
					10000;
				this.widget.find(".loop-price-max-slider").val(val);
				this.updatePriceDisplay();
				this.applyFilters();
			});

			// RESET - render all unique products
			this.widget.find(".loop-filter-reset").on("click", () => {
				this.resetToUniqueProducts();
			});

			// Mobile
			$(".filter-toggle-btn").on("click", (e) => {
				e.preventDefault();
				this.openMobileFilter();
			});

			this.widget
				.find(".filter-close-btn, .filter-overlay")
				.on("click", (e) => {
					e.preventDefault();
					this.closeMobileFilter();
				});
		}

		updateCheckboxArray(filterKey, selector) {
			const values = [];
			this.widget.find(selector + ":checked").each(function () {
				values.push(String($(this).val()));
			});
			this.currentFilters[filterKey] = values;
		}

		updateCustomAttributes() {
			const customAttrs = {};
			this.widget
				.find(".loop-filter-custom-attribute:checked")
				.each(function () {
					const value = $(this).val();
					if (value.includes(":")) {
						const [attrName, attrValue] = value.split(":");
						if (!customAttrs[attrName]) {
							customAttrs[attrName] = [];
						}
						customAttrs[attrName].push(attrValue.toLowerCase());
					}
				});
			this.currentFilters.attributes = customAttrs;
		}

		debounce(func, wait) {
			return (...args) => {
				clearTimeout(this.debounceTimer);
				this.debounceTimer = setTimeout(
					() => func.apply(this, args),
					wait,
				);
			};
		}

		/**
		 * Apply filters - works with UNIQUE products from Map
		 */
		applyFilters() {
			console.log("🔍 Applying filters:", this.currentFilters);

			const matchedProducts = [];

			// Iterate through Map - guaranteed unique by product ID
			this.uniqueProducts.forEach((product) => {
				if (this.productMatchesFilters(product.data)) {
					matchedProducts.push(product);
				}
			});

			console.log(`✅ Matched ${matchedProducts.length} unique products`);

			// Sort
			if (this.currentFilters.sort !== "date") {
				matchedProducts.sort((a, b) => {
					switch (this.currentFilters.sort) {
						case "title":
							return (a.data.title || "").localeCompare(
								b.data.title || "",
							);
						case "price":
							return (a.data.price || 0) - (b.data.price || 0);
						case "price-desc":
							return (b.data.price || 0) - (a.data.price || 0);
						default:
							return a.index - b.index;
					}
				});
			}

			// Rebuild with matched products
			this.renderProducts(matchedProducts);
		}

		productMatchesFilters(data) {
			// Search
			if (this.currentFilters.search) {
				if (
					!data.title ||
					!data.title.includes(this.currentFilters.search)
				) {
					return false;
				}
			}

			// Categories
			if (this.currentFilters.categories.length > 0) {
				const hasCategory = this.currentFilters.categories.some((cat) =>
					data.categories.includes(cat),
				);
				if (!hasCategory) return false;
			}

			// Tags
			if (this.currentFilters.tags.length > 0) {
				const hasTag = this.currentFilters.tags.some((tag) =>
					data.tags.includes(tag),
				);
				if (!hasTag) return false;
			}

			// Attributes
			for (const [attrName, attrValues] of Object.entries(
				this.currentFilters.attributes,
			)) {
				if (
					!data.attributes[attrName] ||
					data.attributes[attrName].length === 0
				) {
					return false;
				}

				const hasMatch = attrValues.some((filterValue) =>
					data.attributes[attrName].includes(filterValue),
				);

				if (!hasMatch) return false;
			}

			// Price
			if (data.price > 0) {
				if (
					data.price < this.currentFilters.minPrice ||
					data.price > this.currentFilters.maxPrice
				) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Render products to grid - GUARANTEED UNIQUE + Reinitialize Widgets
		 */
		renderProducts(products) {
			console.log(`🔨 Rendering ${products.length} UNIQUE products`);

			const container = this.targetGrid[0];

			// Clear grid
			container.innerHTML = "";

			// Restore grid properties
			this.targetGrid.attr("class", this.gridClasses);
			if (this.gridStyles) {
				this.targetGrid.attr("style", this.gridStyles);
			}

			// CRITICAL: Force 4-column grid layout with inline styles
			this.targetGrid.css({
				display: "grid !important",
				"grid-template-columns": "repeat(4, 1fr) !important",
				gap: "30px !important",
				width: "100% !important",
				"justify-items": "stretch !important",
				"align-items": "start !important",
				"justify-content": "start !important",
				"align-content": "start !important",
			});

			// Add inline style attribute to force it
			const inlineStyle = `
				display: grid !important;
				grid-template-columns: repeat(4, 1fr) !important;
				gap: 30px !important;
				width: 100% !important;
				justify-items: stretch !important;
				align-items: start !important;
				justify-content: start !important;
				align-content: start !important;
			`;
			this.targetGrid.attr("style", inlineStyle);

			// CRITICAL: Track rendered IDs to prevent any duplicates
			const renderedIds = new Set();

			// Add products
			products.forEach((product) => {
				// Double-check: Skip if already rendered
				if (renderedIds.has(product.id)) {
					console.warn(
						`⚠️ Prevented duplicate render of product ${product.id}`,
					);
					return;
				}

				// Create fresh clone
				const freshClone = product.element.cloneNode(true);
				container.appendChild(freshClone);

				// Mark as rendered
				renderedIds.add(product.id);
			});

			console.log(
				`   ✅ Rendered ${renderedIds.size} unique products to DOM`,
			);

			// Animate
			this.animateItems();

			// Show/hide no results
			if (products.length === 0) {
				this.showNoResults();
			} else {
				this.hideNoResults();
			}

			// ✨ CRITICAL FIX: Reinitialize all widget scripts
			this.reinitializeWidgets();
		}

		/**
		 * ✨ NEW: Reinitialize all custom widgets after filtering
		 * This ensures scripts like product_image_hover_gallery work again
		 */
		reinitializeWidgets() {
			console.log("🔄 Reinitializing widgets after filter...");

			// Wait for DOM to be ready
			setTimeout(() => {
				// Reinitialize product image hover galleries
				this.reinitializeImageHoverGalleries();

				// Reinitialize any other custom widgets
				this.reinitializeOtherWidgets();

				console.log("✅ Widgets reinitialized successfully");
			}, 100);
		}

		/**
		 * Reinitialize Product Image Hover Gallery widgets
		 */
		reinitializeImageHoverGalleries() {
			const galleries = this.targetGrid.find(".product-hover-gallery");

			if (galleries.length === 0) return;

			console.log(`   🖼️ Found ${galleries.length} image galleries`);

			galleries.each(function () {
				const $gallery = $(this);
				const $wrapper = $gallery.closest(
					".product-hover-gallery-wrapper",
				);
				const images = $gallery.find(".gallery-image");
				const indicators = $wrapper.find(".gallery-indicator");
				const totalImages = images.length;

				if (totalImages <= 1) return;

				let currentIndex = 0;
				let isHovering = false;
				let cycleInterval;
				const autoCycle = $gallery.data("auto-cycle");
				const cycleSpeed = $gallery.data("cycle-speed") || 800;

				function showImage(index) {
					images.removeClass("active");
					indicators.removeClass("active");
					images.eq(index).addClass("active");
					indicators.eq(index).addClass("active");
					currentIndex = index;
				}

				function nextImage() {
					const nextIndex = (currentIndex + 1) % totalImages;
					showImage(nextIndex);
				}

				function startCycle() {
					if (isHovering) {
						if (autoCycle) {
							nextImage();
							cycleInterval = setInterval(nextImage, cycleSpeed);
						} else {
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

				// Remove old event listeners to prevent duplicates
				$wrapper.off("mouseenter mouseleave");
				indicators.off("click");

				// Attach new event listeners
				$wrapper.on("mouseenter", function () {
					isHovering = true;
					startCycle();
				});

				$wrapper.on("mouseleave", function () {
					isHovering = false;
					stopCycle();
					showImage(0);
				});

				indicators.on("click", function (e) {
					e.preventDefault();
					e.stopPropagation();
					const index = $(this).data("index");
					stopCycle();
					showImage(index);
					if (isHovering && autoCycle) {
						setTimeout(function () {
							cycleInterval = setInterval(nextImage, cycleSpeed);
						}, cycleSpeed);
					}
				});

				console.log(
					`      ✅ Gallery initialized with ${totalImages} images`,
				);
			});
		}

		/**
		 * Reinitialize other custom widgets (add as needed)
		 */
		reinitializeOtherWidgets() {
			// Add to cart buttons
			const $addToCartButtons = this.targetGrid.find(".ajax_add_to_cart");
			if ($addToCartButtons.length > 0) {
				console.log(
					`   🛒 Found ${$addToCartButtons.length} add to cart buttons`,
				);
				// WooCommerce handles this automatically, but you can add custom handlers here
			}

			// Product badges (if they have dynamic functionality)
			const $badges = this.targetGrid.find(".product-badge");
			if ($badges.length > 0) {
				console.log(`   🏷️ Found ${$badges.length} product badges`);
				// Add any custom badge functionality here
			}

			// Any other custom widgets can be reinitialized here
		}

		animateItems() {
			this.targetGrid
				.find(".e-loop-item, .product-loop-item")
				.css({
					opacity: 0,
					transform: "translateY(20px)",
				})
				.each(function (index) {
					$(this)
						.delay(index * 50)
						.animate({ opacity: 1 }, 300, function () {
							$(this).css("transform", "translateY(0)");
						});
				});
		}

		showNoResults() {
			if (this.targetGrid.find(".no-results-message").length === 0) {
				const msg = $(
					'<div class="no-results-message" style="grid-column: 1/-1; text-align: center; padding: 60px 20px; font-size: 16px; color: #666;">No products found matching your criteria.</div>',
				);
				this.targetGrid.append(msg);
			}
		}

		hideNoResults() {
			this.targetGrid.find(".no-results-message").remove();
		}

		/**
		 * RESET: Show ALL unique products (from Map)
		 */
		resetToUniqueProducts() {
			console.log("🔄 RESET: Showing all unique products");

			// Clear form
			this.widget.find(".loop-filter-search").val("");
			this.widget.find(".loop-filter-sort").val("date");
			this.widget.find('input[type="checkbox"]').prop("checked", false);

			// Reset price
			const maxPrice =
				parseInt(
					this.widget.find(".loop-price-max-slider").attr("max"),
				) || 10000;
			this.widget.find(".loop-price-min-slider").val(0);
			this.widget.find(".loop-price-max-slider").val(maxPrice);
			this.updatePriceDisplay();

			// Reset state
			this.currentFilters = {
				search: "",
				sort: "date",
				categories: [],
				tags: [],
				attributes: {},
				minPrice: 0,
				maxPrice: maxPrice,
			};

			// Render ALL unique products from Map
			const allUniqueProducts = Array.from(this.uniqueProducts.values());

			// Sort by original index
			allUniqueProducts.sort((a, b) => a.index - b.index);

			console.log(
				`   📦 Resetting to ${allUniqueProducts.length} unique products`,
			);

			this.renderProducts(allUniqueProducts);

			console.log("✅ Reset complete - showing all unique products");
		}

		setupMobile() {
			if ($(".filter-toggle-btn").length === 0) {
				const toggleBtn = $(
					'<button class="filter-toggle-btn" aria-label="Open Filters">' +
						'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
						'<path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"/>' +
						"</svg></button>",
				);
				$("body").append(toggleBtn);
			}

			if ($(".filter-overlay").length === 0) {
				$("body").append('<div class="filter-overlay"></div>');
			}
		}

		openMobileFilter() {
			this.widget.find(".loop-filter-sidebar").addClass("active");
			$(".filter-overlay").addClass("active").fadeIn(300);
			$("body").css("overflow", "hidden");
		}

		closeMobileFilter() {
			this.widget.find(".loop-filter-sidebar").removeClass("active");
			$(".filter-overlay").removeClass("active").fadeOut(300);
			$("body").css("overflow", "");
		}
	}

	// Initialize
	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/loop_grid_filter.default",
			function ($scope) {
				new LoopGridFilter($scope.find(".loop-grid-filter-widget"));
			},
		);

		setTimeout(function () {
			$(".loop-grid-filter-widget").each(function () {
				if (!$(this).data("filter-initialized")) {
					new LoopGridFilter(this);
					$(this).data("filter-initialized", true);
				}
			});
		}, 1000);
	});

	$(document).ready(function () {
		setTimeout(function () {
			$(".loop-grid-filter-widget").each(function () {
				if (!$(this).data("filter-initialized")) {
					new LoopGridFilter(this);
					$(this).data("filter-initialized", true);
				}
			});
		}, 500);
	});
})(jQuery);
