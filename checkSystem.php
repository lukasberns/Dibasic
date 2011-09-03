<?php

$q = "SHOW TABLES LIKE '".DIBASIC_DB_PREFIX."%'";
$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);

$existing_tables = array();
while ($row = mysql_fetch_assoc($qr)) {
	$existing_tables[] = $row;
}

if (!count($existing_tables)) {
	// needs setup, redirect
	echo 'Please setup Dibasic first: <a href="'.DIBASIC_URL.'/setup.php">Setup</a>';
	die();
}
