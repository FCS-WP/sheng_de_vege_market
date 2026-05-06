<?php
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
