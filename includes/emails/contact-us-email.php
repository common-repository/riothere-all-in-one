<?php

function get_contact_us_email($first_name, $last_name, $email, $country_code, $phone_number, $inquiry_type, $body)
{

    ob_start();

    ?>
<!doctype html>
<html lang="en">
	<body>
	  <p>
		First name: <?php echo esc_textarea($first_name) ?>
	  </p>
	  <p>
		Last name: <?php echo esc_textarea($last_name) ?>
	  </p>
	  <p>
		Email: <?php echo esc_textarea($email) ?>
	  </p>
	  <p>
		Phone number: <?php echo esc_textarea($country_code . $phone_number) ?>
	  </p>
	  <p>
		Inquiry type: <?php echo esc_textarea($inquiry_type) ?>
	  </p>

	  <p>
        Body: <?php echo esc_textarea($body) ?>
	</p>
	</body>
</html>

	<?php
return ob_get_clean();
}