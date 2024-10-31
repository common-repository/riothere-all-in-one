<?php

function get_try_and_buy_admin_email($user_first_name, $user_last_name, $order_id, $delivery_time_day, $delivery_time_duration)
{

    ob_start();

    ?>
<!doctype html>
<html lang="en">
	<body>
	  <p>
	  You’ve received the following order from <?php echo esc_textarea($user_first_name) ?>  <?php echo esc_textarea($user_last_name) ?>:
	  </p>
	  <p>
	  [Order # <?php echo esc_textarea($order_id) ?>]
	  </p>
	   <p>

	   The Try & Buy order should be delivered on <?php echo esc_textarea($delivery_time_day) ?> at <?php echo esc_textarea($delivery_time_duration) ?>
	  </p>

	</body>
</html>

	<?php
return ob_get_clean();
}