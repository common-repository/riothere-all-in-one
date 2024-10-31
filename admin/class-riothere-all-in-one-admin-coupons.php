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
class Riothere_All_In_One_Admin_Coupons
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     *
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('manage_shop_coupon_posts_custom_column', function ($column) {
            global $post;

            $coupon = new WC_Coupon($post->ID);
            switch ($column) {
                case 'products_custom':
                    {
                        $product_ids = $coupon->get_product_ids();

                        if (count($product_ids) > 0) {
                            echo esc_html(implode(', ', $product_ids));
                        } else {
                            echo 'All';
                        }
                        break;
                    }
                default:
                    {
                        break;
                    }
            }
        }, 1, 1);

        add_action(
            'manage_edit-shop_coupon_columns',
            function ($columns) {
                unset($columns['products']);
                $columns['products_custom'] = __('Products', 'riothere-all-in-one');

                return $columns;
            }, 10, 1
        );
        add_filter('woocommerce_coupon_discount_types', function ($coupon_types) {
            return [
                'percent' => __('Percentage discount', 'woocommerce'),
                'fixed_cart' => __('Fixed cart discount', 'woocommerce'),
            ];
        });

        add_filter('woocommerce_coupon_get_email_restrictions', array($this, 'riothere_filter_email_restrictions'), 10, 2);

    }

    private function getSellers()
    {
        $args = array(
            'role' => 'seller',
            'orderby' => 'user_nicename',
            'order' => 'ASC',
        );

        return get_users($args);
    }

    public function riothere_filter_email_restrictions($email_restrictions, $coupon)
    {
        if ($email_restrictions === '') {
            $email_restrictions = [];
        }

        $programmatically_allowed_emails = $coupon->get_meta('programmatically_allowed_emails');
        if ($programmatically_allowed_emails === '') {
            $programmatically_allowed_emails = [];
        }

        $email_restrictions = array_unique(array_merge($email_restrictions, $programmatically_allowed_emails));

        return $email_restrictions;
    }

    /*
     * Some fields are being hidden using CSS
     * */
    public function riothere_add_coupon_usage_restrictions($coupon_id, $coupon)
    {
        $sellers = $this->getSellers();
        $sellers_ids = $coupon->get_meta('exclude_product_sellers');
        $minimum_product_price = $coupon->get_meta('minimum_product_price');
        $maximum_product_price = $coupon->get_meta('maximum_product_price');

        $programmatically_allowed_emails = $coupon->get_meta('programmatically_allowed_emails');
        if ($programmatically_allowed_emails === '') {
            $programmatically_allowed_emails = [];
        }

        ?>
        <style>
            .form-field.individual_use_field,
            .form-field.free_shipping_field,
            .form-field.exclude_sale_items_field {
                display: none !important;
            }
        </style>
        <div class="options_group">
			<?php
// Customers.
        woocommerce_wp_text_input(
            array(
                'id' => 'programmatically_allowed_emails',
                'label' => __('Allowed emails (added&nbsp;programmatically)', 'woocommerce'),
                'placeholder' => __('No emails', 'woocommerce'),
                'description' => __('List of emails that either signed up to the newsletter or were awarded this coupon because of a staggered promotion. This field is a readonly',
                    'woocommerce'),
                'value' => implode(', ', (array) $programmatically_allowed_emails),
                'desc_tip' => true,
                'type' => 'email',
                'class' => '',
                'custom_attributes' => array(
                    'readonly' => 'readonly',
                ),
            )
        );
        ?>
        </div>
		<?php
// Individual use.
        //        woocommerce_wp_checkbox(
        //            array(
        //                'id'          => 'restrict_to_allowed_emails',
        //                'label'       => __( 'Restrict to allowed emails', 'woocommerce' ),
        //                'description' => __( 'Check this box if the coupon can be only be used if user email is available in the Allowed Emails field (this would take effect only if the Allowed Emails list was Empty).', 'woocommerce' ),
        //                'value'       => wc_bool_to_string( true ),
        //            )
        //        );

        ?>
        <p class="form-field">
            <label for="exclude_product_sellers"><?php _e('Exclude Sellers', 'woocommerce');?></label>
            <select id="exclude_product_sellers" name="exclude_product_sellers[]" style="width: 50%;"
                    class="wc-enhanced-select" multiple="multiple"
                    data-placeholder="<?php esc_attr_e('No sellers', 'woocommerce');?>">
				<?php

        if ($sellers) {
            foreach ($sellers as $seller) {
                echo '<option value="' . esc_attr($seller->ID) . '"' . wc_selected($seller->ID, $sellers_ids) . '>' . esc_html($seller->display_name . '<' . $seller->user_email . '>') . '</option>';
            }
        }
        ?>
            </select>
			<?php echo wc_help_tip(__('Product sellers that the coupon will not be applied to, or that cannot be in the cart in order for the "Fixed cart discount" to be applied.', 'woocommerce')); ?>
        </p>
        <hr>
        <div class="exclude-price-container">
			<?php

        // minimum spend.
        woocommerce_wp_text_input(
            array(
                'id' => 'minimum_product_price',
                'label' => __('Exclude products with price greater than', 'woocommerce'),
                'placeholder' => __('not set', 'woocommerce'),
                'description' => __('Exclude product with price greater than or equal to the number that you set. This is compared to the price before tax. Leave empty to ignore this rule', 'woocommerce'),
                'data_type' => 'price',
                'desc_tip' => true,
                'value' => $minimum_product_price,
            )
        );
        ?>
            <p>AND</p>
			<?php
// maximum spend.
        woocommerce_wp_text_input(
            array(
                'id' => 'maximum_product_price',
                'label' => __('Exclude products with price less than', 'woocommerce'),
                'placeholder' => __('not set', 'woocommerce'),
                'description' => __('Exclude product with price less than or equal to the number that you set. This is compared to the price before tax. Leave empty to ignore this rule', 'woocommerce'),
                'data_type' => 'price',
                'desc_tip' => true,
                'value' => $maximum_product_price,
            )
        );
        ?>
        </div>
        <hr>
		<?php
}

    public function riothere_save_coupon_text_field($post_id, $coupon)
    {
        if (isset($_POST['exclude_product_sellers'])) {
            $coupon->update_meta_data('exclude_product_sellers', array_filter(array_map('intval', $_POST['exclude_product_sellers'])));
        } else {
            $coupon->update_meta_data('exclude_product_sellers', []);
        }

        // Codemonk: I commented the below 5 if-statements because they were
        // overwriting any programmatic changes onto the coupon anytime someone
        // saves the coupon. I couldn't understand why they were added and
        // after some digging, they seemed uncessesary.

        // if (!isset($_POST['customer_email']) || empty($_POST['customer_email'])) {
        //     $coupon->set_email_restrictions([]);
        // }
        // if (!isset($_POST['product_categories']) || empty($_POST['product_categories'])) {
        //     $coupon->set_product_categories([]);
        // }
        // if (!isset($_POST['exclude_product_ids']) || empty($_POST['exclude_product_ids'])) {
        //     $coupon->set_excluded_product_ids([]);
        // }
        // if (!isset($_POST['exclude_product_categories']) || empty($_POST['exclude_product_categories'])) {
        //     $coupon->set_excluded_product_categories([]);
        // }
        // if (!isset($_POST['product_ids']) || empty($_POST['product_ids'])) {
        //     $coupon->set_product_ids([]);
        // }

        $coupon->update_meta_data('minimum_product_price', wc_format_decimal($_POST['minimum_product_price']));
        $coupon->update_meta_data('maximum_product_price', wc_format_decimal($_POST['maximum_product_price']));

        $coupon->save();
    }

    public function riothere_check_custom_coupon_product_rules($valid, $product, $coupon, $values)
    {
        $product_id = $product->get_id();
        $sellers_ids = $coupon->get_meta('exclude_product_sellers');
        $minimum_product_price = $coupon->get_meta('minimum_product_price');
        $maximum_product_price = $coupon->get_meta('maximum_product_price');
        $seller = get_field('seller', $product_id);
        $seller_id = $seller['ID'];

        $product_price = (float) $product->get_regular_price();

        if (count($sellers_ids) > 0) {
            if (in_array($seller_id, $sellers_ids)) {
                $valid = false;
            }
        }

        if ($valid) {
            if ($minimum_product_price !== '' && $maximum_product_price !== '') {
                $valid = !((float) $minimum_product_price <= $product_price && $product_price <= (float) $maximum_product_price);
            } else if ($minimum_product_price !== '') {
                $valid = !((float) $minimum_product_price <= $product_price);
            } else if ($maximum_product_price !== '') {
                $valid = !($product_price <= (float) $maximum_product_price);
            }
        }

        return $valid;
    }

    /**
     * @param $valid
     * @param $coupon
     * @param $discount
     *
     * @return false
     * @throws Exception
     */
    public function check_custom_coupon_user_email_rule($valid, $coupon, $discount)
    {
        // Get user and posted emails to compare.
        $billing_email = isset($posted['billing_email']) ? $posted['billing_email'] : '';
        $check_emails = [strtolower($billing_email)];
        $email_valid = true;

        $restrictions = $coupon->get_email_restrictions();

        if ($billing_email === '' || (is_array($restrictions) && !WC()->cart->is_coupon_emails_allowed($check_emails, $restrictions))) {
            $valid = false;
            $email_valid = false;
        }
        if (!$email_valid) {
            // the error code aka 400 isn't related to http status errors
            // the list of error codes used by WooCommerce are available here https://woocommerce.github.io/code-reference/classes/WC-Coupon.html
            throw new Exception(sprintf(__('Email not applicable for coupon code "%s"', 'woocommerce'), esc_html($coupon->get_code())), 400);

        }

        return $valid;
    }

    public function riothere_setup_newsletter_popup_option_page()
    {
        if (function_exists('acf_add_options_sub_page')) {
            acf_add_options_sub_page(array(
                'page_title' => 'Newsletter Popup Configuration',
                'menu_title' => 'Newsletter Popup Configuration',
                'menu_slug' => 'newsletter-popup-configuration',
                'capability' => 'edit_posts',
                'parent_slug' => 'woocommerce-marketing',
            ));
        }
    }

}
