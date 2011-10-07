<?php

$q = "SHOW TABLES LIKE '".DIBASIC_DB_PREFIX."%'";
$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);

$required_tables = array(
	DIBASIC_DB_PREFIX.'users',
	DIBASIC_DB_PREFIX.'pages',
	DIBASIC_DB_PREFIX.'page_to_user',
	DIBASIC_DB_PREFIX.'actions',
	DIBASIC_DB_PREFIX.'log'
);
$existing_tables = array();
while ($row = mysql_fetch_array($qr)) {
	$existing_tables[] = $row;
	$i = array_search($row[0], $required_tables);
	if ($i !== false) {
		array_splice($required_tables, $i, 1);
	}
}

if (count($required_tables)) {
	// needs setup, redirect
	echo 'Please setup Dibasic first: <a href="'.DIBASIC_URL.'/setup.php">Setup</a>';
	die();
}
