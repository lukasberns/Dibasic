<?php

// setup the database tables used by Dibasic

require('loadConfig.php');
require('Dibasic.php');

function table_exists($name) {
	$q = "SHOW TABLES LIKE '$name'";
	$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
	return mysql_num_rows($qr) > 0;
}

if (!table_exists(DIBASIC_DB_PREFIX.'pages')) {
	// this creates the table for us
	$_GET['action'] = 'DPCreateForm';
	$_POST['key'] = 'id';
	header('Location: setup.php'); // reload browser as DPCreateForm will die()
	
	require(DIBASIC_ROOT.'/pages/_pages.php');
}

if (!table_exists(DIBASIC_DB_PREFIX.'users')) {
	// this creates the tables for us
	$_GET['action'] = 'DPCreateForm';
	$_POST['key'] = 'id';
	header('Location: setup.php'); // reload browser as DPCreateForm will die()
	
	require(DIBASIC_ROOT.'/pages/_users.php');
}

if (!table_exists(DIBASIC_DB_PREFIX.'actions')) {
	// this creates the tables for us
	$_GET['action'] = 'DPCreateForm';
	$_POST['key'] = 'id';
	header('Location: setup.php'); // reload browser as DPCreateForm will die()
	
	require(DIBASIC_ROOT.'/pages/_actions.php');
}

if (!table_exists(DIBASIC_DB_PREFIX.'log')) {
	// this creates the tables for us
	$_GET['action'] = 'DPCreateForm';
	$_POST['key'] = 'id';
	header('Location: setup.php'); // reload browser as DPCreateForm will die()
	
	require(DIBASIC_ROOT.'/pages/_log.php');
}

$q = "SELECT COUNT(*) FROM `".DIBASIC_DB_PREFIX."users`";
$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
if (mysql_result($qr, 0) == '0') {
	
	// create admin user
	
	$_GET['action'] = 'DPDBInterface';
	$salt1 = md5(uniqid(rand(), true));
	$salt2 = md5(uniqid(rand(), true));
	$salt3 = md5(uniqid(rand(), true));
	$hash1 = md5($salt1.'admin');
	$hash3 = md5($salt3.md5($salt2.'admin'));
	$_POST['insert'] = json_encode(array(
		'username' => 'admin',
		'hash1' => json_encode(array(
			'hash' => $hash1,
			'salt' => $salt1,
			'extraHash' => $hash3,
			'extraSalt1' => $salt2,
			'extraSalt2' => $salt3
		))
	));
	header('Location: setup.php'); // reload browser as DPCreateForm will die()
	
	require(DIBASIC_ROOT.'/pages/_users.php');
}

if (!table_exists(DIBASIC_DB_PREFIX.'page_to_user')) {
	// create the table that manages the privileges of the users to view the pages
	$permissionsTable = DIBASIC_DB_PREFIX.'page_to_user';
	$q = "CREATE TABLE `$permissionsTable` (
		`user` int(10) unsigned NOT NULL,
		`page` int(10) unsigned NOT NULL,
		PRIMARY KEY  (`user`, `page`)
	)";
	mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
	
	$q = "INSERT INTO $permissionsTable (user, page) VALUES (1, 1), (1, 2), (1, 3), (1, 4)";
	mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
}


// create the initial pages

$pages = array(
	array('title'=>'Accounts', 'file'=>'_users.php', 'file_for_permissionless'=>'_ownUser.php', 'order'=>1),
	array('title'=>'Pages', 'file'=>'_pages.php', 'file_for_permissionless'=>'', 'order'=>2),
	array('title'=>'Actions', 'file'=>'_actions.php', 'file_for_permissionless'=>'', 'order'=>3),
	array('title'=>'Log', 'file'=>'_log.php', 'file_for_permissionless'=>'', 'order'=>4)
);

foreach ($pages as $page) {
	$q = "SELECT * FROM `".DIBASIC_DB_PREFIX."pages` WHERE file='$page[file]' LIMIT 1";
	$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
	if (mysql_num_rows($qr) == 0) {
		
		$_GET['action'] = 'DPDBInterface';
		$_POST['insert'] = json_encode(array(
			'title' => $page['title'],
			'group' => 'Dibasic',
			'file' => $page['file'],
			'file_for_permissionless' => $page['file_for_permissionless'],
			'can_open_by_default' => 0,
			'order' => $page['order']
		));
		header('Location: setup.php'); // reload browser as DPCreateForm will die()

		require(DIBASIC_ROOT.'/pages/_pages.php');
	}
}

if (!file_exists(DIBASIC_SUPERROOT.'/index.php')) {
	file_put_contents(DIBASIC_SUPERROOT.'/index.php', '<?php require("Dibasic/index.php"); ?>');
}

$directories = array(
	DIBASIC_SUPERROOT.'/pages',
	DIBASIC_SUPERROOT.'/plugins',
	DIBASIC_SUPERROOT.'/inputs',
	DIBASIC_SUPERROOT.'/uploaded'
);
foreach ($directories as $dir) {
	if (!file_exists($dir)) {
		mkdir($dir);
	}
}
chmod(DIBASIC_SUPERROOT.'/uploaded', 0755);
if (!file_exists(DIBASIC_SUPERROOT.'/uploaded/.htaccess')) {
	file_put_contents(DIBASIC_SUPERROOT.'/uploaded/.htaccess', <<<HTACCESS
Options -Indexes
Options -ExecCGI 
AddHandler cgi-script .php .php3 .php4 .phtml .pl .py .jsp .asp .htm .shtml .sh .cgi 
HTACCESS
	);
}

?>

<h1>Dibasic: Setup finished.</h1>
<p>Please login through the following url: <a href="<?=DIBASIC_SUPERURL?>">Login</a></p>
<dl>
	<dt>Username:</dt>
	<dd>admin</dd>
	<dt>Password</dt>
	<dd>admin</dd>
</dl>