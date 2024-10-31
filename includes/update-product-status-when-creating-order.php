<?php

/**
 * Hook triggered when Admin creates an Order manually.
 */
function riothere_update_product_after_admin_order_creation_or_edit($order_id, $old_status, $new_status, $order)
{
    // The billing email is not always set within $order at this point -> get it from $customer obj
    $customer_id = $order->get_customer_id();
    $order_status = $order->get_status();
    $user = new WP_User($customer_id);
    $billing_email = $user->billing_email;

    $date_created = $order->get_date_created();
    $date_created = $date_created->date("Ymd");

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $product_id = $product->get_id();

        if ($new_status === "shipped" || $new_status === "completed" /* i.e. "Delivered" on UI */ || $new_status === "processing" || $new_status === "refunded") {
            // The items have actually been sold --> remove them (i.e. make them "private" etc...)
            wp_update_post(array('ID' => $product_id, 'post_status' => 'private'));
            $product->set_stock_quantity('0');
            $product->set_stock_status('outofstock');

            update_post_meta($product_id, 'status', 'Sold');
            update_post_meta($product_id, 'buyer', $billing_email);
            update_post_meta($product_id, 'date_of_sale', $date_created);
            update_post_meta($product_id, 'customer', '');
            update_post_meta($product_id, 'date_of_trail', '');
        } else if ($new_status === "try-and-buy") {
            // Move the items to "Try & Buy" status
            wp_update_post(array('ID' => $product_id, 'post_status' => 'private'));
            $product->set_stock_quantity('0');
            $product->set_stock_status('outofstock');

            update_post_meta($product_id, 'status', 'Try and Buy');
            update_post_meta($product_id, 'buyer', '');
            update_post_meta($product_id, 'date_of_sale', '');
            update_post_meta($product_id, 'customer', $billing_email);
            update_post_meta($product_id, 'date_of_trail', $date_created);
        } else {
            // The items have not actually been sold --> return them (i.e. make them "public" etc...)
            wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
            $product->set_stock_quantity('1');
            $product->set_stock_status('instock');

            update_post_meta($product_id, 'status', 'Listed');
            update_post_meta($product_id, 'buyer', '');
            update_post_meta($product_id, 'date_of_sale', '');
            update_post_meta($product_id, 'customer', '');
            update_post_meta($product_id, 'date_of_trail', '');
        }
    }
}

add_action('woocommerce_order_status_changed', 'riothere_update_product_after_admin_order_creation_or_edit', 10, 4);
