<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Riothere_All_In_One
 * @subpackage Riothere_All_In_One/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Riothere_All_In_One
 * @subpackage Riothere_All_In_One/admin
 */
class Riothere_All_In_One_Admin_Dashboard
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        function riothere_set_acf_to_read_only($field)
        {

            if ('number_of_views' === $field['name'] || 'number_of_clicks_in_search_results' === $field['name']) {
                $field['disabled'] = true;
            }

            return $field;
        }

        add_filter('acf/load_field', 'riothere_set_acf_to_read_only');
    }

    // Add Custom Dashboard Widget
    public function riothere_add_most_recent_orders_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'most_recent_orders',
            'Most Recent Orders',
            array($this, 'riothere_most_recent_orders_function')
        );
    }

    public function riothere_most_recent_orders_function()
    {
        // Display whatever you want to show.
        ?>
        <table class="riothere-dashboard-table">
            <tr>
                <th>Order ID</th>
                <th>Status</th>
                <th>Date</th>
                <th>Total</th>
            </tr>

            <?php
$args = [
            'limit' => 5,
            'orderby' => 'ID',
            'order' => 'DESC',
        ];
        $orders = wc_get_orders($args);
        foreach ($orders as $order) {
            $date_created = $order->get_date_created();
            $date_created = $date_created->date("Y-m-d g:i:s");
            echo "<tr>
                    <td>" . $order->get_id() . "</td>
                    <td>" . $order->get_status() . "</td>
                    <td>" . $date_created . "</td>
                    <td>" . $order->get_total() . "</td>
                </tr>";
        }
        ?>
        </table>

    <?php
}

    // Add Custom Dashboard Widget
    public function riothere_add_most_recent_products_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'most_recent_products',
            'Most Recent Products',
            array($this, 'riothere_most_recent_products_function')
        );
    }

    public function riothere_most_recent_products_function()
    {
        // Display whatever you want to show.
        ?>
        <table class="riothere-dashboard-table">
            <tr>
                <th>Name</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Categories</th>
                <th>Tags</th>
                <th>Date</th>
            </tr>

            <?php
$args = array(
            'post_type' => 'product',
            'posts_per_page' => 5,
            'orderby' => 'ID',
            'order' => 'DESC',
        );

        $loop = new WP_Query($args);

        while ($loop->have_posts()) {
            $loop->the_post();
            $product = wc_get_product(get_the_ID());
            $date_created = $product->get_date_created();
            $date_created = $date_created->date("Y-m-d g:i:s");
            echo "<tr>
                <td>" . $product->get_name() . "</td>
                <td>" . $product->get_sku() . "</td>
                <td>" . $product->get_price() . "</td>
                <td>" . wc_get_product_category_list(get_the_ID()) . "</td>
                <td>" . wc_get_product_tag_list(get_the_ID()) . "</td>
                <td>" . $date_created . "</td>
            </tr>";
        }
        ?>
        </table>
    <?php
}

    // Add Custom Dashboard Widget
    public function riothere_add_most_recent_user_account_registrations_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'most_recent_user_account_registrations',
            'Most Recent User Account Registrations',
            array($this, 'riothere_most_recent_user_account_registration_function')
        );
    }

    public function riothere_most_recent_user_account_registration_function()
    {
        // Display whatever you want to show.
        ?>
        <table class="riothere-dashboard-table">
            <tr>
                <th>User ID</th>
                <th>Date of Registration</th>
                <th>Name</th>
                <th>Email</th>
            </tr>

            <?php
$args = array(
            'role' => 'customer',
            'orderby' => 'ID',
            'order' => 'DESC',
            'number' => 5,
        );
        $users = get_users($args);

        foreach ($users as $user) {
            echo "<tr>
            <td>" . $user->ID . "</td>
            <td>" . $user->user_registered . "</td>
            <td>" . $user->first_name . " " . $user->last_name . "</td>
            <td>" . $user->user_email . "</td>
        </tr>";
        }
        ?>

        </table>

    <?php
}

    // Add Custom Dashboard Widget
    public function riothere_add_most_recent_seller_account_registrations_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'most_recent_seller_account_registrations',
            'Most Recent Seller Account Registrations',
            array($this, 'riothere_most_recent_seller_account_registration_function')
        );
    }

    public function riothere_most_recent_seller_account_registration_function()
    {
        // Display whatever you want to show.
        ?>
        <table class="riothere-dashboard-table">
            <tr>
                <th>User ID</th>
                <th>Date of Registration</th>
                <th>Name</th>
                <th>Email</th>
            </tr>
            <?php
$args = array(
            'role' => 'seller',
            'orderby' => 'ID',
            'order' => 'DESC',
            'number' => 5,
        );
        $users = get_users($args);

        foreach ($users as $user) {
            echo "<tr>
            <td>" . $user->ID . "</td>
            <td>" . $user->user_registered . "</td>
            <td>" . $user->first_name . " " . $user->last_name . "</td>
            <td>" . $user->user_email . "</td>
        </tr>";
        }
        ?>
        </table>

    <?php
}

    // Add Custom Dashboard Widget
    public function riothere_add_current_promotion_running_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'current_promotion_running',
            'Current Promotion Running',
            array($this, 'riothere_current_promotion_running_function')
        );
    }

    public function riothere_current_promotion_running_function()
    {
        // Display whatever you want to show.
        ?>
        <table class="riothere-dashboard-table">
            <tr>
                <th>Promotion Name</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>

            <?php
$promotions = get_posts([
            'post_type' => 'riothere-promotion',
            'status' => 'publish',
        ]);
        foreach ($promotions as $promotion) {
            $promotion_id = $promotion->ID;
            $promotion_start_date = get_post_meta($promotion_id, 'promotion_start_date', true);
            $promotion_end_date = get_post_meta($promotion_id, 'promotion_end_date', true);
            echo "<tr>
                <td>" . get_the_title($promotion) . "</td>
                <td>" . $promotion_start_date . "</td>
                <td>" . $promotion_end_date . "</td>
            </tr>";
        }
        ?>
        </table>

    <?php
}

    // Add Custom Dashboard Widget
    public function riothere_add_most_viewed_items_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'most_viewed_items',
            'Most Viewed Items',
            array($this, 'riothere_most_viewed_items_function')
        );
    }

    public function riothere_most_viewed_items_function()
    {
        // Display whatever you want to show.
        ?>
        <table class="riothere-dashboard-table">
            <tr>
                <th>Name</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Categories</th>
                <th>Tags</th>
                <th>Date</th>
            </tr>
            <?php
$args = array(
            'post_type' => 'product',
            'posts_per_page' => 5,
            'orderby' => 'meta_value_num',
            'meta_key' => 'number_of_views',
            'order' => 'DESC',
        );

        $loop = new WP_Query($args);

        while ($loop->have_posts()) {
            $loop->the_post();
            $product = wc_get_product(get_the_ID());
            echo "<tr>
                <td>" . $product->get_name() . "</td>
                <td>" . $product->get_sku() . "</td>
                <td>" . $product->get_price() . "</td>
                <td>" . wc_get_product_category_list(get_the_ID()) . "</td>
                <td>" . wc_get_product_tag_list(get_the_ID()) . "</td>
                <td>" . $product->get_date_created() . "</td>
            </tr>";
        }
        ?>
        </table>

    <?php
}

    // Add Custom Dashboard Widget
    public function riothere_add_most_followed_sellers_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'most_followed_sellers',
            'Most Followed Sellers',
            array($this, 'riothere_most_followed_sellers_function')
        );
    }

    public function riothere_most_followed_sellers_function()
    {
        // Display whatever you want to show.
        ?>
        <table class="riothere-dashboard-table">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Number Of Followers</th>
            </tr>
            <?php
$args = array(
            'role' => 'seller',
        );
        $users = get_users($args);
        $data = [];
        foreach ($users as $user) {
            $followed_id = $user->ID;

            $args = array(
                'post_type' => 'followers',
                'meta_query' => array(
                    'relation' => 'AND',
                    // this array results in no return for both arrays
                    array(
                        'key' => 'follower_id',
                        'value' => $followed_id,
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'action',
                        'value' => 'follow',
                        'compare' => '=',
                    ),
                ),
            );

            $followers = get_posts($args);
            $data[] = [
                'name' => $user->first_name . " " . $user->last_name,
                'email' => $user->user_email,
                'number_of_followers' => count($followers),
            ];
        }

        usort($data, function ($a, $b) {
            return $a['number_of_followers'] < $b['number_of_followers'];
        });
        if (count($data) > 5) {
            $data = array_slice($data, 0, 5, true);
        }
        foreach ($data as $item) {
            echo "<tr>
                <td>" . $item['name'] . "</td>
                <td>" . $item['email'] . "</td>
                <td>" . $item['number_of_followers'] . "</td>
            </tr>";
        }
        ?>
        </table>

    <?php
}

    // Add Custom Dashboard Widget
    public function riothere_add_most_searched_items_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'most_searched_items',
            'Most Searched Items',
            array($this, 'riothere_most_searched_items_function')
        );
    }

    public function riothere_most_searched_items_function()
    {
        // Display whatever you want to show.
        ?>
        <table class="riothere-dashboard-table">
            <tr>
                <th>Name</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Categories</th>
                <th>Tags</th>
                <th>Date</th>
            </tr>
            <?php
$args = array(
            'post_type' => 'product',
            'posts_per_page' => 5,
            'orderby' => 'meta_value_num',
            'meta_key' => 'number_of_clicks_in_search_results',
            'order' => 'DESC',
        );

        $loop = new WP_Query($args);

        while ($loop->have_posts()) {
            $loop->the_post();
            $product = wc_get_product(get_the_ID());
            echo "<tr>
                <td>" . $product->get_name() . "</td>
                <td>" . $product->get_sku() . "</td>
                <td>" . $product->get_price() . "</td>
                <td>" . wc_get_product_category_list(get_the_ID()) . "</td>
                <td>" . wc_get_product_tag_list(get_the_ID()) . "</td>
                <td>" . $product->get_date_created() . "</td>
            </tr>";
        }
        ?>
        </table>

    <?php
}

    // Add Custom Dashboard Widget
    public function riothere_add_new_refund_requests_from_customers_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'new_refund_requests_from_customers',
            'New Refund Requests From Customers',
            array($this, 'riothere_new_refund_requests_from_customers_function')
        );
    }

    public function riothere_new_refund_requests_from_customers_function()
    {
        // Display whatever you want to show.
        ?>
        <table class="riothere-dashboard-table">
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Order ID</th>

            </tr>
            <?php
$args = [
            'post_type' => 'refund-requests',
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'DESC',
            'posts_per_page' => -1,
        ];

        $query = new WP_Query($args);
        while ($query->have_posts()) {
            $query->the_post();
            $refund_post_id = get_the_ID();
            $order_info = get_field('order_id');
            $order_id = $order_info['title'];
            $order_url = admin_url('post.php?post=' . $order_id . '&action=edit');
            $refund_url = admin_url('post.php?post=' . $refund_post_id . '&action=edit');

            echo "<tr>
                <td><a href='" . $refund_url . "'>" . $refund_post_id . "</a></td>
                <td>" . get_the_date() . "</td>
                <td>" . $order_id . "</td>
            </tr>";
        }
        ?>

        </table>
<?php
}

    // Add Custom Dashboard Widget
    public function riothere_add_try_and_buy_dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'try_and_buy',
            'Try and Buy',
            array($this, 'riothere_riothere_try_and_buy_function')
        );
    }

    public function riothere_riothere_try_and_buy_function()
    {
        // Display whatever you want to show.
        ?>
        <form >
  <label for="date_picker">choose a start date:</label>
  <input type="date" id="try-and-buy-date-picker" name="try-and-buy-date-picker">
    <button id="riothere_fetch_try_and_buy_button" class="button button-primary" type="submit">Submit</button>
</form>

        <table class="riothere-dashboard-table" id="riothere_try_and_buy_dashboard_table">
            <thead>
            <tr>
                <th>Order ID</th>
                <th>Items to Deliver</th>
                <th>Items to Retrieve</th>
                <th>Items Purchased</th>

            </tr>
            <tbody>
    </tbody>
    </thead>
        </table>

      <?php
}

    // Add Custom Dashboard Widget
    public function riothere_add_build_frontend_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'build_frontend',
            'Build Frontend',
            array($this, 'riothere_build_frontend_function')
        );
    }

    public function riothere_build_frontend_function()
    {
        // Display whatever you want to show.
        ?>
        <div>Click on the below button to rebuild the frontend for the Homepage and SEO changes to take effect. Please note that this is an expensive operation and can cause a downtime of up to 10 minutes. Try to avoid running it during peak hours.</div>
        <br/>
        <form>
            <button id="riothere_build_frontend_button" class="button button-primary" type="submit">Build Now</button>
        </form>

        <?php
}
}
