<?php

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    add_filter('manage_edit-product_columns', 'riothere_add_column_to_product_table', 999);
    function riothere_add_column_to_product_table($columns)
    {
        $columns_array = array(
            'status' => 'Status',
            'date_of_listing' => 'Date of listing',
            'seller_name' => 'Seller',
            'promotion' => 'Promotion',
            'service' => 'Service',
        );
        foreach ($columns_array as $key => $value) {
            $columns[$key] = $value;
        }

        unset($columns['is_in_stock']);

        return $columns;
    }

    add_action('manage_product_posts_custom_column', 'riothere_add_product_content_to_column', 10, 2);
    function riothere_add_product_content_to_column($column, $product_id)
    {

        $columns_array = array(
            'status' => 'Status',
            'date_of_listing' => 'Date of listing',
            'seller_name' => 'Seller',
            'promotion' => 'Promotion',
            'service' => 'Service',
        );

        foreach ($columns_array as $key => $value) {
            $columns[$key] = $value;
            if ($column == $key) {
                if ($key === 'date_of_listing') {
                    $date = get_field('date_of_listing', $product_id);
                    $sec = strtotime($date);
                    $ndate = date("d-m-Y", $sec);
                    $date1 = new DateTime($ndate);
                    $today = new DateTime();
                    $interval = $date1->diff($today);
                    $diffrence_in_days = $interval->days;
                    echo esc_textarea("$date - $diffrence_in_days days ago");
                } else {
                    echo esc_textarea(get_field($key, $product_id));

                }
            }
        }

    }

    // remove product data tabs
    add_action('woocommerce_product_data_tabs', function ($tabs) {
        unset($tabs['advanced']);
        unset($tabs['attribute']);
        unset($tabs['variations']);
        return $tabs;
    });

    // remove Virtual and Downloadable checkboxes
    add_filter('product_type_options', function ($options) {

        // remove "Virtual" checkbox
        if (isset($options['virtual'])) {
            unset($options['virtual']);
        }

        // remove "Downloadable" checkbox
        if (isset($options['downloadable'])) {
            unset($options['downloadable']);
        }

        return $options;

    });

    // remove product types
    add_filter('product_type_selector', function ($types) {
        unset($types['grouped']);
        unset($types['external']);
        unset($types['variable']);

        return $types;
    });

    // Remove product type options filters
    add_filter('woocommerce_products_admin_list_table_filters', function ($filters) {

        if (isset($filters['product_type'])) {
            unset($filters['product_type']);
        }

        if (isset($filters['stock_status'])) {
            unset($filters['stock_status']);
        }
        return $filters;

    });

}
