<?php

add_filter('acf/load_field', 'riothere_set_cart_acf_fields_to_read_only');
function riothere_set_cart_acf_fields_to_read_only($field)
{
    if ('user_email' === $field['name']) {
        $field['disabled'] = true;
    }

    return $field;
}

function riothere_cart_init()
{
    $args = array(
        'labels' => array(
            'name' => 'Carts',
            'singular_name' => 'Cart',
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'capabilities' => array(
            'create_posts' => false,
        ),
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'carts'),
        'query_var' => true,
        'menu_icon' => 'dashicons-cart',
        'supports' => array(
            'custom-fields',
        ),
        'menu_position' => 56,
    );

    register_post_type('carts', $args);

    // Style needed for autocomplete select2 filters to render correctly
    wp_enqueue_style('woocommerce_admin_styles');
}

add_action('init', 'riothere_cart_init');

function riothere_remove_wp_seo_meta_box_carts()
{
    remove_meta_box('wpseo_meta', 'carts', 'normal');
}
add_action('add_meta_boxes', 'riothere_remove_wp_seo_meta_box_carts', 100);

function riothere_disable_yoast_seo_metabox_carts($post_types)
{
    unset($post_types['carts']);
    return $post_types;
}
add_filter('wpseo_accessible_post_types', 'riothere_disable_yoast_seo_metabox_carts');

add_filter('manage_edit-carts_columns', 'riothere_add_column_to_cart_table', 999);
function riothere_add_column_to_cart_table($columns)
{
    $columns_array = array(
        'user_id' => 'User ID',
        'date_modified' => 'Date Modified',
        'number_of_products' => '# Products',
        'total_value_of_cart' => 'Total (Pre VAT)',
        'is_cart_abandoned' => 'Cart Abandoned',
        // 'is_cart_fulfilled' => 'Is Cart Fulfilled',
        'first_email_sent' => '1st Email Sent',
        'first_email_opened' => '1st Email Opened',
        'first_email_action_taken' => '1st Email Action Taken',
        'second_email_sent' => '2nd Email Sent',
        'second_email_opened' => '2nd Email Opened',
        'second_email_action_taken' => '2nd Email Action Taken',
        'third_email_sent' => '3rd Email Sent',
        'third_email_opened' => '3rd Email Opened',
        'third_email_action_taken' => '3rd Email Action Taken',
        'products' => 'Products',
        'time_since_abandonment' => 'Time Since Abandonment',
    );
    foreach ($columns_array as $key => $value) {
        $columns[$key] = $value;
    }
    return $columns;
}

add_action('manage_carts_posts_custom_column', 'riothere_add_cart_content_to_column', 10, 2);
function riothere_add_cart_content_to_column($column, $cart_id)
{
    $all_products = get_posts(array(
        'post_type' => 'product',
        'numberposts' => -1,
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => 'test', /*category name*/
                'operator' => 'IN',
            ),
        ),
    )
    );

    $products_ids = array();

    foreach ($all_products as $product) {
        array_push($products_ids, $product->ID);
    }

    $cart_products = get_field('products', $cart_id, false);

    $cart_products_ids = array();
    foreach ($cart_products as $product) {
        array_push($cart_products_ids, $product['field_62dec05d9b7f3']['title']);
    }

    if ($column == 'user_id') {
        $value = get_field('user_id', $cart_id)['title'];
        if ($value === 'h') {
            $value = get_field('user_id', $cart_id)[-1];
        }

        $cart_user = get_user($value);
        $url = get_site_url() . "/wp-admin/user-edit.php?user_id=" . $cart_user->ID . "&wp_http_referer=%2Fwp-admin%2Fusers.php";
        echo '<a href="' . esc_url($url) . '" target = "_blank">' . esc_html($cart_user->ID) . '</a>';
    }

    if ($column == 'date_modified') {
        $value = get_field('date_modified', $cart_id);
        echo esc_textarea($value);
    }

    if ($column == 'number_of_products') {
        $value = get_field('number_of_products', $cart_id);
        echo is_array($value) ? esc_textarea($value[0]) : esc_textarea($value);
    }

    if ($column == 'total_value_of_cart') {
        $value = get_field('total_value_of_cart', $cart_id);
        echo esc_textarea($value);
    }

    if ($column == 'is_cart_abandoned') {
        $value = get_field('is_cart_abandoned', $cart_id);
        echo $value ? 'Yes' : 'No';
    }

    // if ($column == 'is_cart_fulfilled') {
    //     $value = get_field('is_cart_fulfilled', $cart_id);
    //     echo $value ? 'Yes' : 'No';
    // }

    if ($column == 'first_email_sent') {
        $value = get_field('first_email_sent', $cart_id);
        echo $value ? 'Yes' : 'No';
    }

    if ($column == 'first_email_opened') {
        $value = get_field('first_email_opened', $cart_id);
        echo $value ? 'Yes' : 'No';
    }

    if ($column == 'first_email_action_taken') {
        $value = get_field('first_email_action_taken', $cart_id);
        echo $value ? 'Yes' : 'No';
    }

    if ($column == 'second_email_sent') {
        $value = get_field('second_email_sent', $cart_id);
        echo $value ? 'Yes' : 'No';
    }

    if ($column == 'second_email_opened') {
        $value = get_field('second_email_opened', $cart_id);
        echo $value ? 'Yes' : 'No';
    }

    if ($column == 'second_email_action_taken') {
        $value = get_field('second_email_action_taken', $cart_id);
        echo $value ? 'Yes' : 'No';
    }

    if ($column == 'third_email_sent') {
        $value = get_field('third_email_sent', $cart_id);
        echo $value ? 'Yes' : 'No';
    }

    if ($column == 'third_email_opened') {
        $value = get_field('third_email_opened', $cart_id);
        echo $value ? 'Yes' : 'No';
    }

    if ($column == 'third_email_action_taken') {
        $value = get_field('third_email_action_taken', $cart_id);
        echo $value ? 'Yes' : 'No';
    }

    if ($column == 'time_since_abandonment') {
        $current_date = date('m/d/Y h:i:s a', time());

        $d1 = new datetime($current_date);

        $cart_last_modified_date = get_field('date_modified', $cart_id, true);
        $d2 = DateTime::createFromFormat("d/m/Y g:i a", $cart_last_modified_date);

        $interval = $d1->diff($d2);
        $days_in_hours = ($interval->d) * 24;

        if (($interval->h + $days_in_hours) >= 24) {
            echo esc_textarea(($interval->h + $days_in_hours) . ' hours and ' . $interval->i . ' minutes');
        } else {
            echo 'Not abandoned';
        }

    }

    if ($column == 'products') {
        $products = get_field('products', $cart_id);
        $product_labels = [];

        if (is_array($products)) {
            foreach ($products as $value) {
                array_push($product_labels, $value['sku']);
            }
        }

        echo esc_textarea(join(", ", $product_labels));
    }

}

add_action('pre_get_posts', 'riothere_carts_default_sorting', 1);
function riothere_carts_default_sorting($query)
{
    if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'carts' && !isset($query->query_vars['orderby'])) {
        $query->set('orderby', 'meta_value');
        $query->set('meta_key', 'date_modified');
        $query->set('order', 'DESC');
    }
}

add_action('pre_get_posts', 'riothere_carts_show_only_non_fulfilled', 1);
function riothere_carts_show_only_non_fulfilled($query)
{
    $on_main_cart_page = isset($_GET['post_type']) && sanitize_text_field($_GET['post_type']) === 'carts' && !isset($_GET['page']);

    if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'carts' && $on_main_cart_page) {
        $query->set('meta_query', array(
            array(
                'key' => 'is_cart_fulfilled', // the user can have multiple carts but only 1 that is not fulfilled
                'value' => '0',
                'compare' => '=',
            ),
        ));
    }
}

/*
Plugin Name: Simple PHPExcel Export
Description: Simple PHPExcel Export Plugin for WordPress
Version: 1.0.0
Author: Mithun
Author URI: http://twitter.com/mithunp
 */

define("RIOTHERE_SPEE_PLUGIN_URL_CARTS", WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)));
define("RIOTHERE_SPEE_PLUGIN_DIR_CARTS", WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)));

add_action('admin_menu', 'riothere_cart_admin_menu');

function riothere_cart_admin_menu()
{
    add_menu_page('PHPExcel Export', 'Export', 'manage_options', 'cart-spee-dashboard', 'cart_spee_dashboard');
}

add_action('admin_menu', 'riothere_cart_menu_items');

function riothere_cart_menu_items()
{
    remove_menu_page('cart-spee-dashboard');
}

function cart_spee_dashboard()
{

    global $wpdb;

    if (isset($_GET['export'])) {

        if (file_exists(RIOTHERE_SPEE_PLUGIN_DIR_CARTS . '/lib/PHPExcel.php')) {

            //Include PHPExcel
            require_once RIOTHERE_SPEE_PLUGIN_DIR_CARTS . "/lib/PHPExcel.php";

            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();

            // Set document properties

            // Add some data
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Title');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Date');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', 'User ID');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Number of Products');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Total Value of Cart');
            $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Is Cart Abandoned');
            $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Is Cart Fulfilled');
            $objPHPExcel->getActiveSheet()->setCellValue('H1', 'First Email Sent');
            $objPHPExcel->getActiveSheet()->setCellValue('I1', 'First Email Opened');
            $objPHPExcel->getActiveSheet()->setCellValue('J1', 'First Email Action Taken');
            $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Second Email Sent');
            $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Second Email Opened');
            $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Second Email Action Taken');
            $objPHPExcel->getActiveSheet()->setCellValue('N1', 'Third Email Sent');
            $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Third Email Opened');
            $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Third Email Action Taken');
            $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Product SKUs');

            $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A:Q')->setAutoSize(true);

            $passed_category_id = absint($_GET['category_id']);
            $passed_product_id = absint($_GET['product_id']);
            $passed_product_brand = absint($_GET['product_brand']);
            $passed_product_size = absint($_GET['product_size']);
            $passed_product_color = absint($_GET['product_color']);

            $args = [
                'post_type' => 'product',
                'numberposts' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $passed_category_id, /// Where term_id of Term 1 is "1".
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

            $carts = get_posts([
                'post_type' => 'carts',
                'status' => 'publish',
                'nopaging' => true,
                'meta_query' => array(
                    array(
                        'key' => 'is_cart_fulfilled', // the user can have multiple carts but only 1 that is not fulfilled
                        'value' => '0',
                        'compare' => '=',
                    ),
                ),
            ]);

            $all_cart_ids = array_map(function ($item) {
                return $item->ID;
            }, $carts);

            $applicable_carts = array();

            $query = "
            SELECT * FROM wp_posts p
            WHERE p.post_type = 'carts'
            AND p.post_status =  'publish'
            AND p.ID IN (" . implode(', ', $all_cart_ids) . ")
            ";

            if ($passed_category_id) {
                foreach ($carts as $cart) {
                    $cart_products = get_field('products', $cart->ID, false);

                    foreach ($cart_products as $product) {
                        if (in_array($product['field_62dec05d9b7f3']['title'], $product_ids)) {
                            array_push($applicable_carts, $cart->ID);
                            break;
                        }
                    }
                }
                if (count($applicable_carts) !== 0) {
                    $query .= " AND p.ID IN (" . implode(', ', $applicable_carts) . ")";
                }
            }

            if ($passed_product_id) {
                $applicable_carts = [];
                foreach ($carts as $cart) {
                    $cart_products = get_field('products', $cart->ID, false);

                    foreach ($cart_products as $product) {
                        if ((int) $product['field_62dec05d9b7f3']['title'] === (int) $passed_product_id) {
                            array_push($applicable_carts, $cart->ID);
                            break;
                        }
                    }
                }
                if (count($applicable_carts) !== 0) {
                    $query .= " AND p.ID IN (" . implode(', ', $applicable_carts) . ")";
                }
            }

            if ($passed_product_brand) {
                $applicable_carts = [];
                foreach ($carts as $cart) {
                    $cart_products = get_field('products', $cart->ID, false);

                    foreach ($cart_products as $product) {
                        $product_data = Riothere_All_In_One_Admin::get_product_categories_data($product['field_62dec05d9b7f3']['title']);
                        if (in_array($passed_product_brand, $product_data['category_ids'])) {
                            array_push($applicable_carts, $cart->ID);
                            break;
                        }
                    }
                }
                if (count($applicable_carts) !== 0) {
                    $query .= " AND p.ID IN (" . implode(', ', $applicable_carts) . ")";
                } else {
                    $query = "";
                }
            }

            if ($passed_product_size) {
                $applicable_carts = [];
                foreach ($carts as $cart) {
                    $cart_products = get_field('products', $cart->ID, false);

                    foreach ($cart_products as $product) {
                        $product_data = Riothere_All_In_One_Admin::get_product_categories_data($product['field_62dec05d9b7f3']['title']);
                        if (in_array($passed_product_brand, $product_data['category_ids'])) {
                            array_push($applicable_carts, $cart->ID);
                            break;
                        }
                    }
                }
                if (count($applicable_carts) !== 0) {
                    $query .= " AND p.ID IN (" . implode(', ', $applicable_carts) . ")";
                } else {
                    $query = "";
                }
            }

            if ($passed_product_color) {
                $applicable_carts = [];
                foreach ($carts as $cart) {
                    $cart_products = get_field('products', $cart->ID, false);

                    foreach ($cart_products as $product) {
                        $product_data = Riothere_All_In_One_Admin::get_product_categories_data($product['field_62dec05d9b7f3']['title']);

                        if (in_array($passed_product_color, $product_data['category_ids'])) {
                            array_push($applicable_carts, $cart->ID);
                        }

                    }
                }
                if (count($applicable_carts) !== 0) {
                    $query .= " AND p.ID IN (" . implode(', ', $applicable_carts) . ")";
                } else {
                    $query = "";
                }
            }

            $posts = $wpdb->get_results($query);

            if ($posts) {
                foreach ($posts as $i => $post) {
                    $products = get_field('products', $post->ID, true);
                    $product_skus = [];

                    foreach ($products as $product) {
                        $product_skus[] = $product["sku"];
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i + 2), $post->post_title);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . ($i + 2), $post->date);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . ($i + 2), $post->post_author);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . ($i + 2), get_field('number_of_products', $post->ID, false));
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . ($i + 2), get_field('total_value_of_cart', $post->ID, false));
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . ($i + 2), get_field('is_cart_abandoned', $post->ID, false) ? 'Yes' : 'No');
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . ($i + 2), get_field('is_cart_fulfilled', $post->ID, false) ? 'Yes' : 'No');
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . ($i + 2), get_field('first_email_sent', $post->ID, false) ? 'Yes' : 'No');
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . ($i + 2), get_field('first_email_opened', $post->ID, false) ? 'Yes' : 'No');
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . ($i + 2), get_field('first_email_action_taken', $post->ID, false) ? 'Yes' : 'No');
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . ($i + 2), get_field('second_email_sent', $post->ID, false) ? 'Yes' : 'No');
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . ($i + 2), get_field('second_email_opened', $post->ID, false) ? 'Yes' : 'No');
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . ($i + 2), get_field('second_email_action_taken', $post->ID, false) ? 'Yes' : 'No');
                    $objPHPExcel->getActiveSheet()->setCellValue('N' . ($i + 2), get_field('third_email_sent', $post->ID, false) ? 'Yes' : 'No');
                    $objPHPExcel->getActiveSheet()->setCellValue('O' . ($i + 2), get_field('third_email_opened', $post->ID, false) ? 'Yes' : 'No');
                    $objPHPExcel->getActiveSheet()->setCellValue('P' . ($i + 2), get_field('third_email_action_taken', $post->ID, false) ? 'Yes' : 'No');
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . ($i + 2), implode(', ', $product_skus));
                }
            }

            // Rename worksheet
            //$objPHPExcel->getActiveSheet()->setTitle('Simple');

            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);

            // Redirect output to a client’s web browser
            $file_name = "carts.csv";
            ob_clean();
            ob_start();
            switch ($_GET['format']) {
                case 'csv':
                    // Redirect output to a client’s web browser (CSV)
                    header("Content-type: text/csv");
                    header("Cache-Control: no-store, no-cache");
                    header('Content-Disposition: attachment; filename="' . $file_name . '"');
                    $objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
                    $objWriter->setDelimiter(',');
                    $objWriter->setEnclosure('"');
                    $objWriter->setLineEnding("\r\n");
                    //$objWriter->setUseBOM(true);
                    $objWriter->setSheetIndex(0);
                    $objWriter->save('php://output');
                    break;
                case 'xls':
                    // Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $file_name . '"');
                    header('Cache-Control: max-age=0');
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                    $objWriter->save('php://output');
                    break;
                case 'xlsx':
                    // Redirect output to a client’s web browser (Excel2007)
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $file_name . '"');
                    header('Cache-Control: max-age=0');
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                    $objWriter->save('php://output');
                    break;
            }
            exit;
        }
    }
}

add_action('woocommerce_product_data_tabs', 'riothere_add_cart_tab_on_product_page', 25, 1);
function riothere_add_cart_tab_on_product_page($default_tabs)
{
    $default_tabs['cart_tab'] = array(
        'label' => __('Carts', 'domain'),
        'target' => 'wk_cart_tab_data',
        'priority' => 60,
        'class' => array(),
    );
    return $default_tabs;
}

global $product;
add_action('woocommerce_product_data_panels', 'riothere_populate_cart_tab');
function riothere_populate_cart_tab()
{
    $current_product_id = get_the_ID();

    $carts = get_posts([
        'post_type' => 'carts',
        'status' => 'publish',
        'nopaging' => true,
        'meta_query' => array(
            array(
                'key' => 'is_cart_fulfilled', // the user can have multiple carts but only 1 that is not fulfilled
                'value' => '0',
                'compare' => '=',
            ),
        ),
    ]);

    ?>
    <div id="wk_cart_tab_data" class="panel woocommerce_options_panel hidden" style="padding:10px;">
    <div>
                <div class="fields">
                    <?php
echo '<b>Users that have this item in their cart: </b>';
    echo '<div style="display: flex">';
    foreach ($carts as $cart) {
        $my_products = get_field('products', $cart->ID, false);
        foreach ($my_products as $product) {
            if (in_array($current_product_id, $product['field_62dec05d9b7f3'])) {
                $user = new WC_Customer($cart->post_author);
                $url = get_site_url() . "/wp-admin/user-edit.php?user_id=" . $user->data['id'] . "&wp_http_referer=%2Fwp-admin%2Fusers.php";

                echo '<div>';
                echo '<a href="' . esc_url($url) . '" target = "_blank">' . esc_html($user->data['id']) . '</a>';
                echo '</div>';
                echo '<div style="padding-left:5px; padding-right:5px"> | </div>';
            }
        }
    }
    echo '</div>';
    ?>
                </div>
            </div>
        </div>
    <?php
}

// To style the icon on the cart tab in Single Product Page
add_action('admin_head', 'riothere_cart_icon_in_product_data_panels');
function riothere_cart_icon_in_product_data_panels()
{
    echo '<style>
	#woocommerce-product-data ul.wc-tabs .cart_tab_options a:before{
		content: "\f174";
	}
	</style>';
}

// The below hook runs when Admin visits a certain cart. It refreshes
// the values of the products in the cart in case they got changed AFTER
// user added said products to their cart
add_action('admin_enqueue_scripts', 'riothere_on_cart_page_visit');
function riothere_on_cart_page_visit($hook)
{
    global $post;
    $screen = get_current_screen();

    if ($hook != 'post.php' || $screen->post_type != 'carts') {
        return;
    }

    $cart_id = $post->ID;
    $rows = get_field('products', $cart_id, false);

    $counter = 1;
    foreach ($rows as $row) {
        // field_62dec05d9b7f3 is the 'id' field.
        // TODO find a way NOT to use `field_62dec05d9b7f3` since it is auto-generated and unreadable
        $product_id = $row['field_62dec05d9b7f3']['title'];

        $product = wc_get_product($product_id);
        if (!($product instanceof WC_Product)) {
            continue;
        }

        $row = [
            'id' => [
                "url" => get_site_url() . "/wp-admin/post.php?post=" . $product->get_id() . "&action=edit",
                "title" => $product->get_id(),
            ],
            'sku' => $product->get_sku(),
            'name' => $product->get_name(),
            'price_aed' => $product->get_price(),
            'is_on_promotion' => (float) $product->get_price() !== (float) $product->get_regular_price() ? 'checked' : '',
        ];

        update_row('products', $counter, $row);
        $counter++;
    }
}

//Functions to filter carts by categories
//Hooks for cart table filter by category id
add_action('parse_query', 'riothere_filter_carts_by_specific_category_id_query');
add_action('restrict_manage_posts', 'riothere_filter_carts_by_specific_category_id_html');
add_action('wp_ajax_woocommerce_json_search_categories', 'riothere_json_search_carts_categories');
add_action('posts_clauses', 'riothere_filter_carts_by_specific_category_id_clauses', 10, 2);

function riothere_filter_carts_by_specific_category_id_html()
{
    global $typenow;
    global $wp_query;
    if ($typenow === 'carts') {
        $category_string = '';
        $category_id = '';

        if (!empty($_GET['_category_id'])) {
            $category_id = absint($_GET['_category_id']); // WPCS: input var ok, sanitization ok.
            $category = get_term($category_id, 'product_cat');

            $category_string = sprintf(
                /* translators: 1: Brand ID 2: brand name */
                esc_html__('#%1$s - %2$s', 'woocommerce'),
                absint($category->term_id),
                $category->name
            );

        }
        ?>
            <select class="wc-riothere-main-categories-search" name="_category_id"
                    data-placeholder="<?php esc_attr_e('Filter by Category', 'woocommerce');?>"
                    data-allow_clear="true">
                <option value="<?php echo esc_attr($category_id); ?>"
                        selected="selected"><?php echo htmlspecialchars(wp_kses_post($category_string)); ?></option>
            </select>
			<?php
}
}

function riothere_json_search_carts_categories()
{
    ob_start();

    check_ajax_referer('search-categories', 'security');

    // if (!current_user_can('edit_wishlists')) {
    //     wp_die(-1);
    // }

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
    wp_send_json(apply_filters('woocommerce_json_search_found_categories', $found_main_categories));
}

function riothere_filter_carts_by_specific_category_id_query($query)
{
    global $pagenow;
    global $wpdb;

    // Get the post type
    $post_type = sanitize_text_field($_GET['post_type']) ?? '';
    try {
        if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'carts' && $pagenow === 'edit.php' && $post_type === 'carts' && isset($_GET['_category_id']) && !empty($_GET['_category_id'])) {
            $query->query_vars['_category_id'] = absint($_GET['_category_id']);
        }
    } catch (Exception $exception) {

    }

    return $query;
}

function riothere_filter_carts_by_specific_category_id_clauses($clauses, $wp_query)
{
    global $wpdb;

    if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'carts' && isset($wp_query->query_vars['_category_id'])) {

        $main_category_id = $wp_query->query_vars['_category_id'];

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

        $carts = get_posts([
            'post_type' => 'carts',
            'status' => 'publish',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => 'is_cart_fulfilled', // the user can have multiple carts but only 1 that is not fulfilled
                    'value' => '0',
                    'compare' => '=',
                ),
            ),
        ]);

        $applicable_carts = array();

        foreach ($carts as $cart) {
            $cart_products = get_field('products', $cart->ID, false);

            foreach ($cart_products as $product) {
                if (in_array($product['field_62dec05d9b7f3']['title'], $product_ids)) {
                    array_push($applicable_carts, $cart->ID);
                    break;
                }
            }
        }

        $clauses['where'] .= " AND {$wpdb->prefix}posts.id IN (" . implode(', ', $applicable_carts) . ")";
    }
    return $clauses;
}

//Hooks for cart table filter by SKU
add_action('parse_query', 'riothere_filter_carts_by_specific_product_id_query');
add_action('restrict_manage_posts', 'riothere_filter_carts_by_specific_product_id_html');
add_action('wp_ajax_woocommerce_json_search_sku', 'riothere_json_search_carts_product_id');
add_action('posts_clauses', 'riothere_filter_carts_by_specific_product_id_clauses', 10, 2);

//SKU Filter
function riothere_filter_carts_by_specific_product_id_html()
{
    global $typenow;
    global $wp_query;
    if ($typenow === 'carts') {
        $category_string = '';
        $product_id = '';
        $current_product = new WC_Product_Simple();

        if (!empty($_GET['_product_id'])) {
            $product_id = absint($_GET['_product_id']); // WPCS: input var ok, sanitization ok.
            $product = wc_get_product($product_id);
            $product_name = $product->get_name();

            $category_string = sprintf(
                /* translators: 1: Brand ID 2: brand name */
                esc_html__('#%1$s - %2$s', 'woocommerce'),
                absint($product_id),
                $product_name
            );
        }
        ?>
            <select class="wc-riothere-products-search" name="_product_id"
                    data-placeholder="<?php esc_attr_e('Search by Product (SKU/name)', 'woocommerce');?>"
                    data-allow_clear="true">
                <option value="<?php echo esc_attr($product_id); ?>"
                        selected="selected"><?php echo htmlspecialchars(wp_kses_post($category_string)); ?></option>
            </select>
			<?php
}
}

function riothere_json_search_carts_product_id()
{
    ob_start();

    check_ajax_referer('search-skus', 'security');

    $term = isset($_GET['term']) ? (string) wc_clean(wp_unslash($_GET['term'])) : '';

    if (empty($term)) {
        wp_die();
    }

    $filtered_main_brands = [];

    $main_products = get_posts([
        'post_type' => 'product',
        'status' => 'publish',
        'nopaging' => true,
    ]);

    foreach ($main_products as $main_brand) {
        $current_product = wc_get_product($main_brand->ID);
        $current_product_id_exist = Riothere_All_In_One_Admin::like_match($current_product->get_id(), $term);
        $current_product_name_exist = Riothere_All_In_One_Admin::like_match($current_product->get_name(), $term);
        $current_product_slug_exist = Riothere_All_In_One_Admin::like_match($current_product->get_sku(), $term);

        if ($current_product_id_exist || $current_product_name_exist || $current_product_slug_exist) {
            $filtered_main_brands[] = $current_product->get_data();
        }
    }

    $found_main_brands = array();

    foreach ($filtered_main_brands as $main_brand) {
        /* translators: 1: user display name 2: user ID 3: user email */
        $found_main_brands[$main_brand['id']] = sprintf(
            /* translators: $1: Brand id, $2 Brand name */
            esc_html__('%1$s - %2$s', 'woocommerce'),
            $main_brand['sku'],
            $main_brand['name']
        );
    }

    wp_send_json(apply_filters('woocommerce_json_search_found_skus', $found_main_brands));
}

function riothere_filter_carts_by_specific_product_id_query($query)
{
    global $pagenow;
    global $wpdb;

    // Get the post type
    $post_type = $_GET['post_type'] ?? '';
    try {
        if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'carts' && $pagenow === 'edit.php' && $post_type === 'carts' && isset($_GET['_product_id']) && !empty($_GET['_product_id'])) {
            $query->query_vars['_product_id'] = absint($_GET['_product_id']);
        }
    } catch (Exception $exception) {

    }

    return $query;
}

function riothere_filter_carts_by_specific_product_id_clauses($clauses, $wp_query)
{
    global $wpdb;

    if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'carts' && isset($wp_query->query_vars['_product_id'])) {

        $main_product_id = $wp_query->query_vars['_product_id'];

        $carts = get_posts([
            'post_type' => 'carts',
            'status' => 'publish',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => 'is_cart_fulfilled', // the user can have multiple carts but only 1 that is not fulfilled
                    'value' => '0',
                    'compare' => '=',
                ),
            ),
        ]);

        $applicable_carts = array();

        foreach ($carts as $cart) {
            $cart_products = get_field('products', $cart->ID, false);

            foreach ($cart_products as $product) {
                if ((int) $product['field_62dec05d9b7f3']['title'] === (int) $main_product_id) {
                    array_push($applicable_carts, $cart->ID);
                    break;
                }
            }
        }

        $clauses['where'] .= " AND {$wpdb->prefix}posts.id IN (" . implode(', ', $applicable_carts) . ")";
    }
    return $clauses;
}

//Filter by brands/designers
add_action('parse_query', 'riothere_filter_carts_by_specific_brand_query');
add_action('restrict_manage_posts', 'riothere_filter_carts_by_specific_brand_html');
add_action('wp_ajax_woocommerce_json_search_carts_brand', 'riothere_json_search_carts_brand');
add_action('posts_clauses', 'riothere_filter_carts_by_specific_product_brand_clauses', 10, 2);

function riothere_filter_carts_by_specific_brand_html()
{
    global $typenow;
    global $wp_query;
    if ($typenow === 'carts') {
        $category_string = '';
        $product_brand_id = '';
        $current_product = new WC_Product_Simple();

        if (!empty($_GET['_product_brand'])) {
            $product_brand_id = absint($_GET['_product_brand']); // WPCS: input var ok, sanitization ok.
            $brand_name = get_term_by('id', $product_brand_id, 'product_cat')->name;

            $category_string = sprintf(
                /* translators: 1: Brand ID 2: brand name */
                esc_html__('#%1$s - %2$s', 'woocommerce'),
                absint($product_brand_id),
                $brand_name
            );
        }
        ?>
            <select class="wc-riothere-carts-brands-search" name="_product_brand"
                    data-placeholder="<?php esc_attr_e('Filter by Product Brand/Designer', 'woocommerce');?>"
                    data-allow_clear="true">
                <option value="<?php echo esc_attr($product_brand_id); ?>" selected="selected">
                    <?php echo htmlspecialchars(wp_kses_post($category_string)); ?>
                </option>
            </select>
			<?php
}
}

function riothere_json_search_carts_brand()
{
    ob_start();

    check_ajax_referer('search-cart-brands', 'security');

    $term = isset($_GET['term']) ? (string) wc_clean(wp_unslash($_GET['term'])) : '';

    if (empty($term)) {
        wp_die();
    }

    $filtered_brands = [];

    $parent_category = get_term_by('slug', 'designers', 'product_cat');

    $main_brands = get_terms([
        'product_cat',
    ], [
        'hide_empty' => false,
        'parent' => $parent_category->term_id,
        // 'child_of' => $parent_category->term_id,
    ]);

    foreach ($main_brands as $brand) {
        $brand_id_exist = Riothere_All_In_One_Admin::like_match($brand->term_id, $term);
        $brand_name_exist = Riothere_All_In_One_Admin::like_match($brand->name, $term);
        $brand_slug_exist = Riothere_All_In_One_Admin::like_match($brand->slug, $term);

        if ($brand_id_exist || $brand_name_exist || $brand_slug_exist) {
            $filtered_brands[] = $brand;
        }
    }

    $found_brands = array();

    foreach ($filtered_brands as $brand) {
        /* translators: 1: user display name 2: user ID 3: user email */
        $found_brands[$brand->term_id] = sprintf(
            /* translators: $1: brand id, $2 brand name */
            esc_html__('#%1$s - %2$s', 'woocommerce'),
            $brand->term_id,
            $brand->name
        );
    }

    wp_send_json(apply_filters('woocommerce_json_search_carts_brand', $found_brands));
}

function riothere_filter_carts_by_specific_brand_query($query)
{
    global $pagenow;
    global $wpdb;

    // Get the post type
    $post_type = sanitize_text_field($_GET['post_type']) ?? '';
    try {
        if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'carts' && $pagenow === 'edit.php' && $post_type === 'carts' && isset($_GET['_product_brand']) && !empty($_GET['_product_brand'])) {
            $query->query_vars['_product_brand'] = absint($_GET['_product_brand']);
        }
    } catch (Exception $exception) {

    }

    return $query;
}

function riothere_filter_carts_by_specific_product_brand_clauses($clauses, $wp_query)
{
    global $wpdb;

    if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'carts' && isset($wp_query->query_vars['_product_brand'])) {

        $main_product_brand = $wp_query->query_vars['_product_brand'];

        $carts = get_posts([
            'post_type' => 'carts',
            'status' => 'publish',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => 'is_cart_fulfilled', // the user can have multiple carts but only 1 that is not fulfilled
                    'value' => '0',
                    'compare' => '=',
                ),
            ),
        ]);

        $all_available_products = get_posts([
            'post_type' => 'product',
            'status' => 'publish',
            'nopaging' => true,
        ]);

        $matching_products = array();

        foreach ($all_available_products as $product) {
            $category_data = Riothere_All_In_One_Admin::get_product_categories_data($product->ID);

            foreach ($category_data['category_ids'] as $cat_id) {
                if ((int) $cat_id === (int) $main_product_brand) {
                    array_push($matching_products, $product->ID);
                }
            }
        }

        $applicable_carts = array();

        foreach ($carts as $cart) {
            $cart_products = get_field('products', $cart->ID, false);

            foreach ($cart_products as $product) {
                if (in_array($product['field_62dec05d9b7f3']['title'], $matching_products)) {
                    array_push($applicable_carts, $cart->ID);
                    break;
                }
            }
        }

        $clauses['where'] .= " AND {$wpdb->prefix}posts.id IN (" . implode(', ', $applicable_carts) . ")";
    }
    return $clauses;
}

//Filter by color
add_action('parse_query', 'riothere_filter_carts_by_specific_color_query');
add_action('restrict_manage_posts', 'riothere_filter_carts_by_specific_color_html');
add_action('wp_ajax_woocommerce_json_search_carts_colors', 'riothere_json_search_carts_color');
add_action('posts_clauses', 'riothere_filter_carts_by_specific_product_color_clauses', 10, 2);

function riothere_filter_carts_by_specific_color_html()
{
    global $typenow;
    global $wp_query;
    if ($typenow === 'carts') {
        $category_string = '';
        $product_color_id = '';
        $current_product = new WC_Product_Simple();

        if (!empty($_GET['_product_color'])) {
            $product_color_id = absint($_GET['_product_color']); // WPCS: input var ok, sanitization ok.
            $color_name = get_term_by('id', $product_color_id, 'product_cat')->name;

            $category_string = sprintf(
                /* translators: 1: Color ID 2: color name */
                esc_html__('#%1$s - %2$s', 'woocommerce'),
                absint($product_color_id),
                $color_name
            );
        }
        ?>
            <select class="wc-riothere-carts-colors-search" name="_product_color"
                    data-placeholder="<?php esc_attr_e('Filter by Product Color', 'woocommerce');?>"
                    data-allow_clear="true">
                <option value="<?php echo esc_attr($product_color_id); ?>"
                        selected="selected"><?php echo htmlspecialchars(wp_kses_post($category_string)); ?></option>
            </select>
			<?php

    }
}

function riothere_json_search_carts_color()
{
    ob_start();

    check_ajax_referer('search-cart-colors', 'security');

    $term = isset($_GET['term']) ? (string) wc_clean(wp_unslash($_GET['term'])) : '';

    if (empty($term)) {
        wp_die();
    }

    $filtered_colors = [];

    $parent_category = get_term_by('slug', 'colors', 'product_cat');
    $main_colors = get_terms([
        'product_cat',
    ], [
        'hide_empty' => false,
        'parent' => $parent_category->term_id,
        // 'child_of' => $parent_category->term_id,
    ]);

    foreach ($main_colors as $color) {
        $color_id_exist = Riothere_All_In_One_Admin::like_match($color->term_id, $term);
        $color_name_exist = Riothere_All_In_One_Admin::like_match($color->name, $term);
        $color_slug_exist = Riothere_All_In_One_Admin::like_match($color->slug, $term);

        if ($color_id_exist || $color_name_exist || $color_slug_exist) {
            $filtered_colors[] = $color;
        }
    }

    $found_colors = array();

    foreach ($filtered_colors as $color) {
        /* translators: 1: user display name 2: user ID 3: user email */
        $found_colors[$color->term_id] = sprintf(
            /* translators: $1: color id, $2 color name */
            esc_html__('#%1$s - %2$s', 'woocommerce'),
            $color->term_id,
            $color->name
        );
    }

    wp_send_json(apply_filters('woocommerce_json_search_carts_colors', $found_colors));
}

function riothere_filter_carts_by_specific_color_query($query)
{
    global $pagenow;
    global $wpdb;

    // Get the post type
    $post_type = sanitize_text_field($_GET['post_type']) ?? '';
    try {
        if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'carts' && $pagenow === 'edit.php' && $post_type === 'carts' && isset($_GET['_product_color']) && !empty($_GET['_product_color'])) {
            $query->query_vars['_product_color'] = absint($_GET['_product_color']);
        }
    } catch (Exception $exception) {

    }
    return $query;
}

function riothere_filter_carts_by_specific_product_color_clauses($clauses, $wp_query)
{
    global $wpdb;

    if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'carts' && isset($wp_query->query_vars['_product_color'])) {

        $main_product_brand = $wp_query->query_vars['_product_color'];

        $carts = get_posts([
            'post_type' => 'carts',
            'status' => 'publish',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => 'is_cart_fulfilled', // the user can have multiple carts but only 1 that is not fulfilled
                    'value' => '0',
                    'compare' => '=',
                ),
            ),
        ]);

        $all_available_products = get_posts([
            'post_type' => 'product',
            'status' => 'publish',
            'nopaging' => true,
        ]);

        $matching_products = array();

        foreach ($all_available_products as $product) {
            $category_data = Riothere_All_In_One_Admin::get_product_categories_data($product->ID);

            foreach ($category_data['category_ids'] as $cat_id) {
                if ((int) $cat_id === (int) $main_product_brand) {
                    array_push($matching_products, $product->ID);
                }
            }
        }

        $applicable_carts = array();

        foreach ($carts as $cart) {
            $cart_products = get_field('products', $cart->ID, false);

            foreach ($cart_products as $product) {
                if (in_array($product['field_62dec05d9b7f3']['title'], $matching_products)) {
                    array_push($applicable_carts, $cart->ID);
                    break;
                }
            }
        }

        $clauses['where'] .= " AND {$wpdb->prefix}posts.id IN (" . implode(', ', $applicable_carts) . ")";
    }
    return $clauses;
}

//Filter by size
add_action('parse_query', 'riothere_filter_carts_by_specific_size_query');
add_action('restrict_manage_posts', 'riothere_filter_carts_by_specific_size_html');
add_action('wp_ajax_woocommerce_json_search_carts_size', 'riothere_json_search_carts_size');
add_action('posts_clauses', 'riothere_filter_carts_by_specific_product_size_clauses', 10, 2);

function riothere_filter_carts_by_specific_size_html()
{
    global $typenow;
    global $wp_query;
    if ($typenow === 'carts') {
        $category_string = '';
        $product_size_id = '';
        $current_product = new WC_Product_Simple();

        if (!empty($_GET['_product_size'])) {
            $product_size_id = absint($_GET['_product_size']); // WPCS: input var ok, sanitization ok.
            $size_name = get_term_by('id', $product_size_id, 'product_cat')->name;

            $category_string = sprintf(
                /* translators: 1: Brand ID 2: brand name */
                esc_html__('#%1$s - %2$s', 'woocommerce'),
                absint($product_size_id),
                $size_name
            );
        }
        ?>
            <select class="wc-riothere-carts-size-search" name="_product_size"
                    data-placeholder="<?php esc_attr_e('Filter by Product Size', 'woocommerce');?>"
                    data-allow_clear="true">
                <option value="<?php echo esc_attr($product_size_id); ?>"
                        selected="selected"><?php echo htmlspecialchars(wp_kses_post($category_string)); ?></option>
            </select>
			<?php
}
}

function riothere_json_search_carts_size()
{
    ob_start();

    check_ajax_referer('search-cart-size', 'security');

    $term = isset($_GET['term']) ? (string) wc_clean(wp_unslash($_GET['term'])) : '';

    if (empty($term)) {
        wp_die();
    }

    $filtered_sizes = [];

    $parent_category = get_term_by('slug', 'sizes', 'product_cat');

    $main_sizes = get_terms([
        'product_cat',
    ], [
        'hide_empty' => false,
        'parent' => $parent_category->term_id,
        // 'child_of' => $parent_category->term_id,
    ]);

    foreach ($main_sizes as $size) {
        $size_id_exist = Riothere_All_In_One_Admin::like_match($size->term_id, $term);
        $size_name_exist = Riothere_All_In_One_Admin::like_match($size->name, $term);
        $size_slug_exist = Riothere_All_In_One_Admin::like_match($size->slug, $term);

        if ($size_id_exist || $size_name_exist || $size_slug_exist) {
            $filtered_sizes[] = $size;
        }
    }

    $found_sizes = array();

    foreach ($filtered_sizes as $size) {
        /* translators: 1: user display name 2: user ID 3: user email */
        $found_sizes[$size->term_id] = sprintf(
            /* translators: $1: size id, $2 size name */
            esc_html__('#%1$s - %2$s', 'woocommerce'),
            $size->term_id,
            $size->name
        );
    }

    wp_send_json(apply_filters('woocommerce_json_search_carts_size', $found_sizes));
}

function riothere_filter_carts_by_specific_size_query($query)
{
    global $pagenow;
    global $wpdb;

    // Get the post type
    $post_type = sanitize_text_field($_GET['post_type']) ?? '';
    try {
        if (is_admin() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'carts' && $pagenow === 'edit.php' && $post_type === 'carts' && isset($_GET['_product_size']) && !empty($_GET['_product_size'])) {
            $query->query_vars['_product_size'] = absint($_GET['_product_size']);
        }
    } catch (Exception $exception) {

    }

    return $query;
}

function riothere_filter_carts_by_specific_product_size_clauses($clauses, $wp_query)
{
    global $wpdb;

    if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'carts' && isset($wp_query->query_vars['_product_size'])) {

        $main_product_brand = $wp_query->query_vars['_product_size'];

        $carts = get_posts([
            'post_type' => 'carts',
            'status' => 'publish',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => 'is_cart_fulfilled', // the user can have multiple carts but only 1 that is not fulfilled
                    'value' => '0',
                    'compare' => '=',
                ),
            ),
        ]);

        $all_available_products = get_posts([
            'post_type' => 'product',
            'status' => 'publish',
            'nopaging' => true,
        ]);

        $matching_products = array();

        foreach ($all_available_products as $product) {
            $category_data = Riothere_All_In_One_Admin::get_product_categories_data($product->ID);

            foreach ($category_data['category_ids'] as $cat_id) {
                if ((int) $cat_id === (int) $main_product_brand) {
                    array_push($matching_products, $product->ID);
                }
            }
        }

        $applicable_carts = array();

        foreach ($carts as $cart) {
            $cart_products = get_field('products', $cart->ID, false);

            foreach ($cart_products as $product) {
                if (in_array($product['field_62dec05d9b7f3']['title'], $matching_products)) {
                    array_push($applicable_carts, $cart->ID);
                    break;
                }
            }
        }

        $clauses['where'] .= " AND {$wpdb->prefix}posts.id IN (" . implode(', ', $applicable_carts) . ")";
    }
    return $clauses;
}