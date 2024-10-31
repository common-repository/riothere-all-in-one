<?php

function riothere_get_riot_home_page_slides()
{
    register_rest_route('riothere/v1', 'home-page-slides', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $args = [
                'post_type' => 'slider',
                'post_status' => 'publish',
                'orderby' => 'meta_value_num',
                'meta_key' => 'order',
                'order' => 'ASC',
                'posts_per_page' => -1,
            ];

            $query = new WP_Query($args);

            $formattedData = [];

            while ($query->have_posts()) {
                $query->the_post();
                $slide = [
                    'back_ground_image' => get_field('back_ground_image')['url'],
                    'order' => get_field('order'),
                    'device_type' => get_field('device_type'),
                    'buttons' => [],
                    'text_areas' => [],
                ];

                $buttons = [];

                $slidButtons = get_field('buttons');
                if ($slidButtons) {
                    foreach ($slidButtons as $slideButton) {
                        $buttons[] = [
                            'text' => get_field('text', $slideButton),
                            'path' => get_field('path', $slideButton),
                            'text_color' => get_field('text_color', $slideButton),
                            'back_ground_color' => get_field('back_ground_color', $slideButton),
                            'border_color' => get_field('border_color', $slideButton),
                            'border_radius' => get_field('border_radius', $slideButton),
                            'left' => get_field('left', $slideButton),
                            'top' => get_field('top', $slideButton),
                            'width' => get_field('width', $slideButton),
                            'height' => get_field('height', $slideButton),
                            'z_index' => get_field('z_index', $slideButton),
                            'font_size' => get_field('font_size', $slideButton),
                            'font_style' => get_field('font_style', $slideButton),
                            'font_type' => get_field('font_type', $slideButton),
                        ];

                        $slide['buttons'] = $buttons;
                    }
                }

                $textAreas = [];

                $slidTextAreas = get_field('text_areas');
                if ($slidTextAreas) {
                    foreach ($slidTextAreas as $slideTextArea) {
                        $post = get_post($slideTextArea);
                        $the_content = $post->post_content;
                        $the_content = apply_filters('the_content', $the_content);
                        $textAreas[] = [
                            'text' => $the_content,
                            'back_ground_color' => get_field('back_ground_color', $slideTextArea),
                            'border_radius' => get_field('border_radius', $slideTextArea),
                            'left' => get_field('left', $slideTextArea),
                            'top' => get_field('top', $slideTextArea),
                            'width' => get_field('width', $slideTextArea),
                            'height' => get_field('height', $slideTextArea),
                            'z_index' => get_field('z_index', $slideTextArea),
                            'font_size' => get_field('font_size', $slideTextArea),
                            'font_type' => get_field('font_type', $slideTextArea),
                        ];

                        $slide['text_areas'] = $textAreas;
                    }
                }

                $formattedData[] = $slide;
            }

            return $formattedData;
        }
    ));
}

add_action('rest_api_init', 'riothere_get_riot_home_page_slides');
