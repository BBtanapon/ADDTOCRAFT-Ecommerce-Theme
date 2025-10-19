/**
 * Loop Grid Filter JavaScript - COMPLETE FIX
 * Properly filters Elementor Loop Grid with categories, attributes, and maintains styles
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
        this.targetContainer = null;
        this.debounceTimer = null;
        this.originalItems = [];
        this.gridClasses = "";
        this.gridStyles = "";
        this.gridDataAttributes = {};
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
        console.log("🎯 Initializing Loop Grid Filter");
  
        this.findTargetGrid();
  
        if (!this.targetGrid || !this.targetGrid.length) {
          console.warn("⚠️ Target Loop Grid not found");
          return;
        }
  
        console.log("✅ Target grid found:", this.targetGrid);
  
        // Wait for auto-attributes to be ready
        $(document).on("loop-grid-attributes-ready", () => {
          console.log("🎨 Attributes ready, initializing filter");
          this.storeGridProperties();
          this.storeOriginalItems();
          this.initPriceSliders();
          this.bindEvents();
          this.setupMobile();
        });
  
        // Fallback: Initialize after delay if event doesn't fire
        setTimeout(() => {
          if (this.originalItems.length === 0) {
            console.log("⏰ Fallback initialization");
            this.storeGridProperties();
            this.storeOriginalItems();
            this.initPriceSliders();
            this.bindEvents();
            this.setupMobile();
          }
        }, 2000);
      }
  
      findTargetGrid() {
        if (this.targetId) {
          let selectors = [
            ".elementor-element-" + this.targetId + " .elementor-loop-container",
            '[data-id="' + this.targetId + '"] .elementor-loop-container',
            "#" + this.targetId + " .elementor-loop-container",
            "#" + this.targetId,
          ];
  
          for (let selector of selectors) {
            this.targetGrid = $(selector);
            if (this.targetGrid.length) {
              console.log("✅ Found grid with selector:", selector);
              break;
            }
          }
        }
  
        if (!this.targetGrid || !this.targetGrid.length) {
          this.targetGrid = $(".elementor-loop-container").first();
        }
  
        if (this.targetGrid.hasClass("elementor-loop-container")) {
          console.log("✅ Grid container found:", this.targetGrid);
        } else {
          const container = this.targetGrid
            .find(".elementor-loop-container")
            .first();
          if (container.length) {
            this.targetGrid = container;
            console.log("✅ Grid container found (nested):", this.targetGrid);
          }
        }
      }
  
      storeGridProperties() {
        this.gridClasses = this.targetGrid.attr("class") || "";
        this.gridStyles = this.targetGrid.attr("style") || "";
  
        this.gridDataAttributes = {};
        if (this.targetGrid[0] && this.targetGrid[0].attributes) {
          $.each(this.targetGrid[0].attributes, (i, attr) => {
            if (attr.name.startsWith("data-")) {
              this.gridDataAttributes[attr.name] = attr.value;
            }
          });
        }
  
        console.log("📝 Stored grid properties:");
        console.log("   Classes:", this.gridClasses);
        console.log("   Styles:", this.gridStyles);
      }
  
      storeOriginalItems() {
        this.originalItems = [];
        const items = this.targetGrid.find(
          '.e-loop-item, [class*="elementor-post"]'
        );
  
        console.log(`📦 Found ${items.length} items to store`);
  
        items.each((index, item) => {
          const $item = $(item);
          const product = this.extractProductData($item);
  
          this.originalItems.push({
            element: item.cloneNode(true),
            $element: $item,
            data: product,
            html: item.outerHTML,
            index: index,
            classes: item.className,
          });
        });
  
        console.log("✅ Stored", this.originalItems.length, "original items");
  
        if (this.originalItems.length > 0) {
          console.log("📋 Sample product data:", this.originalItems[0].data);
        }
      }
  
      extractProductData($item) {
        const data = {
          id: 0,
          title: "",
          categories: [],
          tags: [],
          attributes: {},
          price: 0,
          regularPrice: 0,
          salePrice: 0,
          isVariable: false,
          minPrice: 0,
        };
  
        // Get product ID - try multiple methods
        data.id =
          $item.data("product-id") ||
          $item.attr("data-product-id") ||
          $item.data("productId") ||
          this.extractIdFromClasses($item.attr("class")) ||
          0;
  
        console.log(`📦 Extracting data for product ID: ${data.id}`);
  
        // Get title
        const $title = $item
          .find(".elementor-heading-title, h2, h3, h4, .product-title")
          .first();
        if ($title.length) {
          data.title = $title.text().trim().toLowerCase();
        }
  
        // Get categories - MUST be term IDs as strings
        const cats = $item.data("categories") || $item.attr("data-categories");
        if (cats) {
          data.categories = String(cats)
            .split(",")
            .map((c) => String(c.trim()));
          console.log(`   Categories: ${data.categories.join(", ")}`);
        }
  
        // Get tags - MUST be term IDs as strings
        const tags = $item.data("tags") || $item.attr("data-tags");
        if (tags) {
          data.tags = String(tags)
            .split(",")
            .map((t) => String(t.trim()));
          console.log(`   Tags: ${data.tags.join(", ")}`);
        }
  
        // Get ALL WooCommerce attributes
        $.each($item[0].dataset, (key, value) => {
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
  
          // Convert camelCase back to snake_case
          if (attrName.startsWith("pa") && attrName.length > 2) {
            if (attrName[2] === attrName[2].toUpperCase()) {
              attrName = attrName.replace(/([A-Z])/g, "_$1").toLowerCase();
            }
          }
  
          if (value) {
            data.attributes[attrName] = String(value)
              .split(",")
              .map((v) => v.trim().toLowerCase());
            console.log(`   Attribute ${attrName}: ${data.attributes[attrName].join(", ")}`);
          }
        });
  
        // Get price data
        const regularPrice = parseFloat(
          $item.data("regular-price") || $item.attr("data-regular-price") || 0
        );
        const salePrice = parseFloat(
          $item.data("sale-price") || $item.attr("data-sale-price") || 0
        );
        const displayPrice = parseFloat(
          $item.data("price") || $item.attr("data-price") || 0
        );
  
        data.regularPrice = regularPrice;
        data.salePrice = salePrice;
  
        if (salePrice > 0) {
          data.price = salePrice;
        } else if (regularPrice > 0) {
          data.price = regularPrice;
        } else {
          data.price = displayPrice;
        }
  
        const minPrice = parseFloat(
          $item.data("min-price") || $item.attr("data-min-price") || 0
        );
        if (minPrice > 0) {
          data.isVariable = true;
          data.minPrice = minPrice;
          data.price = minPrice;
        }
  
        console.log(`   Price: ${data.price}`);
  
        return data;
      }
  
      extractIdFromClasses(classes) {
        if (!classes) return 0;
        const patterns = [
          /e-loop-item-(\d+)/,
          /post-(\d+)/,
          /product-id-(\d+)/,
          /elementor-post-(\d+)/,
        ];
  
        for (let pattern of patterns) {
          const match = classes.match(pattern);
          if (match) return parseInt(match[1]);
        }
        return 0;
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
  
        this.widget.find(".price-min-value").text("฿" + minVal.toLocaleString());
        this.widget.find(".price-max-value").text("฿" + maxVal.toLocaleString());
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
        this.widget.find(".loop-filter-search").on(
          "input",
          this.debounce(() => {
            this.currentFilters.search = this.widget
              .find(".loop-filter-search")
              .val()
              .toLowerCase();
            this.applyFilters();
          }, 500)
        );
  
        this.widget.find(".loop-filter-sort").on("change", () => {
          this.currentFilters.sort = this.widget.find(".loop-filter-sort").val();
          this.applyFilters();
        });
  
        this.widget.on("change", ".loop-filter-category", () => {
          this.updateCheckboxArray("categories", ".loop-filter-category");
          this.applyFilters();
        });
  
        this.widget.on("change", ".loop-filter-tag", () => {
          this.updateCheckboxArray("tags", ".loop-filter-tag");
          this.applyFilters();
        });
  
        this.widget.on("change", ".loop-filter-custom-attribute", () => {
          this.updateCustomAttributes();
          this.applyFilters();
        });
  
        this.widget
          .find(".loop-price-min-slider, .loop-price-max-slider")
          .on("change", () => {
            this.applyFilters();
          });
  
        this.widget.find(".loop-price-min-input").on("change", () => {
          const val =
            parseInt(this.widget.find(".loop-price-min-input").val()) || 0;
          this.widget.find(".loop-price-min-slider").val(val);
          this.updatePriceDisplay();
          this.applyFilters();
        });
  
        this.widget.find(".loop-price-max-input").on("change", () => {
          const val =
            parseInt(this.widget.find(".loop-price-max-input").val()) || 10000;
          this.widget.find(".loop-price-max-slider").val(val);
          this.updatePriceDisplay();
          this.applyFilters();
        });
  
        this.widget.find(".loop-filter-reset").on("click", () => {
          this.resetFilters();
        });
  
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
        
        console.log(`🔍 Updated ${filterKey}:`, values);
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
        
        console.log("🎨 Updated attributes:", customAttrs);
      }
  
      debounce(func, wait) {
        return (...args) => {
          clearTimeout(this.debounceTimer);
          this.debounceTimer = setTimeout(() => func.apply(this, args), wait);
        };
      }
  
      applyFilters() {
        console.log("🔍 Applying filters:", this.currentFilters);
  
        let visibleCount = 0;
        let matchedItems = [];
  
        // Filter items
        this.originalItems.forEach((item) => {
          if (this.itemMatchesFilters(item.data)) {
            matchedItems.push(item);
            visibleCount++;
          }
        });
  
        console.log(`✅ Found ${visibleCount} matching items`);
  
        // Sort if needed
        if (this.currentFilters.sort !== "date") {
          matchedItems = this.sortItems(matchedItems);
        }
  
        // Rebuild grid with filtered items
        this.rebuildGrid(matchedItems);
  
        // Show/hide no results message
        this.toggleNoResultsMessage(visibleCount === 0);
      }
  
      itemMatchesFilters(data) {
        // Search filter
        if (this.currentFilters.search) {
          if (!data.title.includes(this.currentFilters.search)) {
            console.log(`   ❌ Search mismatch: "${data.title}"`);
            return false;
          }
        }
  
        // Categories filter
        if (this.currentFilters.categories.length > 0) {
          const hasCategory = this.currentFilters.categories.some((cat) =>
            data.categories.includes(String(cat))
          );
          if (!hasCategory) {
            console.log(`   ❌ Category mismatch. Product cats: [${data.categories}], Filter cats: [${this.currentFilters.categories}]`);
            return false;
          }
        }
  
        // Attributes filter
        if (Object.keys(this.currentFilters.attributes).length > 0) {
          for (const [filterAttrName, filterAttrValues] of Object.entries(
            this.currentFilters.attributes
          )) {
            if (
              !data.attributes[filterAttrName] ||
              data.attributes[filterAttrName].length === 0
            ) {
              console.log(`   ❌ Attribute "${filterAttrName}" not found in product`);
              return false;
            }
  
            const hasMatch = filterAttrValues.some((filterValue) =>
              data.attributes[filterAttrName].includes(filterValue.toLowerCase())
            );
  
            if (!hasMatch) {
              console.log(`   ❌ Attribute "${filterAttrName}" value mismatch. Product: [${data.attributes[filterAttrName]}], Filter: [${filterAttrValues}]`);
              return false;
            }
          }
        }
  
        // Tags filter
        if (this.currentFilters.tags.length > 0) {
          const hasTag = this.currentFilters.tags.some((tag) =>
            data.tags.includes(String(tag))
          );
          if (!hasTag) {
            console.log(`   ❌ Tag mismatch`);
            return false;
          }
        }
  
        // Price filter
        if (data.price) {
          if (
            data.price < this.currentFilters.minPrice ||
            data.price > this.currentFilters.maxPrice
          ) {
            console.log(`   ❌ Price mismatch: ${data.price}`);
            return false;
          }
        }
  
        console.log(`   ✅ Product ${data.id} matches all filters`);
        return true;
      }
  
      sortItems(items) {
        const sortType = this.currentFilters.sort;
        const itemsToSort = [...items];
  
        itemsToSort.sort((a, b) => {
          switch (sortType) {
            case "title":
              return (a.data.title || "").localeCompare(b.data.title || "");
            case "price":
              return (a.data.price || 0) - (b.data.price || 0);
            case "price-desc":
              return (b.data.price || 0) - (a.data.price || 0);
            case "date":
            default:
              return a.index - b.index;
          }
        });
  
        return itemsToSort;
      }
  
      rebuildGrid(items) {
        console.log("🔨 Rebuilding grid with", items.length, "items");
  
        // Clear grid
        this.targetGrid.empty();
  
        // Restore grid properties
        this.targetGrid.attr("class", this.gridClasses);
        if (this.gridStyles) {
          this.targetGrid.attr("style", this.gridStyles);
        }
        $.each(this.gridDataAttributes, (key, value) => {
          this.targetGrid.attr(key, value);
        });
  
        // Add filtered items
        items.forEach((item) => {
          const $clonedElement = $(item.element).clone(true);
          this.targetGrid.append($clonedElement);
        });
  
        // Animate items
        this.animateItems();
  
        console.log("✅ Grid rebuilt successfully");
      }
  
      animateItems() {
        this.targetGrid
          .find('.e-loop-item, [class*="elementor-post"]')
          .css({
            opacity: 0,
            transform: "translateY(20px)",
          })
          .each(function (index) {
            $(this)
              .delay(index * 50)
              .animate(
                {
                  opacity: 1,
                },
                300,
                function () {
                  $(this).css("transform", "translateY(0)");
                }
              );
          });
      }
  
      toggleNoResultsMessage(show) {
        let $noResults = this.targetGrid.find(".no-results-message");
  
        if (show) {
          if ($noResults.length === 0) {
            $noResults = $(
              '<div class="no-results-message" style="grid-column: 1/-1; text-align: center; padding: 60px 20px; font-size: 16px; color: #666;">No products found matching your criteria.</div>'
            );
            this.targetGrid.append($noResults);
          }
          $noResults.fadeIn(300);
        } else {
          $noResults.fadeOut(300, function () {
            $(this).remove();
          });
        }
      }
  
      resetFilters() {
        console.log("🔄 Resetting filters");
  
        this.widget.find(".loop-filter-search").val("");
        this.widget.find(".loop-filter-sort").val("date");
        this.widget.find('input[type="checkbox"]').prop("checked", false);
  
        const maxPrice =
          parseInt(this.widget.find(".loop-price-max-slider").attr("max")) ||
          10000;
        this.widget.find(".loop-price-min-slider").val(0);
        this.widget.find(".loop-price-max-slider").val(maxPrice);
        this.updatePriceDisplay();
  
        this.currentFilters = {
          search: "",
          sort: "date",
          categories: [],
          tags: [],
          attributes: {},
          minPrice: 0,
          maxPrice: maxPrice,
        };
  
        this.rebuildGrid(this.originalItems);
      }
  
      setupMobile() {
        if ($(".filter-toggle-btn").length === 0) {
          const toggleBtn = $(
            '<button class="filter-toggle-btn" aria-label="Open Filters">' +
              '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
              '<path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"/>' +
              "</svg></button>"
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
  
    // Initialize on Elementor frontend init
    $(window).on("elementor/frontend/init", function () {
      elementorFrontend.hooks.addAction(
        "frontend/element_ready/loop_grid_filter.default",
        function ($scope) {
          new LoopGridFilter($scope.find(".loop-grid-filter-widget"));
        }
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
  
    // Also initialize on document ready as fallback
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