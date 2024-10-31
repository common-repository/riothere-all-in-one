<?php

function riothere_get_riot_admin_settings()
{
    register_rest_route('riothere/v1', 'admin-settings', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $phone = get_option('phone_number_setting');
            $email = get_option('email_setting');
            $font = get_option('font_setting');
            $fontColor = get_option('font_color_setting');
            $bannerNoticeMessage = get_option('banner_notice_message_setting');
            $defaultTryAndBuyFee = get_option('default_try_and_buy_fee_setting');
            $websiteLogoUrl = get_option('website_logo_setting');
            $brandName = get_option('brand_name_setting');

            $data = [
                'phone_number' => $phone,
                'email' => $email,
                'font' => $font,
                'font_color' => $fontColor,
                'banner_notice_message' => $bannerNoticeMessage,
                'default_try_and_buy_fee' => $defaultTryAndBuyFee,
                'website_logo_url' => $websiteLogoUrl,
                'brand_name' => $brandName,
            ];

            return $data;
        }));
}

add_action('rest_api_init', 'riothere_get_riot_admin_settings');
