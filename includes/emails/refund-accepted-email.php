<?php
function get_refund_accepted_email()
{
    ob_start();

    ?>
        <!doctype html>
        <html lang="en">
            <body>
              <p>
                We’re sorry to hear you are not happy with your item/s.
              </p>
              <p>
                You are still eligible for the return as you are within the 3-day grace period.
              </p>
              <p>
                ​We will pick up your item/s within the next 48hrs.  Kindly,  put the item/s in their original packaging to ensure no damage is done. Please be sure to include all enclosed materials including invoices, authenticity cards, and dust bags.​
              </p>
              <p>
                ​Kindly allow 7-10 business days to process your refund. Refunds will be done only through the Original Mode of Payment. ​
              </p>
              <p>
                ​You will receive a notification email once the refund has been made.  ​
              </p>
              <p>
                For more information, on our Returns Policy please <a href="https://riothere.com/returns-policy">click here</a>.
              </p>
              <p>
                In the meantime, you can check some of the <a href="https://riothere.com/catalog">latest arrivals</a> which we thought you may like. ​
              </p>
              <br/>
              <p>
                <div>Have a lovely day,</div>
                <div>The RIOT team.</div>
              </p>
            </body>
        </html>

        <?php

    return ob_get_contents();
}
