<?php

function get_try_and_buy_customer_email($delivery_time_day, $delivery_time_duration)
{

    ob_start();

    ?>
<!doctype html>
<html lang="en">
	<body>
	  <p>
		Thank you for shopping with RIOT
	  </p>
	   <p>

	   Your shipment will be with you on <?php echo esc_textarea($delivery_time_day) ?> at <?php echo esc_textarea($delivery_time_duration) ?>
	  </p>
	  <P>
		Complete your order by clicking here <a href="https://riothere.com/order">complete my order</a>
	  </p>

	</body>
</html>

	<?php
return ob_get_clean();
}