<?php

// loadConfig.php
// this file loads the config file
// and redirects to the /setup.php file if neccessary

$config_file = dirname(__FILE__).'/Dibasic.config.php';

if (!file_exists($config_file)):
	// this file is required, display an error page with instructions
	// TODO: make this easy by making this through html
?>
<h1>Configuration File Missing</h1>
<p>The configuration file (<code>Dibasic.config.php</code>) is missing.<br />Please rename the <code>Dibasic.config-sample.php</code> file to <code>Dibasic.config.php</code> and edit its contents.</p>
<?php
	die();
endif;

require($config_file);

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