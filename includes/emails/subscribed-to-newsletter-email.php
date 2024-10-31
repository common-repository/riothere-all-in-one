<?php
function get_subscribed_to_newsletter_email($email, $coupon)
{
    $imgsrc = plugin_dir_url(RIOTHERE_ALL_IN_ONE_VERSION_DIR_PATH) . 'public/assets/android-chrome-192x192.png';
    $email_token = riot_encryptString($email);

    ob_start();

    ?>
        <!doctype html>
        <html lang="en">
            <body>
              <div style="width: 100%; text-align: center; background: black">
                <img src='<?php echo esc_url($imgsrc) ?>' />
              </div>
              <p>
                Hello there,
              </p>
              <br/>
              <p>
                Your subscription to our newsletter has been confirmed.
              </p>
              <br/>
              <p>
                Welcome to RIOT.
              </p>
              <p>
                You are now part of a community of like-minded taste makers who are privy to discovering unique pre-owned fashion finds.
              </p>
              <p>
                A whole new exciting world of fashion and style awaits you and we are here to get the journey started.
              </p>
              <p>
                Our newsletters will keep you up to date on our latest arrivals, unique fashion finds,  curated edits on what is on our radar, as well as insights on sustainable fashion, how to detox your closet, what makes an item iconic and much more.
              </p>
              <br/>
              <p>
                At the heart of it all, we are passionate about fashion - the new and the old - the unique and the special, to enable you to carve out your unique personal style one fashion find at a time.
              </p>
              <br/>
              <?php
if ($coupon !== null) {
        ?>
                  <p>
                      As a welcome treat, enjoy <?php echo esc_textarea($coupon->get_amount('edit')) ?>% off your first purchase with code "<?php echo esc_textarea($coupon->get_code()); ?>" at check out.
                  </p>
                  <?php
}
    ?>

              <a href="<?php echo FRONTEND_URL; ?>/catalog">
                Click here to discover our newest arrivals.
              </a>
              <br/>
              <p>
                If at anytime you wish to stop receiving our newsletters, you can click to <a href="<?php echo FRONTEND_URL; ?>/newsletter-unsubscribe?token=<?php echo esc_textarea(urlencode($email_token)) ?>">UNSUBSCRIBE HERE.</a>
              </p>
              <br/>
              <p>
                We look forward to a great style journey together.
              </p>
              <br/>
              <p>
                <div>Regards,</div>
                <div>The RIOT team.</div>
              </p>
            </body>
        </html>

        <?php

    return ob_get_clean();
}
