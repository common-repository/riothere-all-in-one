<?php
wp_nonce_field('promotions_settings_data', 'promotions_settings_nonce');
global $pagenow;

$promotion_id = get_the_ID();
$is_new_page = in_array($pagenow, array('post-new.php'));

$promotion_percentage = (float) get_post_meta($promotion_id, 'promotion_percentage', true);
$promotion_label = get_post_meta($promotion_id, 'promotion_label', true);
$promotion_start_date = get_post_meta($promotion_id, 'promotion_start_date', true);
$promotion_end_date = get_post_meta($promotion_id, 'promotion_end_date', true);
$products_show_on_home_page = Riothere_All_In_One_Promotions::get_products_show_on_home_page($promotion_id);
$products_show_label = Riothere_All_In_One_Promotions::get_products_show_promotion_label($promotion_id);
$product_ids_without_sellers_excluded = Riothere_All_In_One_Promotions::get_product_ids_without_sellers_excluded($promotion_id);
$sellers_excluded_product_ids = Riothere_All_In_One_Promotions::get_sellers_excluded_product_ids($promotion_id);
$currency_unit = get_woocommerce_currency();

//die(var_dump(get_the_ID()));
// exclude options

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

function renderPromotionEditSettings($promotion_id)
{
    $promotion_percentage = (float) get_post_meta($promotion_id, 'promotion_percentage', true);
    $promotion_label = get_post_meta($promotion_id, 'promotion_label', true);
    $promotion_start_date = get_post_meta($promotion_id, 'promotion_start_date', true);
    $promotion_end_date = get_post_meta($promotion_id, 'promotion_end_date', true);
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

            <div class="promotion-field promotion-field--half">
                <label for="promotion_percentage"><?php echo __('Promotion Label', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper">
                    <input type="text" name="promotion_label"
                           value="<?php echo $promotion_label ?>"
                           id="promotion_label"
                           class="promotion-field-input">
                </div>
                <div class="promotion-field-error"></div>
            </div>
        </div>
        <div class="promotion-field-group">
            <div class="promotion-field promotion-field--half">
                <label for="promotion_start_date"><?php echo __('Promotion Starts On', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper">
                    <input type="date" name="promotion_start_date" id="promotion_start_date"
                           value="<?php echo $promotion_start_date; ?>"
                           class="promotion-field-input">
                </div>
                <div class="promotion-field-error"></div>
            </div>
            <div class="promotion-field promotion-field--half">
                <label for="promotion_end_date"><?php echo __('Promotion Ends On', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper">
                    <input type="date" name="promotion_end_date" id="promotion_end_date"
                           value="<?php echo $promotion_end_date; ?>"
                           class="promotion-field-input">
                </div>
                <div class="promotion-field-error"></div>
            </div>
        </div>
    </div>
	<?php
}

function renderPromotionEditRules($promotion_id)
{
    $give_promotion_to_new_added_items = get_post_meta($promotion_id, 'give_promotion_to_new_added_items', true);
    $filter_include_products_by_filter = get_post_meta($promotion_id, 'filter_include_products_by_filter', true);
    $shop_products = Riothere_All_In_One_Promotions::get_shop_products();
    $sellers = Riothere_All_In_One_Promotions::get_sellers();
    $shop_categories = Riothere_All_In_One_Promotions::get_shop_parent_categories();
    $shop_tags = Riothere_All_In_One_Promotions::get_shop_parent_tags();
    $products_show_on_home_page = Riothere_All_In_One_Promotions::get_products_show_on_home_page($promotion_id);
    $products_show_label = Riothere_All_In_One_Promotions::get_products_show_promotion_label($promotion_id);
    $currency_unit = get_woocommerce_currency();

    // include options
    $include_products = get_post_meta($promotion_id, 'include_products', true);
    $include_sellers = get_post_meta($promotion_id, 'include_sellers', true);
    $include_product_categories = get_post_meta($promotion_id, 'include_product_categories', true);
    $include_product_tags = get_post_meta($promotion_id, 'include_product_tags', true);
    $include_product_min_price = get_post_meta($promotion_id, 'include_product_min_price', true);
    $include_product_max_price = get_post_meta($promotion_id, 'include_product_max_price', true);

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

    $exclude_products = get_post_meta($promotion_id, 'exclude_products', true);
    $exclude_sellers = get_post_meta($promotion_id, 'exclude_sellers', true);
    $exclude_product_categories = get_post_meta($promotion_id, 'exclude_product_categories', true);
    $exclude_product_tags = get_post_meta($promotion_id, 'exclude_product_tags', true);
    $exclude_product_min_price = get_post_meta($promotion_id, 'exclude_product_min_price', true);
    $exclude_product_max_price = get_post_meta($promotion_id, 'exclude_product_max_price', true);
    $filter_exclude_products_by_filter = get_post_meta($promotion_id, 'filter_exclude_products_by_filter', true);

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
        <h3>Product Include Rules</h3>
        <hr>
        <div class="rules-wrapper include-rules-wrapper">

            <div class="promotion-field">
                <label for="include_sellers"><?php echo __('Sellers', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper">
                    <select name="include_sellers[]" id="include_sellers"
                            class="promotion-field-input promotion-field-input--select2" multiple="multiple">
						<?php
foreach ($sellers as $seller) {
        $output = $seller->first_name . ' ' . $seller->last_name . ' <' . $seller->user_email . '>';
        ?>
                            <option value="<?php echo $seller->ID; ?>" <?php echo in_array($seller->ID, $include_sellers) ? 'selected' : '' ?>><?php echo esc_html($output); ?></option>
							<?php
}
    ?>
                    </select>
                </div>
            </div>
            <div class="promotion-field">
                <label for="include_product_categories"><?php echo __('Product Categories', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper">
                    <select name="include_product_categories[]" id="include_product_categories"
                            multiple="multiple"
                            class="promotion-field-input promotion-field-input--select2">
						<?php
foreach ($shop_categories as $shop_category) {
        ?>
                            <option value="<?php echo $shop_category->term_id; ?>"
								<?php echo in_array($shop_category->term_id, $include_product_categories) ? 'selected' : '' ?>
                                    data-level="0"><?php echo $shop_category->name; ?></option>
							<?php
renderTermSubOptions($shop_category, $include_product_categories);
    }
    ?>
                    </select>
                </div>
            </div>
            <div class="promotion-field">
                <label for="include_product_tags"><?php echo __('Product Tags', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper">
                    <select name="include_product_tags[]" id="include_product_tags"
                            multiple="multiple"
                            class="promotion-field-input promotion-field-input--select2">
						<?php
foreach ($shop_tags as $shop_tag) {
        ?>
                            <option value="<?php echo $shop_tag->term_id; ?>"
								<?php echo in_array($shop_tag->term_id, $include_product_tags) ? 'selected' : '' ?>

                                    data-level="0"><?php echo $shop_tag->name; ?></option>
							<?php
renderTermSubOptions($shop_tag, $include_product_tags);
    }
    ?>
                    </select>
                </div>
            </div>
            <div class="promotion-field-group">
                <div class="promotion-field promotion-field--half">
                    <label for="include_product_min_price"><?php echo __('Product Min Price (before tax)', 'riothere-all-in-one'); ?></label>
                    <div class="promotion-field-input-wrapper promotion-field-input-wrapper--with-left-icon">
                        <input type="number" min="0" step="0.01" name="include_product_min_price"
                               value="<?php echo $include_product_min_price; ?>"
                               id="include_product_min_price" class="promotion-field-input">
                        <div class="promotion-field-input-icon">
							<?php echo $currency_unit; ?>
                        </div>
                    </div>
                    <div class="promotion-field-error"></div>
                </div>
                <div class="promotion-field promotion-field--half">
                    <label for="include_product_max_price"><?php echo __('Product Max Price (before tax)', 'riothere-all-in-one'); ?></label>
                    <div class="promotion-field-input-wrapper promotion-field-input-wrapper--with-left-icon">
                        <input type="number" min="0" step="0.01" name="include_product_max_price"
                               value="<?php echo $include_product_max_price; ?>"
                               id="include_product_max_price" class="promotion-field-input">
                        <div class="promotion-field-input-icon">
							<?php echo $currency_unit; ?>
                        </div>
                    </div>
                    <div class="promotion-field-error"></div>
                </div>
            </div>
            <br/>

            <div class="promotion-field">
                <label for="include_products">Products SKU</label>
                <div class="promotion-field-input-wrapper">
                    <select name="include_products[]" id="include_products"
                            class="promotion-field-input promotion-field-input--select2" multiple="multiple">
						<?php
foreach ($shop_products as $shop_product) {
        ?>
                            <option value="<?php echo $shop_product['id']; ?>"
								<?php echo in_array($shop_product['id'], $include_products) ? 'selected' : ''; ?>
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
            <input type="hidden" name="is_include_product_currently_all_loaded" value=""/>
            <input type="checkbox" name="filter_include_products_by_filter"
                   value="true" <?php echo $filter_include_products_by_filter === 'true' ? 'checked' : '' ?>>
            <span class="custom-checkbox-checkmark"></span>
            <span class="custom-checkbox-label"><?php echo __('Only show me products that fit the above rules', 'riothere-all-in-one'); ?></span>
        </label>

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
                class="promotion-btn filter-products-btn"><?php echo __('Show Results', 'riothere-all-in-one'); ?></button>
    </div>
    <input type="hidden" name="edit_products_show_on_home_page_value" id="edit_products_show_on_home_page_value"
           value="<?php echo htmlspecialchars(json_encode($products_show_on_home_page), ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="edit_products_show_promotion_label_value" id="edit_products_show_promotion_label_value"
           value="<?php echo htmlspecialchars(json_encode($products_show_label), ENT_QUOTES, 'UTF-8'); ?>">
    <div class="promotion-container promotion-products-list-section">
        <div class="promotion-products-list-header">
            <div>
                <label class="custom-checkbox-container">
                    <input type="checkbox" name="products_show_on_home_page_toggle"
                           id="products_show_on_home_page_toggle">
                    <span class="custom-checkbox-checkmark"></span>
                    <span class="custom-checkbox-label"><?php echo __('Show on Homepage', 'riothere-all-in-one'); ?></span>
                </label>
            </div>
            <div class="show-label">
                <label class="custom-checkbox-container">
                    <input type="checkbox" name="products_show_promotion_label_toggle"
                           id="products_show_promotion_label_toggle">
                    <span class="custom-checkbox-checkmark"></span>
                    <span class="custom-checkbox-label"><?php echo __('Show Label', 'riothere-all-in-one'); ?></span>
                </label>
            </div>
            <div><?php echo __('Image', 'riothere-all-in-one'); ?></div>
            <div><?php echo __('Product', 'riothere-all-in-one'); ?></div>
            <div><?php echo __('Price', 'riothere-all-in-one'); ?></div>
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
        <div class="product-selected-handler">
            <label class="custom-checkbox-container">
                <input type="checkbox" name="products_show_on_home_page[]">
                <span class="custom-checkbox-checkmark"></span>
            </label>
        </div>
        <div class="product-selected-handler select-label-handler">
            <label class="custom-checkbox-container">
                <input type="checkbox" name="products_show_promotion_label[]">
                <span class="custom-checkbox-checkmark"></span>
            </label>
        </div>
        <div class="promotion-product-item-image">
            <img src="" data-id="" alt="">
        </div>
        <div class="promotion-product-meta">
            <h2><span><?php echo __('SKU:', 'riothere-all-in-one'); ?> </span><span class="product-sku"></span></h2>
            <h3><?php echo __('Product Name:', 'riothere-all-in-one'); ?> <span class="product-name"></span></h3>
        </div>

        <div class="promotion-product-price">
			<?php echo $currency_unit ?>
            <div class="promotion-product-regular-price"></div>
        </div>
    </div>
	<?php
}

?>
    <!-- <h2><?php echo __('Promotions Settings', 'riothere-all-in-one'); ?></h2> -->
    <h2>Configure this promotion type to create percentile discounts on products. Users will be able to see the discount on the Products on the storefront.</h2>
    <input type="hidden" name="is_add_mode" value="<?php echo $is_new_page ? 'true' : 'false' ?>">
    <input type="hidden" name="promotion_id" value="<?php echo get_the_ID(); ?>">

    <div class="promotion-container">
        <h3><?php echo __('General notes', 'riothere-all-in-one'); ?></h3>
        <ol>
            <li><?php echo __('The Promotion Label is a small text that appears on products on the storefront. Leave it empty if you do not wish for this text to appear.', 'riothere-all-in-one'); ?></li>
            <li><?php echo __('Both "Promotion Starts On" and "Promotion Ends On" are optional. For example, leaving "Promotion Ends On" empty would mean that this promotion will be valid infinitely (or until it is unpublished/deleted).', 'riothere-all-in-one'); ?></li>
        </ol>
    </div>
<?php

if ($is_new_page) {
    ?>
    <div id="promotion-edit" class="section section-active">
        <input type="hidden" name="sellers_excluded_product_ids" id="sellers_excluded_product_ids"
               value="<?php echo htmlspecialchars(json_encode([]), ENT_QUOTES, 'UTF-8'); ?>">
		<?php
renderPromotionEditSettings($promotion_id);
    renderPromotionEditRules($promotion_id);
    ?>
    </div>
	<?php
} else {

    $include_products = get_post_meta($promotion_id, 'include_products', true);
    $include_sellers = get_post_meta($promotion_id, 'include_sellers', true);
    $include_product_categories = get_post_meta($promotion_id, 'include_product_categories', true);
    $include_product_tags = get_post_meta($promotion_id, 'include_product_tags', true);
    $include_product_min_price = get_post_meta($promotion_id, 'include_product_min_price', true);
    $include_product_max_price = get_post_meta($promotion_id, 'include_product_max_price', true);

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

    $exclude_products = get_post_meta($promotion_id, 'exclude_products', true);
    $exclude_sellers = get_post_meta($promotion_id, 'exclude_sellers', true);
    $exclude_product_categories = get_post_meta($promotion_id, 'exclude_product_categories', true);
    $exclude_product_tags = get_post_meta($promotion_id, 'exclude_product_tags', true);
    $exclude_product_min_price = get_post_meta($promotion_id, 'exclude_product_min_price', true);
    $exclude_product_max_price = get_post_meta($promotion_id, 'exclude_product_max_price', true);
    $filter_exclude_products_by_filter = get_post_meta($promotion_id, 'filter_exclude_products_by_filter', true);

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
        <input type="hidden" name="view_products_show_on_home_page_value" id="view_products_show_on_home_page_value"
               value="<?php echo htmlspecialchars(json_encode($products_show_on_home_page), ENT_QUOTES, 'UTF-8'); ?>">
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
                        <div class="promotion-setting-label"><?php echo __('Promotion Label:', 'riothere-all-in-one'); ?></div>
                        <div class="promotion-setting-value"><?php echo $promotion_label; ?></div>
                    </div>
                </div>
                <div class="promotion-setting-group">
                    <div class="promotion-setting promotion-setting--half">
                        <div class="promotion-setting-label"><?php echo __('Promotion Starts On:', 'riothere-all-in-one'); ?></div>
                        <div class="promotion-setting-value"><?php echo $promotion_start_date; ?></div>
                    </div>
                    <div class="promotion-setting promotion-setting--half">
                        <div class="promotion-setting-label"><?php echo __('Promotion Ends On:', 'riothere-all-in-one'); ?></div>
                        <div class="promotion-setting-value"><?php echo $promotion_end_date; ?></div>
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
renderPromotionEditSettings($promotion_id);
    ?>
    </div>
    <div id="promotion-view-rules" class="section section-active">
    <div class="promotion-container">
    <h3><?php echo __('Product Include Rules', 'riothere-all-in-one'); ?></h3>

    <div class="promotions-settings">
        <div class="promotion-setting">
            <div class="promotion-setting-label"><?php echo __('Product SKU:', 'riothere-all-in-one'); ?></div>
            <div class="promotion-setting-value">
				<?php
if (count($include_products) > 0) {
        foreach ($include_products as $include_product_id) {
            $product = wc_get_product($include_product_id);
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
if (count($include_sellers) > 0) {
        $outputs = [];
        foreach ($include_sellers as $include_seller_id) {
            $seller = get_user($include_seller_id);
            $output = $seller->user_firstname . ' ' . $seller->user_lastname . '<' . $seller->user_email . '>';
            ob_start();
            ?>
                        <a href="<?php echo get_edit_user_link($include_seller_id); ?>" target="_blank">
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
if (count($include_product_categories) > 0) {
        foreach ($include_product_categories as $include_product_category_id) {
            $term = get_term($include_product_category_id);
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
if (count($include_product_tags) > 0) {
        foreach ($include_product_tags as $include_product_tag_id) {
            $term = get_term($include_product_tag_id);
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
if ($include_product_min_price !== '') {
        echo $currency_unit . '&nbsp;' . $include_product_min_price;

    } else {
        echo 'N/A';
    }
    ?>
                </div>
            </div>
            <div class="promotion-setting promotion-setting--half">
                <div class="promotion-setting-label"><?php echo __('Product Max Price: (before tax)', 'riothere-all-in-one'); ?></div>
                <div class="promotion-setting-value">
					<?php
if ($include_product_max_price !== '') {
        echo $currency_unit . '&nbsp;' . $include_product_max_price;

    } else {
        echo 'N/A';
    }
    ?>
                </div>
            </div>
        </div>
    </div>

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
renderPromotionEditRules($promotion_id);
    ?>
    </div>
    <div id="promotion-view-included-products" class="section section-active">
        <input type="hidden" name="promotion-view-included-products_status" value="false">
        <div class="promotion-container ">
            <h2 class="promotion-h2"><?php echo __('Included Products', 'riothere-all-in-one'); ?>
                <span>View Mode</span></h2>
            <div class="applicable-products-section-view <?php echo trim($promotion_label) !== '' ? 'has-label' : '' ?>">
                <div class="promotion-applicable-products-header">
                    <div>
                        <label class="custom-checkbox-container">
                            <input type="checkbox" name="show_on_home_page_view_toggle"
                                   id="show_on_home_page_view_toggle" disabled>
                            <span class="custom-checkbox-checkmark"></span>
                            <span class="custom-checkbox-label"><?php echo __('Show on Homepage', 'riothere-all-in-one'); ?></span>
                        </label>
                    </div>
					<?php
if (trim($promotion_label) !== '') {
        ?>
                        <div>
                            <label class="custom-checkbox-container">
                                <input type="checkbox" name="show_promotion_label_toggle"
                                       id="show_promotion_label_toggle" disabled>
                                <span class="custom-checkbox-checkmark"></span>
                                <span class="custom-checkbox-label"><?php echo __('Show Label', 'riothere-all-in-one'); ?></span>
                            </label>
                        </div>
						<?php
}
    ?>
                    <div><?php echo __('Image', 'riothere-all-in-one'); ?></div>
                    <div><?php echo __('Product', 'riothere-all-in-one'); ?></div>
                    <div><?php echo __('Price', 'riothere-all-in-one'); ?></div>
                </div>
                <div class="promotion-applicable-products-list">
					<?php
foreach ($product_ids_without_sellers_excluded as $applicable_product_id) {
        $product = wc_get_product($applicable_product_id);
        $image = wp_get_attachment_image_src($product->get_image_id());
        $image_src = $image !== false ? $image[0] : '';
        $product_applicable_promotion_id = Riothere_All_In_One_Promotions::get_product_applicable_promotion_id($applicable_product_id);
        $is_product_applicable = (int) $product_applicable_promotion_id === (int) $promotion_id;
        $is_promotion_valid = Riothere_All_In_One_Promotions::is_promotion_valid($promotion_id);
        ?>
                        <div class="promotion-applicable-product-item <?php echo !$is_product_applicable ? 'not-applicable-item' : '' ?>">
                            <div class="promotion-applicable-product-show-on-home-page">
                                <label class="custom-checkbox-container">
                                    <input type="checkbox" name="show_on_home_page_view[]" disabled
                                           value="<?php echo $applicable_product_id ?>"
										<?php echo in_array($applicable_product_id, $products_show_on_home_page) ? 'checked' : '' ?>
                                    >
                                    <span class="custom-checkbox-checkmark"></span>
                                </label>
                            </div>

							<?php
if (trim($promotion_label) !== '') {
            ?>
                                <div class="promotion-applicable-product-show-label">
                                    <label class="custom-checkbox-container">
                                        <input type="checkbox" name="show_promotion_label[]" disabled
                                               value="<?php echo $applicable_product_id ?>"
											<?php echo in_array($applicable_product_id, $products_show_label) ? 'checked' : '' ?>
                                        >
                                        <span class="custom-checkbox-checkmark"></span>
                                    </label>
                                </div>
								<?php
}
        ?>
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
								<?php
if (!$is_promotion_valid) {
            ?>
                                    <h4>
                                        N.B. promotion is not valid
                                    </h4>
									<?php
} else if (!$is_product_applicable) {
            ?>
                                    <h4>
                                        N.B.
                                        <a href="<?php echo get_edit_post_link($product_applicable_promotion_id); ?>"> <?php echo get_the_title($product_applicable_promotion_id) ?></a> outweighs this promotion
                                    </h4>
									<?php
}
        ?>
                            </div>

                            <div class="promotion-applicable-product-item-price">
                                <div class="regular-price">Regular/Sale price: <?php echo $currency_unit ?>
                                    <span class="promotion-product-regular-price"><?php echo $product->get_price('edit'); ?></span>
                                </div>
                                <div class="promotion-price">
                                    Promotion price: <?php echo $currency_unit ?>
                                    <span class="promotion-product-promotion-price"><?php echo $product->get_price(); ?></span>
                                </div>
                            </div>
                        </div>
						<?php
}
    ?>
                </div>
            </div>
        </div>
        <div class="promotion-container">
            <button type="button" class="promotion-btn edit-promotion-products-btn">
				<?php echo __('Edit promotion products options (Show on Homepage / Show Label)', 'riothere-all-in-one'); ?>
            </button>
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
