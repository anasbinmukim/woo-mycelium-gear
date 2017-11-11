<?php
add_action('admin_menu', 'register_client_email_plugin_page');

function register_client_email_plugin_page() {
	add_submenu_page( 'edit.php?post_type=page', 'Mycelium', 'Mycelium', 'manage_options', 'mycelium-settings', 'mycelium_settings_callback');
}

function mycelium_settings_callback() {
	echo '<div class="wrap">';
		echo '<h2>Mycelium Testing</h2>';

		$gateway_id = 'b629c1ca7ca18492b6a3b50390fe4c6a334eefdde78dfc5f7c70c4a83cf47f7b';
		$gateway_secret = '5fqf7hSiqdYAqbcxExBD6VagJc8ZjjASL1Aa4b83nsUDY9PhpcaXCDFMW3hgRvqb';

		$payment_id = '423423';
		$callback_data = 'heloback';

		$amount = 500.00;
		$keychain_id = '5';

		$geary = new WC_Mycelium_Gear_API($gateway_id, $gateway_secret);
		$order = $geary->create_order($amount, $keychain_id, $callback_data);
		//$order = $geary->check_order($payment_id);

		if ($order->payment_id) {
		    $url = "https://gateway.gear.mycelium.com/pay/{$order->payment_id}";

		    // Show a payment gateway URL
		    echo '<a href="' . $url . '" target="_blank">Pay</a>';
		}

		?>

		<?php
	echo '</div>';

}
