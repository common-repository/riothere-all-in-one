<?php
    /**
     * Created by PhpStorm.
     * User: gabykaram
     * Date: 8/28/21
     * Time: 10:24 PM
     * File: class-riothere-wc-report-customer-list.php
     */
    
    if ( ! class_exists('WC_Report_Customer_List')) {
        include_once(WC_ABSPATH.'includes/admin/reports/class-wc-report-customer-list.php');
    }
    if(!class_exists('My_WC_Report_Customer_List')) {
        class My_WC_Report_Customer_List extends WC_Report_Customer_List
        {
        
            /**
             * Get column value.
             *
             * @param  WP_User  $user
             * @param  string  $column_name
             *
             * @return string
             */
            public function column_default($user, $column_name)
            {
                global $wpdb;
            
                switch ($column_name) {
                
                    case 'city' :
                        return get_user_meta($user->ID, 'billing_city', true);
                }
            
                return parent::column_default($user, $column_name);
            }
        
            /**
             * Get columns.
             *
             * @return array
             */
            public function get_columns()
            {
            
                /* default columns.
                $columns = array(
                    'customer_name'   => __( 'Name (Last, First)', 'woocommerce' ),
                    'username'        => __( 'Username', 'woocommerce' ),
                    'email'           => __( 'Email', 'woocommerce' ),
                    'location'        => __( 'Location', 'woocommerce' ),
                    'orders'          => __( 'Orders', 'woocommerce' ),
                    'spent'           => __( 'Money spent', 'woocommerce' ),
                    'last_order'      => __( 'Last order', 'woocommerce' ),
                    'user_actions'    => __( 'Actions', 'woocommerce' ),
                ); */
            
                // sample adding City next to Location.
                $columns = array(
                    'customer_name' => __('Name (Last, First)', 'woocommerce'),
                    'username'      => __('Username', 'woocommerce'),
                    'email'         => __('Email', 'woocommerce'),
                    'location'      => __('Location', 'woocommerce'),
                    'city'          => __('City', 'woocommerce'),
                    'orders'          => __( 'Orders', 'woocommerce' ),
                    'spent'           => __( 'Money spent', 'woocommerce' ),
                    'last_order'      => __( 'Last order', 'woocommerce' ),
                    'user_actions'    => __( 'Actions', 'woocommerce' ),
                );
            
                return array_merge($columns, parent::get_columns());
            }
        }
    }
