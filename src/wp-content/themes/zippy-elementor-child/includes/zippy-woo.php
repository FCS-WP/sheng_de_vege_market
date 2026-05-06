<?php
$zippy_woocommerce_includes = array(
    'woocommerce/authen/login.php',
    'woocommerce/products/loop-add-to-cart.php',
    'woocommerce/shipping/rates.php',
    'woocommerce/checkout/fields.php',
    'woocommerce/shipping/notes.php',
    'woocommerce/elementor/editor.php',
);

foreach ($zippy_woocommerce_includes as $zippy_woocommerce_include) {
    require_once __DIR__ . '/' . $zippy_woocommerce_include;
}

unset($zippy_woocommerce_include, $zippy_woocommerce_includes);
