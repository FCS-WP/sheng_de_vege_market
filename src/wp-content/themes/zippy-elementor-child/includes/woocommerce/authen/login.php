<?php
add_filter('woocommerce_account_menu_items', 'remove_my_account_downloads');
function remove_my_account_downloads($items)
{
    unset($items['downloads']);
    return $items;
}

add_filter('woocommerce_login_redirect', 'zippy_redirect_customer_login_to_shop', 10, 2);
add_filter('login_redirect', 'zippy_redirect_customer_wp_login_to_shop', 10, 3);
add_filter('gettext', 'zippy_update_login_username_label', 10, 3);

function zippy_update_login_username_label($translation, $text, $domain)
{
    if ('woocommerce' !== $domain) {
        return $translation;
    }

    if ('Username or email address' === $text) {
        return __('Phone or Email Address', 'woocommerce');
    }

    return $translation;
}

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
