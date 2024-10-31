<?php
/**
 * Hook triggered when Admin changes status of a Product.
 * For example, if Admin changes status from "Listed" to "Sold", visibility
 * should be switched to "hidden". This is important because many WP and WC
 * features use visibility behind the scenes. For example, the category count
 * would not count the hidden products.
 */

// TODO handle errors (like when Trashing)
function riothere_update_product_visibility($post_id, $post)
{
    if (!array_key_exists('acf', $_POST)) {
        return;
    }

    // unhook this function so it doesn't loop infinitely
    remove_action('save_post_product', 'riothere_update_product_visibility', 10);

    $values = riothere_sanitize_array($_POST['acf']);

    $fields = get_field_objects();
    $key_of_status_field = $fields['status']['key'];
    $new_status = $values[$key_of_status_field];

    $product = wc_get_product($post_id);

    // Only "listed" products should be visible
    if (strtolower($new_status) === "listed") {
        wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
        $product->set_stock_quantity('1');
        $product->set_stock_status('instock');
    } else {
        wp_update_post(array('ID' => $post_id, 'post_status' => 'private'));
        $product->set_stock_quantity('0');
        $product->set_stock_status('outofstock');
    }

    $product->save();

    // re-hook this function.
    add_action('save_post_product', 'riothere_update_product_visibility', 10, 2);
}

add_action('save_post_product', 'riothere_update_product_visibility', 10, 2);
