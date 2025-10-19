/**
 * Auto Attributes Applier - FIXED VERSION
 * Properly stores attributes with full taxonomy names (pa_color, pa_size)
 * 
 * Save this as: assets/js/auto-attributes.js
 */

(function($) {
    'use strict';

    class AutoAttributesApplier {
        constructor() {
            this.productsData = window.loopGridProductsData || {};
            this.init();
        }

        init() {
            console.log('%c🎨 Auto Attributes: Initializing...', 'color: #4CAF50; font-weight: bold; font-size: 14px;');
            
            console.log('%c📦 Total Products Data Available:', 'color: #2196F3; font-weight: bold;', Object.keys(this.productsData).length);
            
            if (Object.keys(this.productsData).length > 0) {
                console.group('%c📋 All Products Data', 'color: #FF9800; font-weight: bold;');
                Object.entries(this.productsData).forEach(([productId, data]) => {
                    console.group(`%cProduct ID: ${productId}`, 'color: #9C27B0; font-weight: bold;');
                    console.log('📝 Title:', data.title);
                    console.log('💰 Price:', data.price);
                    console.log('💵 Regular Price:', data.regular_price);
                    console.log('🏷️ Sale Price:', data.sale_price);
                    console.log('🔥 On Sale:', data.on_sale);
                    console.log('📁 Categories (IDs):', data.categories);
                    console.log('🏷️ Tags (IDs):', data.tags);
                    console.log('%c🎨 ALL ATTRIBUTES:', 'color: #E91E63; font-weight: bold;', data.attributes);
                    
                    if (data.attributes && Object.keys(data.attributes).length > 0) {
                        console.group('%c   → Attribute Details', 'color: #00BCD4;');
                        Object.entries(data.attributes).forEach(([attrName, attrValues]) => {
                            console.log(`   ${attrName}:`, attrValues);
                        });
                        console.groupEnd();
                    }
                    
                    console.groupEnd();
                });
                console.groupEnd();
            } else {
                console.warn('%c⚠️ No products data found!', 'color: #FF5722; font-weight: bold;');
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.applyAttributes());
            } else {
                this.applyAttributes();
            }

            $(window).on('elementor/frontend/init', () => {
                setTimeout(() => this.applyAttributes(), 500);
            });
        }

        applyAttributes() {
            const loopItems = document.querySelectorAll('.e-loop-item, .elementor-loop-container > *');

            if (loopItems.length === 0) {
                console.warn('%c⚠️ No loop items found on page', 'color: #FF5722; font-weight: bold;');
                return;
            }

            console.log('%c📦 Processing Loop Items...', 'color: #4CAF50; font-weight: bold;');
            console.log(`   Found ${loopItems.length} loop items on page`);

            let processed = 0;
            let skipped = 0;
            
            console.group('%c🔄 Processing Each Item', 'color: #2196F3; font-weight: bold;');
            
            loopItems.forEach((item, index) => {
                const productId = this.getProductId(item);
                
                console.group(`%cItem ${index + 1}`, 'color: #9C27B0;');
                console.log('Element:', item);
                console.log('Product ID Found:', productId);
                
                if (productId && this.productsData[productId]) {
                    console.log('%c✅ Data available - Applying attributes', 'color: #4CAF50;');
                    console.log('Product Data:', this.productsData[productId]);
                    
                    this.applyDataAttributes(item, this.productsData[productId]);
                    
                    // Log what was applied
                    console.log('%cApplied Attributes:', 'color: #00BCD4; font-weight: bold;');
                    console.log('   data-product-id:', item.dataset.productId);
                    console.log('   data-title:', item.dataset.title);
                    console.log('   data-price:', item.dataset.price);
                    console.log('   data-regular-price:', item.dataset.regularPrice);
                    console.log('   data-sale-price:', item.dataset.salePrice);
                    console.log('   data-categories:', item.dataset.categories);
                    console.log('   data-tags:', item.dataset.tags);
                    
                    // Log all custom attributes with their ACTUAL data attribute names
                    const customAttrs = Object.keys(item.dataset).filter(key => 
                        !['productId', 'title', 'price', 'regularPrice', 'salePrice', 'categories', 'tags', 'minPrice', 'maxPrice'].includes(key)
                    );
                    
                    if (customAttrs.length > 0) {
                        console.log('%c   🎨 Custom Attributes (DATA ATTRIBUTES):', 'color: #E91E63; font-weight: bold;');
                        customAttrs.forEach(attr => {
                            // Convert camelCase to kebab-case for display
                            const kebabAttr = attr.replace(/([A-Z])/g, '-$1').toLowerCase();
                            console.log(`      data-${kebabAttr}: "${item.dataset[attr]}"`);
                        });
                    }
                    
                    processed++;
                } else {
                    console.log('%c⚠️ No data available for this product', 'color: #FF9800;');
                    skipped++;
                }
                
                console.groupEnd();
            });
            
            console.groupEnd();

            // Final summary
            console.log('%c─────────────────────────────────────', 'color: #9E9E9E;');
            console.log('%c✅ Processing Complete!', 'color: #4CAF50; font-weight: bold; font-size: 16px;');
            console.log(`   ✓ Successfully processed: ${processed} products`);
            console.log(`   ⚠ Skipped (no data): ${skipped} items`);
            console.log(`   📊 Total items: ${loopItems.length}`);
            console.log('%c─────────────────────────────────────', 'color: #9E9E9E;');
            
            // Trigger custom event
            $(document).trigger('loop-grid-attributes-ready');
            console.log('%c🔔 Triggered event: loop-grid-attributes-ready', 'color: #673AB7;');
        }

        getProductId(element) {
            // CRITICAL FIX: Try multiple methods to get product ID
            
            // 1. Try data attribute
            if (element.dataset.productId && element.dataset.productId !== '{{ post.id }}') {
                return element.dataset.productId;
            }

            // 2. Try classes - IMPROVED to catch e-loop-item-XXX
            const classes = element.className;
            const patterns = [
                /e-loop-item-(\d+)/,  // e-loop-item-500
                /post-(\d+)/,         // post-500
                /product-id-(\d+)/,   // product-id-500
                /product-(\d+)/       // product-500
            ];

            for (const pattern of patterns) {
                const match = classes.match(pattern);
                if (match) {
                    console.log(`   Found ID ${match[1]} using pattern ${pattern}`);
                    return match[1];
                }
            }

            // 3. Try to find in child elements
            const addToCartBtn = element.querySelector('[data-product_id]');
            if (addToCartBtn) {
                return addToCartBtn.dataset.product_id;
            }
            
            // 4. Try finding product link
            const productLink = element.querySelector('a[href*="/product/"]');
            if (productLink) {
                // Extract from URL like /product/max/ -> need to match with our data
                const href = productLink.href;
                const match = href.match(/product\/([^\/]+)/);
                if (match) {
                    const slug = match[1];
                    // Try to find product ID by slug in our data
                    for (const [id, data] of Object.entries(window.loopGridProductsData || {})) {
                        if (data.title && data.title.toLowerCase().replace(/\s+/g, '-') === slug) {
                            console.log(`   Found ID ${id} by matching slug "${slug}"`);
                            return id;
                        }
                    }
                }
            }

            return null;
        }

        applyDataAttributes(element, data) {
            // Basic data
            element.dataset.productId = data.id;
            element.dataset.title = data.title || '';
            
            // Price data
            element.dataset.price = data.price || '0';
            element.dataset.regularPrice = data.regular_price || data.price || '0';
            element.dataset.salePrice = data.sale_price || '0';
            
            // For variable products
            if (data.min_price) {
                element.dataset.minPrice = data.min_price;
            }
            if (data.max_price) {
                element.dataset.maxPrice = data.max_price;
            }

            // Categories - store as term IDs (comma-separated)
            if (data.categories && data.categories.length > 0) {
                element.dataset.categories = data.categories.join(',');
            }

            // Tags - store as term IDs (comma-separated)
            if (data.tags && data.tags.length > 0) {
                element.dataset.tags = data.tags.join(',');
            }

            // CRITICAL FIX: Apply attributes with FULL taxonomy names
            if (data.attributes) {
                console.group('%c      🎯 Applying Attributes', 'color: #FF5722; font-weight: bold;');
                
                for (const [attrName, attrValues] of Object.entries(data.attributes)) {
                    // Convert array to comma-separated string of SLUGS
                    const valueString = Array.isArray(attrValues) 
                        ? attrValues.join(',') 
                        : attrValues;
                    
                    // CRITICAL: Keep the FULL taxonomy name (pa_color, pa_size, etc.)
                    // DO NOT simplify it - the filter expects exact taxonomy names
                    
                    // Convert to camelCase for dataset (JavaScript convention)
                    // pa_color -> paColor, pa_size -> paSize
                    const camelCaseAttr = this.toCamelCase(attrName);
                    
                    // Set the data attribute
                    element.dataset[camelCaseAttr] = valueString;
                    
                    // Log what we're setting
                    console.log(`      ✓ data-${this.toKebabCase(attrName)} = "${valueString}"`);
                    console.log(`        (stored as dataset.${camelCaseAttr})`);
                    console.log(`        Original taxonomy: ${attrName}`);
                }
                
                console.groupEnd();
            }

            // Add class for easier targeting
            if (!element.classList.contains('has-filter-data')) {
                element.classList.add('has-filter-data');
            }
        }

        /**
         * Convert snake_case or kebab-case to camelCase
         * pa_color -> paColor
         * pa-color -> paColor
         */
        toCamelCase(str) {
            return str.replace(/[-_]([a-z])/g, (g) => g[1].toUpperCase());
        }

        /**
         * Convert snake_case to kebab-case
         * pa_color -> pa-color
         */
        toKebabCase(str) {
            return str.replace(/_/g, '-');
        }
    }

    // Initialize when ready
    new AutoAttributesApplier();
    
    // Add helper function to view product data anytime
    window.viewProductData = function(productId) {
        if (!window.loopGridProductsData) {
            console.error('No product data available');
            return;
        }
        
        if (productId) {
            const data = window.loopGridProductsData[productId];
            if (data) {
                console.group(`%c📦 Product ${productId} Data`, 'color: #4CAF50; font-weight: bold; font-size: 16px;');
                console.log('Full Data:', data);
                console.log('Title:', data.title);
                console.log('Price:', data.price);
                console.log('Regular Price:', data.regular_price);
                console.log('Sale Price:', data.sale_price);
                console.log('On Sale:', data.on_sale);
                console.log('Categories:', data.categories);
                console.log('Tags:', data.tags);
                console.log('Attributes:', data.attributes);
                console.groupEnd();
            } else {
                console.error(`Product ${productId} not found`);
            }
        } else {
            console.group('%c📦 All Products Data', 'color: #4CAF50; font-weight: bold; font-size: 16px;');
            console.log(window.loopGridProductsData);
            console.groupEnd();
        }
    };
    
    // Add helper to check what's on a specific element
    window.checkElementData = function(selector) {
        const element = document.querySelector(selector || '.e-loop-item');
        if (!element) {
            console.error('Element not found');
            return;
        }
        
        console.group('%c🔍 Element Data Attributes', 'color: #2196F3; font-weight: bold; font-size: 16px;');
        console.log('Element:', element);
        console.log('All data attributes:', element.dataset);
        
        console.group('Formatted View:');
        Object.entries(element.dataset).forEach(([key, value]) => {
            const kebabKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
            console.log(`data-${kebabKey}: "${value}"`);
        });
        console.groupEnd();
        console.groupEnd();
    };
    
    // Log helper function availability
    console.log('%c💡 Helper Functions Available!', 'color: #00BCD4; font-weight: bold;');
    console.log('   Type: viewProductData() to see all products');
    console.log('   Type: viewProductData(123) to see specific product');
    console.log('   Type: checkElementData() to see data on first loop item');
    console.log('   Type: checkElementData(".e-loop-item:nth-child(2)") for specific item');

})(jQuery);