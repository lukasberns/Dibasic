<?php

// loadConfig.php
// this file loads the config file
// and redirects to the /setup.php file if neccessary

define('DIBASIC_ROOT', dirname(__FILE__));
define('DIBASIC_SUPERROOT', dirname(DIBASIC_ROOT));

$config_dir = DIBASIC_ROOT;
$config_filename = '/Dibasic.config.php';
while (strlen($config_dir) > 1 and $not_found = !file_exists($config_file = $config_dir.$config_filename)) {
	$config_dir = dirname($config_dir);
}

if ($not_found):
	// this file is required, display an error page with instructions
	// TODO: make this easy by making this through html
?>
<h1>Configuration File Missing</h1>
<p>The configuration file (<code>Dibasic.config.php</code>) is missing.<br />Please rename the <code>Dibasic.config-sample.php</code> file to <code>Dibasic.config.php</code> and edit its contents. The configuration file be in or in any directory above the Dibasic/ directory.</p>
<?php
	die();
endif;

require('util/fix_mysql.php');
require($config_file);

if (!defined('COOKIE_NAME')) {
	define('COOKIE_NAME', 'dibasic_session');
}
