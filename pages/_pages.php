<?php

// _pages.php
// this page manages the Dibasic pages
// WARNING: DO NOT modify this page unless you are really sure about what you are doing.
// Dibasic depends on this file

$table = new Dibasic(DIBASIC_DB_PREFIX.'pages');

$table->c('title', 'Text', 'Title', array('rules'=>'required'));
$table->c('group', 'Text', 'Group Name');
$table->c('file', 'UniqueText', 'Filename');
$table->c('file_for_permissionless', 'Text', 'Filename for users without permission');
$table->c('can_open_by_default', 'Checkbox', 'Users can open by default');
$table->c('order', 'Reorder', '');

$d = $table->setDataRenderer('DataTable', array(
	'columns' => array('title', 'group', 'file', 'file_for_permissionless', 'can_open_by_default', 'order')
));
$d->order('', 'order');

Dibasic::import(<<<EOS
javascript:
setTimeout(function() {
	Dibasic.DPDBInterface.observe(function(data) {
		Dibasic.DPNavigation.refresh();
	});
}, 1000);
EOS
);

function createPagePermissions($event) {
	$data = $event->getInfo();
	$pageId = key($data);
	
	if ($data[$pageId]['can_open_by_default']) {
		// for new pages with can_open_by_default == true we’ll kindly add the necessary permission for all users
		
		$usersTable = DIBASIC_DB_PREFIX.'users';
		$q = "SELECT id FROM $usersTable";
		$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$permissionsTable = DIBASIC_DB_PREFIX.'page_to_user';
		while ($user = mysql_fetch_assoc($qr)) {
			$userId = (int) $user['id'];
			$q = "INSERT INTO $permissionsTable (page, user) VALUES ($pageId, $userId)";
			mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
		}
	}
}

function deletePagePermissions($event) {
	$data = $event->getInfo();
	$pageId = $data['id'];
	
	$permissionsTable = DIBASIC_DB_PREFIX.'page_to_user';
	$q = "DELETE FROM $permissionsTable WHERE page = $pageId";
	mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
}

EventCenter::sharedCenter()->addEventListener('db.inserted', 'createPagePermissions');
EventCenter::sharedCenter()->addEventListener('db.delete', 'deletePagePermissions');

$table->run();