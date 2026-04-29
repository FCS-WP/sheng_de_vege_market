<?php
/**
 * The template for displaying product content within loops
 * Child theme override — adds diet badge (from category image) and storage badge (from first tag).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$product_id = $product->get_ID();

// ── Top-left badge: first product TAG — text label only ──
$diet_badge_html = '';
$product_tags    = get_the_terms( $product_id, 'product_tag' );
if ( ! empty( $product_tags ) && ! is_wp_error( $product_tags ) ) {
	$first_tag       = $product_tags[0];
	$diet_badge_html = sprintf(
		'<span class="product-badge product-badge--diet">%s</span>',
		esc_html( $first_tag->name )
	);
}

// ── Bottom-right badge: use the first product CATEGORY that has a thumbnail image (image only) ──
$storage_badge_html = '';
$product_cats       = get_the_terms( $product_id, 'product_cat' );
if ( ! empty( $product_cats ) && ! is_wp_error( $product_cats ) ) {
	foreach ( $product_cats as $cat ) {
		$thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
		if ( $thumbnail_id ) {
			$cat_img_url = wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' );
			if ( $cat_img_url ) {
				$storage_badge_html = sprintf(
					'<span class="product-badge product-badge--storage"><img src="%s" alt="%s" class="product-badge__icon" /></span>',
					esc_url( $cat_img_url ),
					esc_attr( $cat->name )
				);
				break; // Use only the first category that has an image
			}
		}
	}
}
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item' );
	?>

	<div class="product-card__image-wrapper">
		<?php
		/**
		 * Hook: woocommerce_before_shop_loop_item_title.
		 *
		 * @hooked woocommerce_show_product_loop_sale_flash - 10
		 * @hooked woocommerce_template_loop_product_thumbnail - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item_title' );
		?>

		<?php if ( $diet_badge_html ) : ?>
			<div class="product-card__badge-diet"><?php echo $diet_badge_html; ?></div>
		<?php endif; ?>

		<?php if ( $storage_badge_html ) : ?>
			<div class="product-card__badge-storage"><?php echo $storage_badge_html; ?></div>
		<?php endif; ?>
	</div>

	<?php
	/**
	 * Hook: woocommerce_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_product_title - 10
	 */
	do_action( 'woocommerce_shop_loop_item_title' );

	/**
	 * Hook: woocommerce_after_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_rating - 5
	 * @hooked woocommerce_template_loop_price - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item_title' );

	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item' );
	?>
</li>
