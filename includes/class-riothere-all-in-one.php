<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 *
 * @package    Riothere_All_In_One
 * @subpackage Riothere_All_In_One/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Riothere_All_In_One
 * @subpackage Riothere_All_In_One/includes
 */
class Riothere_All_In_One
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Riothere_All_In_One_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('RIOTHERE_ALL_IN_ONE_VERSION')) {
            $this->version = RIOTHERE_ALL_IN_ONE_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'riothere-all-in-one';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Riothere_All_In_One_Loader. Orchestrates the hooks of the plugin.
     * - Riothere_All_In_One_i18n. Defines internationalization functionality.
     * - Riothere_All_In_One_Admin. Defines all hooks for the admin area.
     * - Riothere_All_In_One_Admin_Coupons. Defines all hooks for the admin coupons area.
     * - Riothere_All_In_One_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        // Utils functions
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils.php';

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-riothere-all-in-one-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-riothere-all-in-one-admin-settings.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-riothere-all-in-one-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-riothere-all-in-one-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-riothere-all-in-one-promotions.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-riothere-all-in-one-staggered-promotions.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-riothere-all-in-one-free-shipping-promotions.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-riothere-all-in-one-campaigns.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-riothere-all-in-one-build-frontend.php';

        // Class responsible for the followers management
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-riothere-all-in-one-followers.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-riothere-all-in-one-admin-coupons.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-riothere-all-in-one-admin-dashboard.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-riothere-all-in-one-catalog.php';

        /**
         * Load the custom email templates
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/emails/refund-accepted-email.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/emails/refund-rejected-email.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/emails/subscribed-to-newsletter-email.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/emails/reset-password-email.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/emails/contact-us-email.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/emails/try-and-buy-customer-email.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/emails/try-and-buy-admin-email.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/emails/staggered-promotion-email.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/emails/wishlist-email.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/emails/cart-abandonment-email.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-riothere-all-in-one-public.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/edit-products.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/social-auth/index.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-products.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-products-csv.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-promotion-sliders.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/request-refund.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/cancel-refund-request.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-refund-requests.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-transit-times.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-time-slots.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/campaign-products.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-faqs.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-faq-post-type.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-cart-percent-discount-post-type.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-cart-percent-discount.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-refund-requests-post-type.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/wishlists.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/carts.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cart-reports.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-transit-times-post-type.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-time-slots-post-type.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-homepage-control-menu.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-slider-post-type.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-buttons-post-type.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-text-areas-post-type.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-sections-post-type.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-exchange-rates.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/change-password.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-categories.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-seller-info.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-sellers.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/following-apis.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-admin-settings.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-home-page-slides.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-home-page-sections.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/update-product-number-of-views.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/update-number-of-clicks-in-search-results.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/newsletter-subscribe.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/newsletter-unsubscribe.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-newsletter-popup-config.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/validate-order-coupon.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/new-contact-us-request.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-catalog.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/update-refund-request.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/update-product-visibility.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/send-order-failed-email.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-custom-roles.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/send-subscribed-to-newsletter-email.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/send-contact-us-email.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/send-try-and-buy-customer-email.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/send-try-and-buy-admin-email.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/refresh-search.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/search.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/clear-search.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/kibana-data.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/clear-kibana-data.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/create-custom-payment-gateways.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/try-and-buy.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-try-and-buy-orders-to-retrieve-and-deliver.php';

        // Overwrite the Password Reset Email
        require_once plugin_dir_path(dirname(__FILE__)) . 'jwt-authentication-for-wp-rest-api-by-codemonk91/index.php';

        // Used once to import a csv that links Products to Sellers (the csv is in root of project `products_sku.csv`)
        // require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/import.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-shipping-discount.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/add-to-wishlist.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/remove-from-wishlist.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-wishlist.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/add-to-cart.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/remove-from-cart.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/get-cart.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/fulfill-cart.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/routes/send-cart-abandon-email.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cart-reports.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/seller-reports.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/products.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/update-product-status-when-creating-order.php';

        $this->loader = new Riothere_All_In_One_Loader();
        Riothere_All_In_One_Admin_Settings::add_admin_settings();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Riothere_All_In_One_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Riothere_All_In_One_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Riothere_All_In_One_Admin($this->get_plugin_name(), $this->get_version());
        $plugin_admin_dashboard = new Riothere_All_In_One_Admin_Dashboard($this->get_plugin_name(), $this->get_version());
        $plugin_admin_coupon = new Riothere_All_In_One_Admin_Coupons($this->get_plugin_name(), $this->get_version());
        $plugin_promotions = new Riothere_All_In_One_Promotions($this->get_plugin_name(), $this->get_version());
        $plugin_staggered_promotions = new Riothere_All_In_One_Staggered_Promotions($this->get_plugin_name(), $this->get_version());
        $plugin_free_shipping_promotions = new Riothere_All_In_One_Free_Shipping_Promotions($this->get_plugin_name(), $this->get_version());
        $plugin_campaigns = new Riothere_All_In_One_Campaigns($this->get_plugin_name(), $this->get_version());
        // $plugin_build_frontend = new Riothere_All_In_One_Build_Frontend($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'riothere_enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'riothere_enqueue_scripts');

        $this->loader->add_filter('woocommerce_product_data_store_cpt_get_products_query', $plugin_admin, 'riothere_handle_custom_query_var', 10, 2);

        // Hook for filter WC API product response object
        $this->loader->add_filter('woocommerce_rest_prepare_product_object', $plugin_admin, 'riothere_filter_woocommerce_product_object_response', 10, 3);

        // hook to edit the available columns on the wc orders table
        $this->loader->add_action(
            'manage_edit-shop_order_columns',
            $plugin_admin,
            'riothere_edit_woocommerce_orders_admin_list_table_column'
        );
        $this->loader->add_action(
            'manage_shop_order_posts_custom_column',
            $plugin_admin,
            'riothere_webroom_add_wc_order_admin_list_column_content'
        );

        // hook to make the added columns sortable
        $this->loader->add_action(
            'manage_edit-shop_order_sortable_columns',
            $plugin_admin,
            'riothere_sort_edit_woocommerce_orders_admin_list_table_column'
        );

        $this->loader->add_action(
            'manage_edit-wishlists_sortable_columns',
            $plugin_admin,
            'riothere_sort_edit_woocommerce_wishlists_admin_list_table_column'
        );

        $this->loader->add_action(
            'woocommerce_after_order_itemmeta',
            $plugin_admin,
            'riothere_add_customer_info_after_order_itemmeta',
            20,
            3
        );

        $this->loader->add_filter(
            'wc_order_statuses',
            $plugin_admin,
            'riothere_rename_order_status_msg',
            20,
            1
        );
        $this->loader->add_action('init', $plugin_admin, 'riothere_register_custom_statuses');

        $this->loader->add_filter('woocommerce_admin_reports', $plugin_admin, 'riothere_woocommerce_admin_reports');

        $this->loader->add_action('class_wc_report_customer_list', $plugin_admin, 'riothere_class_wc_report_customer_list');

        $this->loader->add_action('edit_user_profile', $plugin_admin, 'riothere_add_wc_information_to_user_edit_section', -1, 1);

        // Hooks for Orders table filter by order ID
        $this->loader->add_action('parse_query', $plugin_admin, 'riothere_filter_orders_by_specific_order_id_query');
        $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'riothere_filter_orders_by_specific_order_id_html');
        $this->loader->add_action('wp_ajax_woocommerce_json_search_orders', $plugin_admin, 'riothere_json_search_orders');

        // Hooks for Orders table filter by seller
        $this->loader->add_action('parse_query', $plugin_admin, 'riothere_filter_orders_by_specific_seller_id_query');
        $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'riothere_filter_orders_by_specific_seller_id_html');
        $this->loader->add_action('wp_ajax_woocommerce_json_search_sellers', $plugin_admin, 'riothere_json_search_sellers');
        $this->loader->add_action('posts_clauses', $plugin_admin, 'riothere_seller_filter_clauses', 10, 2);

        // Hooks for Orders table filter if items are in try and buy
        $this->loader->add_action('parse_query', $plugin_admin, 'riothere_filter_orders_by_try_and_buy_query');
        $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'riothere_filter_orders_by_try_and_buy');
        $this->loader->add_action('posts_clauses', $plugin_admin, 'riothere_filter_orders_by_try_and_buy_clauses', 10, 2);

        // Hooks for Orders table filter orders by total price range
        $this->loader->add_action('parse_query', $plugin_admin, 'riothere_filter_orders_by_price_range_query');
        $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'riothere_filter_orders_by_price_range');
        $this->loader->add_action('posts_clauses', $plugin_admin, 'riothere_filter_orders_by_price_range_query_clauses', 10, 2);

        // Hooks for orders table filter by created date range
        $this->loader->add_action('parse_query', $plugin_admin, 'riothere_filter_orders_by_create_date_range_query');
        $this->loader->add_filter('months_dropdown_results', $plugin_admin, 'riothere_months_dropdown_results_reset_values');
        $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'riothere_filter_orders_by_create_date_range');

        // Hooks for orders table filter by sub category of designers
        $this->loader->add_action('parse_query', $plugin_admin, 'riothere_filter_orders_by_specific_brand_id_query');
        $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'riothere_filter_orders_by_specific_brand_id_html');
        $this->loader->add_action('wp_ajax_woocommerce_json_search_brands', $plugin_admin, 'riothere_json_search_brands');
        $this->loader->add_action('posts_clauses', $plugin_admin, 'riothere_filter_orders_by_specific_brand_id_clauses', 10, 2);

        // Hooks for orders table filter by sub category of categories
        $this->loader->add_action('parse_query', $plugin_admin, 'riothere_filter_orders_by_specific_main_category_id_query');
        $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'riothere_filter_orders_by_specific_main_category_id_html');
        $this->loader->add_action('wp_ajax_woocommerce_json_search_main_categories', $plugin_admin, 'riothere_json_search_main_categories');
        $this->loader->add_action('posts_clauses', $plugin_admin, 'riothere_filter_orders_by_specific_main_category_id_clauses', 10, 2);

        // Hooks for products table filter by created date range
        $this->loader->add_action('parse_query', $plugin_admin, 'riothere_filter_products_by_create_date_range_query');
        $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'riothere_filter_products_by_create_date_range');

        // Hooks for Products table filter products by total price range
        $this->loader->add_action('parse_query', $plugin_admin, 'riothere_filter_products_by_price_range_query');
        $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'riothere_filter_products_by_price_range');
        $this->loader->add_action('posts_clauses', $plugin_admin, 'riothere_filter_products_by_price_range_clauses', 10, 2);

        // Hooks for products table filter by listing date range
        $this->loader->add_action('parse_query', $plugin_admin, 'riothere_filter_products_by_listed_date_range_query');
        $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'riothere_filter_products_by_listed_date_range');
        $this->loader->add_action('posts_clauses', $plugin_admin, 'riothere_filter_products_by_listed_date_clauses', 10, 2);

        // Hooks for users table to add Seller-related columns
        $this->loader->add_action('manage_users_columns', $plugin_admin, 'riothere_new_modify_user_table');
        $this->loader->add_action('manage_users_custom_column', $plugin_admin, 'riothere_new_modify_user_table_row', 10, 3);
        // $this->loader->add_action('manage_users_sortable_columns', $plugin_admin, 'new_sortable_users_table');

        // Coupon related functionality
        $this->loader->add_action('woocommerce_coupon_options_usage_restriction', $plugin_admin_coupon, 'riothere_add_coupon_usage_restrictions', 10, 2);
        $this->loader->add_action('woocommerce_coupon_options_save', $plugin_admin_coupon, 'riothere_save_coupon_text_field', 10, 2);
        $this->loader->add_action('woocommerce_coupon_is_valid_for_product', $plugin_admin_coupon, 'riothere_check_custom_coupon_product_rules', 10, 4);
        //    $this->loader->add_filter('woocommerce_coupon_is_valid', $plugin_admin_coupon,'check_custom_coupon_user_email_rule', 10,3);
        //    $this->loader->add_action('woocommerce_after_checkout_validation', $plugin_admin_coupon,'check_custom_coupon_user_email_rule', 10,3);
        $this->loader->add_action('init', $plugin_admin_coupon, 'riothere_setup_newsletter_popup_option_page');

        // woocommerce_data_get_email_restrictions filter to filter email restrictions array
        // woocommerce_after_checkout_validation search for check_customer_coupons function
        add_filter('woocommerce_rest_check_permissions', [$this, 'riothere_my_woocommerce_rest_check_permissions'], 90, 4);

        // Code for adding role seller to customer Widget
        // $this->loader->add_action('wp_dashboard_setup', $plugin_admin, 'riothere_add_customer_dashboard_widgets');
        // $this->loader->add_action('wp_ajax_add_sller_role_to_customers_handler', $plugin_admin, 'add_sller_role_to_customers_handler');

        // Code for adding button to Sync products to OpenSearch and stats widgets
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin, 'riothere_add_sync_products_dashboard_widgets');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin_dashboard, 'riothere_add_most_recent_orders_dashboard_widgets');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin_dashboard, 'riothere_add_most_recent_products_dashboard_widgets');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin_dashboard, 'riothere_add_most_recent_user_account_registrations_dashboard_widgets');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin_dashboard, 'riothere_add_most_recent_seller_account_registrations_dashboard_widgets');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin_dashboard, 'riothere_add_current_promotion_running_dashboard_widgets');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin_dashboard, 'riothere_add_most_viewed_items_dashboard_widgets');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin_dashboard, 'riothere_add_most_followed_sellers_dashboard_widgets');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin_dashboard, 'riothere_add_most_searched_items_dashboard_widgets');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin_dashboard, 'riothere_add_new_refund_requests_from_customers_dashboard_widgets');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin_dashboard, 'riothere_add_try_and_buy_dashboard_widgets');
        // $this->loader->add_action('wp_dashboard_setup', $plugin_admin_dashboard, 'riothere_add_build_frontend_dashboard_widget');
    }

    public function riothere_my_woocommerce_rest_check_permissions($permission, $context, $object_id, $post_type)
    {
        return true;
    }
    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Riothere_All_In_One_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'riothere_enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'riothere_enqueue_scripts');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Riothere_All_In_One_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
