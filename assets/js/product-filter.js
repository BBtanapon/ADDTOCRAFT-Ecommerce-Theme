/**
 * Product Filter JavaScript
 * 
 * @package HelloElementorChild
 */

(function($) {
    'use strict';

    class ProductFilter {
        constructor() {
            this.minPrice = 0;
            this.maxPrice = 0;
            this.currentMin = 0;
            this.currentMax = 0;
            this.debounceTimer = null;
            
            this.init();
        }

        init() {
            console.log('Product filter initialized');
            
            // Get URL parameters
            this.urlSearch = $('.custom-product-filter-wrapper').data('url-search') || '';
            this.urlProps = $('.custom-product-filter-wrapper').data('url-props') || '';
            
            // Parse initial props if present
            if (this.urlProps) {
                this.initialProps = this.parseSearchProps(this.urlProps);
            }
            
            // Initialize price slider
            this.initPriceSlider();
            
            // Bind events
            this.bindEvents();
            
            // Trigger initial filter if URL params exist
            if (this.urlSearch || this.urlProps) {
                this.filterProducts();
            }
            
            // Initial product card animation
            this.animateProductCards();
        }

        /**
         * Parse search props from URL
         */
        parseSearchProps(propsString) {
            const props = {};
            const parts = propsString.split('-');
            
            if (parts.length > 0) {
                props.categoryId = parts[0];
            }
            if (parts.length > 1) {
                props.page = parts[1];
            }
            
            return props;
        }

        /**
         * Initialize price slider
         */
        initPriceSlider() {
            this.maxPrice = parseInt($('#price-max-value').data('max')) || 10000;
            this.minPrice = 0;
            this.currentMin = this.minPrice;
            this.currentMax = this.maxPrice;
            
            $('#price-min-slider').attr('min', this.minPrice).attr('max', this.maxPrice).val(this.minPrice);
            $('#price-max-slider').attr('min', this.minPrice).attr('max', this.maxPrice).val(this.maxPrice);
            
            this.updatePriceDisplay();
        }

        /**
         * Update price display
         */
        updatePriceDisplay() {
            let minVal = parseInt($('#price-min-slider').val());
            let maxVal = parseInt($('#price-max-slider').val());
            
            // Ensure sliders don't cross
            if (minVal > maxVal - 100) {
                minVal = maxVal - 100;
                $('#price-min-slider').val(minVal);
            }
            
            this.currentMin = minVal;
            this.currentMax = maxVal;
            
            // Update display values
            $('#price-min-value').text('฿' + minVal.toLocaleString());
            $('#price-max-value').text('฿' + maxVal.toLocaleString());
            $('#price-min-input').val(minVal);
            $('#price-max-input').val(maxVal);
            
            // Update slider track
            const percentMin = ((minVal - this.minPrice) / (this.maxPrice - this.minPrice)) * 100;
            const percentMax = ((maxVal - this.minPrice) / (this.maxPrice - this.minPrice)) * 100;
            
            $('.price-slider-track').css({
                'left': percentMin + '%',
                'width': (percentMax - percentMin) + '%'
            });
        }

        /**
         * Debounce function
         */
        debounce(func, wait) {
            return (...args) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => func.apply(this, args), wait);
            };
        }

        /**
         * Bind all event handlers
         */
        bindEvents() {
            // Price sliders
            $('#price-min-slider, #price-max-slider').on('input', () => {
                this.updatePriceDisplay();
            });
            
            $('#price-min-slider, #price-max-slider').on('change', () => {
                this.filterProducts();
            });
            
            // Price inputs
            $('#price-min-input').on('change', () => {
                let val = parseInt($('#price-min-input').val()) || this.minPrice;
                val = Math.max(this.minPrice, Math.min(val, this.currentMax - 100));
                $('#price-min-slider').val(val);
                this.updatePriceDisplay();
                this.filterProducts();
            });
            
            $('#price-max-input').on('change', () => {
                let val = parseInt($('#price-max-input').val()) || this.maxPrice;
                val = Math.min(this.maxPrice, Math.max(val, this.currentMin + 100));
                $('#price-max-slider').val(val);
                this.updatePriceDisplay();
                this.filterProducts();
            });
            
            // Sort dropdown
            $('#filter-sort').on('change', () => {
                this.filterProducts();
            });
            
            // Search input with debounce
            $('#filter-search').on('input', this.debounce(() => {
                this.filterProducts();
            }, 500));
            
            // Filter checkboxes
            $(document).on('change', '.filter-category, .filter-attribute, .filter-tag, .filter-custom-attribute', () => {
                this.filterProducts();
            });
            
            // Reset filters
            $('#reset-filters').on('click', () => {
                this.resetFilters();
            });
            
            // Mobile filter toggle
            $('.filter-toggle-btn').on('click', () => {
                this.openMobileFilter();
            });
            
            $('.filter-close-btn, .filter-overlay').on('click', () => {
                this.closeMobileFilter();
            });
            
            // AJAX Add to Cart
            $(document).on('click', '.ajax_add_to_cart', (e) => {
                this.handleAddToCart(e);
            });
        }

        /**
         * Get filter values
         */
        getFilterValues() {
            const categories = [];
            $('.filter-category:checked').each(function() {
                categories.push($(this).val());
            });
            
            const attributes = [];
            $('.filter-attribute:checked').each(function() {
                attributes.push($(this).val());
            });
            
            const customAttributes = [];
            $('.filter-custom-attribute:checked').each(function() {
                customAttributes.push($(this).val());
            });
            
            const tags = [];
            $('.filter-tag:checked').each(function() {
                tags.push($(this).val());
            });
            
            const sort = $('#filter-sort').val();
            const searchTerm = $('#filter-search').val();
            
            let orderby = 'date';
            let order = 'DESC';
            
            switch(sort) {
                case 'popularity':
                    orderby = 'popularity';
                    break;
                case 'price':
                    orderby = 'meta_value_num';
                    order = 'ASC';
                    break;
                case 'price-desc':
                    orderby = 'meta_value_num';
                    order = 'DESC';
                    break;
                default:
                    orderby = 'date';
                    order = 'DESC';
            }
            
            return {
                categories: categories.join(','),
                attributes: attributes.join(','),
                custom_attributes: customAttributes.join(','),
                tags: tags.join(','),
                orderby: orderby,
                order: order,
                min_price: $('#price-min-slider').val(),
                max_price: $('#price-max-slider').val(),
                search: searchTerm
            };
        }

        /**
         * Update URL with current filters
         */
        updateURL(searchTerm) {
            const url = new URL(window.location);
            
            if (searchTerm) {
                url.searchParams.set('s', searchTerm);
            } else {
                url.searchParams.delete('s');
            }
            
            window.history.pushState({}, '', url);
        }

        /**
         * Filter products via AJAX
         */
        filterProducts() {
            const filterValues = this.getFilterValues();
            
            // Update URL
            this.updateURL(filterValues.search);
            
            // Show loading overlay
            $('.loading-overlay').fadeIn(200);
            
            $.ajax({
                url: productFilterData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'filter_products',
                    nonce: productFilterData.nonce,
                    ...filterValues
                },
                success: (response) => {
                    $('.products-grid').html(response);
                    this.animateProductCards();
                    $('.loading-overlay').fadeOut(200);
                },
                error: () => {
                    $('.loading-overlay').fadeOut(200);
                    alert('Error loading products. Please try again.');
                }
            });
        }

        /**
         * Reset all filters
         */
        resetFilters() {
            $('.filter-category, .filter-attribute, .filter-tag, .filter-custom-attribute').prop('checked', false);
            $('#filter-sort').val('date');
            $('#filter-search').val('');
            $('#price-min-slider').val(this.minPrice);
            $('#price-max-slider').val(this.maxPrice);
            this.updatePriceDisplay();
            
            // Update URL - remove search params
            const url = new URL(window.location);
            url.searchParams.delete('s');
            url.searchParams.delete('e_search_props');
            window.history.pushState({}, '', url);
            
            this.filterProducts();
        }

        /**
         * Open mobile filter
         */
        openMobileFilter() {
            console.log('Opening mobile filter');
            $('.filter-sidebar').addClass('active');
            $('.filter-overlay').addClass('active').fadeIn(300);
            $('body').css('overflow', 'hidden');
        }

        /**
         * Close mobile filter
         */
        closeMobileFilter() {
            console.log('Closing mobile filter');
            $('.filter-sidebar').removeClass('active');
            $('.filter-overlay').removeClass('active').fadeOut(300);
            $('body').css('overflow', '');
        }

        /**
         * Animate product cards
         */
        animateProductCards() {
            $('.product-card').each(function(index) {
                $(this).css({
                    opacity: 0,
                    transform: 'translateY(20px)'
                }).delay(index * 50).animate({
                    opacity: 1
                }, 400, function() {
                    $(this).css('transform', 'translateY(0)');
                });
            });
        }

        /**
         * Handle AJAX Add to Cart
         */
        handleAddToCart(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const productId = $button.data('product_id');
            const originalText = $button.text();
            
            // Change button state
            $button.removeClass('added').addClass('loading');
            $button.text('ADDING...');
            
            // AJAX request
            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params.ajax_url,
                data: {
                    action: 'woocommerce_ajax_add_to_cart',
                    product_id: productId,
                    quantity: 1
                },
                success: (response) => {
                    if (response.error && response.product_url) {
                        window.location = response.product_url;
                        return;
                    }
                    
                    // Update button state
                    $button.removeClass('loading').addClass('added');
                    $button.text('ADDED!');
                    
                    // Trigger WooCommerce fragments refresh
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        $button.removeClass('added');
                        $button.text(originalText);
                    }, 2000);
                },
                error: () => {
                    $button.removeClass('loading');
                    $button.text(originalText);
                    alert('Error adding product to cart. Please try again.');
                }
            });
        }
    }

    // Initialize on document ready
    $(document).ready(function() {
        new ProductFilter();
    });

})(jQuery);