<?php

function riothere_products_csv_api()
{

    function riothere_get_product_meta($product_data, $meta_key)
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

    register_rest_route('riothere/v1', 'products-csv', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            header('Content-Type: text/csv'); // Supply the mime type
            header('Content-Disposition: attachment; filename="products.csv"'); // Supply a file name to save
            header("Cache-Control: no-cache, must-revalidate"); // Tell browser not to cache
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past (also tells browser not to cache)

            $categories = null;
            $per_page = -1;
            $offset = null;
            $order = null;
            $orderby = null;
            $image_size = "medium";
            $min_price = null;
            $max_price = null;
            $seller_id = null;
            $date_of_sale_from = null;
            $date_of_sale_to = null;
            $status = null;
            $product_ids = null;

            $products_result = Riothere_All_In_One_Products::get_products(
                $categories,
                $per_page,
                $offset,
                $order,
                $orderby,
                $image_size,
                $min_price,
                $max_price,
                $seller_id,
                $date_of_sale_from,
                $date_of_sale_to,
                $status,
                $product_ids,
            );

            $table_headers = array('ID', 'SKU', 'Product Name', 'Category L1', 'Category L2', 'Category L3', 'Original Price', 'Listed Price', 'Image Link', 'Website Link', 'Description', 'Date Listed', 'Availability', 'Condition', 'Brand', 'Size', 'Color');

            // Add double quotes around each cell because spaces are causing problems
            $table_headers = array_map(function ($el) {
                return '"' . $el . '"';
            }, $table_headers);

            $csv = join(",", $table_headers) . "\n";

            if ($products_result) {
                foreach ($products_result['data'] as $i => $product) {
                    $row = array();

                    $row[] = $product['id'];
                    $row[] = $product['sku'];
                    $row[] = $product['name'];
                    $row[] = $product['l1_category'];
                    $row[] = $product['l2_category'];
                    $row[] = $product['l3_category'];
                    $row[] = riothere_get_product_meta($product, "original_price");
                    $row[] = $product['price'];
                    $row[] = $product['image'];
                    $row[] = FRONTEND_URL . "/product/" . $product['slug'];
                    $row[] = htmlspecialchars($product['description'], ENT_QUOTES); // htmlspecialchars() will escape the string so it's csv-safe
                    $date_of_listing = riothere_get_product_meta($product, "date_of_listing");
                    $row[] = $date_of_listing == "" ? "" : date("d/m/Y", strtotime($date_of_listing));
                    $row[] = riothere_get_product_meta($product, "status");
                    $row[] = riothere_get_product_meta($product, "condition");
                    $row[] = $product['brand'];
                    $row[] = $product['size'];
                    $row[] = $product['color'];

                    // Add double quotes around each cell to escape any line breaks
                    $row = array_map(function ($el) {
                        return '"' . $el . '"';
                    }, $row);

                    $csv .= join(",", $row) . "\n";
                }
            }

            echo $csv;
        }));
}

add_action('rest_api_init', 'riothere_products_csv_api');
