<style>
textarea{
	width:700px;
	height: 100px;
}
</style>

<form method="post" action="<?=$_SERVER['PHP_SELF'];?>">
Text to be processed: <br>
<textarea name=input></textarea><br>
<input type=submit name=action value=encrypt></input>
<!--<input type=submit name=action value=decrypt></input>-->
</form>
<?php

if (!isset($_POST["input"]))
{
	die();
}
$input = $_POST["input"];

$privateKey = "file://../scripts/key.pem";

$publicKey = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQD30bFioIM7KAPK2oxDroOGmoJX
PxCBxBsZEi/0bsOlH5fba8aGuKxv6gq8QuTwcFIY1Ib06bEXqf7uFmNmMCdZRipF
Dq6FffhTmPv4KFpv5bo1Fr1sPdlvRamLKTfwLLc2K6AjTJUfQlQAdTFWzbYpPCfR
n82EzpZOdD9a+S4tcwIDAQAB
-----END PUBLIC KEY-----";

$encryptKey = '';
$decryptKey = '';

if ($_POST["action"] == encrypt)
{
	echo '<p>Input = <br><textarea>' . $input . '</textarea><p>';
	
	$ivlen = openssl_cipher_iv_length("AES-256-CBC");					// generate key length
	$aesKey = openssl_random_pseudo_bytes($ivlen, $boolSecure);			// generate AES key
	echo '<p>Raw AES Key = '.base64_encode($aesKey).'<p>';
	echo '<p>Secure = '.$boolSecure.'<p>';
	
	$encryptData = openssl_encrypt($input, "AES-256-CBC", $aesKey);		// encrypt payload with AES
	echo '<p>Encrypted Data (Base64) = <br><textarea>' . base64_encode($encryptData) . '</textarea><p>';
	
	if (!openssl_public_encrypt($aesKey, $encryptKey, $publicKey))		// encrypt AES key with RSA public key
		die('Failed to encrypt data');
	echo '<p>Encrypted AES Key = '.base64_encode($encryptKey).'<p>';
	
	if (!openssl_private_decrypt($encryptKey, $decryptKey, $privateKey))	// decrypt AES key with RSA private key
		die('Failed to decrypt data');
	echo '<p>Decrypted AES Key = '.base64_encode($decryptKey).'<p>';
	
	$decryptData = openssl_decrypt($encryptData, "AES-256-CBC", $decryptKey);	// decrypt payload with the now decrypted AES key
	if (!decryptData)
		die('failed to decrypt data');
	echo '<p>Decrypted Data = <br><textarea>' . $decryptData . '</textarea><p>';
}
else
{
	if (!openssl_private_decrypt(base64_decode($input), $decrypted, $privateKey))  die('Failed to decrypt data');

	echo '<p>Decrypted Data = <br><textarea>' . $decrypted . '</textarea><p>';
}
