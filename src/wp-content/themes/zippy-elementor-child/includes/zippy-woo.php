<?php
add_filter('woocommerce_account_menu_items', 'remove_my_account_downloads');
function remove_my_account_downloads($items)
{
    unset($items['downloads']);
    return $items;
}

add_filter('woocommerce_login_redirect', 'zippy_redirect_customer_login_to_shop', 10, 2);
add_filter('login_redirect', 'zippy_redirect_customer_wp_login_to_shop', 10, 3);

function zippy_get_shop_redirect_url()
{
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '';

    return $shop_url ? $shop_url : home_url('/shop/');
}

function zippy_user_is_admin_role($user)
{
    return $user instanceof WP_User && user_can($user, 'manage_options');
}

function zippy_redirect_customer_login_to_shop($redirect, $user)
{
    if (zippy_user_is_admin_role($user)) {
        return $redirect;
    }

    return zippy_get_shop_redirect_url();
}

function zippy_redirect_customer_wp_login_to_shop($redirect_to, $requested_redirect_to, $user)
{
    if (is_wp_error($user) || zippy_user_is_admin_role($user)) {
        return $redirect_to;
    }

    return zippy_get_shop_redirect_url();
}


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

/* Remove Country, Region, State */
add_filter('woocommerce_checkout_fields', 'custom_remove_checkout_fields');

function custom_remove_checkout_fields($fields)
{
    unset($fields['billing']['billing_state']);
    unset($fields['shipping']['shipping_state']);
    unset($fields['billing']['billing_city']);
    unset($fields['shipping']['shipping_city']);

    if (isset($fields['billing']['billing_country'])) {
        $fields['billing']['billing_country']['required'] = false;
        $fields['billing']['billing_country']['default'] = 'SG';
        $fields['billing']['billing_country']['class'][] = 'hidden';
        $fields['billing']['billing_country']['priority'] = 999;
    }

    if (isset($fields['shipping']['shipping_country'])) {
        $fields['shipping']['shipping_country']['required'] = false;
        $fields['shipping']['shipping_country']['default'] = 'SG';
        $fields['shipping']['shipping_country']['class'][] = 'hidden';
        $fields['shipping']['shipping_country']['priority'] = 999;
    }

    $fields['billing']['billing_postcode']['label'] = 'Postal Code';
    $fields['billing']['billing_postcode']['placeholder'] = 'Postal Code';
    $fields['shipping']['shipping_postcode']['label'] = 'Postal Code';
    $fields['shipping']['shipping_postcode']['placeholder'] = 'Postal Code';
    return $fields;
}

add_filter('woocommerce_checkout_fields', 'zippy_add_priority_delivery_area_checkout_field');

function zippy_add_priority_delivery_area_checkout_field($fields)
{
    $selected = WC()->session ? WC()->session->get('zippy_priority_delivery_area', 'no') : 'no';

    $fields['billing']['zippy_priority_delivery_area'] = array(
        'type'        => 'radio',
        'label'       => __('Delivery area', 'zippy'),
        'description' => __('Do you live in these areas? (Pasir Ris, Sengkang, Buangkok, Punggol, Seletar)', 'zippy'),
        'required'    => true,
        'class'       => array('form-row-wide', 'zippy-checkout-area-question'),
        'priority'    => 1,
        'default'     => 'yes' === $selected ? 'yes' : 'no',
        'options'     => array(
            'yes' => __('Yes', 'zippy'),
            'no'  => __('No', 'zippy'),
        ),
    );

    return $fields;
}

add_action('woocommerce_checkout_update_order_review', 'zippy_update_priority_delivery_area_session');

function zippy_update_priority_delivery_area_session($post_data)
{
    if (!WC()->session) {
        return;
    }

    parse_str($post_data, $posted_data);

    $value = isset($posted_data['zippy_priority_delivery_area'])
        ? wc_clean(wp_unslash($posted_data['zippy_priority_delivery_area']))
        : 'no';

    $normalized_value = 'yes' === $value ? 'yes' : 'no';

    WC()->session->set('zippy_priority_delivery_area', $normalized_value);
    zippy_clear_shipping_package_cache();
}

function zippy_clear_shipping_package_cache()
{
    if (!WC()->session || !WC()->cart) {
        return;
    }

    foreach (WC()->cart->get_shipping_packages() as $package_index => $package) {
        WC()->session->__unset('shipping_for_package_' . $package_index);
    }
}

add_action('woocommerce_checkout_create_order', 'zippy_save_priority_delivery_area_order_meta', 10, 2);

function zippy_save_priority_delivery_area_order_meta($order, $data)
{
    $value = isset($_POST['zippy_priority_delivery_area'])
        ? wc_clean(wp_unslash($_POST['zippy_priority_delivery_area']))
        : 'no';

    $order->update_meta_data(
        'Priority delivery area',
        'yes' === $value ? 'Yes' : 'No'
    );
}

add_filter('woocommerce_package_rates', 'zippy_filter_delivery_rates_by_area', 999, 2);

function zippy_filter_delivery_rates_by_area($rates, $package)
{
    if (!WC()->cart || empty($rates)) {
        return $rates;
    }

    $cart_total = (float) WC()->cart->get_subtotal();
    $is_priority_area = zippy_is_priority_delivery_area_selected();
    $target = zippy_get_delivery_rate_target($cart_total, $is_priority_area);
    $filtered_rates = array();

    foreach ($rates as $rate_id => $rate) {
        if (zippy_shipping_rate_is_pickup($rate)) {
            continue;
        }

        if (zippy_shipping_rate_matches_target($rate, $target)) {
            $filtered_rates[$rate_id] = $rate;
            break;
        }
    }

    if (empty($filtered_rates)) {
        $filtered_rates = 'free' === $target
            ? zippy_build_synthetic_free_shipping_rate()
            : zippy_build_fallback_delivery_rate($rates, $target);
    }

    $self_pickup_rate = zippy_build_self_pickup_rate();

    if ($self_pickup_rate) {
        $filtered_rates[$self_pickup_rate->id] = $self_pickup_rate;
    }

    $filtered_rates = zippy_normalize_shipping_rate_labels($filtered_rates);

    zippy_refresh_chosen_shipping_method($filtered_rates, $target);

    return $filtered_rates;
}

function zippy_get_delivery_rate_target($cart_total, $is_priority_area)
{
    if ($is_priority_area) {
        return $cart_total > 90 ? 'free' : 'delivery_590';
    }

    if ($cart_total > 90) {
        return 'delivery_590';
    }

    return 'delivery_890';
}

function zippy_is_priority_delivery_area_selected()
{
    $value = '';

    if (isset($_POST['post_data'])) {
        parse_str(wp_unslash($_POST['post_data']), $posted_data);

        if (isset($posted_data['zippy_priority_delivery_area'])) {
            $value = wc_clean($posted_data['zippy_priority_delivery_area']);
        }
    }

    if (!$value && isset($_POST['zippy_priority_delivery_area'])) {
        $value = wc_clean(wp_unslash($_POST['zippy_priority_delivery_area']));
    }

    if (!$value && WC()->session) {
        $value = WC()->session->get('zippy_priority_delivery_area', 'no');
    }

    return 'yes' === $value;
}

function zippy_build_delivery_rate($target)
{
    if (!class_exists('WC_Shipping_Rate')) {
        return array();
    }

    if ('free' === $target) {
        return array(
            'free_shipping:zippy_free_shipping' => new WC_Shipping_Rate(
                'free_shipping:zippy_free_shipping',
                'Free Shipping',
                0,
                array(),
                'free_shipping'
            ),
        );
    }

    $cost = 'delivery_590' === $target ? 5.90 : 8.90;
    $rate_id = 'delivery_590' === $target ? 'flat_rate:zippy_delivery_590' : 'flat_rate:zippy_delivery_890';

    return array(
        $rate_id => new WC_Shipping_Rate(
            $rate_id,
            'Delivery Charge / 运输费',
            $cost,
            array(),
            'flat_rate'
        ),
    );
}

function zippy_refresh_chosen_shipping_method($rates, $target = '')
{
    if (!WC()->session || empty($rates)) {
        return;
    }

    $available_rate_ids = array_keys($rates);
    $chosen_methods = WC()->session->get('chosen_shipping_methods', array());
    $current_method = isset($chosen_methods[0]) ? $chosen_methods[0] : '';

    if ('free' === $target) {
        foreach ($rates as $rate) {
            if (!zippy_shipping_rate_is_pickup($rate)) {
                $chosen_methods[0] = $rate->id;
                WC()->session->set('chosen_shipping_methods', $chosen_methods);
                return;
            }
        }
    }

    if ($current_method && in_array($current_method, $available_rate_ids, true)) {
        return;
    }

    $chosen_methods[0] = reset($available_rate_ids);
    WC()->session->set('chosen_shipping_methods', $chosen_methods);
}

function zippy_build_self_pickup_rate()
{
    if (!class_exists('WC_Shipping_Rate')) {
        return null;
    }

    return new WC_Shipping_Rate(
        'local_pickup:zippy_self_pickup',
        'Free Self Pick Up (Best Value - Limited-Time Offer)',
        0,
        array(),
        'local_pickup'
    );
}

function zippy_shipping_rate_matches_target($rate, $target)
{
    $cost = (float) $rate->cost;
    $label = strtolower((string) $rate->label);

    if (zippy_shipping_rate_is_pickup($rate)) {
        return false;
    }

    if ('free' === $target) {
        return 'free_shipping' === $rate->method_id || false !== strpos($label, 'free shipping') || abs($cost) < 0.01;
    }

    if ('free_shipping' === $rate->method_id || false !== strpos($label, 'free shipping')) {
        return false;
    }

    if ('delivery_590' === $target) {
        return 'flat_rate' === $rate->method_id && (abs($cost - 5.90) < 0.01 || false !== strpos($label, '5.90'));
    }

    if ('delivery_890' === $target) {
        return 'flat_rate' === $rate->method_id && (abs($cost - 8.90) < 0.01 || false !== strpos($label, '8.90'));
    }

    return false;
}

function zippy_build_fallback_delivery_rate($rates, $target)
{
    $fallback_rates = array();
    $fallback_rate_id = null;

    foreach ($rates as $rate_id => $rate) {
        $label = strtolower((string) $rate->label);

        if (zippy_shipping_rate_is_pickup($rate) || 'free_shipping' === $rate->method_id || false !== strpos($label, 'free shipping')) {
            continue;
        }

        $fallback_rate_id = $rate_id;
        break;
    }

    if (null === $fallback_rate_id) {
        return zippy_build_delivery_rate($target);
    }

    $fallback_rate = $rates[$fallback_rate_id];

    if ('free' === $target) {
        $fallback_rate->cost = 0;
        $fallback_rate->label = 'Free Shipping';
    } elseif ('delivery_590' === $target) {
        $fallback_rate->cost = 5.90;
        $fallback_rate->label = 'Delivery Charge / 运输费';
    } else {
        $fallback_rate->cost = 8.90;
        $fallback_rate->label = 'Delivery Charge / 运输费';
    }

    if (!empty($fallback_rate->taxes) && is_array($fallback_rate->taxes)) {
        foreach ($fallback_rate->taxes as $key => $tax) {
            $fallback_rate->taxes[$key] = 0;
        }
    }

    $fallback_rates[$fallback_rate_id] = $fallback_rate;

    return $fallback_rates;
}

function zippy_build_synthetic_free_shipping_rate()
{
    if (!class_exists('WC_Shipping_Rate')) {
        return array();
    }

    return array(
        'free_shipping:zippy_free_shipping' => new WC_Shipping_Rate(
            'free_shipping:zippy_free_shipping',
            'Free Shipping',
            0,
            array(),
            'free_shipping'
        ),
    );
}

function zippy_shipping_rate_is_pickup($rate)
{
    $label = strtolower((string) $rate->label);

    return 'local_pickup' === $rate->method_id || false !== strpos($label, 'pick');
}

function zippy_normalize_shipping_rate_labels($rates)
{
    foreach ($rates as $rate_id => $rate) {
        $label = strtolower((string) $rate->label);

        if (zippy_shipping_rate_is_pickup($rate)) {
            $rates[$rate_id]->label = 'Free Self Pick Up (Best Value - Limited-Time Offer)';
            $rates[$rate_id]->cost = 0;

            if (!empty($rates[$rate_id]->taxes) && is_array($rates[$rate_id]->taxes)) {
                foreach ($rates[$rate_id]->taxes as $key => $tax) {
                    $rates[$rate_id]->taxes[$key] = 0;
                }
            }

            continue;
        }

        if ('free_shipping' === $rate->method_id || false !== strpos($label, 'free shipping') || abs((float) $rate->cost) < 0.01) {
            $rates[$rate_id]->label = 'Free Shipping';
            $rates[$rate_id]->cost = 0;

            if (!empty($rates[$rate_id]->taxes) && is_array($rates[$rate_id]->taxes)) {
                foreach ($rates[$rate_id]->taxes as $key => $tax) {
                    $rates[$rate_id]->taxes[$key] = 0;
                }
            }

            continue;
        }

        if ('flat_rate' === $rate->method_id) {
            $rates[$rate_id]->label = 'Delivery Charge / 运输费';
        }
    }

    return $rates;
}

add_action('woocommerce_after_shipping_rate', 'zippy_render_shipping_method_notes', 10, 2);

function zippy_render_shipping_method_notes($method, $index)
{
    if ('local_pickup' === $method->method_id || false !== strpos(strtolower((string) $method->label), 'pick')) {
?>
        <div class="zippy-shipping-notes zippy-shipping-notes--pickup">
            <p class="zippy-shipping-notes__item zippy-shipping-notes__item--success"><span><?php esc_html_e('Pick-up must be arranged at least 1 day in advance.', 'zippy'); ?></span></p>
            <p class="zippy-shipping-notes__item zippy-shipping-notes__item--success"><span><?php esc_html_e('10% Discount redeem for all members.', 'zippy'); ?></span></p>
            <p class="zippy-shipping-notes__item zippy-shipping-notes__item--warning"><span><?php esc_html_e('Last-minute pick-ups are subject to stock availability.', 'zippy'); ?></span></p>
            <p class="zippy-shipping-notes__item zippy-shipping-notes__item--tip"><span><?php esc_html_e('Highly recommended during festive peak periods.', 'zippy'); ?></span></p>
        </div>
<?php
        return;
    }

    $method_label = strtolower((string) $method->label);

    if ('free_shipping' !== $method->method_id && false === strpos($method_label, 'free shipping')) {
        return;
    }
?>
    <div class="zippy-shipping-notes">
        <p class="zippy-shipping-notes__item zippy-shipping-notes__item--success"><span><?php esc_html_e('Delivery will be made between 2 - 5 working days.', 'zippy'); ?></span></p>
        <p class="zippy-shipping-notes__item zippy-shipping-notes__item--success"><span><?php esc_html_e('Delivery notification will be sent on the day of delivery.', 'zippy'); ?></span></p>
    </div>
<?php
}

function enqueue_sprintf_for_elementor()
{
    
    if (did_action('elementor/loaded')) {
        wp_enqueue_script(
            'sprintf-js',
            'https://cdnjs.cloudflare.com/ajax/libs/sprintf/1.1.2/sprintf.min.js',
            [],
            '1.1.2',
            true
        );
    }
}
add_action('elementor/editor/after_enqueue_scripts', 'enqueue_sprintf_for_elementor');
