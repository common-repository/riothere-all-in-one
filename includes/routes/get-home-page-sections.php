<?php

function riothere_get_riot_home_page_sections()
{
    register_rest_route('riothere/v1', 'home-page-sections', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $args = [
                'post_type' => 'sections',
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
                $section = [
                    'type' => get_field('type'),
                    'order' => get_field('order'),
                    'device_type' => get_field('device_type'),
                    'tag' => get_field('tag'),
                    'buttons' => [],
                    'text_areas' => [],
                    'title' => get_the_title(),
                    'height' => get_field('height'),
                    'background_color' => get_field('background_color'),
                    'main_text' => get_field('main_text'),
                    'button_text' => get_field('button_text'),
                    'button_path' => get_field('button_path'),
                ];

                $gridData = [];

                if (get_field('type') == 'grid') {
                    $firstItem = [
                        'image' => get_field('first_grid_item_image')['url'],
                        'caption_text' => get_field('first_grid_item_caption_text'),
                        'button_text' => get_field('first_grid_item_button_text'),
                        'button_path' => get_field('first_grid_item_button_path'),
                        'caption_left' => get_field('first_grid_item_caption_left'),
                        'caption_top' => get_field('first_grid_item_caption_top'),
                        'button_left' => get_field('first_grid_item_button_left'),
                        'button_top' => get_field('first_grid_item_button_top'),
                        'mobile_button_top' => get_field('first_grid_item_mobile_button_top'),
                        'mobile_caption_top' => get_field('first_grid_item_mobile_caption_top'),
                        'caption_text_color' => get_field('first_grid_item_desktop_caption_text_color'),
                        'button_text_color' => get_field('first_grid_item_desktop_button_text_color'),
                        'mobile_caption_text_color' => get_field('first_grid_item_mobile_caption_text_color'),
                        'mobile_button_text_color' => get_field('first_grid_item_mobile_button_text_color'),
                        'mobile_button_background_color' => get_field('first_grid_item_mobile_button_background_color'),
                        'mobile_button_left' => get_field('first_grid_item_mobile_button_left'),
                        'mobile_caption_left' => get_field('first_grid_item_mobile_caption_left'),
                        'mobile_caption_text' => get_field('first_grid_item_mobile_caption_text'),
                        'mobile_button_text' => get_field('first_grid_item_mobile_button_text'),
                    ];
                    $gridData[] = $firstItem;

                    $secondItem = [
                        'image' => get_field('Second_grid_item_image')['url'],
                        'caption_text' => get_field('second_grid_item_caption_text'),
                        'button_text' => get_field('second_grid_item_button_text'),
                        'button_path' => get_field('second_grid_item_button_path'),
                        'caption_left' => get_field('second_grid_item_caption_left'),
                        'caption_top' => get_field('second_grid_item_caption_top'),
                        'button_left' => get_field('second_grid_item_button_left'),
                        'button_top' => get_field('second_grid_item_button_top'),
                        'mobile_button_top' => get_field('second_grid_item_mobile_button_top'),
                        'mobile_caption_top' => get_field('second_grid_item_mobile_caption_top'),
                        'caption_text_color' => get_field('second_grid_item_desktop_caption_text_color'),
                        'button_text_color' => get_field('second_grid_item_desktop_button_text_color'),
                        'mobile_caption_text_color' => get_field('second_grid_item_mobile_caption_text_color'),
                        'mobile_button_text_color' => get_field('second_grid_item_mobile_button_text_color'),
                        'mobile_button_background_color' => get_field('second_grid_item_mobile_button_background_color'),
                        'mobile_button_left' => get_field('second_grid_item_mobile_button_left'),
                        'mobile_caption_left' => get_field('second_grid_item_mobile_caption_left'),
                        'mobile_caption_text' => get_field('second_grid_item_mobile_caption_text'),
                        'mobile_button_text' => get_field('second_grid_item_mobile_button_text'),
                    ];
                    $gridData[] = $secondItem;

                    $thirdItem = [
                        'image' => get_field('third_grid_item_image')['url'],
                        'caption_text' => get_field('third_grid_item_caption_text'),
                        'button_text' => get_field('third_grid_item_button_text'),
                        'button_path' => get_field('third_grid_item_button_path'),
                        'caption_left' => get_field('third_grid_item_caption_left'),
                        'caption_top' => get_field('third_grid_item_caption_top'),
                        'button_left' => get_field('third_grid_item_button_left'),
                        'button_top' => get_field('third_grid_item_button_top'),
                        'mobile_button_top' => get_field('third_grid_item_mobile_button_top'),
                        'mobile_caption_top' => get_field('third_grid_item_mobile_caption_top'),
                        'caption_text_color' => get_field('third_grid_item_desktop_caption_text_color'),
                        'button_text_color' => get_field('third_grid_item_desktop_button_text_color'),
                        'mobile_caption_text_color' => get_field('third_grid_item_mobile_caption_text_color'),
                        'mobile_button_text_color' => get_field('third_grid_item_mobile_button_text_color'),
                        'mobile_button_background_color' => get_field('third_grid_item_mobile_button_background_color'),
                        'mobile_button_left' => get_field('third_grid_item_mobile_button_left'),
                        'mobile_caption_left' => get_field('third_grid_item_mobile_caption_left'),
                        'mobile_caption_text' => get_field('third_grid_item_mobile_caption_text'),
                        'mobile_button_text' => get_field('third_grid_item_mobile_button_text'),
                    ];
                    $gridData[] = $thirdItem;

                    $fourthItem = [
                        'image' => get_field('fourth_grid_item_image')['url'],
                        'caption_text' => get_field('fourth_grid_item_caption_text'),
                        'button_text' => get_field('fourth_grid_item_button_text'),
                        'button_path' => get_field('fourth_grid_item_button_path'),
                        'caption_left' => get_field('fourth_grid_item_caption_left'),
                        'caption_top' => get_field('fourth_grid_item_caption_top'),
                        'button_left' => get_field('fourth_grid_item_button_left'),
                        'button_top' => get_field('fourth_grid_item_button_top'),
                        'mobile_button_top' => get_field('fourth_grid_item_mobile_button_top'),
                        'mobile_caption_top' => get_field('fourth_grid_item_mobile_caption_top'),
                        'caption_text_color' => get_field('fourth_grid_item_desktop_caption_text_color'),
                        'button_text_color' => get_field('fourth_grid_item_desktop_button_text_color'),
                        'mobile_caption_text_color' => get_field('fourth_grid_item_mobile_caption_text_color'),
                        'mobile_button_text_color' => get_field('fourth_grid_item_mobile_button_text_color'),
                        'mobile_button_background_color' => get_field('fourth_grid_item_mobile_button_background_color'),
                        'mobile_button_left' => get_field('fourth_grid_item_mobile_button_left'),
                        'mobile_caption_left' => get_field('fourth_grid_item_mobile_caption_left'),
                        'mobile_caption_text' => get_field('fourth_grid_item_mobile_caption_text'),
                        'mobile_button_text' => get_field('fourth_grid_item_mobile_button_text'),
                    ];
                    $gridData[] = $fourthItem;

                    $fifthItem = [
                        'image' => get_field('fifth_grid_item_image')['url'],
                        'caption_text' => get_field('fifth_grid_item_caption_text'),
                        'button_text' => get_field('fifth_grid_item_button_text'),
                        'button_path' => get_field('fifth_grid_item_button_path'),
                        'caption_left' => get_field('fifth_grid_item_caption_left'),
                        'caption_top' => get_field('fifth_grid_item_caption_top'),
                        'button_left' => get_field('fifth_grid_item_button_left'),
                        'button_top' => get_field('fifth_grid_item_button_top'),
                        'mobile_button_top' => get_field('fifth_grid_item_mobile_button_top'),
                        'mobile_caption_top' => get_field('fifth_grid_item_mobile_caption_top'),
                        'caption_text_color' => get_field('fifth_grid_item_desktop_caption_text_color'),
                        'button_text_color' => get_field('fifth_grid_item_desktop_button_text_color'),
                        'mobile_caption_text_color' => get_field('fifth_grid_item_mobile_caption_text_color'),
                        'mobile_button_text_color' => get_field('fifth_grid_item_mobile_button_text_color'),
                        'mobile_button_background_color' => get_field('fifth_grid_item_mobile_button_background_color'),
                        'mobile_button_left' => get_field('fifth_grid_item_mobile_button_left'),
                        'mobile_caption_left' => get_field('fifth_grid_item_mobile_caption_left'),
                        'mobile_caption_text' => get_field('fifth_grid_item_mobile_caption_text'),
                        'mobile_button_text' => get_field('fifth_grid_item_mobile_button_text'),
                    ];
                    $gridData[] = $fifthItem;

                    $sixthItem = [
                        'image' => get_field('sixth_grid_item_image')['url'],
                        'caption_text' => get_field('sixth_grid_item_caption_text'),
                        'button_text' => get_field('sixth_grid_item_button_text'),
                        'button_path' => get_field('sixth_grid_item_button_path'),
                        'caption_left' => get_field('sixth_grid_item_caption_left'),
                        'caption_top' => get_field('sixth_grid_item_caption_top'),
                        'button_left' => get_field('sixth_grid_item_button_left'),
                        'button_top' => get_field('sixth_grid_item_button_top'),
                        'mobile_button_top' => get_field('sixth_grid_item_mobile_button_top'),
                        'mobile_caption_top' => get_field('sixth_grid_item_mobile_caption_top'),
                        'caption_text_color' => get_field('sixth_grid_item_desktop_caption_text_color'),
                        'button_text_color' => get_field('sixth_grid_item_desktop_button_text_color'),
                        'mobile_caption_text_color' => get_field('sixth_grid_item_mobile_caption_text_color'),
                        'mobile_button_text_color' => get_field('sixth_grid_item_mobile_button_text_color'),
                        'mobile_button_background_color' => get_field('sixth_grid_item_mobile_button_background_color'),
                        'mobile_button_left' => get_field('sixth_grid_item_mobile_button_left'),
                        'mobile_caption_left' => get_field('sixth_grid_item_mobile_caption_left'),
                        'mobile_caption_text' => get_field('sixth_grid_item_mobile_caption_text'),
                        'mobile_button_text' => get_field('sixth_grid_item_mobile_button_text'),
                    ];
                    $gridData[] = $sixthItem;
                }

                $section['grid_data'] = $gridData;

                $buttons = [];

                $sectionButtons = get_field('buttons');
                if ($sectionButtons) {
                    foreach ($sectionButtons as $sectionButton) {
                        $buttons[] = [
                            'text' => get_field('text', $sectionButton),
                            'path' => get_field('path', $sectionButton),
                            'text_color' => get_field('text_color', $sectionButton),
                            'back_ground_color' => get_field('back_ground_color', $sectionButton),
                            'border_color' => get_field('border_color', $sectionButton),
                            'border_radius' => get_field('border_radius', $sectionButton),
                            'left' => get_field('left', $sectionButton),
                            'top' => get_field('top', $sectionButton),
                            'width' => get_field('width', $sectionButton),
                            'height' => get_field('height', $sectionButton),
                            'z_index' => get_field('z_index', $sectionButton),
                            'font_size' => get_field('font_size', $sectionButton),
                            'font_style' => get_field('font_style', $sectionButton),
                            'font_type' => get_field('font_type', $sectionButton),
                        ];

                        $section['buttons'] = $buttons;
                    }
                }

                $textAreas = [];

                $sectionTextAreas = get_field('text_areas');
                if ($sectionTextAreas) {
                    foreach ($sectionTextAreas as $sectionTextArea) {
                        $post = get_post($sectionTextArea);
                        $the_content = $post->post_content;
                        $the_content = apply_filters('the_content', $the_content);
                        $textAreas[] = [
                            'text' => $the_content,
                            'back_ground_color' => get_field('back_ground_color', $sectionTextArea),
                            'border_radius' => get_field('border_radius', $sectionTextArea),
                            'left' => get_field('left', $sectionTextArea),
                            'top' => get_field('top', $sectionTextArea),
                            'width' => get_field('width', $sectionTextArea),
                            'height' => get_field('height', $sectionTextArea),
                            'z_index' => get_field('z_index', $sectionTextArea),
                            'font_size' => get_field('font_size', $sectionTextArea),
                            'font_type' => get_field('font_type', $sectionTextArea),
                        ];

                        $section['text_areas'] = $textAreas;
                    }
                }

                $formattedData[] = $section;
            }

            return $formattedData;
        }
    ));
}

add_action('rest_api_init', 'riothere_get_riot_home_page_sections');
