<?php
function riothere_reset_password_email($email, $reset_code, $expiry)
{
    $user = get_user_by('email', $email);
    $first_name = esc_html($user->first_name);

    $imgsrc = plugin_dir_url(RIOTHERE_ALL_IN_ONE_VERSION_DIR_PATH) . 'public/assets/android-chrome-192x192.png';
    $email_token = riot_encryptString($email);

    $admin_email = get_option('admin_email');

    ob_start();

    ?>
        <!doctype html>
        <html lang="en">
            <body>
              <div style="width: 100%; text-align: center; background: black">
                <img src='<?php echo esc_url($imgsrc) ?>' />
              </div>
              <p>
                Hello <?php echo esc_textarea($first_name) ?>,
              </p>
              <br/>
              <p>
              You had recently requested to reset your password. Please copy the verification code and we'll help you easily get back to your shopping.
              </p>
              <br/>
              <p style='text-align:center; font-size:35px; font-weight:600;'>
                <?php echo esc_textarea($reset_code) ?>
              </p>
              <i>
                If you didn't request this, please ignore this email. Your password won't be changed unless you proceed to reset with the code above and create a new password.
              </i>
              <?php if ($expiry !== 0) {?>
                <i>Please note that this code will expire at <?php echo esc_textarea(get_formatted_date($expiry)); ?>.</i>
              <?php }?>
              <br/>
              <p>
                <div>Regards,</div>
                <div>The RIOT team.</div>
              </p>
              <p>
                If you have any questions you can email us at <a href='mailto:<?php echo esc_textarea($admin_email) ?>'><?php echo esc_textarea($admin_email) ?></a>
              </p>
            </body>
        </html>

        <?php

    return ob_get_clean();
}
