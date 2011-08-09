<?php

// the Dibasic index.php file

$dir = dirname(__FILE__);
require("$dir/loadConfig.php");
require(DIBASIC_ROOT."/auth/verify_user.php");
require(DIBASIC_ROOT."/Dibasic.php");

$file = '';

// load the requested page here
if (isset($_GET['page']) && $_GET['page']) {
	$page = (int) $_GET['page']; // the (int) is important for security
	$userId = (int) $user['id'];
	
	$q = "SELECT * FROM `".DIBASIC_DB_PREFIX."pages` WHERE id = '$page' LIMIT 1";
	$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
	$page_info = mysql_fetch_assoc($qr);
	
	if (!$page_info) {
		header('HTTP/1.0 404 Not Found');
		?>
<h1>Not Found</h1>
<p>The page you requested (id = <?=$page?>) could not be found. Make sure the page exists in the pages editor.</p>
<?php
		die();
	}
	
	// check if user has permission to access the page
	$permissionsTable = DIBASIC_DB_PREFIX.'page_to_user';
	$q = "SELECT * FROM $permissionsTable WHERE user = $userId AND page = $page LIMIT 1";
	$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
	
	if (!mysql_num_rows($qr)) {
		if (!$page_info['file_for_permissionless']) {
			header('HTTP/1.0 403 Permission Denied');
			?>
<h1>Permission Denied</h1>
<p>You do not have permission to access the page you requested. Please contact your website administrator.</p>
<?php
			die();
		}
		else {
			// there’s an alternate file
			$file = $page_info['file_for_permissionless'];
		}
	}
	else {
		// user has permission
		$file = $page_info['file'];
	}
}
else {
	// load the topmost page
	$q = "SELECT id FROM `".DIBASIC_DB_PREFIX."pages` ORDER BY `order` ASC LIMIT 1";
	$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
	header('Location: '.DIBASIC_SUPERURL.'?page='.mysql_result($qr, 0));
	die();
}

$found = false;
if ($file) {
	$path_candidates = array(DIBASIC_SUPERROOT."/pages/$file", DIBASIC_ROOT."/pages/$file");
	foreach ($path_candidates as $path) {
		if (file_exists($path)) {
			require($path);
			$found = true;
			break;
		}
	}
}

if (!$found) {
	header('HTTP/1.0 404 Not Found');
	?>
<h1>Not Found</h1>
<p>The page you requested could not be found, because the file “<?=$file?>” could not be found. Please check the filename in the pages editor.</p>
<?php
}

