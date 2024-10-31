<?php
function get_refund_rejected_email()
{
    ob_start();

    ?>
        <!doctype html>
        <html lang="en">
            <body>
              <p>
                We’re sorry to hear you were not happy with your item/s.​
              </p>
              <p>
                Unfortunately, you are not eligible for a refund on this item/s as they have surpassed the 3-day grace period for returns or were items that we originally marked to be on “SALE”.​
              </p>
              <p>
                ​You can <a href="https://riothere.com/returns-policy">click here</a> to learn more about our Refund Policy.
              </p>
              <p>
                We are truly sorry that we could not accommodate your request.
              </p>
              <p>
                We hope to service you and help you find some unique fashion finds in the future.
              </p>
              <br/>
              <p>
                <div>The RIOT team.</div>
              </p>
            </body>
        </html>

        <?php

    return ob_get_contents();
}
