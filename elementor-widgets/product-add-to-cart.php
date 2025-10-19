<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Elementor_Product_Add_To_Cart extends Widget_Base {
    public function get_name() { return 'product_add_to_cart'; }
    public function get_title() { return __('Product Add To Cart', 'hello-elementor-child'); }
    public function get_icon() { return 'eicon-cart-medium'; }
    public function get_categories() { return ['custom-widgets']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => __('Settings', 'hello-elementor-child')]);
        $this->add_control('product_id', [
            'label' => __('Product ID', 'hello-elementor-child'),
            'type' => Controls_Manager::NUMBER,
            'default' => '',
        ]);
        $this->add_control('button_text', [
            'label' => __('Button Text', 'hello-elementor-child'),
            'type' => Controls_Manager::TEXT,
            'default' => __('Add to Cart', 'hello-elementor-child'),
        ]);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $product_id = $settings['product_id'];
        $button_text = $settings['button_text'];

        if (!$product_id) {
            echo '<div class="custom-add-to-cart-error">⚠️ Please set Product ID.</div>';
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            echo '<div class="custom-add-to-cart-error">⚠️ Invalid Product ID.</div>';
            return;
        }

        echo '<div class="custom-add-to-cart" data-product-id="' . esc_attr($product_id) . '">';

        if ($product->is_type('variable')) {
            $attributes = $product->get_attributes();
            echo '<form class="custom-variable-form">';
            foreach ($attributes as $attribute_name => $options) {
                echo '<div class="attribute-group">';
                echo '<strong>' . wc_attribute_label($attribute_name) . '</strong>';
                $values = $options->get_options();
                foreach ($values as $value) {
                    $term = get_term($value);
                    $label = $term ? $term->name : $value;
                    echo '<label><input type="radio" name="' . esc_attr($attribute_name) . '" value="' . esc_attr($label) . '"> ' . esc_html($label) . '</label>';
                }
                echo '</div>';
            }
            echo '<button type="submit" class="custom-add-to-cart-btn" data-variable="true">' . esc_html($button_text) . '</button>';
            echo '</form>';
        } else {
            echo '<button class="custom-add-to-cart-btn" data-variable="false">' . esc_html($button_text) . '</button>';
        }

        echo '</div>';
    }
}
