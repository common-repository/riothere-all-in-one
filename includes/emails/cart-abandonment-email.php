<?php

function get_cart_abandonment_email($product_ids, $customer_name, $header, $footer)
{
    $imgsrc = plugin_dir_url(RIOTHERE_ALL_IN_ONE_VERSION_DIR_PATH) . 'public/assets/android-chrome-192x192.png';

    ob_start();

    ?>
        <!doctype html>
        <html lang="en">
            <head>
              <style type="text/css">
                .shopping-bag {
                  width: 90% !important;
                  margin: 0 auto !important;
                }

                @media only screen and (max-width: 640px) {
                  .shopping-bag {
                    width: 100% !important;
                  }

                  .button {
                    font-size: 10px !important;
                  }
                }
              </style>
            </head>
            <body>
              <div style="width: 100%; text-align: center; background: black">
                <img src='<?php echo esc_url($imgsrc) ?>' />
              </div>
              <br/>
              <p>
                Hi <?php echo esc_textarea($customer_name) ?>,
              </p>
              <?php echo esc_textarea($header) ?>
              <br/>
              <div class="shopping-bag" style="">
                <table style="width:100%; border-spacing:0 10px;">
                  <tr style="background:#f7f7f7; color:gray; font-size:14px; font-weight:600; height:30px;">
                    <td style="padding-left:15px;">Item</td>
                    <td style="padding-left:15px;">Size</td>
                    <td style="padding-left:15px;">Color</td>
                    <td style="padding-left:15px;">Price</td>
                  </tr>
                  <?php

    foreach ($product_ids as $product_id) {
        $current_product = Riothere_All_In_One_Admin::get_product_data($product_id);
        ?>
                      <tr>
                        <td style="padding-left:15px;">
                          <table>
                            <tr>
                              <td>
                                <div>
                                  <?php echo "<img src=' " . esc_url($current_product['image']) . "'/>"; ?>
                                </div>
                              </td>
                              <td>
                                <div style="margin-left:15px;">
                                  <div style="font-weight:600;"><?php echo esc_textarea($current_product['brand']); ?></div>
                                  <div><?php echo esc_textarea($current_product['sku']); ?></div>
                                  <div><?php echo esc_textarea($current_product['name']); ?></div>
                                </div>
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td style="padding-left:15px;"><?php echo esc_textarea($current_product['size']) ?></td>
                        <td style="padding-left:15px;"><?php echo esc_textarea($current_product['color']) ?></td>
                        <td style="padding-left:15px;"><?php echo "AED " . esc_textarea($current_product['price']) ?></td>
                      </tr>
                    <?php
}
    ?>
                </table>
                <table style="width: 100%">
                  <tr>
                    <td style="white-space: nowrap;"><a href="<?php echo FRONTEND_URL . "/catalog" ?>" class="button" style="background-color: #000; border: none; outline: none; color: #fff; min-width: 180px; padding: 12.5px; font-size: 11px; cursor: pointer; text-decoration: none;">CONTINUE SHOPPING</a></td>
                    <td style="float: right; white-space: nowrap;"><a href="<?php echo FRONTEND_URL . "/shopping-cart" ?>" class="button" style="background-color: #000; border: none; outline: none; color: #fff; min-width: 180px; padding: 12.5px; font-size: 11px; cursor: pointer; text-decoration: none;">COMPLETE YOUR PURCHASE</a></td>
                  </tr>
                </table>
              </div>
              <br/>
              <br/>
              <br/>
              <?php echo esc_textarea($footer) ?>
              <p>
                <div>Thank you,</div>
                <div>The RIOT team.</div>
              </p>
            </body>
        </html>

        <?php

    return ob_get_clean();
}
