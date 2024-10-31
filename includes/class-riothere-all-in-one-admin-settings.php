<?php

/**
 * Register all actions and filters for the plugin
 *
 * @since      1.0.0
 *
 * @package    Riothere_All_In_One
 * @subpackage Riothere_All_In_One/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Riothere_All_In_One
 * @subpackage Riothere_All_In_One/includes
 */
class Riothere_All_In_One_Admin_Settings
{

    public static function add_admin_settings()
    {
        function riothere_site_details_settings_api_init()
        {
            // Add the section to general settings so we can add our
            // fields to it
            add_settings_section(
                'site_details_setting_section',
                'Site Details settings',
                'site_details_setting_section_callback_function',
                'general'
            );

            // Add the field with the names and function to use for our new
            // settings, put it in our new section
            add_settings_field(
                'phone_number_setting',
                'Phone Number',
                'phone_number_setting_callback_function',
                'general',
                'site_details_setting_section'
            );

            // Register our setting so that $_POST handling is done for us and
            // our callback function just has to echo the <input>
            register_setting('general', 'phone_number_setting');

            // Add the field with the names and function to use for our new
            // settings, put it in our new section
            add_settings_field(
                'email_setting',
                'Email',
                'email_setting_callback_function',
                'general',
                'site_details_setting_section'
            );

            // Register our setting so that $_POST handling is done for us and
            // our callback function just has to echo the <input>
            register_setting('general', 'email_setting');

            add_settings_field(
                'font_setting',
                'Font',
                'font_setting_callback_function',
                'general',
                'site_details_setting_section'
            );

            // Register our setting so that $_POST handling is done for us and
            // our callback function just has to echo the <input>
            register_setting('general', 'font_setting');

            add_settings_field(
                'font_color_setting',
                'Font Color',
                'font_color_setting_callback_function',
                'general',
                'site_details_setting_section'
            );

            // Register our setting so that $_POST handling is done for us and
            // our callback function just has to echo the <input>
            register_setting('general', 'font_color_setting');

            add_settings_field(
                'banner_notice_message_setting',
                'Banner Notice Message',
                'banner_notice_message_setting_callback_function',
                'general',
                'site_details_setting_section'
            );

            // Register our setting so that $_POST handling is done for us and
            // our callback function just has to echo the <input>
            register_setting('general', 'banner_notice_message_setting');

            add_settings_field(
                'default_try_and_buy_fee_setting',
                'Default Try And Buy Fee',
                'default_try_and_buy_fee_setting_callback_function',
                'general',
                'site_details_setting_section'
            );

            // Register our setting so that $_POST handling is done for us and
            // our callback function just has to echo the <input>
            register_setting('general', 'default_try_and_buy_fee_setting');

            add_settings_field(
                'website_logo_setting',
                'Website Logo URL',
                'website_logo_setting_callback_function',
                'general',
                'site_details_setting_section'
            );

            // Register our setting so that $_POST handling is done for us and
            // our callback function just has to echo the <input>
            register_setting('general', 'website_logo_setting');

            add_settings_field(
                'brand_name_setting',
                'Brand Name',
                'brand_name_setting_callback_function',
                'general',
                'site_details_setting_section'
            );

            // Register our setting so that $_POST handling is done for us and
            // our callback function just has to echo the <input>
            register_setting('general', 'brand_name_setting');
        }
        add_action('admin_init', 'riothere_site_details_settings_api_init');

        /**
         * Settings section callback function
         *
         * This function is needed if we added a new section. This function
         * will be run at the start of our section
         */

        function site_details_setting_section_callback_function()
        {
        }

        /*
         * Callback function for our example setting
         *
         * creates a checkbox true/false option. Other types are surely possible
         */

        function phone_number_setting_callback_function()
        {
            echo '<input name="phone_number_setting" id="phone_number_setting" type="text" value="' . get_option('phone_number_setting') . '" class="code"/>';
        }

        function email_setting_callback_function()
        {
            echo '<input name="email_setting" id="email_setting" type="text" value="' . get_option('email_setting') . '" class="code"/>';
        }

        function font_setting_callback_function()
        {
            echo '<input name="font_setting" id="font_setting" type="text" value="' . get_option('font_setting') . '" class="code"/>';
        }

        function font_color_setting_callback_function()
        {
            echo '<input name="font_color_setting" id="font_color_setting" type="text" value="' . get_option('font_color_setting') . '" class="code"/>';
        }

        function banner_notice_message_setting_callback_function()
        {
            echo '<input name="banner_notice_message_setting" id="banner_notice_message_setting" type="text" value="' . get_option('banner_notice_message_setting') . '" class="code"/>';
        }

        function default_try_and_buy_fee_setting_callback_function()
        {
            echo '<input name="default_try_and_buy_fee_setting" id="default_try_and_buy_fee_setting" type="number" value="' . get_option('default_try_and_buy_fee_setting') . '" class="code"/>';
        }

        function website_logo_setting_callback_function()
        {
            echo '<input name="website_logo_setting" id="website_logo_setting" type="text" value="' . get_option('website_logo_setting') . '" class="code"/>';
        }

        function brand_name_setting_callback_function()
        {
            echo '<input name="brand_name_setting" id="brand_name_setting" type="text" value="' . get_option('brand_name_setting') . '" class="code"/>';
        }
    }

}
