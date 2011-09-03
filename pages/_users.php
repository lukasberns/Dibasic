<?php

// _users.php
// this page manages the users
// WARNING: DO NOT modify this page unless you are really sure about what you are doing.
// Dibasic depends on this file

$table = new Dibasic(DIBASIC_DB_PREFIX.'users');
$userPermissionsManager = $table->addPlugin('ManageUserPermissions');


$c_user = $table->c('username', 'UniqueText', 'Username', array(
	'rules' => array(
		'minlength' => 5
	)));
$c_user->dataType = 'char(32)';
$c_user->mysql_extra = 'UNIQUE NOT NULL';

$table->c('realname', 'Text', 'Real name');

$userPermissionsManager->insertHere();


$c_hash1 = $table->c('hash1', 'SecureText', 'Password', array( // md5(salt1.$password)
	'saltColumnName' => 'salt1',
	'extraSaltColumnName1' => 'salt2',
	'extraSaltColumnName2' => 'salt3',
	'extraHashColumnName' => 'hash3' // md5(salt3.md5(salt2.md5(salt1.$password)))
));

$table->c('session_id', 'Ignore', '', array('dataType' => 'varchar(32)'));
$table->c('session_ip', 'Ignore', '', array('dataType' => 'varchar(15)'));
$table->c('session_login_expire', 'Ignore', '', array('dataType' => 'datetime')); // user for both session and login expiration
$table->c('login_challenge', 'Ignore', '', array('dataType' => 'varchar(32)'));



$d = $table->setDataRenderer('DataTable', array(
	'columns' => array('username', 'realname')
));
$d->order('', 'username');

function preventOwnAccountFromDeletion($event) {
	$row = $event->getInfo();
	if (!isset($_COOKIE['session_id'])) {
		return;
	}
	if ($row['session_id'] == $_COOKIE['session_id']) {
		// current account, die() to prevent deletion
		die("You can’t delete your own account!");
	}
}

EventCenter::sharedCenter()->addEventListener('db.delete', 'preventOwnAccountFromDeletion');

$table->run();