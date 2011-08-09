<?php

// logout.php
// logs the user out

require('../Dibasic.config.php');

$session_id = isset($_COOKIE['session_id']) ? $_COOKIE['session_id'] : '';

if ($session_id) {
	$q = "UPDATE `".DIBASIC_DB_PREFIX."users` "
		."SET session_id='', session_login_expire='', login_challenge='' "
		."WHERE session_id = '".mysql_real_escape_string($session_id)."' "
		."LIMIT 1";
	mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
}

header('Location: '.DIBASIC_SUPERURL);
