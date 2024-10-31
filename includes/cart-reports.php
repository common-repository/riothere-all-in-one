<?php

add_action('admin_menu', 'riothere_register_cart_reports_page');
function riothere_register_cart_reports_page()
{
    global $new_menu_page;

    add_submenu_page(
        'edit.php?post_type=carts',
        'Cart Reports',
        'Cart Reports',
        'edit_posts',
        'cart-reports',
        'riothere_cart_report_page',
        1
    );
}

function riothere_cart_report_page()
{
    if (!class_exists('WP_List_Table')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }

    class Cart_Reports_Table extends WP_List_Table
    {

        /**
         * Constructor, we override the parent to pass our own arguments
         * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
         */
        function __construct()
        {
            parent::__construct(array(
                'singular' => 'Cart Report', //singular name of the listed records
                'plural' => 'Cart Reports', //plural name of the listed records
                'ajax' => false,
            ));
        }

        function get_columns()
        {
            $columns = array(
                'fulfilled_cart_after_first_email' => 'Number of Users who fulfilled their carts after the first email was sent',
                'fulfilled_cart_after_second_email' => 'Number of Users who fulfilled their carts after the second email was sent',
                'fulfilled_cart_after_third_email' => 'Number of Users who fulfilled their carts after the third email was sent',
                'cart_not_fulfilled' => 'Number of users who have not fulfilled their carts',
            );
            return $columns;
        }

        public function column_default($cart, $column_name)
        {
            switch ($column_name) {
                case 'image':
                    if ($item[$column_name] === "") {
                        return "";
                    }

                    return "<img height=100 width=100 src='" . $item[$column_name] . "'/>";
                case 'fulfilled_cart_after_first_email':
                    return '<h1>' . $cart[$column_name] . '</h1>';

                case 'fulfilled_cart_after_second_email':
                    return '<h1>' . $cart[$column_name] . '</h1>';

                case 'fulfilled_cart_after_third_email':
                    return '<h1>' . $cart[$column_name] . '</h1>';

                case 'cart_not_fulfilled':
                    return '<h1>' . $cart[$column_name] . '</h1>';

                default:
                    return print_r($item, true); //Show the whole array for troubleshooting purposes
            }
        }

        function prepare_items()
        {
            global $_wp_column_headers;
            $screen = get_current_screen();

            $data = array();

            $carts = get_posts([
                'post_type' => 'carts',
                'status' => 'any',
                'nopaging' => true,
            ]);

            if ($carts) {
                $carts_fulfilled_after_first_email_sent = 0;
                $carts_fulfilled_after_second_email_sent = 0;
                $carts_fulfilled_after_third_email_sent = 0;
                $carts_not_fulfilled = 0;

                foreach ($carts as $cart) {
                    $first_email_condition = get_field('first_email_sent', $cart->ID, true);
                    $second_email_condition = get_field('second_email_sent', $cart->ID, true);
                    $third_email_condition = get_field('third_email_sent', $cart->ID, true);
                    $is_cart_fulfilled = get_field('is_cart_fulfilled', $cart->ID, true);

                    if ($first_email_condition && !$second_email_condition && !$third_email_condition && $is_cart_fulfilled) {
                        $carts_fulfilled_after_first_email_sent++;
                    } else if ($second_email_condition && !$third_email_condition && $is_cart_fulfilled) {
                        $carts_fulfilled_after_second_email_sent++;
                    } else if ($third_email_condition && $is_cart_fulfilled) {
                        $carts_fulfilled_after_third_email_sent++;
                    } else if (!$is_cart_fulfilled) {
                        $carts_not_fulfilled++;
                    }
                }

                array_push($data, array(
                    'fulfilled_cart_after_first_email' => $carts_fulfilled_after_first_email_sent,
                    'fulfilled_cart_after_second_email' => $carts_fulfilled_after_second_email_sent,
                    'fulfilled_cart_after_third_email' => $carts_fulfilled_after_third_email_sent,
                    'cart_not_fulfilled' => $carts_not_fulfilled,
                ));

            }

            /* -- Register the Columns -- */
            $columns = $this->get_columns();
            $hidden = [];
            // $hidden = $this->get_hidden_columns();
            $hidden = array();
            $_wp_column_headers[$screen->id] = $columns;

            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->items = $data;
        }
    }

    $wp_list_table = new Cart_Reports_Table();
    $wp_list_table->prepare_items();

    ?>
      <div class="wrap">
        <h2>Cart Reports</h2>

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