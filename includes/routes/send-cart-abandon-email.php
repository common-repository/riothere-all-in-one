<?php
function riothere_send_cart_abandon_email_api()
{
    register_rest_route('riothere/v1', 'send-cart-abandon-email', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_params();

            if (!is_user_logged_in()) {
                return array(
                    'status' => 'error',
                );
            }

            $carts = get_posts([
                'post_type' => 'carts',
                'status' => 'publish',
                'nopaging' => true,
            ]);

            $current_date = date('m/d/Y h:i:s a', time());
            $d1 = new DateTime($current_date);

            $first_hour_condition = 24;
            $second_hour_condition = 72;
            $third_hour_condition = 120;

            foreach ($carts as $cart) {
                $is_fulfilled = get_field('is_cart_fulfilled', $cart->ID, false);

                $my_products = get_field('products', $cart->ID, false);
                $product_ids = array();

                foreach ($my_products as $product) {
                    $product_id = $product['field_62dec05d9b7f3']['title'];
                    $meta = get_post_meta($product_id);

                    // We only add the "Listed" ones
                    if (strtolower($meta["status"][0]) === "listed") {
                        array_push($product_ids, $product_id);
                    }
                }

                if (!$is_fulfilled && count($product_ids)) {
                    $cart_last_modified_date = get_field('date_modified', $cart->ID, true);
                    $d2 = DateTime::createFromFormat("d/m/Y g:i a", $cart_last_modified_date);

                    $difference_between_current_and_last_modified = $d1->diff($d2);
                    $day_difference_in_hours = ($difference_between_current_and_last_modified->d) * 24;
                    $interval = $day_difference_in_hours + $difference_between_current_and_last_modified->h;

                    $all_emails_already_sent = false;
                    $user_email = get_field('user_email', $cart->ID, false);
                    $riot_phone = get_option('phone_number_setting');
                    $riot_email = get_option('email_setting');
                    $footer = "<p>If you need any help or have any concerns before completing your order, don't hesitate to get in touch. You can whatsapp us on " . $riot_phone . " or email us on " . $riot_email . ".</p>";

                    if ($interval >= $first_hour_condition && $interval < $second_hour_condition && !get_field('first_email_sent', $cart->ID, false)) {
                        $subject = "Don't Miss Out!";
                        $header = "<p>There are some unique fashion finds in your cart! Hurry back, our items are one of a kind so they could sell out and we wouldn't want you to miss out on them.</p>";

                        update_post_meta($cart->ID, 'first_email_sent', 1);
                        update_post_meta($cart->ID, 'is_cart_abandoned', 1);
                    } else if ($interval >= $second_hour_condition && $interval < $third_hour_condition && !get_field('second_email_sent', $cart->ID, false)) {
                        $subject = "A little something just for you!";
                        $header = "<p>Still thinking about it? Here's an extra 10% discount to ensure you get what you love. This offer is available to you for the next 48hrs, hurry up so you don't miss out!</p><p><b>Promocode: wishlist10</b></p>";

                        update_post_meta($cart->ID, 'second_email_sent', 1);
                    } else if ($interval >= $third_hour_condition && !get_field('third_email_sent', $cart->ID, false)) {
                        $subject = "Hurry up! Your offer expires today";
                        $header = "<p>The 10% discount on the items in your cart expires today! Don't miss out and grab your unique fashion finds before the end of the day!</p><p><b>Promocode: wishlist10</b></p>";

                        update_post_meta($cart->ID, 'third_email_sent', 1);
                    } else {
                        $all_emails_already_sent = true;
                    }

                    $user = get_field('user_id', $cart->ID);
                    $cart_user = get_user_by('id', $user['title']);
                    $customer_name = $cart_user->data->display_name;

                    $message = get_cart_abandonment_email($product_ids, $customer_name, $header, $footer);
                    $headers = array('Content-Type: text/html; charset=UTF-8');

                    if (!$all_emails_already_sent) {
                        wp_mail($user_email, $subject, $message, $headers);
                    }
                }
            }

            return array(
                'status' => 'ok',
            );
        }));
}

add_action('rest_api_init', 'riothere_send_cart_abandon_email_api');
