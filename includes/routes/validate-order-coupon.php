<?php

function riothere_publish_product($product_id)
{
    $product = wc_get_product($product_id);

    wp_update_post(array('ID' => $product_id, 'post_status' => 'publish'));
    $product->set_stock_quantity('1');
    $product->set_stock_status('instock');
    $product->save();
}

function riothere_unpublish_product($product_id)
{
    $product = wc_get_product($product_id);

    wp_update_post(array('ID' => $product_id, 'post_status' => 'private'));
    $product->set_stock_quantity('0');
    $product->set_stock_status('outofstock');
    $product->save();
}

function riothere_validate_order_coupon()
{
    register_rest_route('riothere/v1', 'validate-order-coupon', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'args' => array(
            'coupon_code' => array(
                'description' => __('Coupon codes', 'newsletter'),
                'type' => 'string',
                'required' => true,
            ),
            'order' => array(
                'description' => __('Order data', 'newsletter'),
                'required' => true,
            ),

        ),
        'callback' => function (WP_REST_Request $request) {
            $result = [
                'success' => true,
                'data' => null,
            ];

            $params = $request->get_params();
            $coupon_code = $params['coupon_code'];
            $order_data = $params['order'];
            $errors = [];

            if ($coupon_code === "try-and-buy") {
                // Some user is trying to MANUALLY use this Coupon. We should
                // not allow this! This Coupon is only to be used behind the
                // scenes during Try & Buy Journey 1 orders
                $errors[] = [
                    'code' => 'invalid_coupon',
                    'message' => __('Invalid Coupon Code'),
                    'data' => [
                        'coupon_code' => $coupon_code,
                    ],
                ];
            }

            if (defined('WC_ABSPATH')) {
                // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
                include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
                include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
                include_once WC_ABSPATH . 'includes/wc-template-hooks.php';
            }

            if (null === WC()->session) {
                $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');

                WC()->session = new $session_class();
                WC()->session->init();
            }

            if (null === WC()->customer) {
                WC()->customer = new WC_Customer(get_current_user_id(), true);
            }

            if (null === WC()->cart) {
                WC()->cart = new WC_Cart();

                // We need to force a refresh of the cart contents from session here (cart contents are normally refreshed on wp_loaded, which has already happened by this point).
                WC()->cart->get_cart();
            }

            foreach (WC()->cart->get_cart() as $cart_item) {
                WC()->cart->remove_cart_item($cart_item['key']);
            }

            $line_items = $order_data['line_items'];

            foreach ($line_items as $line_item) {
                $product_id = $line_item['product_id'];
                $product = wc_get_product($product_id);
                $status = $product->get_meta("status");

                if (strtolower($status) === "try and buy") {
                    // We have to publish any product in order to be able to add it to cart
                    // (because in Journey 2 of Try & Buy, the products are in "Try & Buy"
                    // status and are unpublished with stock quantity set to 0).
                    // Then we re-unpublish it of course
                    riothere_publish_product($product_id);
                    WC()->cart->add_to_cart($product_id, $line_item['quantity']);
                    riothere_unpublish_product($product_id);
                } else {
                    WC()->cart->add_to_cart($product_id, $line_item['quantity']);
                }
            }

            $discounts = new WC_Discounts(WC()->cart);
            $discounts->set_items_from_cart(WC()->cart);

            // Get user and posted emails to compare.
            $user_email = '';
            if (isset($order_data['billing']) && isset($order_data['billing']['email'])) {
                $user_email = strtolower(sanitize_email($order_data['billing']['email']));
            }

            $coupon = new WC_Coupon($coupon_code);
            WC()->cart->apply_coupon($coupon_code);
            $discounts->apply_coupon($coupon_code);
            $valid = $discounts->is_coupon_valid($coupon);
            $subtotal = (float) WC()->cart->get_displayed_subtotal();

            if ($coupon->get_date_expires() && apply_filters('woocommerce_coupon_validate_expiry_date', time() > $coupon->get_date_expires()->getTimestamp(), $coupon, $discounts)) {
                $errors[] = [
                    'code' => 'coupon_expired',
                    'message' => __('This coupon has expired', 'woocommerce'),
                    'data' => [
                        'coupon_code' => $coupon_code,
                        'expiry_date' => $coupon->get_date_expires(),
                        'expiry_date_timestamp' => $coupon->get_date_expires()->getTimestamp(),
                    ],
                ];
            } else if ($coupon->get_maximum_amount() > 0 && apply_filters('woocommerce_coupon_validate_maximum_amount', $coupon->get_maximum_amount() < $subtotal, $coupon)) {
                $errors[] = [
                    'code' => 'maximum_coupon',
                    'message' => sprintf(__('The maximum spend for this coupon is %s.', 'woocommerce'), wc_price($coupon->get_maximum_amount())),
                    'data' => [
                        'coupon_code' => $coupon_code,
                        'maximum_amount' => $coupon->get_maximum_amount(),
                    ],
                ];
            } else if ($coupon->get_minimum_amount() > 0 && apply_filters('woocommerce_coupon_validate_minimum_amount', $coupon->get_minimum_amount() > $subtotal, $coupon, $subtotal)) {
                $errors[] = [
                    'code' => 'minimum_coupon',
                    'message' => sprintf(__('The minimum spend for this coupon is %s.', 'woocommerce'), wc_price($coupon->get_maximum_amount())),
                    'data' => [
                        'coupon_code' => $coupon_code,
                        'minimum_amount' => $coupon->get_minimum_amount(),
                    ],
                ];
            } else if (is_wp_error($valid)) {
                WC()->cart->remove_coupon($coupon_code);
                $errors[] = [
                    'code' => $valid->get_error_code(),
                    'message' => $valid->get_error_message(),
                    'data' => $valid->get_error_data(),
                ];
            } else {
                // Limit to defined email addresses.
                // @todo check the validation for allowed emails and add a new hidden field that contains the allowed emails added by the newsletter popup
                // woocommerce_data_get_email_restrictions
                $restrictions = $coupon->get_email_restrictions();

                // we need to add the validation rule for email here because WooCommerce
                // only check the allowed emails on woocommerce_after_checkout_validation and that when the order is being created

                $coupon_usage_limit = $coupon->get_usage_limit_per_user();
                if (0 < $coupon_usage_limit) {
                    $coupon_data_store = $coupon->get_data_store();

                    if ($coupon_data_store && $coupon_data_store->get_usage_by_email($coupon, $user_email) >= $coupon_usage_limit) {
                        $errors[] = [
                            'code' => 'coupon_user_limit_reached',
                            'message' => __('You have reached the limit of using this coupon'),
                            'data' => [
                                'coupon_code' => $coupon_code,
                            ],
                        ];
                    }
                }

                if ($coupon_usage_limit > 0) {
                    $data_store = $coupon->get_data_store();
                    $customer = get_user_by_email($user_email);
                    $usage_count = $customer ? $data_store->get_usage_by_user_id($coupon,
                        $customer->ID) : $data_store->get_usage_by_email($coupon, $user_email);

                    if ($usage_count >= $coupon_usage_limit) {

                        $errors[] = [
                            'code' => 'coupon_user_limit_reached',
                            'message' => __('You have reached the limit of using this coupon'),
                            'data' => [
                                'coupon_code' => $coupon_code,
                            ],
                        ];
                    }
                }

                if (!WC()->cart->is_coupon_emails_allowed([$user_email], $restrictions)) {
                    WC()->cart->remove_coupon($coupon_code);

                    $errors[] = [
                        'code' => 'email_not_allowed_for_coupon',
                        'message' => __('Your email is not eligible'),
                        'data' => [
                            'coupon_code' => $coupon_code,
                        ],
                    ];

                }
            }

            $cart_items = [];

            foreach (WC()->cart->get_cart() as $cart_item) {
                $cart_items[] = [
                    'price' => $cart_item['line_total'] + $cart_item['line_tax'],
                    'coupon' => $cart_item,
                ];
            }

            $applied_coupons = [];
            foreach (WC()->cart->get_applied_coupons() as $coupon_code) {
                $coupon = new WC_Coupon($coupon_code);
                $applied_coupons[] = $coupon->get_data();
            }

            if (count($errors) > 0) {
                $result['success'] = false;
                $result['error'] = $errors[0];
            } else {
                $affected_product_ids = [];
                foreach (WC()->cart->get_cart() as $cart_item) {
                    // if the subtotal different than the total we would know that the product has discount
                    if ($cart_item['line_subtotal'] !== $cart_item['line_total']) {
                        $affected_product_ids[] = $cart_item['product_id'];
                    }
                }

                $result['data'] = $order_data;
                $result['data']['currency'] = 'AED';
                $result['data']['affected_product_ids'] = $affected_product_ids;
                $result['data']['discount_total'] = WC()->cart->get_discount_total();
                $result['data']['discount_tax'] = WC()->cart->get_discount_tax();
                $result['data']['shipping_total'] = WC()->cart->get_shipping_total();
                $result['data']['shipping_tax'] = WC()->cart->get_shipping_tax();
                $cart_total_tax = wc_round_tax_total(WC()->cart->get_cart_contents_tax() + WC()->cart->get_shipping_tax() + WC()->cart->get_fee_tax());

                $result['data']['cart_tax'] = $cart_total_tax;
                $result['data']['total'] = (float) WC()->cart->get_total('edit');
                $result['data']['total_tax'] = (float) WC()->cart->get_total_tax();
                $result['data']['cart_items'] = WC()->cart->get_cart();
            }

            return rest_ensure_response($result);
        },
    ));
}

add_action('rest_api_init', 'riothere_validate_order_coupon');
