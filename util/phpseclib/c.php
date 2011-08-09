<?php
	include('Crypt/AES.php');
	
	$aes = new Crypt_AES();
	
	$aes->setKey('abcdefghijklmnop');
	
	$size = 10 * 1024;
	$plaintext = '';
	for ($i = 0; $i < $size; $i++) {
	    $plaintext.= 'a';
	}
	
	echo base64_encode($aes->encrypt($plaintext));
?>