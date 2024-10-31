<?php

/**
 * The admin-specific promotions functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Riothere_All_In_One
 * @subpackage Riothere_All_In_One/admin
 */

class Riothere_All_In_One_Promotions
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     *
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Initial hooks for loading the plugin admin UI
        add_action('admin_enqueue_scripts', array($this, 'riothere_enqueue_scripts'));
        add_action('init', [$this, 'riothere_register_promotions_custom_post_type']);
        add_action('add_meta_boxes', [$this, 'riothere_add_promotions_settings_fields']);

        // Handle Saving/Adding promotion. Triggered when the post type get updated
        add_action('save_post', [$this, 'riothere_handle_save_promotions']);

        // ajax call for the admin UI
        add_action('wp_ajax_promotions_get_products', array($this, 'riothere_ajax_get_products'));
        add_action('wp_ajax_promotions_get_products_by_rules', array(
            $this,
            'riothere_ajax_promotions_get_products_by_rules',
        ));

        add_filter('woocommerce_product_data_store_cpt_get_products_query', array(
            $this,
            'riothere_handle_custom_query_var',
        ), 10, 2);

        // changing product price on run time
        add_filter('woocommerce_product_get_price', array($this, 'riothere_handle_change_product_price'), 99, 2);

        // Changing WooCommerce  product object response body
        add_filter('woocommerce_rest_prepare_product_object', array(
            $this,
            'riothere_filter_woocommerce_product_object_response',
        ), 10, 2);
        add_filter('riothere_get_product_data', [$this, 'riothere_filter_product_data'], 10, 2);

        /*
         * Hooks that get triggered when WC Product get updated, deleted and created
         * */
        add_action('before_delete_post', [$this, 'riothere_handle_deleting_product']);
        add_action('save_post', array($this, 'riothere_handle_save_product'), 1000);
        add_action('woocommerce_product_import_inserted_product_object', array(
            $this,
            'riothere_handle_save_product_from_import',
        ));
    }

    public function riothere_filter_product_data($product_data, $product)
    {
        $product_id = $product->get_id();
        $applicable_promotion_id = Riothere_All_In_One_Promotions::get_product_applicable_promotion_id($product_id);
        $product_data["on_promotion"] = $applicable_promotion_id !== null;

        $is_on_sale = $product->is_on_sale();
        $product_data['priceBeforePromotion'] = $is_on_sale ? $product->get_sale_price() : $product->get_regular_price();

        if ($applicable_promotion_id !== null) {
            $promotion_percentage = get_post_meta($applicable_promotion_id, 'promotion_percentage', true);
            $promotion_label = get_post_meta($applicable_promotion_id, 'promotion_label', true);
            $promotion_items_with_labels = get_post_meta($applicable_promotion_id, 'products_show_promotion_label', true);

            if ($promotion_items_with_labels === '') {
                $promotion_items_with_labels = [];
            }
            $has_promotion_label = $promotion_label !== '' && in_array($product_id, $promotion_items_with_labels);
            $product_data["has_promotion_label"] = $has_promotion_label;

            if ($has_promotion_label) {
                $product_data["promotionLabel"] = $promotion_label;

            }

            $product_data["promotion_percentage"] = $promotion_percentage;
        }

        return $product_data;
    }

    public function riothere_handle_save_product_from_import($product)
    {
        $this->riothere_handle_save_product($product->get_id());
    }

    public function riothere_handle_save_product($product_id)
    {
        if (get_post_type($product_id) === 'product') {
            $promotions = get_posts([
                'post_type' => 'riothere-promotion',
                'status' => 'publish',
                'nopaging' => true,
            ]);

            $seller = get_field('seller', $product_id);
            $seller_id = null;
            if (is_array($seller)) {
                $seller_id = $seller['ID'];
            }
            $product_promotion_ids = self::get_product_promotion_ids($product_id);

            foreach ($promotions as $promotion) {
                $promotion_id = $promotion->ID;
                $give_promotion_to_new_added_items = get_post_meta($promotion_id, 'give_promotion_to_new_added_items', true);
                $new_applicable_product_ids = self::get_new_applicable_product_ids($promotion_id);
                $sellers_excluded_product_ids = self::get_sellers_excluded_product_ids($promotion_id);
                $previously_applicable_product_ids = self::get_applicable_product_ids($promotion_id);

                // If product is already present in the seller excluded items list than skip
                if (!in_array($product_id, $sellers_excluded_product_ids)) {

                    if (in_array($product_id, $previously_applicable_product_ids) && !in_array($product_id, $new_applicable_product_ids)) {
                        update_post_meta($promotion_id, 'promotion_applicable_product_ids', array_diff($previously_applicable_product_ids, [$product_id]));
                        update_post_meta($product_id, 'promotion_ids', array_diff($product_promotion_ids, [$promotion_id]));
                    } else if ($give_promotion_to_new_added_items === 'true') {
                        if (in_array($product_id, $new_applicable_product_ids) && !in_array($product_id, $previously_applicable_product_ids)) {
                            update_post_meta($promotion_id, 'promotion_applicable_product_ids', array_unique(array_merge($previously_applicable_product_ids, [$product_id])));
                            if ($seller_id !== null) {
                                // @todo phase 3 send emails you have the seller id and the $product_ids newly added into promotion ($seller_id, [$product_id] )
                                update_post_meta($product_id, 'promotion_ids', array_unique(array_merge($product_promotion_ids, [$promotion_id])));
                            }
                        }
                    }

                }

            }
        }
    }

    public function riothere_handle_deleting_product($product_id)
    {
        if (get_post_type($product_id) === 'product') {
            $promotions = get_posts([
                'post_type' => 'riothere-promotion',
                'status' => 'any',
                'nopaging' => true,
            ]);

            foreach ($promotions as $promotion) {
                $promotion_id = $promotion->ID;
                $products_show_on_home_page = self::get_products_show_on_home_page($promotions);
                $products_show_on_home_page = $products_show_on_home_page ? $products_show_on_home_page : [];

                $products_show_promotion_label = self::get_products_show_promotion_label($promotions);
                $products_show_promotion_label = $products_show_promotion_label ? $products_show_promotion_label : [];

                $applicable_product_ids = self::get_applicable_product_ids($promotions);
                $applicable_product_ids = $applicable_product_ids ? $applicable_product_ids : [];

                $products_show_on_home_page = array_diff($products_show_on_home_page, [$product_id]);
                $products_show_promotion_label = array_diff($products_show_promotion_label, [$product_id]);
                $applicable_product_ids = array_diff($applicable_product_ids, [$product_id]);

                update_post_meta($promotion_id, 'products_show_on_home_page', $products_show_on_home_page);
                update_post_meta($promotion_id, 'products_show_promotion_label', $products_show_promotion_label);
                update_post_meta($promotion_id, 'promotion_applicable_product_ids', $applicable_product_ids);
            }

        }
    }

    public function riothere_filter_woocommerce_product_object_response($response, $product)
    {
        // Customize response data here
        $product_id = $product->get_id();
        $applicable_promotion_id = self::get_product_applicable_promotion_id($product_id);
        $response->data["on_promotion"] = $applicable_promotion_id !== null;

        $is_on_sale = $product->is_on_sale();
        $response->data['priceBeforePromotion'] = $is_on_sale ? $product->get_sale_price() : $product->get_regular_price();

        // $response->data["priceBeforePromotion"] = $product->
        if ($applicable_promotion_id !== null) {

            $promotion_percentage = get_post_meta($applicable_promotion_id, 'promotion_percentage', true);
            $promotion_label = get_post_meta($applicable_promotion_id, 'promotion_label', true);
            $promotion_items_with_labels = get_post_meta($applicable_promotion_id, 'products_show_promotion_label', true);

            if ($promotion_items_with_labels === '') {
                $promotion_items_with_labels = [];
            }

            $has_promotion_label = $promotion_label !== '' && in_array($product_id, $promotion_items_with_labels);
            $response->data["has_promotion_label"] = $has_promotion_label;

            if ($has_promotion_label) {
                $response->data["promotionLabel"] = $promotion_label;

            }

            $response->data["promotion_percentage"] = $promotion_percentage;
        }

        return $response;
    }

    public static function get_promotion_sliders()
    {
        $active_promotion_ids = [];
        $promotions = get_posts([
            'post_type' => 'riothere-promotion',
            'status' => 'publish',
            'nopaging' => true,
        ]);

        foreach ($promotions as $promotion) {
            $promotion_id = $promotion->ID;
            $is_valid = self::is_promotion_valid($promotion_id);

            if ($is_valid) {
                $active_promotion_ids[] = $promotion_id;
            }
        }

        $data = [];

        foreach ($active_promotion_ids as $active_promotion_id) {
            $products_show_on_home_page = self::get_products_show_on_home_page($active_promotion_id);

            if (count($products_show_on_home_page) > 0) {
                $promotion_active_products = [];
                foreach ($products_show_on_home_page as $product_id) {
                    // the if conditions aren't joined together for performance to not check applicable promotion id for the product if the product isn't published
                    if ('publish' === get_post_status($product_id)) {
                        $applicable_promotion_id = self::get_product_applicable_promotion_id($product_id);
                        if ($applicable_promotion_id === $active_promotion_id) {
                            $promotion_active_products[] = $product_id;
                        }
                    }
                }

                if (count($promotion_active_products) > 0) {
                    $data[] = [
                        'title' => get_the_title($active_promotion_id),
                        'product_ids' => $promotion_active_products,
                    ];
                }
            }

        }

        return $data;
    }

    public static function get_products_show_on_home_page($promotion_id)
    {
        $products_show_on_home_page = get_post_meta($promotion_id, 'products_show_on_home_page', true);
        if ($products_show_on_home_page === '') {
            $products_show_on_home_page = [];
        }

        return $products_show_on_home_page;
    }

    public static function get_products_show_promotion_label($promotion_id)
    {
        $products_show_promotion_label = get_post_meta($promotion_id, 'products_show_promotion_label', true);
        if ($products_show_promotion_label === '') {
            $products_show_promotion_label = [];
        }

        return $products_show_promotion_label;
    }

    public function riothere_handle_change_product_price($price, $product)
    {
        if (!$price) {
            return;
        }

        $applicable_promotion_id = self::get_product_applicable_promotion_id($product->get_id());

        if ($applicable_promotion_id !== null) {
            $promotion_percentage = get_post_meta($applicable_promotion_id, 'promotion_percentage', true);
            $price -= (float) $price * ((float) $promotion_percentage / 100);
        }

        return $price;
    }

    // There are multiple promotions attached to any product. This function
    // returns ALL of them. Note that only 1 of them is applicable at any point
    // in time. To get the applicable one, use `get_product_applicable_promotion_id`
    public static function get_product_promotion_ids($product_id)
    {
        $promotion_ids = get_post_meta($product_id, 'promotion_ids', true);
        if ($promotion_ids === '') {
            $promotion_ids = [];
        }

        return $promotion_ids;
    }

    // There are multiple promotions attached to any product. This function
    // returns only the applicable one.
    public static function get_product_applicable_promotion_id($product_id)
    {
        $applicable_promotion_id = null;
        $applicable_promotion_percentage = null;
        $promotion_ids = self::get_product_promotion_ids($product_id);

        if (count($promotion_ids) > 0) {
            foreach ($promotion_ids as $promotion_id) {
                $promotion_status = get_post_status($promotion_id);
                $excluded_products = self::get_sellers_excluded_product_ids($promotion_id);
                $applicable_products = self::get_applicable_product_ids($promotion_id);
                if ($promotion_status !== 'publish' || in_array($product_id, $excluded_products)) {
                    continue;
                }

                if (!in_array($product_id, $applicable_products)) {
                    update_post_meta($product_id, 'promotion_ids', array_diff($promotion_ids, [$promotion_id]));
                    continue;
                }

                $promotion_percentage = get_post_meta($promotion_id, 'promotion_percentage', true);
                $is_valid = self::is_promotion_valid($promotion_id);

                if ($is_valid) {
                    if ($applicable_promotion_id === null || ((float) $promotion_percentage > (float) $applicable_promotion_percentage)) {
                        $applicable_promotion_id = $promotion_id;
                        $applicable_promotion_percentage = $promotion_percentage;
                    }
                }
            }
        }

        return $applicable_promotion_id;
    }

    public static function is_promotion_valid($promotion_id)
    {
        $current_date = date("Y-m-d");
        $current_date_time = strtotime($current_date);
        $promotion_start_date = get_post_meta($promotion_id, 'promotion_start_date', true);
        $promotion_end_date = get_post_meta($promotion_id, 'promotion_end_date', true);
        $start_date_time = strtotime($promotion_start_date);
        $end_date_time = strtotime($promotion_end_date);
        $is_valid = false;

        if ($promotion_start_date === '' && $promotion_end_date === '') {
            $is_valid = true;
        } else if ($promotion_start_date !== '' && $promotion_end_date !== '') {
            if ($start_date_time <= $current_date_time && $end_date_time >= $current_date_time) {
                $is_valid = true;
            }
        } else if ($promotion_start_date !== '') {
            if ($start_date_time <= $current_date_time) {
                $is_valid = true;
            }
        } else if ($promotion_end_date !== '') {
            if ($end_date_time >= $current_date_time) {
                $is_valid = true;
            }
        }

        return $is_valid;
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

        wp_enqueue_script(
            $this->plugin_name . '_promotions',
            plugin_dir_url(__FILE__) . 'js/riothere-all-in-on-promotions.js',
            array('jquery', 'select2', 'wc-enhanced-select', 'jquery-ui-datepicker'),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name . '_promotions',
            'riothere_admin_promotions_global',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'promotions_nonce' => wp_create_nonce('promotions'),
            )
        );

    }

    public function riothere_register_promotions_custom_post_type()
    {
        $labels = array(
            'name' => _x('Promotions', 'Post Type General Name', 'riothere-all-in-one'),
            'singular_name' => _x('Promotion', 'Post Type Singular Name', 'riothere-all-in-one'),
            'menu_name' => _x('Promotions', 'Admin Menu text', 'riothere-all-in-one'),
            'name_admin_bar' => _x('Promotion', 'Add New on Toolbar', 'riothere-all-in-one'),
            'archives' => __('Promotion Archives', 'riothere-all-in-one'),
            'attributes' => __('Promotion Attributes', 'riothere-all-in-one'),
            'parent_item_colon' => __('Parent Promotion:', 'riothere-all-in-one'),
            'all_items' => __('Promotions', 'riothere-all-in-one'),
            'add_new_item' => __('Add New Promotion', 'riothere-all-in-one'),
            'add_new' => __('Add New', 'riothere-all-in-one'),
            'new_item' => __('New Promotion', 'riothere-all-in-one'),
            'edit_item' => __('Edit Promotion', 'riothere-all-in-one'),
            'update_item' => __('Update Promotion', 'riothere-all-in-one'),
            'view_item' => __('View Promotion', 'riothere-all-in-one'),
            'view_items' => __('View Promotions', 'riothere-all-in-one'),
            'search_items' => __('Search Promotion', 'riothere-all-in-one'),
            'not_found' => __('Not found', 'riothere-all-in-one'),
            'not_found_in_trash' => __('Not found in Trash', 'riothere-all-in-one'),
            'featured_image' => __('Featured Image', 'riothere-all-in-one'),
            'set_featured_image' => __('Set featured image', 'riothere-all-in-one'),
            'remove_featured_image' => __('Remove featured image', 'riothere-all-in-one'),
            'use_featured_image' => __('Use as featured image', 'riothere-all-in-one'),
            'insert_into_item' => __('Insert into Promotion', 'riothere-all-in-one'),
            'uploaded_to_this_item' => __('Uploaded to this Promotion', 'riothere-all-in-one'),
            'items_list' => __('Promotions list', 'riothere-all-in-one'),
            'items_list_navigation' => __('Promotions list navigation', 'riothere-all-in-one'),
            'filter_items_list' => __('Filter Promotions list', 'riothere-all-in-one'),
        );
        $args = array(
            'label' => __('Promotion', 'riothere-all-in-one'),
            'description' => __('', 'riothere-all-in-one'),
            'labels' => $labels,
            'menu_icon' => '',
            'supports' => array('title', 'custom-fields'),
            'taxonomies' => array(),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'woocommerce-marketing',
            'menu_position' => 5,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => false,
            'hierarchical' => false,
            'exclude_from_search' => false,
            'show_in_rest' => false,
            'publicly_queryable' => false,
            'capability_type' => 'post',
            'rewrite' => false,
        );
        register_post_type('riothere-promotion', $args);
    }

    public function riothere_add_promotions_settings_fields()
    {
        add_meta_box(
            'promotions_settings', // Unique ID
            esc_html__('Promotions', 'example'), // Title
            array($this, 'riothere_promotion_meta_box_display'), // Callback function
            'riothere-promotion', // Admin page (or post type)
            'advanced', // Context
            'default' // Priority
        );
    }

    public function riothere_promotion_meta_box_display()
    {
        require_once __DIR__ . '/partials/riothere-all-in-one-promotion-display.php';
    }

    private function save_promotion($promotion_id)
    {
        $is_add_mode = isset($_POST['is_add_mode']) && $_POST['is_add_mode'] === 'true';
        $edit_promotion_settings = isset($_POST['edit-promotion-settings']) && $_POST['edit-promotion-settings'] === 'true';
        $edit_promotion_rules = isset($_POST['edit-promotion-rules']) && $_POST['edit-promotion-rules'] === 'true';
        $edit_promotion_products = isset($_POST['promotion-view-included-products_status']) && $_POST['promotion-view-included-products_status'] === 'true';
        $promotion_percentage = sanitize_text_field($_POST['promotion_percentage']);
        $promotion_label = sanitize_text_field($_POST['promotion_label']);
        $promotion_start_date = sanitize_text_field($_POST['promotion_start_date']);
        $promotion_end_date = sanitize_text_field($_POST['promotion_end_date']);
        $products_show_on_home_page = !$is_add_mode && $edit_promotion_products ? riothere_sanitize_array($_POST['show_on_home_page_view']) : riothere_sanitize_array($_POST['products_show_on_home_page']);
        $products_show_promotion_label = !$is_add_mode && $edit_promotion_products ? riothere_sanitize_array($_POST['show_promotion_label']) : riothere_sanitize_array($_POST['products_show_promotion_label']);
        $give_promotion_to_new_added_items = sanitize_text_field($_POST['give_promotion_to_new_added_items']);
        $filter_include_products_by_filter = riothere_sanitize_array($_POST['filter_include_products_by_filter']);
        $filter_exclude_products_by_filter = riothere_sanitize_array($_POST['filter_exclude_products_by_filter']);

        $include_products = riothere_sanitize_array($_POST['include_products']);
        $include_sellers = riothere_sanitize_array($_POST['include_sellers']);
        $include_product_categories = riothere_sanitize_array($_POST['include_product_categories']);
        $include_product_tags = riothere_sanitize_array($_POST['include_product_tags']);
        $include_product_min_price = sanitize_text_field($_POST['include_product_min_price']);
        $include_product_max_price = sanitize_text_field($_POST['include_product_max_price']);

        $exclude_products = riothere_sanitize_array($_POST['exclude_products']);
        $exclude_sellers = riothere_sanitize_array($_POST['exclude_sellers']);
        $exclude_product_categories = riothere_sanitize_array($_POST['exclude_product_categories']);
        $exclude_product_tags = riothere_sanitize_array($_POST['exclude_product_tags']);
        $exclude_product_min_price = sanitize_text_field($_POST['exclude_product_min_price']);
        $exclude_product_max_price = sanitize_text_field($_POST['exclude_product_max_price']);

        // promotion settings
        if ($is_add_mode || $edit_promotion_settings) {
            if (isset($promotion_label)) {
                update_post_meta($promotion_id, 'promotion_label', $promotion_label);
            } else {
                delete_post_meta($promotion_id, 'promotion_label');
                delete_post_meta($promotion_id, 'products_show_promotion_label');
            }

            if (isset($promotion_percentage)) {
                update_post_meta($promotion_id, 'promotion_percentage', (float) $promotion_percentage);
            }

            // @todo validate that its a valid date
            if (isset($promotion_start_date) && !empty($promotion_start_date)) {
                update_post_meta($promotion_id, 'promotion_start_date', $promotion_start_date);
            } else {
                delete_post_meta($promotion_id, 'promotion_start_date');
            }

            // @todo validate that its a valid date
            if (isset($promotion_end_date) && !empty($promotion_end_date)) {
                update_post_meta($promotion_id, 'promotion_end_date', $promotion_end_date);
            } else {
                delete_post_meta($promotion_id, 'promotion_end_date');
            }
        }

        // promotion rules
        if ($is_add_mode || $edit_promotion_rules) {
            if (is_array($include_products) && count($include_products) > 0) {
                update_post_meta($promotion_id, 'include_products', $include_products);
            } else {
                delete_post_meta($promotion_id, 'include_products');
            }

            if (is_array($include_sellers)) {
                update_post_meta($promotion_id, 'include_sellers', $include_sellers);
            } else {
                delete_post_meta($promotion_id, 'include_sellers');
            }

            if (is_array($include_product_categories)) {
                update_post_meta($promotion_id, 'include_product_categories', $include_product_categories);
            } else {
                delete_post_meta($promotion_id, 'include_product_categories');
            }
            if (is_array($include_product_tags)) {
                update_post_meta($promotion_id, 'include_product_tags', $include_product_tags);
            } else {
                delete_post_meta($promotion_id, 'include_product_tags');
            }

            if (isset($include_product_min_price) && !is_nan((float) $include_product_min_price) && (float) $include_product_min_price >= 0) {
                update_post_meta($promotion_id, 'include_product_min_price', $include_product_min_price);
            } else {
                delete_post_meta($promotion_id, 'include_product_min_price');
            }

            if (isset($include_product_max_price) && !is_nan((float) $include_product_max_price) && (float) $include_product_max_price >= 0) {
                update_post_meta($promotion_id, 'include_product_max_price', $include_product_max_price);
            } else {
                delete_post_meta($promotion_id, 'include_product_max_price');
            }

            // Exclude products

            if (is_array($exclude_products) && count($exclude_products) > 0) {
                update_post_meta($promotion_id, 'exclude_products', $exclude_products);
            } else {
                delete_post_meta($promotion_id, 'exclude_products');
            }

            if (is_array($exclude_sellers)) {
                update_post_meta($promotion_id, 'exclude_sellers', $exclude_sellers);
            } else {
                delete_post_meta($promotion_id, 'exclude_sellers');
            }

            if (is_array($exclude_product_categories)) {
                update_post_meta($promotion_id, 'exclude_product_categories', $exclude_product_categories);
            } else {
                delete_post_meta($promotion_id, 'exclude_product_categories');
            }

            if (is_array($exclude_product_tags)) {
                update_post_meta($promotion_id, 'exclude_product_tags', $exclude_product_tags);
            } else {
                delete_post_meta($promotion_id, 'exclude_product_tags');
            }

            if (isset($exclude_product_min_price) && !is_nan((float) $exclude_product_min_price) && (float) $exclude_product_min_price >= 0) {
                update_post_meta($promotion_id, 'exclude_product_min_price', $exclude_product_min_price);
            } else {
                delete_post_meta($promotion_id, 'exclude_product_min_price');
            }

            if (isset($exclude_product_max_price) && !is_nan((float) $exclude_product_max_price) && (float) $exclude_product_max_price >= 0) {
                update_post_meta($promotion_id, 'exclude_product_max_price', $exclude_product_max_price);
            } else {
                delete_post_meta($promotion_id, 'exclude_product_max_price');
            }

            if (isset($give_promotion_to_new_added_items) && !empty($give_promotion_to_new_added_items)) {
                update_post_meta($promotion_id, 'give_promotion_to_new_added_items', $give_promotion_to_new_added_items);
            } else {
                delete_post_meta($promotion_id, 'give_promotion_to_new_added_items');
            }

            if (isset($filter_include_products_by_filter) && !empty($filter_include_products_by_filter)) {
                update_post_meta($promotion_id, 'filter_include_products_by_filter', $filter_include_products_by_filter);
            } else {
                delete_post_meta($promotion_id, 'filter_include_products_by_filter');
            }
            if (isset($filter_exclude_products_by_filter) && !empty($filter_exclude_products_by_filter)) {
                update_post_meta($promotion_id, 'filter_exclude_products_by_filter', $filter_exclude_products_by_filter);
            } else {
                delete_post_meta($promotion_id, 'filter_exclude_products_by_filter');
            }
        }

        if ($is_add_mode || $edit_promotion_rules || $edit_promotion_products) {
            if (isset($products_show_on_home_page) && !empty($products_show_on_home_page)) {
                update_post_meta($promotion_id, 'products_show_on_home_page', $products_show_on_home_page);
            } else {
                delete_post_meta($promotion_id, 'products_show_on_home_page');
            }

            if (isset($products_show_promotion_label) && !empty($products_show_promotion_label)) {
                update_post_meta($promotion_id, 'products_show_promotion_label', $products_show_promotion_label);
            } else {
                delete_post_meta($promotion_id, 'products_show_promotion_label');
            }
        }

    }

    public function handle_sending_wishlist_emails($previously_applicable_product_ids, $newly_added_product_ids)
    {
        $wishlists = get_posts([
            'post_type' => 'wishlists',
            'status' => 'published',
            'nopaging' => true,
        ]);
        $wishlist_product_ids = array();

        foreach ($wishlists as $wishlist) {
            $user = new WC_Customer($wishlist->post_author);
            $my_products = get_field('products', $wishlist->ID, false);

            foreach ($my_products as $product) {
                $product_id = $product['field_62c42ff3308b2']['title'];
                $meta = get_post_meta($product_id);

                // We only add the "Listed" ones
                if (strtolower($meta["status"][0]) === "listed") {
                    array_push($wishlist_product_ids, $product_id);
                }
            }

            $products_on_new_promotion = array_intersect($wishlist_product_ids, $newly_added_product_ids);

            if (count($products_on_new_promotion)) {
                $wishlist_user = get_user_by('id', $user->get_id());
                $customer_name = $wishlist_user->data->display_name;

                $header = "<p>There are some unique fashion finds in your wishlist and guess what? They're now on sale for a limited time only!</p>";
                $footer = "<p>Hurry up and grab them as items priced like this don't last very long and we don't want you to miss out on making your wish come true!</p>";
                $message = get_wishlist_email($products_on_new_promotion, $customer_name, $header, $footer);
                $subject = 'Items in your wishlist are on sale!';
                $headers = array('Content-Type: text/html; charset=UTF-8');
                wp_mail($user->data['email'], $subject, $message, $headers);
            }

            $wishlist_product_ids = array();
        }
    }

    public function riothere_handle_save_promotions($promotion_id)
    {
        if (!isset($_POST['promotions_settings_nonce'])) {
            return $promotion_id;
        }
        $nonce = $_POST['promotions_settings_nonce'];

        if (!wp_verify_nonce($nonce, 'promotions_settings_data')) {
            return $promotion_id;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $promotion_id;
        }

        $previously_applicable_product_ids = self::get_applicable_product_ids($promotion_id);
        $this->save_promotion($promotion_id);

        $newly_added_product_ids = [];
        $new_applicable_product_ids = self::get_new_applicable_product_ids($promotion_id);

        /*
         * - Handle filtering new rules to get the new added products that weren't previously available
         *
         * - Handle adding the promotion id relation to the product post type
         * */
        foreach ($new_applicable_product_ids as $applicable_product_id) {
            if (!in_array($applicable_product_id, $previously_applicable_product_ids)) {
                $newly_added_product_ids[] = $applicable_product_id;
            }

            $product_promotion_ids = self::get_product_promotion_ids($applicable_product_id);

            if (!in_array($applicable_product_id, $product_promotion_ids)) {
                $product_promotion_ids[] = $promotion_id;
                update_post_meta($applicable_product_id, 'promotion_ids', array_unique($product_promotion_ids));
            }
        }

        self::handle_sending_wishlist_emails($previously_applicable_product_ids, $newly_added_product_ids);

        // handle deleting the relation for the previous products with the promotion id if the previous product isn't available
        foreach ($previously_applicable_product_ids as $previous_added_product_id) {
            if (!in_array($previous_added_product_id, $new_applicable_product_ids)) {
                $product_promotion_ids = self::get_product_promotion_ids($previous_added_product_id);
                if (count($product_promotion_ids) > 0 && in_array($previous_added_product_id, $product_promotion_ids)) {
                    $updated_product_promotion_ids = array_diff($product_promotion_ids, [$promotion_id]);
                    update_post_meta($previous_added_product_id, 'promotion_ids', array_unique($updated_product_promotion_ids));
                }
            }
        }

        update_post_meta($promotion_id, 'promotion_applicable_product_ids', $new_applicable_product_ids);

        $products_by_seller = [];
        foreach ($newly_added_product_ids as $product_id) {
            $seller = get_field('seller', $product_id);

            if (is_array($seller)) {
                $seller_id = $seller['ID'];
                if (isset($products_by_seller[$seller_id])) {
                    $products_by_seller[$seller_id][] = $product_id;
                } else {
                    $products_by_seller[$seller_id] = [$product_id];
                }
            }
        }

        $this->handleSendingEmailsToSellers($products_by_seller);
    }

    /**
     * @param $products_by_seller array ( "seller_id" => [ "product_id", "product_id" ] )
     */
    public function handleSendingEmailsToSellers(array $products_by_seller)
    {
        foreach ($products_by_seller as $seller_id => $product_ids) {
            // @todo phase 3 send emails you have the seller id and the $product_ids newly added into promotion
        }
    }

    public static function get_sellers()
    {
        $args = [
            'role' => 'seller',
            'orderby' => 'user_nicename',
            'order' => 'ASC',
        ];

        return get_users($args);
    }

    public static function get_shop_parent_categories()
    {
        $cat_args = array(
            'orderby' => 'name',
            'order' => 'asc',
            'parent' => 0,
            'hide_empty' => false,
        );

        return get_terms('product_cat', $cat_args);

    }

    public static function get_shop_parent_tags()
    {
        $cat_args = array(
            'orderby' => 'name',
            'order' => 'asc',
            'parent' => 0,
            'hide_empty' => false,
        );

        return get_terms('product_tag', $cat_args);
    }

    public static function get_shop_products($args = [])
    {
        $defaults = array(
            'status' => 'publish', // UI will lag for large numbers. Limit to published ones only
            'limit' => -1,
        );

        $args = wp_parse_args($args, $defaults);
        $products_obj = wc_get_products($args);
        $products = [];

        foreach ($products_obj as $product_obj) {
            $image = wp_get_attachment_image_src($product_obj->get_image_id());
            // Price as set on the database level (regular price or sale price if set) not price after promotion
            $product_price = $product_obj->get_price('edit');

            $products[] = [
                'id' => $product_obj->get_id(),
                'title' => $product_obj->get_title(),
                'sku' => $product_obj->get_sku(),
                'price' => $product_price,
                'image_id' => $product_obj->get_image_id(),
                'image_url' => $image !== false ? $image[0] : '',
            ];
        }

        return $products;
    }

    public function riothere_ajax_promotions_get_products_by_rules()
    {
        check_ajax_referer('promotions', 'ajax_nonce');
        $result = [
            'success' => true,
            'data' => [],
        ];
        $rules = self::parse_rules($_POST);
        $args = self::get_product_args_from_rules($rules);

        $products = self::get_shop_products($args);
        $result['data'] = $products;
        echo json_encode($result);
        die();
    }

    public static function get_product_args_from_rules($rules)
    {
        $args = [];

        if (isset($rules['min_price'])) {
            $args['productPriceFrom'] = $rules['min_price'];
        }

        if (isset($rules['max_price'])) {
            $args['productPriceTo'] = $rules['max_price'];
        }

        if (isset($rules['sellers'])) {
            $sellers_ids = $rules['sellers'];
            $args['sellers_ids'] = $sellers_ids;
        }

        if (isset($rules['categories'])) {
            $categories_ids = $rules['categories'];
            $args['product_categories_ids'] = $categories_ids;
        }

        if (isset($rules['tags'])) {
            $tags_ids = $rules['tags'];
            $args['product_tags_ids'] = $tags_ids;
        }

        return $args;
    }

    public static function get_sellers_excluded_product_ids($promotion_id)
    {
        $sellers_excluded_product_ids = get_post_meta($promotion_id, 'sellers_excluded_product_ids', true);
        if ($sellers_excluded_product_ids === '') {
            $sellers_excluded_product_ids = [];
        }

        return $sellers_excluded_product_ids;
    }

    public static function get_applicable_product_ids($promotion_id)
    {
        $promotion_applicable_product_ids = get_post_meta($promotion_id, 'promotion_applicable_product_ids', true);

        if ($promotion_applicable_product_ids === '') {
            return [];
        } else {
            return $promotion_applicable_product_ids;
        }
    }

    public static function get_product_ids_without_sellers_excluded($promotion_id)
    {
        $promotion_applicable_product_ids = get_post_meta($promotion_id, 'promotion_applicable_product_ids', true);
        if ($promotion_applicable_product_ids === '') {
            return [];
        } else {
            $sellers_excluded_product_ids = self::get_sellers_excluded_product_ids($promotion_id);

            return array_diff($promotion_applicable_product_ids, $sellers_excluded_product_ids);
        }
    }

    /*
     * Only use this function on the save promotion
     *
     * ** This function will ignore the `give_promotion_to_new_added_items` setting
     * */
    public static function get_new_applicable_product_ids($promotion_id)
    {
        $include_rules = self::get_promotion_products_rules_by_type($promotion_id, 'include');
        $exclude_rules = self::get_promotion_products_rules_by_type($promotion_id, 'exclude');

        $sellers_excluded_product_ids = self::get_sellers_excluded_product_ids($promotion_id);

        $args = array(
            'status' => 'any',
            'limit' => -1,
            'return' => 'ids',
        );

        $exclude_ids = [];
        $exclude_rule_counter_check = 0;
        if (isset($exclude_rules['product_ids'])) {
            $exclude_ids = $exclude_rules['product_ids'];
            $exclude_rule_counter_check = 1;
        }

        if (count($exclude_rules) > $exclude_rule_counter_check) {

            $args_exclude = [
                'return' => 'ids',
                'status' => 'any',
                'limit' => -1,
            ];

            $args_exclude = array_merge($args_exclude, self::get_product_args_from_rules($exclude_rules));
            $exclude_products = wc_get_products($args_exclude);
            $exclude_ids = array_unique(array_merge($exclude_ids, $exclude_products));

        }

        // remove products that are excluded by sellers
        if (count($sellers_excluded_product_ids) > 0) {
            $exclude_ids = array_unique(array_merge($exclude_ids, $sellers_excluded_product_ids));
        }

        if (isset($include_rules['product_ids'])) {
            $product_ids = $include_rules['product_ids'];

            if (count($exclude_rules) > 0) {
                $product_ids = array_diff($product_ids, $exclude_ids);
            }
            $args['include'] = $product_ids;
        }

        $args = array_merge($args, self::get_product_args_from_rules($include_rules));

        if (count($exclude_ids) > 0) {
            $args['exclude'] = $exclude_ids;
        }

        return wc_get_products($args);

    }

    public static function get_promotion_products_rules_by_type($promotion_id, $type)
    {
        $rules = [];

        $products = get_post_meta($promotion_id, $type . '_products', true);
        $sellers = get_post_meta($promotion_id, $type . '_sellers', true);
        $product_categories = get_post_meta($promotion_id, $type . '_product_categories', true);
        $product_tags = get_post_meta($promotion_id, $type . '_product_tags', true);
        $product_min_price = get_post_meta($promotion_id, $type . '_product_min_price', true);
        $product_max_price = get_post_meta($promotion_id, $type . '_product_max_price', true);
        if ($products === '') {
            $products = [];
        }
        if ($sellers === '') {
            $sellers = [];
        }
        if ($product_categories === '') {
            $product_categories = [];
        }
        if ($product_tags === '') {
            $product_tags = [];
        }

        if (count($products) > 0) {
            $rules['product_ids'] = $products;
        }
        if (count($sellers) > 0) {
            $rules['sellers'] = $sellers;
        }
        if (count($product_categories) > 0) {
            $rules['categories'] = $product_categories;
        }
        if (count($product_tags) > 0) {
            $rules['tags'] = $product_tags;
        }
        if ($product_min_price !== '' && !is_nan((float) $product_min_price) && (float) $product_min_price >= 0) {
            $rules['min_price'] = (float) $product_min_price;
        }

        if (!is_nan((float) $product_max_price) && (float) $product_max_price > 0) {
            $rules['max_price'] = (float) $product_max_price;
        }

        return $rules;

    }

    public function riothere_ajax_get_products()
    {
        check_ajax_referer('promotions', 'ajax_nonce');

        $result = [
            'success' => true,
            'data' => [],
        ];

        $args = [];
        $include_rules = self::parse_rules($_POST['include']);
        $exclude_rules = self::parse_rules($_POST['exclude']);

        $exclude_ids = [];

        $exclude_rule_counter_check = 0;
        if (isset($exclude_rules['product_ids'])) {
            $exclude_ids = $exclude_rules['product_ids'];
            $exclude_rule_counter_check++;
        }

        if (count($exclude_rules) > $exclude_rule_counter_check) {

            $args_exclude = [
                'return' => 'ids',
                'status' => 'any',
                'limit' => -1,
            ];

            $args_exclude = array_merge($args_exclude, self::get_product_args_from_rules($exclude_rules));
            $exclude_products = wc_get_products($args_exclude);
            $exclude_ids = array_unique(array_merge($exclude_ids, $exclude_products));

        }

        if (isset($include_rules['product_ids'])) {
            $product_ids = $include_rules['product_ids'];
            if (count($exclude_rules) > 0) {
                $product_ids = array_diff($product_ids, $exclude_ids);
            }
            $args['include'] = $product_ids;
        }

        $args = array_merge($args, self::get_product_args_from_rules($include_rules));

        if (count($exclude_ids) > 0) {
            $args['exclude'] = $exclude_ids;
        }

        $products = self::get_shop_products($args);

        $result['data'] = $products;

        echo json_encode($result);
        die();
    }

    public static function parse_rules($rules_arr)
    {
        $rules = [];

        if (isset($rules_arr['sellers']) && is_array($rules_arr['sellers'])) {
            $rules['sellers'] = riothere_sanitize_array($rules_arr['sellers']);
        }

        if (isset($rules_arr['categories']) && is_array($rules_arr['categories'])) {
            $rules['categories'] = riothere_sanitize_array($rules_arr['categories']);
        }

        if (isset($rules_arr['tags']) && is_array($rules_arr['tags'])) {
            $rules['tags'] = riothere_sanitize_array($rules_arr['tags']);
        }

        if (isset($rules_arr['product_ids']) && is_array($rules_arr['product_ids'])) {
            $rules['product_ids'] = riothere_sanitize_array($rules_arr['product_ids']);
        }

        if (isset($rules_arr['min_price']) && $rules_arr['min_price'] !== '' && !is_nan((float) $rules_arr['min_price']) && (float) $rules_arr['min_price'] >= 0) {
            $rules['min_price'] = (float) sanitize_text_field($rules_arr['min_price']);
        }

        if (isset($rules_arr['max_price']) && !is_nan((float) $rules_arr['max_price']) && (float) $rules_arr['max_price'] > 0) {
            $rules['max_price'] = (float) sanitize_text_field($rules_arr['max_price']);
        }

        return $rules;
    }

    /**
     * Handle a custom 'customvar' query var to get products with the 'customvar' meta.
     *
     * @param array $query - Args for WP_Query.
     * @param array $query_vars - Query vars from WC_Product_Query.
     *
     * @return array modified $query
     */
    public function riothere_handle_custom_query_var($query, $query_vars)
    {
        if (isset($query_vars['sellers_ids']) && !empty($query_vars['sellers_ids'])) {
            $query['meta_query'][] = array(
                'key' => 'seller',
                'value' => array_map('esc_attr', $query_vars['sellers_ids']),
                'compare' => 'IN',
            );
            unset($query_vars['sellers_ids']);
        }

        if (isset($query_vars['product_categories_ids']) && !empty($query_vars['product_categories_ids'])) {
            $query['tax_query']['relation'] = 'AND';

            foreach ($query_vars['product_categories_ids'] as $tag_id) {
                $query['tax_query'][] = array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => esc_attr($tag_id),
                    'include_children' => true,
                );
            }
            unset($query_vars['product_categories_ids']);
        }

        if (isset($query_vars['product_tags_ids']) && !empty($query_vars['product_tags_ids'])) {
            $query['tax_query']['relation'] = 'AND';

            foreach ($query_vars['product_tags_ids'] as $tag_id) {
                $query['tax_query'][] = array(
                    'taxonomy' => 'product_tag',
                    'field' => 'term_id',
                    'terms' => esc_attr($tag_id),
                    'include_children' => true,
                );
            }
//            Example of the or relation ship
            //            $query['tax_query'][] = array(
            //                'taxonomy'         => 'product_tag',
            //                'field'            => 'term_id',
            //                'terms'            => array_map( 'esc_attr', $query_vars['product_tags_ids'] ),
            //                'operator'         => 'IN',
            //                'include_children' => true
            //            );
            unset($query_vars['product_tags_ids']);
        }

        return $query;
    }

}
