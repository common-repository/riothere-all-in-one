<?php

function riot_encryptString($string) {
	$ciphering = "AES-128-CTR";
	// Non-NULL Initialization Vector for encryption
	$encryption_iv = '1234567891011121';

	// Store the encryption key
	$encryption_key = "GeeksforGeeks";

	// Use openssl_encrypt() function to encrypt the data
	return openssl_encrypt($string, $ciphering,
		$encryption_key, 0, $encryption_iv);

}

function riot_decrypt_string($encrypted_string) {
	$ciphering = "AES-128-CTR";
	// Non-NULL Initialization Vector for encryption
	$encryption_iv = '1234567891011121';

	// Store the encryption key
	$encryption_key = "GeeksforGeeks";

	return openssl_decrypt ($encrypted_string, $ciphering,
		$encryption_key, 0, $encryption_iv);;
}
