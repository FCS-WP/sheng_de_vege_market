<?php
add_filter('woocommerce_account_menu_items', 'remove_my_account_downloads');
function remove_my_account_downloads($items)
{
    unset($items['downloads']);
    return $items;
}


// add_action('woocommerce_after_shop_loop_item', 'add_quantity_field_to_shop_loop', 15);

// function add_quantity_field_to_shop_loop()
// {
//     global $product;

//     if ($product && $product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) {
//         echo '<div class="custom-quantity-wrapper" data-product_id="' . esc_attr($product->get_id()) . '">';
//         echo '<button type="button" class="qty-minus">-</button>';
//         echo '<input type="number" class="qty-input" value="1" min="1" step="1">';
//         echo '<button type="button" class="qty-plus">+</button>';
//         echo '</div>';
//     }
// }

add_action('woocommerce_after_shop_loop_item', 'shop_loop_add_to_cart', 15);

function shop_loop_add_to_cart()
{
    global $product;

    if (!$product || !$product->is_purchasable()) {
        return;
    }

    $product_id = $product->get_id();
    $product_sku = $product->get_sku();
    $product_name = $product->get_name();

    echo '<div class="custom-add-to-cart-wrapper" style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 10px;">';

    echo '<button type="button" class="qty-minus-btn" data-product_id="' . $product_id . '">-</button>';
    echo '<input type="number" class="qty-input-val" value="1" min="1" id="qty-' . $product_id . '" style="width: 50px; text-align: center;">';
    echo '<button type="button" class="qty-plus-btn" data-product_id="' . $product_id . '">+</button>';

    echo '</div>';
    echo '<div class="woocommerce-loop-product__buttons">';
    echo sprintf(
        '<a href="%s" data-quantity="1" class="%s" data-product_id="%s" data-product_sku="%s" aria-label="Add to cart: &ldquo;%s&rdquo;" rel="nofollow">Add to cart</a>',
        esc_url($product->add_to_cart_url()),
        esc_attr(implode(' ', array_filter(['button', 'product_type_' . $product->get_type(), 'add_to_cart_button', 'ajax_add_to_cart', 'shin_add_to_cart_btn']))),
        esc_attr($product_id),
        esc_attr($product_sku),
        esc_attr($product_name)
    );
    echo '</div>';
}

add_filter('woocommerce_loop_add_to_cart_args', 'add_quantity_attribute_to_ajax_button', 10, 2);

function add_quantity_attribute_to_ajax_button($args, $product)
{
    $args['attributes']['data-quantity'] = '1';
    return $args;
}
