jQuery(document).ready(function($) {
    
    // Handle Filter Form Submission
    $('#product-filter-form').on('submit', function(e) {
        e.preventDefault();
        applyFilters();
    });
    
    // Handle Reset Button
    $('.reset-btn').on('click', function(e) {
        e.preventDefault();
        $('#product-filter-form')[0].reset();
        applyFilters();
    });
    
    // Optional: Apply filters on checkbox change (real-time filtering)
    $('#product-filter-form input[type="checkbox"]').on('change', function() {
        applyFilters();
    });
    
    // Optional: Apply filters on select change
    $('.sort-select').on('change', function() {
        applyFilters();
    });
    
    function applyFilters() {
        var formData = $('#product-filter-form').serialize();
        
        // Show loader
        $('#filter-loader').fadeIn();
        $('.filterable-product').css('opacity', '0.5');
        
        $.ajax({
            url: filterAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_products',
                nonce: filterAjax.nonce,
                category: getCheckboxValues('category[]'),
                min_price: $('input[name="min_price"]').val(),
                max_price: $('input[name="max_price"]').val(),
                orderby: $('select[name="orderby"]').val(),
                // Add attribute filters dynamically
                ...getAttributeFilters()
            },
            success: function(response) {
                if (response.success) {
                    filterProducts(response.data.product_ids);
                    
                    // Update results count
                    updateResultsCount(response.data.found_posts);
                }
            },
            error: function(xhr, status, error) {
                console.error('Filter error:', error);
            },
            complete: function() {
                // Hide loader
                $('#filter-loader').fadeOut();
            }
        });
    }
    
    function filterProducts(productIds) {
        var $products = $('.filterable-product');
        
        if (productIds.length === 0) {
            // No products found
            $products.fadeOut(300);
            showNoResultsMessage();
            return;
        }
        
        // Hide no results message
        hideNoResultsMessage();
        
        $products.each(function() {
            var $product = $(this);
            var productId = getProductId($product);
            
            if (productIds.includes(productId)) {
                // Show matching products with animation
                $product.fadeIn(300).css('opacity', '1');
            } else {
                // Hide non-matching products
                $product.fadeOut(300);
            }
        });
        
        // Trigger Elementor animation if exists
        if (typeof elementorFrontend !== 'undefined') {
            elementorFrontend.elementsHandler.runReadyTrigger($products);
        }
    }
    
    function getProductId($element) {
        var classes = $element.attr('class').split(' ');
        for (var i = 0; i < classes.length; i++) {
            if (classes[i].indexOf('product-id-') === 0) {
                return parseInt(classes[i].replace('product-id-', ''));
            }
        }
        return 0;
    }
    
    function getCheckboxValues(name) {
        var values = [];
        $('input[name="' + name + '"]:checked').each(function() {
            values.push($(this).val());
        });
        return values;
    }
    
    function getAttributeFilters() {
        var attributes = {};
        
        // Find all attribute inputs (inputs with name containing 'pa_')
        $('input[type="checkbox"][name*="pa_"]').each(function() {
            var name = $(this).attr('name');
            
            if ($(this).is(':checked')) {
                if (!attributes[name]) {
                    attributes[name] = [];
                }
                attributes[name].push($(this).val());
            }
        });
        
        return attributes;
    }
    
    function updateResultsCount(count) {
        var $countElement = $('.filter-results-count');
        
        if ($countElement.length === 0) {
            // Create count element if it doesn't exist
            $countElement = $('<div class="filter-results-count"></div>');
            $('.custom-product-filter').after($countElement);
        }
        
        $countElement.html('<p>' + count + ' product(s) found</p>').fadeIn();
    }
    
    function showNoResultsMessage() {
        var $noResults = $('.no-results-message');
        
        if ($noResults.length === 0) {
            $noResults = $('<div class="no-results-message"><p>No products found matching your criteria.</p></div>');
            $('.filterable-product').first().parent().append($noResults);
        }
        
        $noResults.fadeIn(300);
    }
    
    function hideNoResultsMessage() {
        $('.no-results-message').fadeOut(300);
    }
    
    // Price range validation
    $('input[name="min_price"], input[name="max_price"]').on('change', function() {
        var minPrice = parseFloat($('input[name="min_price"]').val()) || 0;
        var maxPrice = parseFloat($('input[name="max_price"]').val()) || 999999;
        
        if (minPrice > maxPrice) {
            alert('Minimum price cannot be greater than maximum price');
            $(this).val('');
        }
    });
    
});