<?php

/**
 * Hook triggered when Admin changes status of a Refund Request
 */
function riothere_update_refund_request($post_id)
{
    if (get_post_type($post_id) !== "refund-requests") {
        return;
    }

    // Get previous values.
    $prev_values = get_fields($post_id);

    // Get submitted values.
    $values = riothere_sanitize_array($_POST['acf']);

    $fields = get_field_objects();
    $key_of_status_field = $fields['status']['key'];

    // Check if a specific value was updated.
    if (isset($values[$key_of_status_field])) {
        $new_status = $values[$key_of_status_field];
        $old_status = $prev_values['status'];

        if ($new_status !== $old_status) {
            // Change detected --> send email
            $order_id = $fields['order_id']['value']['title'];
            $customer_id = $fields['customer']['value']['title'];
            $user = get_userdata($customer_id);
            $email = $user->data->user_email;
            $first_name = get_user_meta($user->ID, 'first_name', true);
            $body = "<p>Hello {$first_name},</p><br/>";
            $headers = array('Content-Type: text/html; charset=UTF-8');

            if ($new_status === "rejected") {
                $title = "Refund Declined";
                $body .= get_refund_rejected_email();
                wp_mail($email, $title, $body, $headers);
            } else if ($new_status === "accepted") {
                // Find the pickup_time field
                foreach ($values as $field_key => $field_value) {
                    if (get_field_object($field_key)["name"] === "pickup_time") {
                        $title = "Refund Accepted";
                        $body .= get_refund_accepted_email();
                        wp_mail($email, $title, $body, $headers);
                    }
                }
            }
        }
    }
}

add_action('acf/save_post', 'riothere_update_refund_request', 5/*should be less than 10 to run BEFORE save*/, 1);

// Below was replaced with the above acf hook even though it probably works as well
// add_action('save_post_refund-requests', 'riothere_update_refund_request', 10, 1);
