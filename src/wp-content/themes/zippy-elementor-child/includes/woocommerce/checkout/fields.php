<?php
/* Remove Country, Region, State */
add_filter('woocommerce_checkout_fields', 'custom_remove_checkout_fields');

function custom_remove_checkout_fields($fields)
{
    unset($fields['billing']['billing_country']);
    unset($fields['shipping']['shipping_country']);
    unset($fields['billing']['billing_state']);
    unset($fields['shipping']['shipping_state']);
    unset($fields['billing']['billing_city']);
    unset($fields['shipping']['shipping_city']);

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
