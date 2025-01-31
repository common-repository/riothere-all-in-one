<?php

// This hook sends the Order Failed and Order Cancelled emails to customers
// (because by default WC only sends these to Admins)
function riothere_send_custom_email_notifications($order_id, $old_status, $new_status, $order)
{
    if ($new_status == "cancelled" || $new_status == "failed") {
        $wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
        $customer_email = $order->get_billing_email(); // The customer email
    }

    if ($new_status == "cancelled") {
        // Change the recipient of this instance
        $wc_emails["WC_Email_Cancelled_Order"]->recipient = $customer_email;
        // Sending the email from this instance
        $wc_emails["WC_Email_Cancelled_Order"]->trigger($order_id);
    } else if ($new_status == "failed") {
        // change the recipient of this instance
        $wc_emails["WC_Email_Failed_Order"]->recipient = $customer_email;
        // Sending the email from this instance
        $wc_emails["WC_Email_Failed_Order"]->trigger($order_id);
    }
}

add_action("woocommerce_order_status_changed", "riothere_send_custom_email_notifications", 10, 4);
