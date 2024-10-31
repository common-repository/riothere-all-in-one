<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Riothere_All_In_One
 * @subpackage Riothere_All_In_One/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Riothere_All_In_One
 * @subpackage Riothere_All_In_One/admin
 */
class Riothere_All_In_One_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function riothere_handle_custom_query_var($query, $query_vars)
    {
        if (isset($query_vars['sellers_id']) && !empty($query_vars['sellers_id'])) {
            $query['meta_query'][] = array(
                'key' => 'seller',
                'value' => esc_attr($query_vars['sellers_id']),
                'compare' => '=',
            );
            unset($query_vars['sellers_id']);
        }

        return $query;
    }

    // Add Custom Dashboard Widget
    public function riothere_add_customer_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'my_custom_widget',
            'Transfer Customer To Sellers',
            array($this, 'riothere_dashboard_widget_function')
        );

        // Forcing Widget to top
        global $wp_meta_boxes;
        $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
        $my_custom_widget_backup = array('my_custom_widget' => $normal_dashboard['my_custom_widget']);
        unset($normal_dashboard['my_custom_widget']);
        $sorted_dashboard = array_merge($my_custom_widget_backup, $normal_dashboard);
        $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
    }

    // Add Custom Dashboard Widget
    public function riothere_add_sync_products_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'products_sync_widget',
            'Sync Products to OpenSearch',
            array($this, 'riothere_products_sync_widget_function')
        );

        // Forcing Widget to top
        global $wp_meta_boxes;
        $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
        $products_sync_widget_backup = array('products_sync_widget' => $normal_dashboard['products_sync_widget']);
        unset($normal_dashboard['products_sync_widget']);
        $sorted_dashboard = array_merge($products_sync_widget_backup, $normal_dashboard);
        $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
    }

    public function riothere_products_sync_widget_function()
    {
        // Display whatever you want to show.
        ?>
        <div>Products get synced to OpenSearch once every 24 hours at night but if you wish to force the syncing right away, you can click on the following button:</div>
        </br>
        <button type="button" class="button button-primary products_sync_button">Sync Now</button>
    <?php
}

    public function riothere_dashboard_widget_function()
    {

        // Display whatever you want to show.
        ?>
        Hi WordPress, I'm a custom Dashboard Widget from wp-hasty.com

        <button type="button" class="add_seller_role_to_customers_who_own_product"></button>
    <?php
}

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function riothere_enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Riothere_All_In_One_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Riothere_All_In_One_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/riothere-all-in-one-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function riothere_enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Riothere_All_In_One_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Riothere_All_In_One_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        // wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css');
        // wp_enqueue_script('jquery-ui-datepicker');

        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/riothere-all-in-one-admin.js',
            array('jquery', 'wc-enhanced-select', 'jquery-ui-datepicker'),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name,
            'riothere_global',
            array(
                'i18n_no_matches' => _x('No matches found', 'enhanced select', 'woocommerce'),
                'i18n_ajax_error' => _x('Loading failed', 'enhanced select', 'woocommerce'),
                'i18n_input_too_short_1' => _x(
                    'Please enter 1 or more characters',
                    'enhanced select',
                    'woocommerce'
                ),
                'i18n_input_too_short_n' => _x(
                    'Please enter %qty% or more characters',
                    'enhanced select',
                    'woocommerce'
                ),
                'i18n_input_too_long_1' => _x('Please delete 1 character', 'enhanced select', 'woocommerce'),
                'i18n_input_too_long_n' => _x(
                    'Please delete %qty% characters',
                    'enhanced select',
                    'woocommerce'
                ),
                'i18n_selection_too_long_1' => _x('You can only select 1 item', 'enhanced select', 'woocommerce'),
                'i18n_selection_too_long_n' => _x(
                    'You can only select %qty% items',
                    'enhanced select',
                    'woocommerce'
                ),
                'i18n_load_more' => _x('Loading more results&hellip;', 'enhanced select', 'woocommerce'),
                'i18n_searching' => _x('Searching&hellip;', 'enhanced select', 'woocommerce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('riothere'),
                'search_orders_nonce' => wp_create_nonce('search-orders'),
                'search_sellers_nonce' => wp_create_nonce('search-sellers'),
                'search_wishlists_brands_nonce' => wp_create_nonce('search-wishlist-brands'),
                'search_wishlists_colors_nonce' => wp_create_nonce('search-wishlist-colors'),
                'search_wishlists_size_nonce' => wp_create_nonce('search-wishlist-size'),
                'search_sku_nonce' => wp_create_nonce('search-skus'),

                'search_carts_brands_nonce' => wp_create_nonce('search-cart-brands'),
                'search_carts_colors_nonce' => wp_create_nonce('search-cart-colors'),
                'search_carts_size_nonce' => wp_create_nonce('search-cart-size'),

                'search_main_categories_nonce' => wp_create_nonce('search-main_categories'),
                'rest_url' => get_rest_url(),
            )
        );
    }

    public function riothere_edit_woocommerce_orders_admin_list_table_column($columns)
    {
        $new_columns = [
            'cb' => '<input type="checkbox" />',
            'riothere_oder_id' => __('Order ID', 'riothere-all-in-one'),
            'order_status' => __('Status', 'woocommerce'),
            'order_date' => __('Date', 'woocommerce'),
            'riothere_oder_items_skus' => __('Items SKUs', 'riothere-all-in-one'),
            'riothere_oder_number_of_items' => __('Number of items', 'riothere-all-in-one'),
            'riothere_oder_payment_method_title' => __('Payment Method', 'riothere-all-in-one'),
            'order_total' => __('Total', 'woocommerce'),
            'riothere_oder_buyer_email' => __('Buyer email', 'riothere-all-in-one'),
            'riothere_oder_buyer_name' => __('Buyer name', 'riothere-all-in-one'),
            'riothere_oder_promo_code' => __('Promo Code', 'riothere-all-in-one'),
            'riothere_oder_seller_names' => __('Seller Names', 'riothere-all-in-one'),
            'riothere_oder_is_in_try_buy' => __('Is in "Try & Buy"', 'riothere-all-in-one'),
            'wc_actions' => __('Actions', 'woocommerce'),

        ];

        return $new_columns;
    }

    // Our own wrapper around `wc_get_product`. It returns the products
    // in our own custom format (including applying discount on productions (if any))
    public static function get_product_data($product_id, $image_size = 'thumbnail')
    {
        $product = wc_get_product($product_id);

        // Mimic the behavior of `wc_get_product` when no product is found
        if (!$product) {
            return $product;
        }

        $product_data = $product->get_data();
        $product_data['price'] = $product->get_price();
        $product_data['on_sale'] = $product->is_on_sale();
        $product_data['images'] = array(
            array(
                'id' => $product_data['image_id'],
                'src' => wp_get_attachment_image_src($product_data['image_id'], $image_size)[0],
            ),
        );
        $image = wp_get_attachment_image_src($product->get_image_id(), $image_size);
        $image_src = $image !== false ? $image[0] : '';
        $product_data['image'] = $image_src;

        $categories_data = self::get_product_categories_data($product_id);

        $product_data['category_ids'] = $categories_data['category_ids'];
        $product_data['brand'] = $categories_data['brand'];
        $product_data['size'] = $categories_data['size'];
        $product_data['color'] = $categories_data['color'];
        $product_data['l1_category'] = $categories_data['l1_category'];
        $product_data['l2_category'] = $categories_data['l2_category'];
        $product_data['l3_category'] = $categories_data['l3_category'];
        $product_data['is_on_outlet_sale'] = $categories_data['is_on_outlet_sale'];

        return apply_filters('riothere_get_product_data', $product_data, $product);
    }

    public static function get_product_categories_data($product_id)
    {
        $category_ids = wc_get_product_term_ids($product_id, 'product_cat');

        $designer = get_term_by('slug', 'designers', 'product_cat');
        $sizes = get_term_by('slug', 'sizes', 'product_cat');
        $colors = get_term_by('slug', 'colors', 'product_cat');
        $category = get_term_by('slug', 'categories', 'product_cat');

        $brand = null;
        $size = null;
        $color = null;
        $l1_category = null;
        $l2_category = null;
        $l3_category = null;
        $is_on_outlet_sale = false;

        foreach ($category_ids as $category_id) {
            $term = get_term($category_id, 'product_cat');

            $parent_cats = get_ancestors($category_id, 'product_cat');
            $is_category_or_subcategory = false; // whether it's a category/subcategory (i.e. shoes, bags, accessories, clothes and all their children)

            foreach ($parent_cats as $parent_cat) {
                $term_internal = get_term($parent_cat, 'product_cat');
                $is_category_or_subcategory = $term_internal->slug === "categories";
            }

            if ($term->slug === "outlet-sale") {
                $is_on_outlet_sale = true;
                $is_category_or_subcategory = false; // "outlet-sale" is a child of "Categories" but should NOT be taken into account (it's an exception)
            }

            if ($designer && cat_is_ancestor_of($designer, $term)) {
                $brand = $term;
            } else if (cat_is_ancestor_of($sizes, $term)) {
                $size = $term;
            } else if ($colors && cat_is_ancestor_of($colors, $term)) {
                $color = $term;
            } else if ($is_category_or_subcategory) {
                $depth = count(get_ancestors($category_id, 'product_cat', 'taxonomy'));

                if ($depth === 1) {
                    $l1_category = $term;
                } else if ($depth === 2) {
                    $l2_category = $term;
                    $l1_category = get_term($l2_category->parent, 'product_cat');
                } else if ($depth === 3) {
                    $l3_category = $term;
                    $l2_category = get_term($l3_category->parent, 'product_cat');
                    $l1_category = get_term($l2_category->parent, 'product_cat');
                }
            }
        }

        if ($brand instanceof WP_Term) {
            $brand = $brand->name;
        } else {
            $brand = 'N/A';
        }

        if ($size instanceof WP_Term) {
            $size = $size->name;
        } else {
            $size = 'N/A';
        }

        if ($color instanceof WP_Term) {
            $color = $color->name;
        } else {
            $color = 'N/A';
        }

        if ($l1_category instanceof WP_Term) {
            $l1_category = $l1_category->name;
        } else {
            $l1_category = 'N/A';
        }

        if ($l2_category instanceof WP_Term) {
            $l2_category = $l2_category->name;
        } else {
            $l2_category = 'N/A';
        }

        if ($l3_category instanceof WP_Term) {
            $l3_category = $l3_category->name;
        } else {
            $l3_category = 'N/A';
        }

        return [
            'category_ids' => $category_ids,
            'brand' => $brand,
            'size' => $size,
            'color' => $color,
            'l1_category' => $l1_category,
            'l2_category' => $l2_category,
            'l3_category' => $l3_category,
            'is_on_outlet_sale' => $is_on_outlet_sale,
        ];
    }

    public function riothere_filter_woocommerce_product_object_response($response, $product, $request)
    {
        $product_id = $product->get_id();
        $params = $request->get_params();
        $image_size = $params['image_size'];

        $categories_data = self::get_product_categories_data($product_id);
        $response->data['category_ids'] = $categories_data['category_ids'];
        $response->data['brand'] = $categories_data['brand'];
        $response->data['size'] = $categories_data['size'];
        $response->data['color'] = $categories_data['color'];
        $response->data['l1_category'] = $categories_data['l1_category'];
        $response->data['l2_category'] = $categories_data['l2_category'];
        $response->data['l3_category'] = $categories_data['l3_category'];

        $image = wp_get_attachment_image_src($product->get_image_id(), $image_size);
        $image_src = $image !== false ? $image[0] : '';
        $response->data['image'] = $image_src;

        return $response;
    }

    public function riothere_webroom_add_wc_order_admin_list_column_content($column)
    {

        global $post;

        switch ($column) {
            case 'riothere_oder_id':{
                    // Original code can be found (woocommerce/includes/admin/list-tables/class-wc-admin-list-table-orders.php)[render_order_number_column function]
                    $order = wc_get_order($post->ID);

                    if ($order->get_status() === 'trash') {
                        echo '<strong>#' . esc_attr($order->get_order_number()) . '</strong>';
                    } else {
                        echo '<a href="#" class="order-preview" data-order-id="' . absint($order->get_id()) . '" title="' . esc_attr(__(
                            'Preview',
                            'woocommerce'
                        )) . '">' . esc_html(__('Preview', 'woocommerce')) . '</a>';
                        echo '<a href="' . esc_url(admin_url('post.php?post=' . absint($order->get_id())) . '&action=edit') . '" class="order-view"><strong>#' . esc_attr($order->get_order_number()) . '</strong></a>';
                    }
                    break;
                }
            case 'riothere_oder_items_skus':{
                    $items_skus = array();
                    $order = wc_get_order($post->ID);

                    foreach ($order->get_items() as $item) {
                        if ($item->is_type('line_item')) {
                            $product = wc_get_product($item->get_product_id());
                            if ($product instanceof WC_Product) {
                                $items_skus[] = $product->get_sku();
                            }
                        }
                    }
                    echo esc_textarea(implode(', ', $items_skus));

                    break;
                }
            case 'riothere_oder_number_of_items':{
                    $order = wc_get_order($post->ID);
                    $total_quantity = 0;
                    // Get and Loop Over Order Items
                    foreach ($order->get_items() as $item_id => $item) {
                        if ($item->is_type('line_item')) {
                            $product = wc_get_product($item->get_product_id());
                            if ($product instanceof WC_Product) {
                                $quantity = $item->get_quantity();
                                $total_quantity += $quantity;
                            }
                        }
                    }
                    echo esc_textarea($total_quantity);
                    break;
                }
            case 'riothere_oder_payment_method_title':{
                    $order = wc_get_order($post->ID);
                    echo esc_textarea($order->get_payment_method_title());
                    break;
                }
            case 'riothere_oder_buyer_email':{
                    $order = wc_get_order($post->ID);
                    echo esc_textarea($order->get_billing_email());
                    break;
                }
            case 'riothere_oder_buyer_name':{
                    $order = wc_get_order($post->ID);
                    echo esc_textarea($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
                    break;
                }
            case 'riothere_oder_promo_code':{
                    $order = wc_get_order($post->ID);
                    $promo_codes = $order->get_coupon_codes();
                    echo esc_textarea(implode(', ', $promo_codes));
                    break;
                }
            case 'riothere_oder_seller_names':{
                    $order = wc_get_order($post->ID);
                    $sellers = [];
                    // Get and Loop Over Order Items
                    foreach ($order->get_items() as $item_id => $item) {
                        if ($item->is_type('line_item')) {
                            $product_id = $item->get_product_id();
                            $product = wc_get_product($product_id);
                            if ($product instanceof WC_Product) {
                                $seller = get_field('seller', $product_id);

                                if (is_array($seller)) {
                                    $customer_id = $seller['ID'];
                                    $customer_name = $seller['user_firstname'] . ' ' . $seller['user_lastname'];
                                    $customer_edit_profile_link = get_edit_user_link($customer_id);
                                    $sellers[] = sprintf(
                                        '<a href="%s">%s</a>',
                                        $customer_edit_profile_link,
                                        $customer_name
                                    );
                                }
                            }
                        }
                    }
                    $sellers = array_unique($sellers);
                    echo esc_textarea(implode(', ', $sellers));
                    break;
                }
            case "riothere_oder_is_in_try_buy":{
                    $order = wc_get_order($post->ID);
                    $is_try_and_buy = false;
                    if ($order->has_status('try-and-buy')) {
                        $is_try_and_buy = true;
                    }
                    // Get and Loop Over Order Items
                    // foreach ($order->get_items() as $item_id => $item) {
                    //     if ($item->is_type('line_item')) {
                    //         $product_id = $item->get_product_id();
                    //         $product = wc_get_product($product_id);
                    //         if ($product instanceof WC_Product) {
                    //             if (get_field('try_and_buy', $product_id)) {
                    //                 $is_try_and_buy = true;
                    //                 break;
                    //             }
                    //         }
                    //     }
                    // }
                    if ($is_try_and_buy) {
                        echo 'True';
                    } else {
                        echo '-';
                    }
                    break;
                }
            default:{
                    break;
                }
        }
    }

    public function riothere_sort_edit_woocommerce_orders_admin_list_table_column($columns)
    {

        $custom = [
            'order_status' => 'order_status',
            'riothere_oder_items_skus' => 'riothere_oder_items_skus',
            'riothere_oder_number_of_items' => 'riothere_oder_number_of_items',
            'riothere_oder_payment_method_title' => 'riothere_oder_payment_method_title',
            'riothere_oder_buyer_email' => 'riothere_oder_buyer_email',
            'riothere_oder_buyer_name' => 'riothere_oder_buyer_name',
            'riothere_oder_promo_code' => 'riothere_oder_promo_code',
        ];

        return wp_parse_args($custom, $columns);
    }

    public function riothere_sort_edit_woocommerce_wishlists_admin_list_table_column($columns)
    {

        $custom = [
            'user_id' => 'user_id',
            'user_email' => 'user_email',
            'user_fullname' => 'user_fullname',
            'date_modified' => 'date_modified',
            'number_of_products' => 'number_of_products',
        ];

        return wp_parse_args($custom, $columns);
    }

    public function riothere_add_customer_info_after_order_itemmeta($item_id, $item, $product)
    {
        // Only for "line item" order items
        if (!$item->is_type('line_item') || !is_admin()) {
            return;
        }
        $product_id = $product->get_id();
        $seller = get_field('seller', $product_id);

        if (is_array($seller)) {
            $customer_id = $seller['ID'];
            $customer_name = $seller['user_firstname'] . ' ' . $seller['user_lastname'];
            $customer_edit_profile_link = get_edit_user_link($customer_id);
            $customer_email = $seller['user_email'];
        }
        echo sprintf(
            '<div class="wc-order-item-seller"><strong>Seller:</strong> <a href="%s">%s</a></div>',
            esc_url($customer_edit_profile_link),
            esc_html($customer_name)
        );
        echo sprintf(
            '<div class="wc-order-item-seller-email"><strong>Seller Email:</strong> <a href="mailto:%s">%s</a></div>',
            esc_html($customer_email),
            esc_html($customer_email)
        );
        // Only for backend and  for product ID 123
    }

    public function riothere_rename_order_status_msg($order_statuses)
    {
        /**
         * ## Original status
         * "wc-pending": "Pending payment",
         * "wc-processing": "Processing",
         * "wc-on-hold": "On hold",
         * "wc-completed": "Completed",
         * "wc-§cancelled": "Cancelled",
         * "wc-refunded": "Refunded",
         * "wc-failed": "Failed"
         *
         * "wc-" is required and when checking for order status on the frontend remove "wc-"
         * */
        $order_statuses['wc-pending'] = _x('Pending', 'Order status', 'riothere-all-in-one');
        $order_statuses['wc-processing'] = _x('Processing', 'Order status', 'riothere-all-in-one');
        $order_statuses['wc-shipped'] = _x('Shipped', 'Order status', 'riothere-all-in-one');
        $order_statuses['wc-completed'] = _x('Delivered', 'Order status', 'riothere-all-in-one');
        $order_statuses['wc-failed'] = _x('Order Failed', 'Order status', 'riothere-all-in-one');
        $order_statuses['wc-cancelled'] = _x('Order Cancelled', 'Order status', 'riothere-all-in-one');
        $order_statuses['wc-refunded'] = _x('Refunded', 'Order status', 'riothere-all-in-one');
        $order_statuses['wc-returned'] = _x('Returned', 'Order status', 'riothere-all-in-one');

        return $order_statuses;
    }

    public function riothere_register_custom_statuses()
    {
        register_post_status('wc-shipped', array(
            'label' => _x('Shipped', 'Order status', 'riothere-all-in-one'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Shipped <span class="count">(%s)</span>', 'Shipped<span class="count">(%s)</span>', 'woocommerce'),
        ));

        register_post_status('wc-returned', array(
            'label' => _x('Returned', 'Order status', 'riothere-all-in-one'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Returned <span class="count">(%s)</span>', 'Returned<span class="count">(%s)</span>', 'woocommerce'),
        ));
    }

    public function riothere_woocommerce_admin_reports($reports)
    {

        $reports['customers']['reports']['customer_list']['callback'] = array($this, 'customer_list_get_report');

        return $reports;
    }

    public function customer_list_get_report($name)
    {

        $class = 'My_WC_Report_Customer_List';

        do_action('riothere_class_wc_report_customer_list');

        if (!class_exists($class)) {
            return;
        }

        $report = new $class();
        $report->output_report();
    }

    public function riothere_class_wc_report_customer_list()
    {
        /**
         * The class responsible for defining WC reports/customers/list changes in the admin side
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-riothere-wc-report-customer-list.php';
    }

    public function riothere_add_wc_information_to_user_edit_section($profileuser)
    {
        $wishlist = get_posts([
            'post_type' => 'wishlists',
            'status' => 'publish',
            'nopaging' => true,
            'author' => $profileuser->ID,
        ]);

        ?>
        <div class="profile-edit-woocommerce-info">
            <div class="fields">
                <div class="field">
                    <div class="label">Name (Last, First): </div>
                    <div class="value"><?php echo esc_html($this->getUserInformation($profileuser, 'user_id')); ?></div>
                </div>
                <div class="field">
                    <div class="label">Username: </div>
                    <div class="value"><?php echo esc_html($this->getUserInformation($profileuser, 'username')); ?></div>
                </div>
                <div class="field">
                    <div class="label">Location: </div>
                    <div class="value"><?php echo esc_html($this->getUserInformation($profileuser, 'location')); ?></div>
                </div>
                <div class="field">
                    <div class="label">Last login date: </div>
                    <div class="value"><?php echo esc_html($this->getUserInformation($profileuser, 'last_active')); ?></div>
                </div>
                <div class="field">
                    <div class="label">Email: </div>
                    <div class="value"><?php echo esc_html($this->getUserInformation($profileuser, 'email')); ?></div>
                </div>
                <div class="field">
                    <div class="label">Spent: </div>
                    <div class="value"><?php echo esc_html($this->getUserInformation($profileuser, 'spent')); ?></div>
                </div>
                <div class="field">
                    <div class="label">Previous orders: </div>
                    <div class="value"><?php echo esc_html($this->getUserInformation($profileuser, 'orders')); ?></div>
                </div>
                <div class="field">
                    <div class="label">Last order: </div>
                    <div class="value"><?php echo esc_html($this->getUserInformation($profileuser, 'last_order')); ?></div>
                </div>
                <div class="field">
                    <div class="label">Is subscribed to newsletter: </div>
                    <div class="value"><?php echo esc_html($this->getUserInformation(
            $profileuser,
            'email_subscription_status')
        ); ?></div>
                </div>

                <div class="field">
                    <div class="label">Items sold: </div>
                    <div class="value"><?php echo esc_html($this->get_SKUs_of_sold_items($profileuser->ID)); ?></div>
                </div>

                <div class="field">
                    <div class="label">Number of Listed Items: </div>
                    <div class="value"><?php echo esc_html($this->get_listed_items($profileuser->ID))['found_posts']; ?></div>
                </div>

                <div class="field">
                    <div class="label">Amount generated in sales: </div>
                    <div class="value"><?php echo esc_html(wc_price($this->calculate_amount_generated_in_sales($profileuser->ID))); ?></div>
                </div>

                <div class="field">
                    <div class="label">Wishlist # : </div>
                    <div class="value"><?php
if (count($wishlist) != 0) {
            $url = get_site_url() . "/wp-admin/post.php?post=" . $wishlist[0]->ID . "&action=edit";
            echo '<a href="' . esc_url($url) . '" target = "_blank">' . esc_html($wishlist[0]->ID) . '</a>';
        } else {
            echo 'No wishlist found';
        }
        ?></div>
                </div>

            </div>
        </div>
        <?php
}

    private function getUserInformation($user, $field)
    {
        switch ($field) {

            case 'customer_name':
                if ($user->last_name && $user->first_name) {
                    return $user->last_name . ', ' . $user->first_name;
                } else {
                    return '-';
                }

            case 'username':
                return $user->user_login;

            case 'location':
                $state_code = get_user_meta($user->ID, 'shipping_state', true);
                $country_code = get_user_meta($user->ID, 'shipping_country', true);

                $state = isset(WC()->countries->states[$country_code][$state_code]) ? WC()->countries->states[$country_code][$state_code] : $state_code;
                $country = isset(WC()->countries->countries[$country_code]) ? WC()->countries->countries[$country_code] : $country_code;

                $value = '';

                if ($state) {
                    $value .= $state . ', ';
                }

                $value .= $country;

                if ($value) {
                    return $value;
                } else {
                    return '-';
                }

            case 'email':
                return '<a href="mailto:' . $user->user_email . '">' . $user->user_email . '</a>';

            case 'spent':
                return wc_price(wc_get_customer_total_spent($user->ID));

            case 'orders':
                $orders = wc_get_orders(
                    array(
                        'status' => array_map('wc_get_order_status_name', wc_get_is_paid_statuses()),
                        'customer' => $user->ID,
                    )
                );

                if (!empty($orders)) {
                    $output = [];
                    $show_view_all = count($orders) > 3;

                    if ($show_view_all) {
                        $orders = array_slice($orders, 0, 3);
                    }

                    foreach ($orders as $order) {

                        $output[] = '<a href="' . admin_url('post.php?post=' . $order->get_id() . '&action=edit') . '">' . _x(
                            '#',
                            'hash before order number',
                            'woocommerce'
                        ) . $order->get_order_number() . '</a>';
                    }

                    $user_orders_url = admin_url('edit.php?s&post_status=all&post_type=shop_order&_customer_user=' . $user->ID);

                    return implode(', ', $output) . ($show_view_all ? " --- <a href='$user_orders_url'>View all orders by $user->first_name</a>" : '');
                }

                return '-';
                break;

            case 'last_order':
                $orders = wc_get_orders(
                    array(
                        'limit' => 1,
                        'status' => array_map('wc_get_order_status_name', wc_get_is_paid_statuses()),
                        'customer' => $user->ID,
                    )
                );

                if (!empty($orders)) {
                    $order = $orders[0];

                    return '<a href="' . admin_url('post.php?post=' . $order->get_id() . '&action=edit') . '">' . _x(
                        '#',
                        'hash before order number',
                        'woocommerce'
                    ) . $order->get_order_number() . '</a> &ndash; ' . wc_format_datetime($order->get_date_created());
                }

                return '-';

                break;
            case 'last_active':
                try {
                    $customer = new WC_Customer($user->ID);
                    $last_active = $customer->get_meta('wc_last_active', true, 'edit');
                    if ($last_active) {
                        return gmdate('Y-m-d', $last_active);
                    }

                    return '-';
                } catch (Exception $e) {
                    return '';
                }

            case 'email_subscription_status':{
                    $subscriber = NewsletterUsers::instance()->get_user($user->user_email);
                    if (!is_null($subscriber)) {
                        return 'Subscribed';
                    } else {
                        return 'Not subscribed';
                    }
                }
        }

        return '';
    }
    public function riothere_filter_orders_by_specific_order_id_query($query)
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'shop_order' && $pagenow === 'edit.php' && $post_type === 'shop_order' && isset($_GET['_order_id']) && !empty($_GET['_order_id'])) {
            $query->query_vars['post__in'] = [absint($_GET['_order_id'])];
        }

        return $query;
    }

    public function riothere_filter_orders_by_specific_order_id_html()
    {
        global $typenow;
        global $wp_query;
        if ($typenow === 'shop_order') {
            $order_string = '';
            $order_id = '';

            if (!empty($_GET['_order_id'])) {
                $order_id = absint($_GET['_order_id']); // WPCS: input var ok, sanitization ok.
                $order = new WC_Order($order_id);

                $order_string = sprintf(
                    /* translators: 1: Order ID 2: customer name 3: user customer email */
                    esc_html__('#%1$s ( %2$s &ndash; %3$s)', 'woocommerce'),
                    absint($order->ID),
                    $order->get_billing_first_name() . ' ' . $order->get_billing_first_name(),
                    $order->get_billing_email()
                );
            }
            ?>
            <select class="wc-riothere-orders-search" name="_order_id" data-placeholder="<?php esc_attr_e('Filter by Order ID', 'woocommerce');?>" data-allow_clear="true">
                <option value="<?php echo esc_attr($order_id); ?>" selected="selected"><?php echo htmlspecialchars(wp_kses_post($order_string)); ?></option>
            </select>
        <?php
}
    }

    public function riothere_json_search_orders()
    {
        ob_start();

        check_ajax_referer('search-orders', 'security');

        if (!current_user_can('edit_shop_orders')) {
            wp_die(-1);
        }

        $term = isset($_GET['term']) ? (string) wc_clean(wp_unslash($_GET['term'])) : '';
        $limit = 0;

        if (empty($term)) {
            wp_die();
        }

        $ids = array();
        // Search by ID.
        if (is_numeric($term)) {
            global $wpdb;
            $ids = [];

            // Get all the IDs you want to choose from
            $sql = $wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE ID LIKE CONCAT('%',%d,'%') AND post_type='shop_order'",
                $term
            );

            $results = $wpdb->get_results($sql);

            // Convert the IDs from row objects to an array of IDs
            foreach ($results as $row) {
                $ids[] = $row->ID;
            }
        }

        // Usernames can be numeric so we first check that no users was found by ID before searching for numeric username, this prevents performance issues with ID lookups.
        if (empty($ids)) {
            $data_store = WC_Data_Store::load('order');

            // If search is smaller than 3 characters, limit result set to avoid
            // too many rows being returned.
            if (3 > strlen($term)) {
                $limit = 20;
            }
            $ids = $data_store->search_orders($term, $limit);
        }

        $found_orders = array();

        if (!empty($_GET['exclude'])) {
            $ids = array_diff($ids, array_map('absint', (array) wp_unslash($_GET['exclude'])));
        }

        foreach ($ids as $id) {
            $order = new WC_Order($id);
            /* translators: 1: user display name 2: user ID 3: user email */
            $found_orders[$id] = sprintf(
                /* translators: $1: customer name, $2 customer id, $3: customer email */
                esc_html__('#%1$s (%2$s &ndash; %3$s)', 'woocommerce'),
                $order->get_id(),
                $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                $order->get_billing_email()
            );
        }

        wp_send_json(apply_filters('woocommerce_json_search_found_orders', $found_orders));
    }

    public function riothere_filter_orders_by_specific_seller_id_query($query)
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';

        if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'shop_order' && $pagenow === 'edit.php' && $post_type === 'shop_order' && isset($_GET['_seller_id']) && !empty($_GET['_seller_id'])) {
            $query->query_vars['seller_id'] = absint($_GET['_seller_id']);

        }

        return $query;
    }

    public function riothere_filter_orders_by_specific_seller_id_html()
    {
        global $typenow;
        if ($typenow === 'shop_order') {
            $seller_string = '';
            $seller_id = '';

            if (!empty($_GET['_seller_id'])) {
                $seller_id = absint($_GET['_seller_id']); // WPCS: input var ok, sanitization ok.
                $seller = new WP_User($seller_id);

                $seller_string = sprintf(
                    /* translators: 1: Seller ID 2: customer name 3: user customer email */
                    esc_html__('#%1$s ( %2$s &ndash; %3$s)', 'woocommerce'),
                    absint($seller->ID),
                    $seller->first_name . ' ' . $seller->last_name,
                    $seller->user_email
                );
            }
            ?>
            <select class="wc-riothere-sellers-search" name="_seller_id" data-placeholder="<?php esc_attr_e('Filter by Seller', 'woocommerce');?>" data-allow_clear="true">
                <option value="<?php echo esc_attr($seller_id); ?>" selected="selected"><?php echo htmlspecialchars(wp_kses_post($seller_string)); ?></option>
            </select>
        <?php
}
    }

    /**
     * SQL Like operator in PHP.
     * Returns TRUE if match else FALSE.
     *
     * @param  string  $str
     * @param  string  $searchTerm
     *
     * @return bool
     */
    public static function like_match($str, $searchTerm): bool
    {
        $searchTerm = strtolower($searchTerm);
        $str = strtolower($str);
        $pos = strpos($str, $searchTerm);

        return !($pos === false);
    }

    public function riothere_json_search_sellers()
    {
        ob_start();

        check_ajax_referer('search-sellers', 'security');

        if (!current_user_can('edit_shop_orders')) {
            wp_die(-1);
        }

        $term = isset($_GET['term']) ? (string) wc_clean(wp_unslash($_GET['term'])) : '';

        if (empty($term)) {
            wp_die();
        }

        $sellers = [];
        // $order_ids = wc_get_orders([
        //     'return' => 'ids',
        // ]);

        // foreach ($order_ids as $order_id) {
        //     $order = wc_get_order($order_id);
        //     // Get and Loop Over Order Items
        //     foreach ($order->get_items() as $item_id => $item) {
        //         $product_id = $item->get_product_id();

        //         $seller = get_field('seller', $product_id);

        //         $id_term_exist = $this->like_match($seller['ID'], $term);
        //         $user_firstname_term_exist = $this->like_match($seller['user_firstname'], $term);
        //         $user_lastname_term_exist = $this->like_match($seller['user_lastname'], $term);
        //         $user_email_term_exist = $this->like_match($seller['user_email'], $term);
        //         $display_name_term_exist = $this->like_match($seller['display_name'], $term);

        //         if ($id_term_exist || $user_firstname_term_exist || $user_lastname_term_exist || $user_email_term_exist || $display_name_term_exist) {
        //             $sellers[] = $seller;
        //         }
        //     }
        // }

        // $sellers = array_unique($sellers);

        // $found_sellers = array();

        // foreach ($sellers as $seller) {
        //     /* translators: 1: user display name 2: user ID 3: user email */
        //     $found_sellers[$seller['ID']] = sprintf(
        //         /* translators: $1: customer name, $2 customer id, $3: customer email */
        //         esc_html__('#%1$s (%2$s &ndash; %3$s)', 'woocommerce'),
        //         $seller['ID'],
        //         $seller['display_name'],
        //         $seller['user_email']
        //     );
        // }

        $found_sellers = array();
        $matched_sellers = array();
        $sellers = get_users(['role' => 'seller']);

        foreach ($sellers as $seller) {
            $id_term_exist = $this->like_match($seller->ID, $term);
            $user_firstname_term_exist = $this->like_match($seller->user_firstname, $term);
            $user_lastname_term_exist = $this->like_match($seller->user_lastname, $term);
            $user_email_term_exist = $this->like_match($seller->user_email, $term);
            $display_name_term_exist = $this->like_match($seller->display_name, $term);

            if ($id_term_exist || $user_firstname_term_exist || $user_lastname_term_exist || $user_email_term_exist || $display_name_term_exist) {
                $matched_sellers[] = $seller;
            }
        }

        foreach ($matched_sellers as $seller) {
            /* translators: 1: user display name 2: user ID 3: user email */
            $found_sellers[$seller->ID] = sprintf(
                /* translators: $1: customer name, $2 customer id, $3: customer email */
                esc_html__('#%1$s (%2$s &ndash; %3$s)', 'woocommerce'),
                $seller->ID,
                $seller->display_name,
                $seller->user_email
            );
        }

        wp_send_json(apply_filters('woocommerce_json_search_found_sellers', $found_sellers));
    }

    public function riothere_seller_filter_clauses($clauses, $wp_query)
    {
        if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'shop_order' && isset($wp_query->query_vars['seller_id'])) {

            $seller_id = $wp_query->query_vars['seller_id'];
            global $wpdb;
            $clauses['join'] .= " JOIN {$wpdb->prefix}woocommerce_order_items as seller_order_items on seller_order_items.order_id = {$wpdb->posts}.ID ";
            $clauses['join'] .= " JOIN {$wpdb->prefix}woocommerce_order_itemmeta as seller_order_itemmeta on seller_order_itemmeta.order_item_id = seller_order_items.order_item_id AND seller_order_itemmeta.meta_key ='_product_id'";
            $clauses['join'] .= " JOIN {$wpdb->prefix}postmeta as seller_postmeta on seller_postmeta.post_id = seller_order_itemmeta.meta_value AND seller_postmeta.meta_key ='seller' AND seller_postmeta.meta_value = $seller_id";

            $clauses['distinct'] = 'DISTINCT';
        }

        return $clauses;
    }

    public function riothere_filter_orders_by_try_and_buy()
    {
        global $typenow;
        global $wp_query;
        if ($typenow === 'shop_order') { // Your custom post type slug
            $options = array(
                'Not in Try & Buy',
                'In Try & Buy',
            );
            $current_plugin = '';
            if (isset($_GET['try_and_buy'])) {
                $current_plugin = sanitize_text_field($_GET['try_and_buy']); // Check if option has been selected
            }
            ?>
            <select name="try_and_buy" id="try_and_buy">
                <option value="all" <?php selected('all', $current_plugin);?>><?php _e(
                'Try & Buy (All) ',
                'wisdom-plugin'
            );?></option>
                <?php
foreach ($options as $key => $value) {
                ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $current_plugin);?>>
                        <?php echo esc_attr($value); ?>
                    </option>
                <?php
}
            ?>
            </select>
        <?php
}
    }

    public function riothere_filter_orders_by_try_and_buy_query($query)
    {
        global $pagenow;
        // Get the post type
        $post_type = $_GET['post_type'] ?? '';
        if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'shop_order' && $pagenow === 'edit.php' && $post_type === 'shop_order' && isset($_GET['try_and_buy']) && $_GET['try_and_buy'] !== 'all') {
            $query->query_vars['order_is_in_try_and_buy'] = sanitize_text_field($_GET['try_and_buy']);
        }
    }

    public function riothere_filter_orders_by_try_and_buy_clauses($clauses, $wp_query)
    {
        if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'shop_order' && isset($wp_query->query_vars['order_is_in_try_and_buy'])) {

            $order_is_in_try_and_buy = $wp_query->query_vars['order_is_in_try_and_buy'];
            global $wpdb;
            $clauses['join'] .= " JOIN {$wpdb->prefix}woocommerce_order_items as try_and_buy_order_items on try_and_buy_order_items.order_id = {$wpdb->posts}.ID ";
            $clauses['join'] .= " JOIN {$wpdb->prefix}woocommerce_order_itemmeta as try_and_buy_order_itemmeta on try_and_buy_order_itemmeta.order_item_id = try_and_buy_order_items.order_item_id AND try_and_buy_order_itemmeta.meta_key ='_product_id'";
            $clauses['join'] .= " JOIN {$wpdb->prefix}postmeta as try_and_buy_postmeta on try_and_buy_postmeta.post_id = try_and_buy_order_itemmeta.meta_value AND try_and_buy_postmeta.meta_key ='try_and_buy' AND try_and_buy_postmeta.meta_value = $order_is_in_try_and_buy";

            $clauses['distinct'] = 'DISTINCT';
        }

        return $clauses;
    }

    public function riothere_filter_orders_by_price_range()
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        $from = (isset($_GET['orderTotalFrom']) && $_GET['orderTotalFrom']) ? abs($_GET['orderTotalFrom']) : '';
        $to = (isset($_GET['orderTotalTo']) && $_GET['orderTotalTo']) ? abs($_GET['orderTotalTo']) : '';
        if (is_admin() && $pagenow === 'edit.php' && $post_type === 'shop_order') {
            ?>
            <div class="riothere-price-range">
                <div class="filters-container">
                    <div class="input-container">
                        <input type="number" name="orderTotalFrom" id="orderTotalFrom" placeholder="Total From" value="<?php echo esc_attr($from); ?>" />
                    </div>
                    <div class="input-container">
                        <input type="number" name="orderTotalTo" id="orderTotalTo" placeholder="Total to" value="<?php echo esc_attr($to) ?>" />
                    </div>
                </div>
            </div>
        <?php
}
    }

    public function riothere_filter_orders_by_price_range_query($query)
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'shop_order' && $pagenow === 'edit.php' && $post_type === 'shop_order' && isset($_GET['orderTotalFrom']) && !empty($_GET['orderTotalFrom'])) {
            $query->query_vars['orderTotalFrom'] = abs($_GET['orderTotalFrom']);
        }
        if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'shop_order' && $pagenow === 'edit.php' && $post_type === 'shop_order' && isset($_GET['orderTotalTo']) && !empty($_GET['orderTotalTo'])) {
            $query->query_vars['orderTotalTo'] = abs($_GET['orderTotalTo']);
        }

        return $query;
    }

    public function riothere_filter_orders_by_price_range_query_clauses($clauses, $wp_query)
    {
        if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'shop_order') {

            if (isset($wp_query->query_vars['orderTotalFrom']) || isset($wp_query->query_vars['orderTotalTo'])) {

                global $wpdb;
                $clauses['join'] .= " JOIN {$wpdb->prefix}postmeta as filter_postmeta on filter_postmeta.post_id = {$wpdb->posts}.ID AND filter_postmeta.meta_key= '_order_total'";

                $clauses['distinct'] = 'DISTINCT';
            }

            if (isset($wp_query->query_vars['orderTotalFrom'])) {
                // $from_query $to_query
                $orderTotalFrom = $wp_query->query_vars['orderTotalFrom'];
                $from_query = !empty($orderTotalFrom) ? " AND filter_postmeta.meta_value >= $orderTotalFrom " : ' ';
                $clauses['join'] .= $from_query;
            }

            if (isset($wp_query->query_vars['orderTotalTo'])) {
                $orderTotalTo = $wp_query->query_vars['orderTotalTo'];
                $to_query = !empty($orderTotalTo) ? " AND filter_postmeta.meta_value <= $orderTotalTo " : ' ';
                $clauses['join'] .= $to_query;
            }
        }

        return $clauses;
    }

    public function riothere_months_dropdown_results_reset_values($months)
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        if (is_admin() && $pagenow === 'edit.php' && ($post_type === 'shop_order' || $post_type === 'product')) {
            return [];
        } else {
            return $months;
        }
    }

    public function riothere_filter_orders_by_create_date_range()
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        $from = (isset($_GET['riothereDateFrom']) && $_GET['riothereDateFrom']) ? sanitize_text_field($_GET['riothereDateFrom']) : '';
        $to = (isset($_GET['riothereDateTo']) && $_GET['riothereDateTo']) ? sanitize_text_field($_GET['riothereDateTo']) : '';
        if (is_admin() && $pagenow === 'edit.php' && $post_type === 'shop_order') {
            ?>
            <div class="filter-date">
                <input type="text" class="riothere-date-from" name="riothereDateFrom" placeholder="Date From" value="<?php echo esc_attr($from); ?>" />
                <input type="text" class="riothere-date-to" name="riothereDateTo" placeholder="Date To" value="<?php echo esc_attr($to); ?>" />
            </div>
        <?php
}
    }

    public function riothere_filter_orders_by_create_date_range_query($query)
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        if (
            is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'shop_order' && $pagenow === 'edit.php' && $post_type === 'shop_order'
            && ((isset($_GET['riothereDateFrom']) && !empty($_GET['riothereDateFrom'])) || (isset($_GET['riothereDateTo']) && !empty($_GET['riothereDateTo'])))
        ) {
            $query->query_vars['date_query'] = array(
                'inclusive' => true, // include the selected days as well
                'column' => 'post_date', // 'post_modified', 'post_date_gmt', 'post_modified_gmt'
            );

            if (isset($_GET['riothereDateFrom']) && !empty($_GET['riothereDateFrom'])) {
                $query->query_vars['date_query']['after'] = sanitize_text_field($_GET['riothereDateFrom']);
            }

            if (isset($_GET['riothereDateTo']) && !empty($_GET['riothereDateTo'])) {
                $query->query_vars['date_query']['before'] = sanitize_text_field($_GET['riothereDateTo']);
            }
        }
        return $query;
    }

    public function riothere_filter_orders_by_specific_brand_id_html()
    {
        global $typenow;
        global $wp_query;
        if ($typenow === 'shop_order') {
            $brand_string = '';
            $brand_id = '';

            if (!empty($_GET['_brand_id'])) {
                $brand_id = absint($_GET['_brand_id']); // WPCS: input var ok, sanitization ok.
                $brand = get_term($brand_id, 'product_cat');

                $brand_string = sprintf(
                    /* translators: 1: Brand ID 2: brand name */
                    esc_html__('#%1$s - %2$s', 'woocommerce'),
                    absint($brand->term_id),
                    $brand->name
                );
            }
            ?>
            <select class="wc-riothere-brands-search" name="_brand_id" data-placeholder="<?php esc_attr_e('Filter by Brand', 'woocommerce');?>" data-allow_clear="true">
                <option value="<?php echo esc_attr($brand_id); ?>" selected="selected"><?php echo htmlspecialchars(wp_kses_post($brand_string)); ?></option>
            </select>
        <?php
}
    }

    public function riothere_json_search_brands()
    {
        ob_start();

        check_ajax_referer('search-brands', 'security');

        if (!current_user_can('edit_shop_orders')) {
            wp_die(-1);
        }

        $term = isset($_GET['term']) ? (string) wc_clean(wp_unslash($_GET['term'])) : '';

        if (empty($term)) {
            wp_die();
        }

        $filtered_brands = [];

        $parent_category = get_term_by('slug', 'designers', 'product_cat');
        $brands = get_terms([
            'product_cat',
        ], [
            'parent' => $parent_category->term_id,
            'child_of' => $parent_category->term_id,
        ]);

        foreach ($brands as $brand) {

            $brand_id_exist = $this->like_match($brand->term_id, $term);
            $brand_name_exist = $this->like_match($brand->name, $term);
            $brand_slug_exist = $this->like_match($brand->slug, $term);

            if ($brand_id_exist || $brand_name_exist || $brand_slug_exist) {
                $filtered_brands[] = $brand;
            }
        }

        $found_brands = array();

        foreach ($filtered_brands as $brand) {
            /* translators: 1: user display name 2: user ID 3: user email */
            $found_brands[$brand->term_id] = sprintf(
                /* translators: $1: Brand id, $2 Brand name */
                esc_html__('#%1$s - %2$s', 'woocommerce'),
                $brand->term_id,
                $brand->name
            );
        }

        wp_send_json(apply_filters('woocommerce_json_search_found_brands', $found_brands));
    }

    public function riothere_filter_orders_by_specific_brand_id_query($query)
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'shop_order' && $pagenow === 'edit.php' && $post_type === 'shop_order' && isset($_GET['_brand_id']) && !empty($_GET['_brand_id'])) {
            $query->query_vars['_brand_id'] = absint($_GET['_brand_id']);
        }

        return $query;
    }

    public function riothere_filter_orders_by_specific_brand_id_clauses($clauses, $wp_query)
    {
        global $wpdb;

        if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'shop_order' && isset($wp_query->query_vars['_brand_id'])) {

            $brand_id = $wp_query->query_vars['_brand_id'];

            $args = [
                'post_type' => 'product',
                'numberposts' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $brand_id, /// Where term_id of Term 1 is "1".
                        'include_children' => true,
                    ),
                ),
            ];

            $products = get_posts($args);
            $product_ids = [];
            foreach ($products as $product) {
                $product_ids[] = $product->ID;
            }
            if (count($product_ids) === 0) {
                $product_ids[] = 0; // for the query not to fail when IN () is empty
            }
            $clauses['join'] .= " JOIN {$wpdb->prefix}woocommerce_order_items as brand_order_items on brand_order_items.order_id = {$wpdb->posts}.ID ";
            $clauses['join'] .= " JOIN {$wpdb->prefix}woocommerce_order_itemmeta as brand_order_itemmeta on brand_order_itemmeta.order_item_id = brand_order_items.order_item_id AND brand_order_itemmeta.meta_key ='_product_id'";
            $clauses['join'] .= " JOIN {$wpdb->prefix}postmeta as brand_postmeta on brand_postmeta.post_id = brand_order_itemmeta.meta_value AND brand_postmeta.post_id IN (" . implode(
                ', ',
                $product_ids
            ) . ")";

            $clauses['distinct'] = 'DISTINCT';
        }

        return $clauses;
    }

    public function riothere_filter_orders_by_specific_main_category_id_html()
    {
        global $typenow;
        global $wp_query;
        if ($typenow === 'shop_order') {
            $main_category_string = '';
            $main_category_id = '';

            if (!empty($_GET['_main_category_id'])) {
                $main_category_id = absint($_GET['_main_category_id']); // WPCS: input var ok, sanitization ok.
                $main_category = get_term($main_category_id, 'product_cat');

                $main_category_string = sprintf(
                    /* translators: 1: Brand ID 2: brand name */
                    esc_html__('#%1$s - %2$s', 'woocommerce'),
                    absint($main_category->term_id),
                    $main_category->name
                );
            }
            ?>
            <select class="wc-riothere-main-categories-search" name="_main_category_id" data-placeholder="<?php esc_attr_e('Filter by Main Category', 'woocommerce');?>" data-allow_clear="true">
                <option value="<?php echo esc_attr($main_category_id); ?>" selected="selected"><?php echo htmlspecialchars(wp_kses_post($main_category_string)); ?></option>
            </select>
        <?php
}
    }

    public function riothere_json_search_main_categories()
    {
        ob_start();

        check_ajax_referer('search-main_categories', 'security');

        if (!current_user_can('edit_shop_orders')) {
            wp_die(-1);
        }

        $term = isset($_GET['term']) ? (string) wc_clean(wp_unslash($_GET['term'])) : '';

        if (empty($term)) {
            wp_die();
        }

        $filtered_main_categories = [];

        $parent_category = get_term_by('slug', 'categories', 'product_cat');
        $main_categories = get_terms([
            'product_cat',
        ], [
            'hide_empty' => false,
            'parent' => $parent_category->term_id,
            'child_of' => $parent_category->term_id,
        ]);

        foreach ($main_categories as $main_category) {

            $main_category_id_exist = $this->like_match($main_category->term_id, $term);
            $main_category_name_exist = $this->like_match($main_category->name, $term);
            $main_category_slug_exist = $this->like_match($main_category->slug, $term);

            if ($main_category_id_exist || $main_category_name_exist || $main_category_slug_exist) {
                $filtered_main_categories[] = $main_category;
            }
        }

        $found_main_categories = array();

        foreach ($filtered_main_categories as $main_category) {
            /* translators: 1: user display name 2: user ID 3: user email */
            $found_main_categories[$main_category->term_id] = sprintf(
                /* translators: $1: Brand id, $2 Brand name */
                esc_html__('#%1$s - %2$s', 'woocommerce'),
                $main_category->term_id,
                $main_category->name
            );
        }

        wp_send_json(apply_filters('woocommerce_json_search_found_main_categories', $found_main_categories));
    }

    public function riothere_filter_orders_by_specific_main_category_id_query($query)
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        try {
            if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'shop_order' && $pagenow === 'edit.php' && $post_type === 'shop_order' && isset($_GET['_main_category_id']) && !empty($_GET['_main_category_id'])) {
                $query->query_vars['_main_category_id'] = absint($_GET['_main_category_id']);
            }
        } catch (Exception $exception) {
        }

        return $query;
    }

    public function riothere_filter_orders_by_specific_main_category_id_clauses($clauses, $wp_query)
    {
        global $wpdb;

        if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'shop_order' && isset($wp_query->query_vars['_main_category_id'])) {

            $main_category_id = $wp_query->query_vars['_main_category_id'];

            $args = [
                'post_type' => 'product',
                'numberposts' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $main_category_id, /// Where term_id of Term 1 is "1".
                        'include_children' => true,
                    ),
                ),
            ];

            $products = get_posts($args);
            $product_ids = [];
            foreach ($products as $product) {
                $product_ids[] = $product->ID;
            }

            if (count($product_ids) === 0) {
                $product_ids[] = 0; // for the query not to fail when IN () is empty
            }

            $clauses['join'] .= " JOIN {$wpdb->prefix}woocommerce_order_items as main_category_id_order_items on main_category_id_order_items.order_id = {$wpdb->posts}.ID ";
            $clauses['join'] .= " JOIN {$wpdb->prefix}woocommerce_order_itemmeta as main_category_id_order_itemmeta on main_category_id_order_itemmeta.order_item_id = main_category_id_order_items.order_item_id AND main_category_id_order_itemmeta.meta_key ='_product_id'";
            $clauses['join'] .= " JOIN {$wpdb->prefix}postmeta as main_category_id_postmeta on main_category_id_postmeta.post_id = main_category_id_order_itemmeta.meta_value AND main_category_id_postmeta.post_id IN (" . implode(
                ', ',
                $product_ids
            ) . ")";

            $clauses['distinct'] = 'DISTINCT';
        }

        return $clauses;
    }

    public function riothere_filter_products_by_create_date_range()
    {

        global $pagenow;

        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        $from = (isset($_GET['riothereCreatedDateFrom']) && $_GET['riothereCreatedDateFrom']) ? sanitize_text_field($_GET['riothereCreatedDateFrom']) : '';
        $to = (isset($_GET['riothereCreatedDateTo']) && $_GET['riothereCreatedDateTo']) ? sanitize_text_field($_GET['riothereCreatedDateTo']) : '';
        if (is_admin() && $pagenow === 'edit.php' && $post_type === 'product') {
            ?>
            <div class="filter-date">
                <input type="text" class="riothere-date-from" name="riothereCreatedDateFrom" placeholder="Created Date From" value="<?php echo esc_attr($from); ?>" />
                <input type="text" class="riothere-date-to" name="riothereCreatedDateTo" placeholder="Created Date To" value="<?php echo esc_attr($to); ?>" />
            </div>
        <?php
}
    }

    public function riothere_filter_products_by_create_date_range_query($query)
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        if (
            is_admin() && $pagenow === 'edit.php' && $post_type === 'product'
            && ((isset($_GET['riothereCreatedDateFrom']) && !empty($_GET['riothereCreatedDateFrom'])) || (isset($_GET['riothereCreatedDateTo']) && !empty($_GET['riothereCreatedDateTo'])))
        ) {
            $query->query_vars['date_query'] = array(
                'inclusive' => true, // include the selected days as well
                'column' => 'post_date', // 'post_modified', 'post_date_gmt', 'post_modified_gmt'
            );

            if (isset($_GET['riothereCreatedDateFrom']) && !empty($_GET['riothereCreatedDateFrom'])) {
                $query->query_vars['date_query']['after'] = sanitize_text_field($_GET['riothereCreatedDateFrom']);
            }
            if (isset($_GET['riothereCreatedDateTo']) && !empty($_GET['riothereCreatedDateTo'])) {
                $query->query_vars['date_query']['before'] = sanitize_text_field($_GET['riothereCreatedDateTo']);
            }
        }

        return $query;
    }

    public function riothere_filter_products_by_price_range()
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        $from = (isset($_GET['productPriceFrom']) && $_GET['productPriceFrom']) ? abs($_GET['productPriceFrom']) : '';
        $to = (isset($_GET['productPriceTo']) && $_GET['productPriceTo']) ? abs($_GET['productPriceTo']) : '';
        if (is_admin() && $pagenow === 'edit.php' && $post_type === 'product') {
            ?>
            <div class="riothere-price-range">
                <div class="filters-container">
                    <div class="input-container">
                        <input type="number" name="productPriceFrom" id="productPriceFrom" placeholder="Price From" value="<?php echo esc_attr($from); ?>" />
                    </div>
                    <div class="input-container">
                        <input type="number" name="productPriceTo" id="productPriceTo" placeholder="Price To" value="<?php echo esc_attr($to) ?>" />
                    </div>
                </div>
            </div>
        <?php
}
    }

    public function riothere_filter_products_by_price_range_query($query)
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        if (is_admin() && $pagenow === 'edit.php' && $post_type === 'product' && isset($_GET['productPriceFrom']) && !empty($_GET['productPriceFrom'])) {
            $query->query_vars['productPriceFrom'] = abs($_GET['productPriceFrom']);
        }
        if (is_admin() && $pagenow === 'edit.php' && $post_type === 'product' && isset($_GET['productPriceTo']) && !empty($_GET['productPriceTo'])) {
            $query->query_vars['productPriceTo'] = abs($_GET['productPriceTo']);
        }

        return $query;
    }

    public function riothere_filter_products_by_price_range_clauses($clauses, $wp_query)
    {
        if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'product') {

            if (isset($wp_query->query_vars['productPriceFrom']) || isset($wp_query->query_vars['productPriceTo'])) {

                global $wpdb;
                $clauses['join'] .= " JOIN {$wpdb->prefix}postmeta as filter_postmeta on filter_postmeta.post_id = {$wpdb->posts}.ID AND filter_postmeta.meta_key= '_price'";

                $clauses['distinct'] = 'DISTINCT';
            }

            if (isset($wp_query->query_vars['productPriceFrom'])) {
                // $from_query $to_query
                $productPriceFrom = $wp_query->query_vars['productPriceFrom'];
                $from_query = !empty($productPriceFrom) ? " AND filter_postmeta.meta_value >= $productPriceFrom " : ' ';
                $clauses['join'] .= $from_query;
            }

            if (isset($wp_query->query_vars['productPriceTo'])) {
                $productPriceTo = $wp_query->query_vars['productPriceTo'];
                $to_query = !empty($productPriceTo) ? " AND filter_postmeta.meta_value <= $productPriceTo " : ' ';
                $clauses['join'] .= $to_query;
            }
        }

        return $clauses;
    }

    public function riothere_filter_products_by_listed_date_range_query($query)
    {
        global $pagenow;
        // Get the post type
        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        if (
            is_admin() && $pagenow === 'edit.php' && $post_type === 'product'
            && ((isset($_GET['riothereListedDateFrom']) && !empty($_GET['riothereListedDateFrom'])) || (isset($_GET['riothereListedDateTo']) && !empty($_GET['riothereListedDateTo'])))
        ) {
            $from = (isset($_GET['riothereListedDateFrom']) && $_GET['riothereListedDateFrom']) ? sanitize_text_field($_GET['riothereListedDateFrom']) : '';
            $to = (isset($_GET['riothereListedDateTo']) && $_GET['riothereListedDateTo']) ? sanitize_text_field($_GET['riothereListedDateTo']) : '';
            $query->query_vars['listed_date_query'] = array(
                'after' => sanitize_text_field($from),
                // any strtotime()-acceptable format!
                'before' => sanitize_text_field($to),
            );
        }

        return $query;
    }

    public function riothere_filter_products_by_listed_date_range()
    {

        global $pagenow;

        $post_type = sanitize_text_field($_GET['post_type']) ?? '';
        $from = (isset($_GET['riothereListedDateFrom']) && $_GET['riothereListedDateFrom']) ? sanitize_text_field($_GET['riothereListedDateFrom']) : '';
        $to = (isset($_GET['riothereListedDateTo']) && $_GET['riothereListedDateTo']) ? sanitize_text_field($_GET['riothereListedDateTo']) : '';
        if (is_admin() && $pagenow === 'edit.php' && $post_type === 'product') {
            ?>
            <div class="days-ago-filters">
                <input type="number" name="riothereListedDateFrom" placeholder="Listed Date From (days ago)" value="<?php echo esc_attr($from); ?>" />
                <input type="number" name="riothereListedDateTo" placeholder="Listed Date To (days ago)" value="<?php echo esc_attr($to); ?>" />
            </div>
<?php
}
    }

    public function riothere_filter_products_by_listed_date_clauses($clauses, $wp_query)
    {
        if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'product' && isset($wp_query->query_vars['listed_date_query'])) {

            global $wpdb;

            $filters = $wp_query->query_vars['listed_date_query'];

            $clauses['join'] .= " JOIN {$wpdb->prefix}postmeta as listed_date_postmeta on listed_date_postmeta.post_id = {$wpdb->posts}.ID AND listed_date_postmeta.meta_key= 'date_of_listing'";

            if (!empty($filters['after'])) {
                $after_date = (new \DateTime())->modify("-" . $filters['after'] . " day")->format('Ymd');
                $from_query = " AND listed_date_postmeta.meta_value <= $after_date ";
                $clauses['join'] .= $from_query;
            }

            if (!empty($filters['before'])) {
                $before_date = (new \DateTime())->modify("-" . $filters['before'] . " day")->format('Ymd');
                $to_query = " AND listed_date_postmeta.meta_value >= $before_date ";
                $clauses['join'] .= $to_query;
            }

            $clauses['distinct'] = 'DISTINCT';
        }

        return $clauses;
    }

    private function get_sold_items($user_id)
    {
        $args = [
            'post_type' => 'product',
            'posts_per_page' => -1, // all posts
            'meta_query' => array(
                array(
                    'key' => 'status',
                    'value' => 'sold',
                ),
                array(
                    'key' => 'seller',
                    'value' => $user_id,
                ),
            ),
        ];

        $the_query = new WP_Query($args);

        if (!$the_query->have_posts()) {
            return [
                'product_ids' => [],
                'found_posts' => 0,
            ];
        }

        $product_ids = [];
        $posts = $the_query->posts;

        foreach ($posts as $post) {
            $product_ids[] = $post->ID;
        }

        return [
            'product_ids' => $product_ids,
            'found_posts' => $the_query->found_posts,
        ];
    }

    private function calculate_amount_generated_in_sales($user_id)
    {
        $product_ids = $this->get_sold_items($user_id)['product_ids'];

        $products_output = [];
        if (sizeof($product_ids) > 0) {
            $products_output = wc_get_products([
                'include' => $product_ids,
                'limit' => -1, // unlimited
            ]);
        }

        $total = 0;
        foreach ($products_output as $product) {
            $product_data = $product->get_data();

            if (is_numeric($product_data['price'])) {
                $total += $product_data['price'];
            }
        }

        return $total;
    }

    private function get_listed_items($user_id)
    {
        $args = [
            'post_type' => 'product',
            'posts_per_page' => -1, // all posts
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'status',
                    'value' => 'listed',
                ),
                array(
                    'key' => 'seller',
                    'value' => $user_id,
                ),
            ),
        ];

        $the_query = new WP_Query($args);

        if (!$the_query->have_posts()) {
            return [
                'product_ids' => [],
                'found_posts' => 0,
            ];
        }

        $product_ids = [];
        $posts = $the_query->posts;

        foreach ($posts as $post) {
            $product_ids[] = $post->ID;
        }

        return [
            'product_ids' => $product_ids,
            'found_posts' => $the_query->found_posts,
        ];
    }

    private function get_SKUs_of_sold_items($user_id)
    {
        $product_ids = $this->get_sold_items($user_id)['product_ids'];

        $products_output = [];
        if (sizeof($product_ids) > 0) {
            $products_output = wc_get_products([
                'include' => $product_ids,
                'limit' => -1, // unlimited
            ]);
        }

        $items_skus = array();
        foreach ($products_output as $product) {
            if ($product instanceof WC_Product) {
                $items_skus[] = '<a href="' . esc_url(admin_url('post.php?post=' . $product->get_id()) . '&action=edit') . '" class="order-view"><strong>#' . esc_attr($product->get_sku()) . '</strong></a>';
            }
        }

        return sizeof($items_skus) > 0 ? implode(', ', $items_skus) : '-';
    }

    public function riothere_new_modify_user_table($column)
    {
        $column['date_of_registration'] = 'Date of Registration';
        $column['number_of_listed_item'] = 'Number of Listed Items';
        $column['amount_generated_in_sales'] = 'Amount Generated in Sales (AED)';
        return $column;
    }

    public function riothere_new_modify_user_table_row($val, $column_name, $user_id)
    {
        switch ($column_name) {
            case 'date_of_registration':
                return date("d-M-Y", strtotime(get_userdata($user_id)->user_registered));

            case 'number_of_listed_item':
                return $this->get_listed_items($user_id)['found_posts'];

            case 'amount_generated_in_sales':
                return $this->calculate_amount_generated_in_sales($user_id);

            default:
        }

        return $val;
    }

    public function add_sller_role_to_customers_handler()
    {

        $results = [
            'success' => true,
        ];

        echo json_encode($results);
        die();
    }
}
