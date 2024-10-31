<?php

add_filter('woocommerce_payment_gateways', 'riothere_add_your_gateway_class');

function riothere_add_your_gateway_class($methods)
{
    $methods[] = 'WC_Custom_Spotii';
    $methods[] = 'WC_Custom_Postpay';
    $methods[] = 'WC_Custom_Paypal';
    $methods[] = 'WC_Custom_Ngenius';
    return $methods;
}

add_action('plugins_loaded', 'riothere_init_wc_custom_payment_gateway');

function riothere_init_wc_custom_payment_gateway()
{
    class WC_Custom_Spotii extends WC_Payment_Gateway
    {
        function __construct()
        {
            $this->id = 'spotii';
            $this->method_title = 'Spotii';
            $this->title = 'Spotii';
            $this->has_fields = true;
            $this->method_description = 'Custom gateway added by Riothere All In One plugin to be able to link it to Vend';

            //load the settings
            $this->init_form_fields();
            $this->init_settings();
            $this->enabled = $this->get_option('enabled');
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');

            //process settings with parent method
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'type' => 'checkbox',
                    'label' => 'Enable Spotii',
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => 'Method Title',
                    'type' => 'text',
                    'description' => 'This controls the payment method title',
                    'default' => 'Spotii',
                    'desc_tip' => true,
                ),
                // 'description' => array(
                //     'title' => 'Customer Message',
                //     'type' => 'textarea',
                //     'css' => 'width:500px;',
                //     'default' => 'Your Payment Gateway Description',
                //     'description' => 'The message which you want it to appear to the customer in the checkout page.',
                // ),
            );
        }
    }

    class WC_Custom_Postpay extends WC_Payment_Gateway
    {
        function __construct()
        {
            $this->id = 'postpay';
            $this->method_title = 'Postpay';
            $this->title = 'Postpay';
            $this->has_fields = true;
            $this->method_description = 'Custom gateway added by Riothere All In One plugin to be able to link it to Vend';

            //load the settings
            $this->init_form_fields();
            $this->init_settings();
            $this->enabled = $this->get_option('enabled');
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');

            //process settings with parent method
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'type' => 'checkbox',
                    'label' => 'Enable Postpay',
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => 'Method Title',
                    'type' => 'text',
                    'description' => 'This controls the payment method title',
                    'default' => 'Postpay',
                    'desc_tip' => true,
                ),
                // 'description' => array(
                //     'title' => 'Customer Message',
                //     'type' => 'textarea',
                //     'css' => 'width:500px;',
                //     'default' => 'Your Payment Gateway Description',
                //     'description' => 'The message which you want it to appear to the customer in the checkout page.',
                // ),
            );
        }
    }

    class WC_Custom_Paypal extends WC_Payment_Gateway
    {
        function __construct()
        {
            $this->id = 'paypal';
            $this->method_title = 'PayPal';
            $this->title = 'PayPal';
            $this->has_fields = true;
            $this->method_description = 'Custom gateway added by Riothere All In One plugin to be able to link it to Vend';

            //load the settings
            $this->init_form_fields();
            $this->init_settings();
            $this->enabled = $this->get_option('enabled');
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');

            //process settings with parent method
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'type' => 'checkbox',
                    'label' => 'Enable PayPal',
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => 'Method Title',
                    'type' => 'text',
                    'description' => 'This controls the payment method title',
                    'default' => 'PayPal',
                    'desc_tip' => true,
                ),
                // 'description' => array(
                //     'title' => 'Customer Message',
                //     'type' => 'textarea',
                //     'css' => 'width:500px;',
                //     'default' => 'Your Payment Gateway Description',
                //     'description' => 'The message which you want it to appear to the customer in the checkout page.',
                // ),
            );
        }
    }

    class WC_Custom_Ngenius extends WC_Payment_Gateway
    {
        function __construct()
        {
            $this->id = 'ngenius';
            $this->method_title = 'Payment Cards';
            $this->title = 'Payment Cards';
            $this->has_fields = true;
            $this->method_description = 'Custom gateway added by Riothere All In One plugin to be able to link it to Vend';

            //load the settings
            $this->init_form_fields();
            $this->init_settings();
            $this->enabled = $this->get_option('enabled');
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');

            //process settings with parent method
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'type' => 'checkbox',
                    'label' => 'Enable Payment Cards',
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => 'Method Title',
                    'type' => 'text',
                    'description' => 'This controls the payment method title',
                    'default' => 'Payment Cards',
                    'desc_tip' => true,
                ),
                // 'description' => array(
                //     'title' => 'Customer Message',
                //     'type' => 'textarea',
                //     'css' => 'width:500px;',
                //     'default' => 'Your Payment Gateway Description',
                //     'description' => 'The message which you want it to appear to the customer in the checkout page.',
                // ),
            );
        }
    }
}
