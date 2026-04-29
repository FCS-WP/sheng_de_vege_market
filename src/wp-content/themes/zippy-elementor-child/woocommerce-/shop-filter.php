<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('zippy_render_shop_filter_shortcode')) {
	function zippy_render_shop_filter_shortcode()
	{
		$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
		$search_value = get_search_query();
		$current_category = get_query_var('product_cat');

		if (isset($_GET['product_cat'])) {
			$current_category = sanitize_title(wp_unslash($_GET['product_cat']));
		}

		$categories = get_terms(array(
			'taxonomy' => 'product_cat',
			'hide_empty' => true,
			'parent' => 0,
		));

		ob_start();
		?>
		<div class="zippy-shop-filter" data-zippy-shop-filter>
			<form class="zippy-shop-filter__search" action="<?php echo esc_url($shop_url); ?>" method="get" role="search">
				<input type="hidden" name="post_type" value="product">
				<input type="hidden" name="product_cat" value="<?php echo esc_attr($current_category); ?>" data-zippy-category-input>

				<label class="screen-reader-text" for="zippy_shop_filter_search">
					<?php esc_html_e('Search for products', 'woocommerce'); ?>
				</label>

				<img
					class="zippy-shop-filter__search-icon"
					src="https://zippy-staging6.theshin.info/wp-content/uploads/2026/04/search-interface-symbol.png"
					alt=""
					aria-hidden="true"
				>
				<input
					type="search"
					id="zippy_shop_filter_search"
					name="s"
					value="<?php echo esc_attr($search_value); ?>"
					placeholder="<?php esc_attr_e('Search for products', 'woocommerce'); ?>"
					autocomplete="off"
				>

				<button class="zippy-shop-filter__button" type="button" data-zippy-filter-open aria-label="<?php esc_attr_e('Open product filters', 'woocommerce'); ?>">
					<img src="https://zippy-staging6.theshin.info/wp-content/uploads/2026/04/filter.png" alt="" aria-hidden="true">
				</button>
			</form>

			<div class="zippy-shop-filter__drawer" data-zippy-filter-drawer aria-hidden="true">
				<div class="zippy-shop-filter__overlay" data-zippy-filter-close></div>
				<div class="zippy-shop-filter__panel" role="dialog" aria-modal="true" aria-labelledby="zippy_shop_filter_title">
					<div class="zippy-shop-filter__panel-header">
						<h3 id="zippy_shop_filter_title"><?php esc_html_e('Filter', 'woocommerce'); ?></h3>
						<button type="button" data-zippy-filter-close aria-label="<?php esc_attr_e('Close filters', 'woocommerce'); ?>">×</button>
					</div>

					<div class="zippy-shop-filter__group">
						<p class="zippy-shop-filter__group-title"><?php esc_html_e('Categories', 'woocommerce'); ?></p>

						<div class="zippy-shop-filter__categories">
							<label class="zippy-shop-filter__category">
								<input type="radio" name="zippy_filter_category" value="" <?php checked('', $current_category); ?>>
								<span><?php esc_html_e('All products', 'woocommerce'); ?></span>
							</label>

							<?php if (!is_wp_error($categories) && !empty($categories)) : ?>
								<?php foreach ($categories as $category) : ?>
									<label class="zippy-shop-filter__category">
										<input type="radio" name="zippy_filter_category" value="<?php echo esc_attr($category->slug); ?>" <?php checked($category->slug, $current_category); ?>>
										<span><?php echo esc_html($category->name); ?></span>
									</label>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>

					<div class="zippy-shop-filter__actions">
						<button type="button" class="zippy-shop-filter__reset" data-zippy-filter-reset>
							<?php esc_html_e('Reset', 'woocommerce'); ?>
						</button>
						<button type="button" class="zippy-shop-filter__apply" data-zippy-filter-apply>
							<?php esc_html_e('Apply filter', 'woocommerce'); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

add_shortcode('zippy_shop_filter', 'zippy_render_shop_filter_shortcode');
