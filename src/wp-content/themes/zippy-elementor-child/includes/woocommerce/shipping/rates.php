<?php
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

function zippy_clear_shipping_package_cache()
{
    if (!WC()->session || !WC()->cart) {
        return;
    }

    foreach (WC()->cart->get_shipping_packages() as $package_index => $package) {
        WC()->session->__unset('shipping_for_package_' . $package_index);
    }
}

function zippy_get_delivery_rate_target($cart_total, $is_priority_area)
{
    if ($is_priority_area) {
        return $cart_total >= 90 ? 'free' : 'delivery_590';
    }

    if ($cart_total >= 110) {
        return 'free';
    }

    if ($cart_total >= 80) {
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
