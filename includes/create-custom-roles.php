<?php
/**
 * Creates the "Seller" role which for now has similar permissions to "Customer"
 * but this way at least the Admins have a way of differentiating between Sellers
 * and Customers
 */
function riothere_create_seller_role()
{
    add_role(
        'seller',
        'Seller',
        array(
            'read' => true,
        )
    );
}
add_action('init', 'riothere_create_seller_role');
