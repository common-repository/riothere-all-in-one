<?php
wp_nonce_field('staggered_promotions_settings_data', 'staggered_promotions_settings_nonce');
global $pagenow;

$staggered_promotion_id = get_the_ID();
$is_new_page = in_array($pagenow, array('post-new.php'));

$promotion_percentage = (float) get_post_meta($staggered_promotion_id, 'promotion_percentage', true);
$number_of_orders = get_post_meta($staggered_promotion_id, 'number_of_orders', true);
$discount_validity = get_post_meta($staggered_promotion_id, 'discount_validity', true);
$product_ids_without_sellers_excluded = Riothere_All_In_One_Staggered_Promotions::get_product_ids_without_sellers_excluded($staggered_promotion_id);
$sellers_excluded_product_ids = Riothere_All_In_One_Staggered_Promotions::get_sellers_excluded_product_ids($staggered_promotion_id);
$currency_unit = get_woocommerce_currency();

function renderTermSubOptions($term, $selectedIds = [], $counter = 1)
{
    if (count(get_term_children($term->term_id, $term->taxonomy)) > 0) {
        // The term has  children
        $terms = get_terms($term->taxonomy, [
            'orderby' => 'name',
            'order' => 'asc',
            'parent' => $term->term_id,
            'hide_empty' => false,
        ]);
        foreach ($terms as $_term) {
            ?>
            <option value="<?php echo $_term->term_id ?>"
				<?php echo in_array($_term->term_id, $selectedIds) ? 'selected' : ''; ?>
                    data-level="<?php echo $counter; ?>">
				<?php echo $_term->name; ?></option>
			<?php
renderTermSubOptions($_term, $selectedIds, $counter + 1);
        }
    }
}

function renderPromotionEditSettings($staggered_promotion_id)
{
    $promotion_percentage = (float) get_post_meta($staggered_promotion_id, 'promotion_percentage', true);
    $number_of_orders = get_post_meta($staggered_promotion_id, 'number_of_orders', true);
    $discount_validity = get_post_meta($staggered_promotion_id, 'discount_validity', true);

    //array that keeps track of users who already benefitted from the staggered promotion
    ?>
    <div class="promotion-container">
        <div class="promotion-field-group">
            <div class="promotion-field promotion-field--half">
                <label for="promotion_percentage">Discount Percentage</label>
                <div class="promotion-field-input-wrapper promotion-field-input-wrapper--with-left-icon">
                    <input type="number" step="0.01" min="0.01" name="promotion_percentage"
                           value="<?php echo $promotion_percentage > 0 ? $promotion_percentage : '' ?>"
                           id="promotion_percentage"
                           class="promotion-field-input">
                    <div class="promotion-field-input-icon">
                        %
                    </div>
                </div>
                <div class="promotion-field-error"></div>
            </div>
        </div>
        <div class="promotion-field-group">
            <div class="promotion-field promotion-field--half">
                <label for="Number of Orders"><?php echo __('Number of required orders', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper">
                    <input type="number" name="number_of_orders" id="Number of Orders"
                           value="<?php echo $number_of_orders ?>"
                           class="promotion-field-input">
                </div>
                <div class="promotion-field-error"></div>
            </div>
            <div class="promotion-field promotion-field--half">
                <label for="discount_validity"><?php echo __('Discount Validity Days', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper">
                    <input type="number" name="discount_validity" id="discount_validity"
                           value="<?php echo $discount_validity; ?>"
                           class="promotion-field-input">
                </div>
                <div class="promotion-field-error"></div>
            </div>

        </div>


    </div>
	<?php
}

function renderPromotionEditRules($staggered_promotion_id)
{
    $give_promotion_to_new_added_items = get_post_meta($staggered_promotion_id, 'give_promotion_to_new_added_items', true);
    $filter_include_products_by_filter = get_post_meta($staggered_promotion_id, 'filter_include_products_by_filter', true);
    $shop_products = Riothere_All_In_One_Promotions::get_shop_products(); // unified function for all kinds of Promotions
    $sellers = Riothere_All_In_One_Staggered_Promotions::get_sellers();
    $shop_categories = Riothere_All_In_One_Staggered_Promotions::get_shop_parent_categories();
    $shop_tags = Riothere_All_In_One_Staggered_Promotions::get_shop_parent_tags();
    $currency_unit = get_woocommerce_currency();

    // include options
    $include_products = get_post_meta($staggered_promotion_id, 'include_products', true);
    $include_sellers = get_post_meta($staggered_promotion_id, 'include_sellers', true);
    $include_product_categories = get_post_meta($staggered_promotion_id, 'include_product_categories', true);
    $include_product_tags = get_post_meta($staggered_promotion_id, 'include_product_tags', true);
    $include_product_min_price = get_post_meta($staggered_promotion_id, 'include_product_min_price', true);
    $include_product_max_price = get_post_meta($staggered_promotion_id, 'include_product_max_price', true);

    if ($include_products === '') {
        $include_products = [];
    }
    if ($include_sellers === '') {
        $include_sellers = [];
    }
    if ($include_product_categories === '') {
        $include_product_categories = [];
    }
    if ($include_product_tags === '') {
        $include_product_tags = [];
    }

    $exclude_products = get_post_meta($staggered_promotion_id, 'exclude_products', true);
    $exclude_sellers = get_post_meta($staggered_promotion_id, 'exclude_sellers', true);
    $exclude_product_categories = get_post_meta($staggered_promotion_id, 'exclude_product_categories', true);
    $exclude_product_tags = get_post_meta($staggered_promotion_id, 'exclude_product_tags', true);
    $exclude_product_min_price = get_post_meta($staggered_promotion_id, 'exclude_product_min_price', true);
    $exclude_product_max_price = get_post_meta($staggered_promotion_id, 'exclude_product_max_price', true);
    $filter_exclude_products_by_filter = get_post_meta($staggered_promotion_id, 'filter_exclude_products_by_filter', true);

    if ($exclude_products === '') {
        $exclude_products = [];
    }
    if ($exclude_sellers === '') {
        $exclude_sellers = [];
    }
    if ($exclude_product_categories === '') {
        $exclude_product_categories = [];
    }
    if ($exclude_product_tags === '') {
        $exclude_product_tags = [];
    }
    ?>
    <div class="promotion-container">
        <div>
            <label class="custom-checkbox-container">
                <input type="checkbox" name="give_promotion_to_new_added_items"
                       value="true" <?php echo $give_promotion_to_new_added_items === 'true' ? 'checked' : '' ?>>
                <span class="custom-checkbox-checkmark"></span>
                <span class="custom-checkbox-label"><?php echo __('Apply this discount to products that are created in the future', 'riothere-all-in-one'); ?></span>
            </label>

        </div>
    </div>


    <div class="promotion-container">
        <h3><?php echo __('Product Exclude Rules', 'riothere-all-in-one'); ?></h3>
        <hr>

        <div class="promotion-field">
            <label for="exclude_sellers"><?php echo __('Sellers', 'riothere-all-in-one'); ?></label>
            <div class="promotion-field-input-wrapper">
                <select name="exclude_sellers[]" id="exclude_sellers"
                        class="promotion-field-input promotion-field-input--select2" multiple="multiple">
					<?php
foreach ($sellers as $seller) {
        $output = $seller->first_name . ' ' . $seller->last_name . ' <' . $seller->user_email . '>';
        ?>
                        <option value="<?php echo $seller->ID; ?>"
							<?php echo in_array($seller->ID, $exclude_sellers) ? 'selected' : ''; ?>

                        ><?php echo esc_html($output) ?></option>
						<?php
}
    ?>
                </select>
            </div>
        </div>
        <div class="promotion-field">
            <label for="exclude_product_categories"><?php echo __('Product Categories', 'riothere-all-in-one'); ?></label>
            <div class="promotion-field-input-wrapper">
                <select name="exclude_product_categories[]" id="exclude_product_categories"
                        multiple="multiple"
                        class="promotion-field-input promotion-field-input--select2">
					<?php
foreach ($shop_categories as $shop_category) {
        ?>
                        <option value="<?php echo $shop_category->term_id; ?>"
							<?php echo in_array($shop_category->term_id, $exclude_product_categories) ? 'selected' : ''; ?>
                                data-level="0"><?php echo $shop_category->name; ?></option>
						<?php
renderTermSubOptions($shop_category, $exclude_product_categories);
    }
    ?>
                </select>
            </div>
        </div>
        <div class="promotion-field">
            <label for="exclude_product_tags"><?php echo __('Product Tags', 'riothere-all-in-one'); ?></label>
            <div class="promotion-field-input-wrapper">
                <select name="exclude_product_tags[]" id="exclude_product_tags"
                        multiple="multiple"
                        class="promotion-field-input promotion-field-input--select2">
					<?php
foreach ($shop_tags as $shop_tag) {
        ?>
                        <option value="<?php echo $shop_tag->term_id; ?>"
							<?php echo in_array($shop_tag->term_id, $exclude_product_tags) ? 'selected' : ''; ?>
                                data-level="0"><?php echo $shop_tag->name; ?></option>
						<?php
renderTermSubOptions($shop_tag, $exclude_product_tags);

    }
    ?>
                </select>
            </div>
        </div>
        <div class="promotion-field-group">
            <div class="promotion-field promotion-field--half">
                <label for="exclude_product_min_price"><?php echo __('Product Min Price (before tax)', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper promotion-field-input-wrapper--with-left-icon">
                    <input type="number" min="0" step="0.01" name="exclude_product_min_price"
                           value="<?php echo $exclude_product_min_price; ?>"
                           id="exclude_product_min_price" class="promotion-field-input">
                    <div class="promotion-field-input-icon">
						<?php echo $currency_unit; ?>
                    </div>
                </div>
                <div class="promotion-field-error"></div>
            </div>
            <div class="promotion-field promotion-field--half">
                <label for="exclude_product_max_price"><?php echo __('Product Max Price (before tax)', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper promotion-field-input-wrapper--with-left-icon">
                    <input type="number" min="0" step="0.01" name="exclude_product_max_price"
                           value="<?php echo $exclude_product_max_price; ?>"
                           id="exclude_product_max_price" class="promotion-field-input">
                    <div class="promotion-field-input-icon">
						<?php echo $currency_unit; ?>
                    </div>
                </div>
                <div class="promotion-field-error"></div>
            </div>
        </div>

        <br/>
        <div class="rules-wrapper exclude-rules-wrapper">
            <div class="promotion-field">
                <label for="exclude_products"><?php echo __('Products SKU', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper">
                    <select name="exclude_products[]" id="exclude_products"
                            class="promotion-field-input promotion-field-input--select2" multiple="multiple">
						<?php
foreach ($shop_products as $shop_product) {
        ?>
                            <option value="<?php echo $shop_product['id']; ?>"
								<?php echo in_array($shop_product['id'], $exclude_products) ? 'selected' : ''; ?>
                                    data-product="<?php echo htmlspecialchars(json_encode($shop_product), ENT_QUOTES, 'UTF-8'); ?>"
                            >
								<?php echo $shop_product['sku']; ?>
                            </option>
							<?php
}
    ?>
                    </select>
                </div>
            </div>
            <div class="rule-loading">
                <div class="lds-dual-ring"></div>
            </div>
        </div>
        <label class="custom-checkbox-container">
            <input type="hidden" name="is_exclude_product_currently_all_loaded" value=""/>
            <input type="checkbox" name="filter_exclude_products_by_filter"
                   value="true" <?php echo $filter_exclude_products_by_filter === 'true' ? 'checked' : '' ?>>
            <span class="custom-checkbox-checkmark"></span>
            <span class="custom-checkbox-label"><?php echo __('Only show me products that fit the above rules', 'riothere-all-in-one'); ?></span>
        </label>
    </div>
    <div class="promotion-container">
        <button type="button"
                class="promotion-btn staggered-filter-products-btn"><?php echo __('Show Results', 'riothere-all-in-one'); ?></button>
    </div>
    <div class="promotion-container promotion-products-list-section">
        <div class="promotion-products-list-header">
            <div><?php echo __('Image', 'riothere-all-in-one'); ?></div>
            <div><?php echo __('Product', 'riothere-all-in-one'); ?></div>
            <!-- <div><?php echo __('Price', 'riothere-all-in-one'); ?></div> -->
        </div>
        <div class="promotion-products-list">
        </div>
        <div class="promotion-list-status promotion-disable-edit">
            <p><?php echo __("Click on 'Show Results' to refresh", 'riothere-all-in-one'); ?></p>
        </div>
        <div class="promotion-list-status promotion-products-empty">
            <p><?php echo __('No products are under promotions with the selected rules', 'riothere-all-in-one'); ?></p>
        </div>
        <div class="promotion-list-status promotion-products-loading">
            <div class="lds-dual-ring"></div>
        </div>
    </div>
    <div class="promotion-product-item template">
        <input type="hidden" name="applicable_product_ids[]" class="" value="" checked>
        <div class="promotion-product-item-image">
            <img src="" data-id="" alt="">
        </div>
        <div class="promotion-product-meta">
            <h2><span><?php echo __('SKU:', 'riothere-all-in-one'); ?> </span><span class="product-sku"></span></h2>
            <h3><?php echo __('Product Name:', 'riothere-all-in-one'); ?> <span class="product-name"></span></h3>
        </div>

        <!-- <div class="promotion-product-price">
			<?php echo $currency_unit ?>
            <div class="promotion-product-regular-price"></div>
        </div> -->
    </div>
	<?php
}

?>
    <h2>Configure this promotion type to automatically generate and send Coupons to users that complete a certain number of orders.</h2>
    <input type="hidden" name="is_add_mode" value="<?php echo $is_new_page ? 'true' : 'false' ?>">
    <input type="hidden" name="promotion_id" value="<?php echo get_the_ID(); ?>">

    <div class="promotion-container">
        <h3><?php echo __('General notes', 'riothere-all-in-one'); ?></h3>
        <ol>
            <li><?php echo __('"Discount Percentage": the % discount of the generated Coupon.', 'riothere-all-in-one'); ?></li>
            <li><?php echo __('"Number of Orders": the number of orders that need to be delivered to a user for him/her to receive a Coupon by email.', 'riothere-all-in-one'); ?></li>
            <li><?php echo __('"Discount Validity Days": the number of days since receiving the Coupon after which it can no longer be used.', 'riothere-all-in-one'); ?></li>
        </ol>
    </div>
<?php

if ($is_new_page) {
    ?>
    <div id="promotion-edit" class="section section-active">
        <input type="hidden" name="sellers_excluded_product_ids" id="sellers_excluded_product_ids"
               value="<?php echo htmlspecialchars(json_encode([]), ENT_QUOTES, 'UTF-8'); ?>">
		<?php
renderPromotionEditSettings($staggered_promotion_id);
    renderPromotionEditRules($staggered_promotion_id);
    ?>
    </div>
	<?php
} else {

    $exclude_products = get_post_meta($staggered_promotion_id, 'exclude_products', true);
    $exclude_sellers = get_post_meta($staggered_promotion_id, 'exclude_sellers', true);
    $exclude_product_categories = get_post_meta($staggered_promotion_id, 'exclude_product_categories', true);
    $exclude_product_tags = get_post_meta($staggered_promotion_id, 'exclude_product_tags', true);
    $exclude_product_min_price = get_post_meta($staggered_promotion_id, 'exclude_product_min_price', true);
    $exclude_product_max_price = get_post_meta($staggered_promotion_id, 'exclude_product_max_price', true);
    $filter_exclude_products_by_filter = get_post_meta($staggered_promotion_id, 'filter_exclude_products_by_filter', true);

    if ($exclude_products === '') {
        $exclude_products = [];
    }
    if ($exclude_sellers === '') {
        $exclude_sellers = [];
    }
    if ($exclude_product_categories === '') {
        $exclude_product_categories = [];
    }
    if ($exclude_product_tags === '') {
        $exclude_product_tags = [];
    }

    ?>
    <div id="promotion-view-settings" class="section section-active">
        <input type="hidden" name="view_applicable_product_ids" id="view_applicable_product_ids"
               value="<?php echo htmlspecialchars(json_encode($product_ids_without_sellers_excluded), ENT_QUOTES, 'UTF-8'); ?>">

        <input type="hidden" name="sellers_excluded_product_ids" id="sellers_excluded_product_ids"
               value="<?php echo htmlspecialchars(json_encode($sellers_excluded_product_ids), ENT_QUOTES, 'UTF-8'); ?>">

        <div class="promotion-container">
            <div class="promotions-settings">
                <div class="promotion-setting-group">
                    <div class="promotion-setting promotion-setting--half">
                        <div class="promotion-setting-label"><?php echo __('Discount Percentage:', 'riothere-all-in-one'); ?></div>
                        <div class="promotion-setting-value"><?php echo $promotion_percentage; ?>%</div>
                    </div>
                    <div class="promotion-setting promotion-setting--half">
                        <div class="promotion-setting-label"><?php echo __('Number of Orders', 'riothere-all-in-one'); ?></div>
                        <div class="promotion-setting-value"><?php echo $number_of_orders; ?></div>
                    </div>
                </div>
                <div class="promotion-setting-group">
                    <div class="promotion-setting promotion-setting--half">
                        <div class="promotion-setting-label"><?php echo __('Discount Validity Days:', 'riothere-all-in-one'); ?></div>
                        <div class="promotion-setting-value"><?php echo $discount_validity; ?></div>
                    </div>
                </div>

            </div>
        </div>
        <div class="promotion-container">
            <button type="button"
                    class="promotion-btn edit-promotion-settings-btn"><?php echo __('Edit promotion settings', 'riothere-all-in-one'); ?></button>
        </div>
    </div>
    <div id="promotion-edit-settings" class="section">
        <input type="hidden" name="edit-promotion-settings" value="false">
		<?php
renderPromotionEditSettings($staggered_promotion_id);
    ?>
    </div>
    <div id="promotion-view-rules" class="section section-active">
    <div class="promotion-container">




    <h3><?php echo __('Product Exclude Rules', 'riothere-all-in-one'); ?></h3>

    <div class="promotions-settings">
    <div class="promotion-setting">
        <div class="promotion-setting-label"><?php echo __('Product SKU:', 'riothere-all-in-one'); ?></div>
        <div class="promotion-setting-value">
			<?php
if (count($exclude_products) > 0) {
        foreach ($exclude_products as $exclude_product_id) {
            $product = wc_get_product($exclude_product_id);
            echo $product->get_title() . ' | ';
        }
    } else {
        echo 'N/A';
    }

    ?>
        </div>
    </div>
    <div class="promotion-setting">
        <div class="promotion-setting-label"><?php echo __('Sellers:', 'riothere-all-in-one'); ?></div>
        <div class="promotion-setting-value">
			<?php
if (count($exclude_sellers) > 0) {
        $outputs = [];
        foreach ($exclude_sellers as $exclude_seller_id) {
            $seller = get_user($exclude_seller_id);
            $output = $seller->user_firstname . ' ' . $seller->user_lastname . '<' . $seller->user_email . '>';
            ob_start();
            ?>
                    <a href="<?php echo get_edit_user_link($exclude_seller_id); ?>" target="_blank">
						<?php echo esc_html($output) ?>
                    </a>
					<?php
$outputs[] = ob_get_clean();
        }
        echo implode(', ', $outputs);
    } else {
        echo 'N/A';
    }

    ?>
        </div>
    </div>
    <div class="promotion-setting">
        <div class="promotion-setting-label"><?php echo __('Product Categories:', 'riothere-all-in-one'); ?></div>
        <div class="promotion-setting-value">
			<?php
if (count($exclude_product_categories) > 0) {
        foreach ($exclude_product_categories as $exclude_product_category_id) {
            $term = get_term($exclude_product_category_id);
            echo $term->name . '&nbsp;';
        }
    } else {
        echo 'N/A';
    }

    ?>
        </div>
    </div>
    <div class="promotion-setting">
        <div class="promotion-setting-label"><?php echo __('Product Tags:', 'riothere-all-in-one'); ?></div>
        <div class="promotion-setting-value">
			<?php
if (count($exclude_product_tags) > 0) {
        foreach ($exclude_product_tags as $exclude_product_tag_id) {
            $term = get_term($exclude_product_tag_id);
            echo $term->name . '&nbsp;';
        }
    } else {
        echo 'N/A';
    }

    ?>
        </div>
    </div>
    <div class="promotion-setting-group">
    <div class="promotion-setting promotion-setting--half">
        <div class="promotion-setting-label"><?php echo __('Product Min Price: (before tax)', 'riothere-all-in-one'); ?></div>
        <div class="promotion-setting-value">
			<?php
if ($exclude_product_min_price !== '') {
        echo $currency_unit . '&nbsp;' . $exclude_product_min_price;

    } else {
        echo 'N/A';
    }
    ?>
        </div>
    </div>
    <div class="promotion-setting promotion-setting--half">
        <div class="promotion-setting-label"><?php echo __('Product Max Price  (before tax):', 'riothere-all-in-one'); ?></div>
                        <div class="promotion-setting-value">
							<?php
if ($exclude_product_max_price !== '') {
        echo $currency_unit . '&nbsp;' . $exclude_product_max_price;

    } else {
        echo 'N/A';
    }
    ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="promotion-container">
            <button type="button"
                    class="promotion-btn edit-promotion-rules-btn"><?php echo __('Edit promotion rules', 'riothere-all-in-one'); ?></button>
        </div>
    </div>
    <div id="promotion-edit-rules" class="section">
        <input type="hidden" name="edit-promotion-rules" value="false">
		<?php
renderPromotionEditRules($staggered_promotion_id);
    ?>
    </div>
    <div id="promotion-view-included-products" class="section section-active">
        <input type="hidden" name="promotion-view-included-products_status" value="false">
        <div class="promotion-container ">
            <h2 class="promotion-h2"><?php echo __('Included Products', 'riothere-all-in-one'); ?>
                <!-- <span>View Mode</span> -->
            </h2>
            <div class="applicable-products-section-view">
                <div class="promotion-applicable-products-header">
                    <div><?php echo __('Image', 'riothere-all-in-one'); ?></div>
                    <div><?php echo __('Product', 'riothere-all-in-one'); ?></div>
                    <!-- <div><?php echo __('Price', 'riothere-all-in-one'); ?></div> -->
                </div>
                <div class="promotion-applicable-products-list">
					<?php
foreach ($product_ids_without_sellers_excluded as $applicable_product_id) {
        $product = wc_get_product($applicable_product_id);
        $image = wp_get_attachment_image_src($product->get_image_id());
        $image_src = $image !== false ? $image[0] : '';
        ?>
                        <div class="promotion-applicable-product-item">
                            <div class="promotion-applicable-product-item-image">
                                <img src="<?php echo $image_src; ?>" data-id="" alt="">
                            </div>
                            <div class="promotion-applicable-product-item-meta">
                                <h2><span><?php echo __('SKU:', 'riothere-all-in-one'); ?> </span><span
                                            class="product-sku"><?php echo $product->get_sku(); ?></span>
                                </h2>
                                <h3><?php echo __('Product Name:', 'riothere-all-in-one'); ?> <span
                                            class="product-name"><?php echo $product->get_title(); ?></span>
                                </h3>
                                <h4><?php echo __('Product Status:', 'riothere-all-in-one'); ?> <span
                                            class="product-name"><?php echo get_field('status', $product->get_id()); ?>
                                </h4>
                            </div>
                        </div>
						<?php
}
    ?>
                </div>
            </div>
        </div>
    </div>

	<?php

}

if (count($sellers_excluded_product_ids) > 0) {
    ?>
    <div class="promotion-container">
        <h2 class="promotion-h2"><?php echo __('Excluded Products By Seller', 'riothere-all-in-one'); ?></h2>
        <table class="display-table">
            <thead>
            <tr>
                <th><?php echo __('Product ID', 'riothere-all-in-one'); ?></th>
                <th><?php echo __('Product SKU', 'riothere-all-in-one'); ?></th>
                <th><?php echo __('Product Name', 'riothere-all-in-one'); ?></th>
                <th><?php echo __('Product Seller', 'riothere-all-in-one'); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
foreach ($sellers_excluded_product_ids as $sellers_excluded_product_id) {
        $product = wc_get_product($sellers_excluded_product_id);
        $seller = get_field('seller', $sellers_excluded_product_id);
        ?>
                <tr>
                    <td><?php echo $sellers_excluded_product_id; ?></td>
                    <td><?php echo $product->get_sku() ?></td>
                    <td><?php echo $product->get_title() ?></td>
                    <td><?php echo is_array($seller) ? $seller['user_firstname'] . ' ' . $seller['user_lastname'] . '{' . $seller['user_email'] . '}' : '' ?></td>
                </tr>
				<?php
}
    ?>
            </tbody>
        </table>
    </div>
	<?php
}
