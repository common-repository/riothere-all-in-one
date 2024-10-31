<?php

function riothere_get_faqs_api()
{
    register_rest_route('riothere/v1', 'faq', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {

            $results = array(
                'faqBuyers' => array(),
                'faqSellers' => array(),
            );

            $args_buyers_query = array(
                'posts_per_page' => -1,
                'post_type' => 'faqs',
                'meta_key' => 'order',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
                'meta_query' => array(

                    array(
                        'key' => 'faq_type',
                        'compare' => 'LIKE',
                        'value' => "Buyers",
                    ),
                ),
            );

            $buyers_query = new WP_Query($args_buyers_query);

            if ($buyers_query->have_posts()) {
                while ($buyers_query->have_posts()) {
                    $buyers_query->the_post();
                    array_push($results['faqBuyers'], array(
                        'question' => get_field('question', get_the_ID()),
                        'answer' => get_field('answer', get_the_ID()),
                        'showOnContactUs' => get_field('show_on_contact_us', get_the_ID()),
                    ));
                }
            }

            wp_reset_postdata();

            $args_sellers_query = array(
                'posts_per_page' => -1,
                'post_type' => 'faqs',
                'meta_key' => 'order',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
                'meta_query' => array(

                    array(
                        'key' => 'faq_type',
                        'compare' => 'LIKE',
                        'value' => "Sellers",
                    ),
                ),
            );

            $sellers_query = new WP_Query($args_sellers_query);

            if ($sellers_query->have_posts()) {
                while ($sellers_query->have_posts()) {
                    $sellers_query->the_post();

                    array_push($results['faqSellers'], array(
                        'question' => get_field('question', get_the_ID()),
                        'answer' => get_field('answer', get_the_ID()),
                        'showOnContactUs' => get_field('show_on_contact_us', get_the_ID()),
                    ));
                }
            }
            wp_reset_postdata();

            return $results;

        }));
}

add_action('rest_api_init', 'riothere_get_faqs_api');
