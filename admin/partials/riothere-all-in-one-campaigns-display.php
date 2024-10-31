<?php
wp_nonce_field('campaigns_settings_data', 'campaigns_settings_nonce');
global $pagenow;

$campaign_id = get_the_ID();
$is_new_page = in_array($pagenow, array('post-new.php'));
$selected_campaign_page_id = get_post_meta($campaign_id, 'selected_campaign_page_id', true);
$selected_campaign_page_name = get_the_title($selected_campaign_page_id);
$promotion_start_date = get_post_meta($campaign_id, 'promotion_start_date', true);
$promotion_end_date = get_post_meta($campaign_id, 'promotion_end_date', true);
$cart_price_greater_than = get_post_meta($campaign_id, 'cart_price_greater_than', true);
$cart_price_less_than = get_post_meta($campaign_id, 'cart_price_less_than', true);
$product_ids_without_sellers_excluded = Riothere_All_In_One_Campaigns::get_product_ids_without_sellers_excluded($campaign_id);
$sellers_excluded_product_ids = Riothere_All_In_One_Campaigns::get_sellers_excluded_product_ids($campaign_id);
$currency_unit = get_woocommerce_currency();
$selected_countries = get_post_meta($campaign_id, 'selected_countries', true);
$selected_customers = get_post_meta($campaign_id, 'selected_customers', true);

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
            <option value="<?php echo esc_textarea($_term->term_id) ?>"
				<?php echo esc_textarea(in_array($_term->term_id, $selectedIds)) ? 'selected' : ''; ?>
                    data-level="<?php echo esc_textarea($counter); ?>">
				<?php echo esc_textarea($_term->name); ?></option>
			<?php
renderTermSubOptions($_term, $selectedIds, $counter + 1);
        }
    }
}

function renderCampaignEditSettings($campaign_id)
{

    $selected_campaign_page_id = get_post_meta($campaign_id, 'selected_campaign_page_id', true);
    $selected_campaign_page_name = get_the_title($selected_campaign_page_id);

    $all_pages = get_pages(
        array(
            // 'sort_order' => 'asc',
            // 'sort_column' => 'post_title',
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish',
        ));

    $promotion_start_date = get_post_meta($campaign_id, 'promotion_start_date', true);
    $promotion_end_date = get_post_meta($campaign_id, 'promotion_end_date', true);
    $cart_price_greater_than = get_post_meta($campaign_id, 'cart_price_greater_than', true);
    $cart_price_less_than = get_post_meta($campaign_id, 'cart_price_less_than', true);
    $countries = new WC_Countries();
    $all_countries = $countries->get_countries();
    $all_customers = get_users();
    $selected_countries = get_post_meta($campaign_id, 'selected_countries', true);
    $selected_customers = get_post_meta($campaign_id, 'selected_customers', true);
    if ($selected_countries === '') {
        $selected_countries = [];
    }
    if ($selected_customers === '') {
        $selected_customers = [];
    }

    //array that keeps track of users who already benefitted from the staggered promotion
    ?>
    <div class="promotion-container">
        <div class="promotion-field-group">
            <div class="promotion-field promotion-field--half">
                <label for="selected_campaign_page_id"><?php echo __('Selected Page', 'riothere-all-in-one'); ?></label>
                <div class="promotion-field-input-wrapper">
                <select name="selected_campaign_page_id" id="selected_campaign_page_id"
                            class="promotion-field-input promotion-field-input--select2" >
						<?php
foreach ($all_pages as $page) {

        ?>
                            <option value="<?php echo esc_textarea($page->ID); ?>"
                            <?php echo $page->ID == $selected_campaign_page_id ? 'selected' : ''; ?>
                                    data-product="<?php echo htmlspecialchars(json_encode($page->post_title), ENT_QUOTES, 'UTF-8'); ?>"
                            >
								<?php echo esc_textarea($page->post_title); ?>

                            </option>
							<?php
}
    ?>
                    </select>
                </div>
                <div class="promotion-field-error"></div>
            </div>

        </div>
    </div>
	<?php
}

function renderCampaignEditRules($campaign_id)
{
    $give_promotion_to_new_added_items = get_post_meta($campaign_id, 'give_promotion_to_new_added_items', true);
    $filter_include_products_by_filter = get_post_meta($campaign_id, 'filter_include_products_by_filter', true);
    $shop_products = Riothere_All_In_One_Promotions::get_shop_products(); // unified function for all kinds of Promotions
    $sellers = Riothere_All_In_One_Campaigns::get_sellers();
    $shop_categories = Riothere_All_In_One_Campaigns::get_shop_parent_categories();
    $shop_tags = Riothere_All_In_One_Campaigns::get_shop_parent_tags();
    $currency_unit = get_woocommerce_currency();

    $countries = new WC_Countries();
    $all_countries = $countries->get_countries();

    $all_customers = get_users();

    // include options
    $include_products = get_post_meta($campaign_id, 'include_products', true);
    $include_sellers = get_post_meta($campaign_id, 'include_sellers', true);
    $include_product_categories = get_post_meta($campaign_id, 'include_product_categories', true);
    $include_product_tags = get_post_meta($campaign_id, 'include_product_tags', true);
    $include_product_min_price = get_post_meta($campaign_id, 'include_product_min_price', true);
    $include_product_max_price = get_post_meta($campaign_id, 'include_product_max_price', true);

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

    $exclude_products = get_post_meta($campaign_id, 'exclude_products', true);
    $exclude_sellers = get_post_meta($campaign_id, 'exclude_sellers', true);
    $exclude_product_categories = get_post_meta($campaign_id, 'exclude_product_categories', true);
    $exclude_product_tags = get_post_meta($campaign_id, 'exclude_product_tags', true);
    $exclude_product_min_price = get_post_meta($campaign_id, 'exclude_product_min_price', true);
    $exclude_product_max_price = get_post_meta($campaign_id, 'exclude_product_max_price', true);
    $filter_exclude_products_by_filter = get_post_meta($campaign_id, 'filter_exclude_products_by_filter', true);

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
                            <option value="<?php echo esc_textarea($seller->ID); ?>" <?php echo in_array($seller->ID, $include_sellers) ? 'selected' : '' ?>><?php echo esc_html($output); ?></option>
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
                            <option value="<?php echo esc_textarea($shop_category->term_id); ?>"
								<?php echo in_array($shop_category->term_id, $include_product_categories) ? 'selected' : '' ?>
                                    data-level="0"><?php echo esc_textarea($shop_category->name); ?></option>
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
                            <option value="<?php echo esc_textarea($shop_tag->term_id); ?>"
								<?php echo in_array($shop_tag->term_id, $include_product_tags) ? 'selected' : '' ?>

                                    data-level="0"><?php echo esc_textarea($shop_tag->name); ?></option>
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
                               value="<?php echo esc_textarea($include_product_min_price); ?>"
                               id="include_product_min_price" class="promotion-field-input">
                        <div class="promotion-field-input-icon">
							<?php echo esc_textarea($currency_unit); ?>
                        </div>
                    </div>
                    <div class="promotion-field-error"></div>
                </div>
                <div class="promotion-field promotion-field--half">
                    <label for="include_product_max_price"><?php echo __('Product Max Price (before tax)', 'riothere-all-in-one'); ?></label>
                    <div class="promotion-field-input-wrapper promotion-field-input-wrapper--with-left-icon">
                        <input type="number" min="0" step="0.01" name="include_product_max_price"
                               value="<?php echo esc_textarea($include_product_max_price); ?>"
                               id="include_product_max_price" class="promotion-field-input">
                        <div class="promotion-field-input-icon">
							<?php echo esc_textarea($currency_unit); ?>
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
                            <option value="<?php echo esc_textarea($shop_product['id']); ?>"
								<?php echo in_array($shop_product['id'], $include_products) ? 'selected' : ''; ?>
                                    data-product="<?php echo htmlspecialchars(json_encode($shop_product), ENT_QUOTES, 'UTF-8'); ?>"
                            >
								<?php echo esc_textarea($shop_product['sku']); ?>
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
            <!-- <span class="custom-checkbox-checkmark"></span> -->
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
                        <option value="<?php echo esc_textarea($seller->ID); ?>"
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

        <!-- To be relocated into the include rules -->

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
            <!-- <span class="custom-checkbox-checkmark"></span> -->
            <span class="custom-checkbox-label"><?php echo __('Only show me products that fit the above rules', 'riothere-all-in-one'); ?></span>
        </label>
    </div>
    <div class="promotion-container">
        <button type="button"
                class="promotion-btn campaign-filter-products-btn"><?php echo __('Show Results', 'riothere-all-in-one'); ?></button>
    </div>
    <div class="promotion-container promotion-products-list-section">
        <div class="promotion-products-list-header">

            <div><?php echo __('Image', 'riothere-all-in-one'); ?></div>
            <div><?php echo __('Product', 'riothere-all-in-one'); ?></div>
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
    </div>
	<?php
}

?>
    <h2><?php echo __('Promotions Settings', 'riothere-all-in-one'); ?></h2>
    <input type="hidden" name="is_add_mode" value="<?php echo $is_new_page ? 'true' : 'false' ?>">
    <input type="hidden" name="promotion_id" value="<?php echo get_the_ID(); ?>">

    <div class="promotion-container">
        <h3><?php echo __('General notes', 'riothere-all-in-one'); ?></h3>
        <!-- Insert select page for campaigns here TODO ahmad -->
        <ul>
            <li><?php echo __('Select the Campain page that you want to add the products to', 'riothere-all-in-one'); ?></li>
        </ul>


    </div>
<?php

if ($is_new_page) {
    ?>
    <div id="promotion-edit" class="section section-active">
        <input type="hidden" name="sellers_excluded_product_ids" id="sellers_excluded_product_ids"
               value="<?php echo htmlspecialchars(json_encode([]), ENT_QUOTES, 'UTF-8'); ?>">
		<?php
renderCampaignEditSettings($campaign_id);
    renderCampaignEditRules($campaign_id);
    ?>
    </div>
	<?php
} else {
    $include_products = get_post_meta($campaign_id, 'include_products', true);
    $include_sellers = get_post_meta($campaign_id, 'include_sellers', true);
    $include_product_categories = get_post_meta($campaign_id, 'include_product_categories', true);
    $include_product_tags = get_post_meta($campaign_id, 'include_product_tags', true);
    $include_product_min_price = get_post_meta($campaign_id, 'include_product_min_price', true);
    $include_product_max_price = get_post_meta($campaign_id, 'include_product_max_price', true);

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

    $exclude_products = get_post_meta($campaign_id, 'exclude_products', true);
    $exclude_sellers = get_post_meta($campaign_id, 'exclude_sellers', true);
    $exclude_product_categories = get_post_meta($campaign_id, 'exclude_product_categories', true);
    $exclude_product_tags = get_post_meta($campaign_id, 'exclude_product_tags', true);
    $exclude_product_min_price = get_post_meta($campaign_id, 'exclude_product_min_price', true);
    $exclude_product_max_price = get_post_meta($campaign_id, 'exclude_product_max_price', true);
    $filter_exclude_products_by_filter = get_post_meta($campaign_id, 'filter_exclude_products_by_filter', true);

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
                        <div class="promotion-setting-label"><?php echo __('Campaign Page', 'riothere-all-in-one'); ?></div>
                        <div class="promotion-setting-value"><?php echo $selected_campaign_page_name; ?></div>
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
renderCampaignEditSettings($campaign_id);
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
                    class="promotion-btn edit-promotion-rules-btn"><?php echo __('Edit campaign rules', 'riothere-all-in-one'); ?></button>
        </div>
    </div>
    <div id="promotion-edit-rules" class="section">
        <input type="hidden" name="edit-promotion-rules" value="false">
		<?php
renderCampaignEditRules($campaign_id);
    ?>
    </div>
    <div id="promotion-view-included-products" class="section section-active">
        <input type="hidden" name="promotion-view-included-products_status" value="false">
        <div class="promotion-container ">
            <h2 class="promotion-h2"><?php echo __('Included Products', 'riothere-all-in-one'); ?>
                <span>View Mode</span></h2>
            <div class="applicable-products-section-view">
                <div class="promotion-applicable-products-header">
                    <div><?php echo __('Image', 'riothere-all-in-one'); ?></div>
                    <div><?php echo __('Product', 'riothere-all-in-one'); ?></div>
                </div>
                <div class="promotion-applicable-products-list">
					<?php
foreach ($product_ids_without_sellers_excluded as $applicable_product_id) {
        $product = wc_get_product($applicable_product_id);
        $image = wp_get_attachment_image_src($product->get_image_id());
        $image_src = $image !== false ? $image[0] : '';
        $is_product_applicable = (int) $product_applicable_promotion_id === (int) $campaign_id;
        ?>
                        <div class="promotion-applicable-product-item <?php echo !$is_product_applicable ? 'not-applicable-item' : '' ?>">
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
if (!$is_product_applicable) {
            ?>

									<?php
}
        ?>
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
