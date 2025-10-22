/**
 * Loop Grid Pagination - FIXED Load More Function
 * Complete working version for Elementor Loop Grid
 *
 * @package HelloElementorChild
 */

(function ($) {
	"use strict";

	class LoopGridPaginationFixed {
		constructor(wrapper) {
			this.$wrapper = $(wrapper);
			this.widgetId = this.$wrapper.data("widget-id");
			this.targetId = this.$wrapper.data("target");
			this.paginationType = this.$wrapper.data("pagination-type");
			this.currentPage =
				parseInt(this.$wrapper.data("current-page")) || 1;
			this.maxPages = parseInt(this.$wrapper.data("max-pages")) || 1;

			// Decode base64 query args and settings
			try {
				this.queryArgs = JSON.parse(
					atob(this.$wrapper.data("query") || ""),
				);
				this.settings = JSON.parse(
					atob(this.$wrapper.data("settings") || ""),
				);
			} catch (e) {
				console.error("Error decoding pagination data:", e);
				this.queryArgs = {};
				this.settings = {};
			}

			this.$grid = this.$wrapper.find(".custom-product-loop-grid");
			this.isLoading = false;

			console.log("🔄 Pagination Init:", {
				type: this.paginationType,
				currentPage: this.currentPage,
				maxPages: this.maxPages,
				gridFound: this.$grid.length > 0,
			});

			this.init();
		}

		init() {
			if (this.paginationType === "none" || this.maxPages <= 1) {
				console.log("⏭️ Pagination disabled or only 1 page");
				return;
			}

			switch (this.paginationType) {
				case "load_more":
					this.initLoadMore();
					break;
				case "infinite":
					this.initInfiniteScroll();
					break;
				case "numbers":
					console.log("📄 Page numbers - no JS needed");
					break;
			}
		}

		initLoadMore() {
			console.log("📦 Initializing Load More button");

			const $btn = this.$wrapper.find(".loop-load-more-btn");

			if ($btn.length === 0) {
				console.error("❌ Load More button not found");
				return;
			}

			console.log("✅ Load More button found and bound");

			// Remove any existing click handlers
			$btn.off("click");

			$btn.on("click", (e) => {
				e.preventDefault();
				e.stopPropagation();

				if (this.isLoading) {
					console.log("⏳ Already loading, please wait...");
					return;
				}

				const nextPage = this.currentPage + 1;
				console.log(`📄 Loading page ${nextPage}/${this.maxPages}`);

				if (nextPage > this.maxPages) {
					console.log("✅ All products loaded");
					$btn.hide();
					this.showNoMoreMessage();
					return;
				}

				this.loadMoreProducts(nextPage, $btn);
			});

			// Update button data attribute
			$btn.data("page", this.currentPage);
			console.log("✅ Load More initialized successfully");
		}

		initInfiniteScroll() {
			console.log("♾️ Initializing Infinite Scroll");

			const $trigger = this.$wrapper.find(
				".loop-infinite-scroll-trigger",
			);

			if ($trigger.length === 0) {
				console.warn("⚠️ Infinite scroll trigger not found");
				return;
			}

			const threshold = $trigger.data("threshold") || 300;

			const observer = new IntersectionObserver(
				(entries) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting && !this.isLoading) {
							const nextPage = this.currentPage + 1;

							if (nextPage > this.maxPages) {
								console.log(
									"✅ All products loaded - stopping infinite scroll",
								);
								this.showNoMoreMessage();
								observer.disconnect();
								return;
							}

							console.log(
								`📄 Infinite scroll loading page ${nextPage}`,
							);
							this.loadMoreProducts(nextPage, $trigger);
						}
					});
				},
				{
					rootMargin: `${threshold}px`,
				},
			);

			observer.observe($trigger[0]);
			console.log("✅ Infinite scroll initialized");
		}

		loadMoreProducts(page, $element) {
			console.log(`🔄 Loading products for page ${page}...`);

			this.isLoading = true;
			this.showLoadingMessage();

			// Hide load more button while loading
			if (this.paginationType === "load_more") {
				this.$wrapper.find(".loop-load-more-btn").hide();
			}

			$.ajax({
				url: loopGridPaginationData.ajaxUrl,
				type: "POST",
				data: {
					action: "load_more_products",
					nonce: loopGridPaginationData.nonce,
					page: page,
					query_args: JSON.stringify(this.queryArgs),
					settings: JSON.stringify(this.settings),
					widget_id: this.widgetId,
				},
				success: (response) => {
					console.log("✅ AJAX success:", response);

					if (response.success && response.data.html) {
						this.appendProducts(response.data.html);
						this.currentPage = page;
						$element.data("page", page);

						// Check if there are more pages
						if (page >= this.maxPages) {
							console.log("📊 Reached max pages");
							this.showNoMoreMessage();
							if (this.paginationType === "load_more") {
								this.$wrapper
									.find(".loop-load-more-btn")
									.hide();
							}
						} else {
							// Show load more button again
							if (this.paginationType === "load_more") {
								this.$wrapper
									.find(".loop-load-more-btn")
									.show();
							}
						}
					} else {
						console.error(
							"❌ Failed to load products:",
							response.data,
						);
						this.showNoMoreMessage();
					}
				},
				error: (xhr, status, error) => {
					console.error("❌ AJAX error:", error);
					console.error("Status:", status);
					console.error("Response:", xhr.responseText);
					alert("Error loading products. Please try again.");
					if (this.paginationType === "load_more") {
						this.$wrapper.find(".loop-load-more-btn").show();
					}
				},
				complete: () => {
					this.isLoading = false;
					this.hideLoadingMessage();
				},
			});
		}

		appendProducts(html) {
			console.log("🔨 Appending products to grid");

			const $newProducts = $(html);

			// Apply auto-attributes to new products
			if (window.loopGridProductsData) {
				$newProducts.each((index, item) => {
					const $item = $(item);
					const productId =
						$item.data("product-id") ||
						$item.attr("data-product-id");

					if (productId && window.loopGridProductsData[productId]) {
						const data = window.loopGridProductsData[productId];
						$item.data("product-id", data.id);
						$item.data("title", data.title || "");
						$item.data("price", data.price || "0");
						$item.data(
							"regular-price",
							data.regular_price || data.price || "0",
						);
						$item.data("sale-price", data.sale_price || "0");

						if (data.categories) {
							$item.data("categories", data.categories.join(","));
						}
						if (data.tags) {
							$item.data("tags", data.tags.join(","));
						}
					}
				});
			}

			// Append to grid with animation
			$newProducts.css({
				opacity: 0,
				transform: "translateY(20px)",
			});

			this.$grid.append($newProducts);

			// Animate in
			$newProducts.each(function (index) {
				$(this)
					.delay(index * 50)
					.animate(
						{
							opacity: 1,
						},
						300,
						function () {
							$(this).css("transform", "translateY(0)");
						},
					);
			});

			console.log(`✅ Appended ${$newProducts.length} products`);

			// Trigger custom event for third-party scripts
			$(document).trigger("loop-grid-products-loaded", [$newProducts]);
		}

		showLoadingMessage() {
			const $loader = this.$wrapper.find(".loop-loading-message");
			if ($loader.length) {
				$loader.stop(true, true).fadeIn(300);
			}
		}

		hideLoadingMessage() {
			const $loader = this.$wrapper.find(".loop-loading-message");
			if ($loader.length) {
				$loader.stop(true, true).fadeOut(300);
			}
		}

		showNoMoreMessage() {
			const $noMore = this.$wrapper.find(".loop-no-more-message");
			if ($noMore.length) {
				$noMore.stop(true, true).fadeIn(300);
			}
		}
	}

	// Initialize on document ready
	$(document).ready(function () {
		console.log("📦 Checking for pagination wrappers...");

		const $wrappers = $(".custom-product-loop-wrapper");
		console.log(`Found ${$wrappers.length} wrapper(s)`);

		$wrappers.each(function () {
			const $wrapper = $(this);
			const paginationType = $wrapper.data("pagination-type");

			if (paginationType && paginationType !== "none") {
				console.log(`🎯 Initializing ${paginationType} for widget`);
				new LoopGridPaginationFixed(this);
			}
		});
	});

	// Initialize on Elementor frontend
	$(window).on("elementor/frontend/init", function () {
		console.log("🎨 Elementor frontend loaded, reinitializing pagination");

		if (typeof elementorFrontend !== "undefined") {
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/custom_product_loop_grid.default",
				function ($scope) {
					console.log("🔄 Custom product loop grid detected");

					const $wrapper = $scope.find(
						".custom-product-loop-wrapper",
					);
					const paginationType = $wrapper.data("pagination-type");

					if (paginationType && paginationType !== "none") {
						new LoopGridPaginationFixed($wrapper[0]);
					}
				},
			);
		}
	});

	// Global function for debugging
	window.debugPagination = function (widgetId) {
		const $wrapper = widgetId
			? $(`.custom-product-loop-wrapper[data-widget-id="${widgetId}"]`)
			: $(".custom-product-loop-wrapper").first();

		if ($wrapper.length === 0) {
			console.error("Wrapper not found");
			return;
		}

		console.group("📊 Pagination Debug Info");
		console.log("Widget ID:", $wrapper.data("widget-id"));
		console.log("Pagination Type:", $wrapper.data("pagination-type"));
		console.log("Current Page:", $wrapper.data("current-page"));
		console.log("Max Pages:", $wrapper.data("max-pages"));
		console.log("Button:", $wrapper.find(".loop-load-more-btn"));
		console.log("Grid:", $wrapper.find(".custom-product-loop-grid"));
		console.log("Loading Message:", $wrapper.find(".loop-loading-message"));
		console.log("No More Message:", $wrapper.find(".loop-no-more-message"));
		console.groupEnd();
	};

	console.log(
		"%c💡 Load More Pagination Ready!",
		"color: #4CAF50; font-weight: bold; font-size: 14px",
	);
	console.log("Type: debugPagination() to debug the first pagination widget");
	console.log(
		"Type: debugPagination('widget-id') to debug a specific widget",
	);
})(jQuery);
