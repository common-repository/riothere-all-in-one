<?php

function riothere_get_time_slots_api()
{
    register_rest_route('riothere/v1', 'time-slots', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {

            // Note that the keys NEED to be exactly equal to the options of the
            // "Day" Custom Field in the group "Time Slots"
            $dayToTimeSlotMap = array(
                "Monday" => [],
                "Tuesday" => [],
                "Wednesday" => [],
                "Thursday" => [],
                "Friday" => [],
                "Saturday" => [],
                "Sunday" => [],
            );

            $args = array(
                'post_type' => 'time-slots',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'from_time_order' => array(
                        'key' => 'from_time',
                    ),
                ),
                'orderby' => array(
                    'from_time_order' => 'ASC',
                ),
            );

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $dayToTimeSlotMap[get_field('day')][] = get_field('from_time') . " - " . get_field('to_time');
                }
            }

            $data = [];

            foreach ($dayToTimeSlotMap as $day => $timeslot) {
                $time = strtotime('next ' . $day);
                $is_date_in_the_future = $time > strtotime('now');
                $has_time_slots = count($dayToTimeSlotMap[$day]) > 0;

                if ($is_date_in_the_future && $has_time_slots) {
                    $date = date("l, F j, Y", $time);
                    $data[] = [
                        "date" => $date,
                        "time_slots" => $dayToTimeSlotMap[$day],
                    ];
                }
            }

            // Sort the data by closest date to furthest date
            function date_compare($element1, $element2)
        {
                $datetime1 = strtotime($element1['date']);
                $datetime2 = strtotime($element2['date']);
                return $datetime1 - $datetime2;
            }

            usort($data, 'date_compare');

            return $data;
        }));
}

add_action('rest_api_init', 'riothere_get_time_slots_api');
