<?php

// loginn.php
// handels the login authentication

require('../loadConfig.php');

function r_POST($key) {
	global $errors;
	if (!isset($_POST[$key])) {
		die('Required input: “'.htmlspecialchars($key).'” was missing.');
	}
	return $_POST[$key];
}

$username = r_POST('username');
$action = r_POST('action');

$q = "SELECT *, session_login_expire > NOW() AS valid_challenge FROM `".DIBASIC_DB_PREFIX."users` WHERE username='".mysql_real_escape_string($username)."'";
$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
$user = mysql_fetch_assoc($qr);

if ($action == 'spice') {
	// lookup the user in the db
	// if it exists, return the salt
	// if not, we return a dummy salt, otherwise we’d give a malcious user information about whether an user exists
	
	$challenge = md5(uniqid(rand(), true));
	if ($user) {
		$user_exists = true;
		$salt1 = $user['salt1'];
		$salt2 = $user['salt2'];
		
		// associate the $challenge with the user along with a expiration date
		// (expiration date 1 min into future)
		$q = "UPDATE `".DIBASIC_DB_PREFIX."users` SET login_challenge = '$challenge', session_login_expire = NOW() + INTERVAL 1 MINUTE, session_ip = '$_SERVER[REMOTE_ADDR]' WHERE username = '".mysql_real_escape_string($username)."'";
		mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
	}
	else {
		$salt1 = md5(uniqid(rand(), true));
		$salt2 = md5(uniqid(rand(), true));
	}
	
	
	echo json_encode(array(
		'challenge' => $challenge,
		's1' => $salt1,
		's2' => $salt2
	));
	die();
}

function clearChallenge($id) {
	$q = "UPDATE `".DIBASIC_DB_PREFIX."users` SET session_id='', session_login_expire='', login_challenge='' WHERE id='".(int)$id."' LIMIT 1";
	mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
}

if ($action == 'login') {
	$challenge = r_POST('challenge');
	$resp = r_POST('resp');
	$h2 = r_POST('h2');
	
	// look if challenge is associated with user and has not expired.
	// if ok, expire that challenge to prevent replay attacks
	// a friendly user won’t be able to reuse a challenge anyway
	
	// also check if the ip has been the same since the last login
	
	
	if ($challenge != $user['login_challenge']
		or !$user['valid_challenge']
		or $_SERVER['REMOTE_ADDR'] != $user['session_ip']) {
		
		clearChallenge($user['id']);
		die('0');
	}
	
	// match with db
	
	if ($resp == md5($challenge.$user['hash1']) and md5($user['salt3'].$h2) == $user['hash3']) {
		// valid login
		// create the session id
		// invalidate the login challenge
		
		$session_id = md5(uniqid(rand(), true));
		
		$q = "UPDATE `".DIBASIC_DB_PREFIX."users` SET session_id='$session_id', session_login_expire = NOW() + INTERVAL 2 HOUR, login_challenge='' WHERE id='".(int)$user['id']."' LIMIT 1";
		mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
		
		// login will be valid in the directory the Dibasic folder is in
		setcookie('session_id', $session_id, time()+5*3600, COOKIE_DIR);
		
		die('1');
	}
	
	clearChallenge($user['id']);
	die('0');
}