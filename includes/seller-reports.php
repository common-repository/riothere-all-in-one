<?php

/*
Plugin Name: Simple PHPExcel Export
Description: Simple PHPExcel Export Plugin for WordPress
Version: 1.0.0
Author: Mithun
Author URI: http://twitter.com/mithunp
 */

define("RIOTHERE_SPEE_PLUGIN_URL_SELLER_REPORTS", WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)));
define("RIOTHERE_SPEE_PLUGIN_DIR_SELLER_REPORTS", WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)));

add_action('admin_menu', 'riothere_seller_reports_admin_menu');
add_action('admin_menu', 'riothere_seller_reports_menu_items');

function riothere_seller_reports_menu_items()
{
    remove_menu_page('spee-dashboard');
}

function riothere_seller_reports_admin_menu()
{
    add_menu_page('PHPExcel Export', 'Export', 'manage_options', 'seller-reports-spee-dashboard', 'seller_reports_spee_dashboard');
}

function seller_reports_spee_dashboard()
{
    global $wpdb;

    if (isset($_GET['export'])) {
        if (file_exists(RIOTHERE_SPEE_PLUGIN_DIR_SELLER_REPORTS . '/lib/PHPExcel.php')) {
            //Include PHPExcel
            require_once RIOTHERE_SPEE_PLUGIN_DIR_SELLER_REPORTS . "/lib/PHPExcel.php";

            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();

            // Set document properties

            // Add some data
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'ID');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', 'SKU');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Image');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Brand');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Name');
            $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Date of Sale');
            $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Price (AED)');
            $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Seller Revenue (AED)');
            $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Seller Name');

            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A:I')->setAutoSize(true);

            // The input
            $seller_id = !empty($_GET["seller_id"]) ? absint($_GET["seller_id"]) : "";
            $date_from = !empty($_GET["date_from"]) ? sanitize_text_field($_GET["date_from"]) : "";
            $date_to = !empty($_GET["date_to"]) ? sanitize_text_field($_GET["date_to"]) : "";
            $status = !empty($_GET["status"]) ? sanitize_text_field($_GET["status"]) : "";

            if ($status != "") {
                if ($status == "not sold") {
                    $status = "listed";
                }
            }

            $all_data = array();
            $total_seller_revenue = 0;

            // The query
            $all_products_result = Riothere_All_In_One_Products::get_products(
                null,
                -1, // ALL prods
                null,
                null,
                null,
                "medium",
                null,
                null,
                $seller_id,
                $date_from,
                $date_to,
                $status
            );

            // The printing
            if ($all_products_result) {
                foreach ($all_products_result['data'] as $i => $product) {
                    $date_of_sale = riothere_seller_reports_get_product_meta($product, "date_of_sale");
                    $date_of_sale = ($date_of_sale && $date_of_sale != "") ? date("Y-m-d", strtotime($date_of_sale)) : "--";
                    $seller_revenue_aed = riothere_seller_reports_get_product_meta($product, "seller_revenue_aed");
                    $seller_id = riothere_seller_reports_get_product_meta($product, "seller");
                    $seller = new WP_User($seller_id);
                    $seller_name = $seller->first_name . ' ' . $seller->last_name;

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . ($i + 2), $product['id']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . ($i + 2), $product['sku']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . ($i + 2), $product['image']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . ($i + 2), $product['brand']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . ($i + 2), $product['name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . ($i + 2), $date_of_sale);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . ($i + 2), $product['price']);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . ($i + 2), $seller_revenue_aed);
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . ($i + 2), $seller_name);
                }
            }

            // Rename worksheet
            //$objPHPExcel->getActiveSheet()->setTitle('Simple');

            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);

            // Redirect output to a client’s web browser
            $file_name = "seller-report.csv";
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

// The following writes the 'brand' into a meta field 'brand_for_sorting_purposes'.
// It is needed to simplify the sorting process in the Seller Reports page
add_action('save_post_product', 'riothere_update_brands_for_sorting', 11, 2);
function riothere_update_brands_for_sorting($post_id, $post)
{
    $product = Riothere_All_In_One_Admin::get_product_data($post_id, "medium");
    update_post_meta($post_id, "brand_for_sorting_purposes", $product['brand']);
}

function riothere_seller_reports_get_product_meta($product_data, $meta_key)
{
    $product_meta = $product_data['meta_data'];

    foreach ($product_meta as $each_meta) {
        $each_meta_data = $each_meta->get_data();

        if ($each_meta_data['key'] === $meta_key) {
            return $each_meta_data['value'];
        }
    }

    return null;
}

add_action('admin_menu', 'riothere_register_seller_reports_page');
function riothere_register_seller_reports_page()
{
    global $new_menu_page;

    add_menu_page(
        'Seller Reports',
        'Seller Reports',
        'edit_posts',
        'seller-reports',
        'seller_report_page',
        'dashicons-list-view',
        8
    );
}

// The following hook is responsible for computing the Seller Revenue when
// an order is placed
function riothere_set_seller_revenue($order_id, $order)
{

    $items = $order->get_items();

    foreach ($items as $item) {
        $product_id = $item->get_product_id();
        $product_data = Riothere_All_In_One_Admin::get_product_data($product_id, "medium");

        if ($product_data) {
            $product_price = $product_data["price"];
            $seller_id = riothere_seller_reports_get_product_meta($product_data, "seller");
            $default_seller_commission_percent = 10;
            $seller_commission_percent_array = get_user_meta($seller_id, 'seller_commission_percent');
            if ($seller_commission_percent_array && $seller_commission_percent_array != []) {
                $seller_commission_percent = $seller_commission_percent_array[0];
            } else {
                $seller_commission_percent = $default_seller_commission_percent;
            }

            $seller_revenue_aed = round($product_price * $seller_commission_percent / 100, 2);
            update_post_meta($product_id, 'seller_revenue_aed', $seller_revenue_aed);
        }
    }
}
add_action('woocommerce_order_status_processing', 'riothere_set_seller_revenue', 100, 2);

function seller_report_page()
{
    if (!class_exists('WP_List_Table')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }

    // Style needed for autocomplete select2 filters to render correctly
    wp_enqueue_style('woocommerce_admin_styles');

    class Seller_Reports_Table extends WP_List_Table
    {

        /**
         * Constructor, we override the parent to pass our own arguments
         * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
         */
        function __construct()
        {
            parent::__construct(array(
                'singular' => 'Seller Report', //singular name of the listed records
                'plural' => 'Seller Reports', //plural name of the listed records
                'ajax' => false,
            ));
        }

        function get_columns()
        {
            $columns = array(
                'id' => 'ID',
                'sku' => 'SKU',
                'image' => 'Image',
                'brand' => 'Brand',
                'name' => 'Name',
                'sold_date' => 'Sold On',
                'price' => 'Price (AED)',
                'seller_revenue' => 'Seller Revenue (AED)',
                'seller_name' => 'Seller Name',
            );
            return $columns;
        }

        public function get_sortable_columns()
        {
            return $sortable = array(
                'brand' => array('brand', false),
                'name' => array('name', false),
                'sold_date' => array('date_of_sale', false),
                'price' => array('price', false),
                'seller_revenue' => array('seller_revenue', false),
            );
        }

        function column_default($item, $column_name)
        {

            switch ($column_name) {
                case 'image':
                    if ($item[$column_name] === "") {
                        return "";
                    }

                    return "<img height=100 width=100 src='" . $item[$column_name] . "'/>";
                case 'id':
                    if ($item[$column_name] === "Total Seller Revenue") {
                        return "<div><strong>" . $item[$column_name] . "</strong></div>";
                    } else {
                        return '<a href="' . esc_url(admin_url('post.php?post=' . absint($item[$column_name])) . '&action=edit') . '" class="order-view"><strong>' . esc_attr($item[$column_name]) . '</strong></a>';
                    }

                case 'sku':
                case 'brand':
                case 'sold_date':
                case 'name':
                case 'price':
                case 'seller_revenue':
                    if ($item['id'] === "Total Seller Revenue") {
                        return "<div><strong>" . $item[$column_name] . "</strong></div>";
                    }
                case 'seller_name':
                    return $item[$column_name];
                default:
                    return print_r($item, true); //Show the whole array for troubleshooting purposes
            }
        }

        function extra_tablenav($which)
        {
            switch ($which) {
                case 'top':
                    // Your html code to output
                    // global $wpdb, $wp_locale;

                    $date_from = !empty($_GET["date_from"]) ? sanitize_text_field($_GET["date_from"]) : "";
                    $date_to = !empty($_GET["date_to"]) ? sanitize_text_field($_GET["date_to"]) : "";
                    $status = !empty($_GET["status"]) ? sanitize_text_field($_GET["status"]) : "";
                    $date_range = !empty($_GET["date_range"]) ? sanitize_text_field($_GET["date_range"]) : "";

                    $seller_string = '';
                    $seller_id = '';
                    if (!empty($_GET['seller_id'])) {
                        $seller_id = absint($_GET['seller_id']);
                        $seller = new WP_User($seller_id);
                        $seller_string = sprintf(
                            /* translators: 1: Seller ID 2: customer name 3: user customer email */
                            esc_html__('#%1$s ( %2$s - %3$s)', 'woocommerce'),
                            absint($seller->ID),
                            $seller->first_name . ' ' . $seller->last_name,
                            $seller->user_email
                        );
                    }

                    ?>
                        <div class="alignleft actions">
                            <select class="wc-riothere-sellers-search" name="riothere_seller_reports_seller_id" id="riothere_seller_reports_seller_id" data-placeholder="<?php esc_attr_e('Filter by Seller', 'woocommerce');?>" data-allow_clear="true">
                                <option value="<?php echo esc_attr($seller_id); ?>" selected="selected">
                                    <?php echo htmlspecialchars(wp_kses_post($seller_string)); ?>
                                </option>
                            </select>

                            <select id="riothere_seller_reports_daterange" name="riothere_seller_reports_daterange" data-placeholder="Date Range" data-allow_clear="true">
                            <option value="<?php echo esc_attr(($date_range != "" && $date_range != "null" && $date_range) ? $date_range : "All Time"); ?>" selected="selected" hidden ><?php echo ($date_range != "" && $date_range != "null") ? $date_range : "All Time"; ?></option>
                                <option value="All Time">All Time</option>
                                <option value="Today" >Today</option>
                                <option value="This Week">This Week</option>
                                <option value="This Month">This Month</option>
                                <option value="Custom">Custom</option>
                            </select>

                            <div id="riothere_seller_reports_date_input" class="filter-date" style="display: none">
                                <input type="text" class="riothere-date-from" id="riothere_seller_reports_date_from" name="riothere_seller_reports_date_from" placeholder="Date From"
                                value="<?php echo esc_attr($date_from); ?>" />
                                <input type="text" class="riothere-date-to" id="riothere_seller_reports_date_to" name="riothere_seller_reports_date_to" placeholder="Date To"
                                value="<?php echo esc_attr($date_to); ?>" />
                            </div>
                            <select id="riothere_seller_reports_status" name="riothere_seller_reports_status" data-placeholder="Status" data-allow_clear="true">
                                <option value="<?php echo esc_attr(($status != "" && $status != "null" && $status) ? $status : ""); ?>" selected="selected" hidden ><?php echo ($status != "" && $status != "null") ? $status : "Item Status"; ?></option>
                                <option value="">All</option>
                                <option value="sold" >sold</option>
                                <option value="not sold">not sold</option>
                                <option value="returned">returned</option>
                                <option value="requested to return">requested to return</option>
                            </select>

                            <a id="riothere_seller_report_submit_btn" href="javascript:void(0)" class="button">Filter</a>
                        </div>
                        <?php
break;

                case 'bottom':
                    ?>

                <!-- a total seller field below the table use it if any other field is needed
                     <div style="width:86.3%; display:flex; justify-content:flex-end;font-weight:400;line-height: 1.4em;font-size: 14px;">
             <div style=" display:flex; flex-direction:column;">
             <div >Total Seller Revenue</div>
               <div>700</div>
             </div>

                </div> -->


                    <?php
break;
            }
        }

        function prepare_items()
        {
            global $_wp_column_headers;
            $screen = get_current_screen();

            $seller_id = !empty($_GET["seller_id"]) ? absint($_GET["seller_id"]) : "";
            $date_from = !empty($_GET["date_from"]) ? sanitize_text_field($_GET["date_from"]) : "";
            $date_to = !empty($_GET["date_to"]) ? sanitize_text_field($_GET["date_to"]) : "";
            $orderby = !empty($_GET["orderby"]) ? sanitize_text_field($_GET["orderby"]) : "";
            $order = !empty($_GET["order"]) ? sanitize_text_field($_GET["order"]) : "ASC";
            $per_page = 20;
            $paged = !empty($_GET["paged"]) ? sanitize_text_field($_GET["paged"]) : "";
            if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
                $paged = 1;
            }

            $status = !empty($_GET["status"]) ? sanitize_text_field($_GET["status"]) : "";
            if ($status != "") {
                if ($status == "not sold") {
                    $status = "listed";
                }
            }

            $all_data = array();
            $total_seller_revenue = 0;
            $all_products_result = Riothere_All_In_One_Products::get_products(
                null,
                -1, // ALL prods
                null,
                null,
                null,
                "medium",
                null,
                null,
                $seller_id,
                $date_from,
                $date_to,
                $status
            );

            if ($all_products_result) {
                foreach ($all_products_result['data'] as $product) {
                    $total_seller_revenue += (float) riothere_seller_reports_get_product_meta($product, "seller_revenue_aed");
                }
            }

            $paginated_data = array();
            $paginated_products_result = Riothere_All_In_One_Products::get_products(
                null,
                $per_page,
                $paged,
                $order,
                strtoupper($orderby),
                "medium",
                null,
                null,
                $seller_id,
                $date_from,
                $date_to,
                $status
            );

            if ($paginated_products_result) {
                foreach ($paginated_products_result['data'] as $product) {
                    $date_of_sale = riothere_seller_reports_get_product_meta($product, "date_of_sale");
                    $date_of_sale = ($date_of_sale && $date_of_sale != "") ? date("Y-m-d", strtotime($date_of_sale)) : "--";
                    $seller_revenue_aed = riothere_seller_reports_get_product_meta($product, "seller_revenue_aed");
                    $seller_id = riothere_seller_reports_get_product_meta($product, "seller");
                    $seller = new WP_User($seller_id);
                    $seller_name = $seller->first_name . ' ' . $seller->last_name;

                    array_push($paginated_data, array(
                        'id' => $product['id'],
                        'sku' => $product['sku'],
                        'image' => $product['image'],
                        'brand' => $product['brand'],
                        'name' => $product['name'],
                        'sold_date' => $date_of_sale,
                        'price' => $product['price'],
                        'seller_revenue' => $seller_revenue_aed,
                        'seller_name' => $seller_name,
                    ));
                }
            }

            // Show the Seller Revenue as last row
            array_push($paginated_data, array(
                'id' => "Total Seller Revenue",
                'sku' => "",
                'image' => "",
                'brand' => "",
                'name' => "",
                'sold_date' => "",
                'price' => "",
                'seller_revenue' => $total_seller_revenue,
                'seller_name' => "",
            ));

            $total_items = $paginated_products_result['found_posts'];
            $total_pages = $paginated_products_result['max_num_pages'];

            $this->set_pagination_args(array(
                "total_items" => $total_items,
                "total_pages" => $total_pages,
                "per_page" => $per_page,
            ));

            /* -- Register the Columns -- */
            $columns = $this->get_columns();
            $hidden = [];
            $sortable = $this->get_sortable_columns();
            // $hidden = $this->get_hidden_columns();
            $hidden = array();
            $_wp_column_headers[$screen->id] = $columns;

            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->items = $paginated_data;
        }
    }

    $wp_list_table = new Seller_Reports_Table();
    $wp_list_table->prepare_items();

    ?>
      <div class="wrap">
        <h2>Seller Report</h2>

        <div id="poststuff">
          <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
              <div class="meta-box-sortables ui-sortable">
                <form method="post">
                    <?php $wp_list_table->display();?>
                </form>
              </div>
            </div>
          </div>
          <br class="clear">
        </div>
      </div>
    <?php
}
