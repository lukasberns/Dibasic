<?php

// checks if user is logged in and redirects if neccessary to the login page
// this has to be run before anything else, right after the db connection is made

$session_id = isset($_COOKIE[COOKIE_NAME]) ? $_COOKIE[COOKIE_NAME] : '';

if ($session_id) {
	$q = "SELECT *, session_login_expire > NOW() AS valid_session_id "
		."FROM `".DIBASIC_DB_PREFIX."users` "
		."WHERE session_id = '".mysql_real_escape_string($session_id)."' "
		."LIMIT 1";
	$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);

	$user = mysql_fetch_assoc($qr);
}
else {
	$user = false;
}

if (!$user or !$user['valid_session_id']) {
	// session_id expired, delete the cookie and redirect to login page
	setcookie(COOKIE_NAME, '', time()-3600, COOKIE_DIR);
	
	$q = "UPDATE `".DIBASIC_DB_PREFIX."users` "
		."SET session_id='', session_login_expire='', session_ip='' "
		."WHERE id='".mysql_real_escape_string($user['id'])."' "
		."LIMIT 1";
	mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
	
	header("Location: ".DIBASIC_URL."/auth/login.php?goto=".rawurlencode($_SERVER['REQUEST_URI']));
	die();
}
else {
	setcookie(COOKIE_NAME, $session_id, time()+5*3600, COOKIE_DIR);
	
	$q = "UPDATE `".DIBASIC_DB_PREFIX."users` "
		."SET session_login_expire = NOW() + INTERVAL 5 HOUR "
		."WHERE id='".mysql_real_escape_string($user['id'])."' "
		."LIMIT 1";
	mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
}
