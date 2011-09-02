<?php

// setup the database tables used by Dibasic

require('Dibasic.config.php');
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
	
	$q = "INSERT INTO $permissionsTable (user, page) VALUES (1, 1), (1, 2)";
	mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
}


// create the initial pages

$pages = array(
	array('title'=>'Accounts', 'file'=>'_users.php', 'file_for_permissionless'=>'_ownUser.php', 'order'=>1),
	array('title'=>'Pages', 'file'=>'_pages.php', 'file_for_permissionless'=>'', 'order'=>2)
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

file_put_contents(DIBASIC_SUPERROOT.'/index.php', '<?php require("Dibasic/index.php"); ?>');
mkdir(DIBASIC_SUPERROOT.'/pages');
mkdir(DIBASIC_SUPERROOT.'/plugins');
mkdir(DIBASIC_SUPERROOT.'/inputs');
mkdir(DIBASIC_SUPERROOT.'/uploaded');


?>

<h1>Dibasic: Setup finished.</h1>
<p>Please login through the following url: <a href="<?=DIBASIC_SUPERURL?>">Login</a></p>
<dl>
	<dt>Username:</dt>
	<dd>admin</dd>
	<dt>Password</dt>
	<dd>admin</dd>
</dl>