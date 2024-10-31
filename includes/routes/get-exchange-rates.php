<?php

function riothere_curl($url)
{
    $response = wp_remote_get($url);
    $body = wp_remote_retrieve_body($response);
    return $body;
}

function riothere_get_exchange_rates()
{
    register_rest_route('riothere/v1', 'exchange-rates', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request) {
            $refresh_rate_in_hours = 12;
            $now = (new DateTime('NOW'))->format('Y-m-d H:i:s');
            $minutes = INF;

            $rates = array(
                "AED" => array(
                    "USD" => "0.27",
                    "GBP" => "0.2",
                    "EUR" => "0.23",
                ),
                "last_refresh" => $now,
            );

            // Fetch the cached exchange rates
            $cache = get_option('exchange_rates');
            // error_log("cache: " . print_r($cache, true) . "\n", 3, 'codemonk.log');

            if ($cache) {
                // error_log("in if cache: " . print_r("", true) . "\n", 3, 'codemonk.log');
                $rates = json_decode($cache);
                $last_refresh = new DateTime($rates->last_refresh);
                $diff = $last_refresh->diff(new DateTime('NOW'));
                $minutes = $diff->days * 24 * 60;
                $minutes += $diff->h * 60;
                $minutes += $diff->i;
            }
            // error_log("minutes: " . print_r($minutes, true) . "\n", 3, 'codemonk.log');
            // error_log("rate after cache: " . print_r($rates, true) . "\n", 3, 'codemonk.log');

            if (!$cache || $minutes > 60 * $refresh_rate_in_hours) {
                // error_log("in api call: " . print_r("", true) . "\n", 3, 'codemonk.log');
                // No exchange rates in cache OR last cache refresh was more
                // than `refresh_rate_in_hours` ago -> Refresh cache

                // Free version allows max of 2 pairs per request
                try {
                    $details1 = riothere_curl('https://free.currconv.com/api/v7/convert?q=AED_USD,AED_GBP&compact=ultra&apiKey=' . EXCHANGE_RATE_API_KEY);
                    $details2 = riothere_curl('https://free.currconv.com/api/v7/convert?q=AED_EUR&compact=ultra&apiKey=' . EXCHANGE_RATE_API_KEY);
                } catch (Exception $e) {
                    // error_log("caught: " . print_r($e->getMessage(), true) . "\n", 3, 'codemonk.log');
                    $details1 = null;
                    $details2 = null;
                }

                $details1 = json_decode($details1);
                $details2 = json_decode($details2);

                if ($details1 && $details2) {
                    $rates = array(
                        "AED" => array(
                            "USD" => $details1->AED_USD,
                            "GBP" => $details1->AED_GBP,
                            "EUR" => $details2->AED_EUR,
                        ),
                        "last_refresh" => $now,
                    );
                    // error_log("rate after api: " . print_r($rates, true) . "\n", 3, 'codemonk.log');

                    $result = update_option('exchange_rates', json_encode($rates));
                    // error_log("result of setting cache: " . print_r($result, true) . "\n", 3, 'codemonk.log');
                }
            }

            // error_log("rate to return: " . print_r($rates, true) . "\n", 3, 'codemonk.log');

            // TODO remove this. It's a temporary measure while the API key is re-generated (because it expired)
            $rates = array(
                "AED" => array(
                    "USD" => "0.27",
                    "GBP" => "0.23",
                    "EUR" => "0.27",
                ),
                "last_refresh" => $now,
            );

            return $rates;
        }));
}

add_action('rest_api_init', 'riothere_get_exchange_rates');
