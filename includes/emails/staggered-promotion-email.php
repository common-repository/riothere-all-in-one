<?php
function get_staggered_promotion_email($first_name, $coupon)
{
    $imgsrc = plugin_dir_url(RIOTHERE_ALL_IN_ONE_VERSION_DIR_PATH) . 'public/assets/android-chrome-192x192.png';

    ob_start();

    ?>
        <!doctype html>
        <html lang="en">
            <body>
              <div style="width: 100%; text-align: center; background: black">
                <img src='<?php echo esc_url($imgsrc) ?>' />
              </div>
              <p>
                Dear <?php echo esc_textarea($first_name); ?>,
              </p>
              <br/>
              <p>
                We are so pleased to let you know that you are eligible to use the coupon code "<?php echo esc_textarea($coupon->get_code()); ?>" to get an additional <?php echo esc_textarea($coupon->get_amount('edit')) ?>% off your next purchase.
              </p>
              <br/>
              <p>
                Simply enter the coupon code at checkout. Coupon code expires on <?php echo esc_textarea(date_format($coupon->get_date_expires(), "d/m/Y")); ?>.
              </p>
              <br/>
              <p>
                <div>All the best,</div>
                <div>The RIOT team.</div>
              </p>
            </body>
        </html>

        <?php

    return ob_get_clean();
}
