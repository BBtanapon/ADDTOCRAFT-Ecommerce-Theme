/**
 * Loop Grid Pagination - Load More, Infinite Scroll, Page Numbers
 *
 * @package HelloElementorChild
 */

(function ($) {
	"use strict";

	class LoopGridPagination {
		constructor(wrapper) {
			this.$wrapper = $(wrapper);
			this.widgetId = this.$wrapper.data("widget-id");
			this.paginationType = this.$wrapper.data("pagination-type");
			this.maxPages = this.$wrapper.data("max-pages");
			this.currentPage = this.$wrapper.data("current-page");
			this.queryArgs = JSON.parse(
				atob(this.$wrapper.data("query") || ""),
			);
			this.settings = JSON.parse(
				atob(this.$wrapper.data("settings") || ""),
			);
			this.$grid = this.$wrapper.find(".custom-product-loop-grid");
			this.isLoading = false;

			this.init();
		}

		init() {
			console.log("🔄 Initializing Loop Grid Pagination");
			console.log("   Type:", this.paginationType);
			console.log("   Current Page:", this.currentPage);
			console.log("   Max Pages:", this.maxPages);

			switch (this.paginationType) {
				case "load_more":
					this.initLoadMore();
					break;
				case "infinite":
					this.initInfiniteScroll();
					break;
				case "numbers":
					this.initPageNumbers();
					break;
			}
		}

		initLoadMore() {
			const $btn = this.$wrapper.find(".loop-load-more-btn");

			$btn.on("click", () => {
				if (this.isLoading) return;

				const nextPage = parseInt($btn.data("page")) + 1;

				if (nextPage > this.maxPages) {
					this.showNoMoreMessage();
					$btn.hide();
					return;
				}

				this.loadMoreProducts(nextPage, $btn);
			});
		}

		initInfiniteScroll() {
			const $trigger = this.$wrapper.find(
				".loop-infinite-scroll-trigger",
			);
			const threshold = $trigger.data("threshold") || 300;

			let observer = new IntersectionObserver(
				(entries) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting && !this.isLoading) {
							const nextPage =
								parseInt($trigger.data("page")) + 1;

							if (nextPage > this.maxPages) {
								this.showNoMoreMessage();
								observer.disconnect();
								return;
							}

							this.loadMoreProducts(nextPage, $trigger);
						}
					});
				},
				{
					rootMargin: `${threshold}px`,
				},
			);

			observer.observe($trigger[0]);
		}

		initPageNumbers() {
			// Page numbers use standard WordPress pagination
			// No special JS needed - just regular page links
			console.log("📄 Page numbers navigation initialized");
		}

		loadMoreProducts(page, $element) {
			console.log("📦 Loading page:", page);

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
					query_args: this.queryArgs,
					settings: this.settings,
					widget_id: this.widgetId,
				},
				success: (response) => {
					if (response.success && response.data.html) {
						this.appendProducts(response.data.html);
						$element.data("page", page);

						// Check if there are more pages
						if (page >= this.maxPages) {
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
						console.error("Failed to load products");
						this.showNoMoreMessage();
					}
				},
				error: (xhr, status, error) => {
					console.error("AJAX error:", error);
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
			const $newProducts = $(html);

			// Apply auto-attributes to new products
			if (window.loopGridProductsData) {
				$newProducts.each((index, item) => {
					const productId = this.getProductId($(item));
					if (productId && window.loopGridProductsData[productId]) {
						this.applyDataAttributes(
							item,
							window.loopGridProductsData[productId],
						);
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

			console.log("✅ Products appended successfully");
		}

		getProductId($element) {
			if ($element.data("product-id")) {
				return $element.data("product-id");
			}

			const classes = $element.attr("class");
			const patterns = [
				/e-loop-item-(\d+)/,
				/post-(\d+)/,
				/product-id-(\d+)/,
			];

			for (const pattern of patterns) {
				const match = classes.match(pattern);
				if (match) return match[1];
			}

			return null;
		}

		applyDataAttributes(element, data) {
			element.dataset.productId = data.id;
			element.dataset.title = data.title || "";
			element.dataset.price = data.price || "0";
			element.dataset.regularPrice =
				data.regular_price || data.price || "0";
			element.dataset.salePrice = data.sale_price || "0";

			if (data.min_price) {
				element.dataset.minPrice = data.min_price;
			}
			if (data.max_price) {
				element.dataset.maxPrice = data.max_price;
			}

			if (data.categories && data.categories.length > 0) {
				element.dataset.categories = data.categories.join(",");
			}

			if (data.tags && data.tags.length > 0) {
				element.dataset.tags = data.tags.join(",");
			}

			if (data.attributes) {
				for (const [attrName, attrValues] of Object.entries(
					data.attributes,
				)) {
					const valueString = Array.isArray(attrValues)
						? attrValues.join(",")
						: attrValues;
					const camelCaseAttr = this.toCamelCase(attrName);
					element.dataset[camelCaseAttr] = valueString;
				}
			}
		}

		toCamelCase(str) {
			return str.replace(/[-_]([a-z])/g, (g) => g[1].toUpperCase());
		}

		showLoadingMessage() {
			this.$wrapper.find(".loop-loading-message").fadeIn(300);
		}

		hideLoadingMessage() {
			this.$wrapper.find(".loop-loading-message").fadeOut(300);
		}

		showNoMoreMessage() {
			this.$wrapper.find(".loop-no-more-message").fadeIn(300);
		}
	}

	// Initialize on document ready
	$(document).ready(function () {
		$(".custom-product-loop-wrapper").each(function () {
			const paginationType = $(this).data("pagination-type");
			if (paginationType && paginationType !== "none") {
				new LoopGridPagination(this);
			}
		});
	});

	// Initialize on Elementor frontend
	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/custom_product_loop_grid.default",
			function ($scope) {
				const $wrapper = $scope.find(".custom-product-loop-wrapper");
				const paginationType = $wrapper.data("pagination-type");
				if (paginationType && paginationType !== "none") {
					new LoopGridPagination($wrapper[0]);
				}
			},
		);
	});
})(jQuery);
