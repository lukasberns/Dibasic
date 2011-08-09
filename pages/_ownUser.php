<?php

// _ownUser.php
// this page is for users who can’t view the _users.php page
// WARNING: DO NOT modify this page unless you are really sure about what you are doing.
// Dibasic depends on this file

$table = new Dibasic(DIBASIC_DB_PREFIX.'users');

$c_user = $table->c('username', 'UniqueText', 'Username', array(
	'rules' => array(
		'minlength' => 5
	)));
$c_user->dataType = 'char(32)';
$c_user->mysql_extra = 'UNIQUE NOT NULL';

$table->c('realname', 'Ignore');

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



$d = $table->setDataRenderer('DataTemplate');
$d->options['template'] = <<<TEMPLATE

[[

<div style="padding: 1em">

<p style="font-size: 1.2em; margin: 0 0 1em">
Username: {{=row.username}}
</p>

<p>
{{=Dibasic.DPUpdateForm.widget(row.id)}}
</p>

</div>

]]

TEMPLATE;

$d->where('id=', $user['id']);


$table->mainStructure = <<<TEMPLATE

{{= Dibasic.DPNavigation.widget() }}

<div>
{{= Dibasic.dataRenderer.widget() }}
</div>

TEMPLATE;

function deny($event) {
	die("You do not have permission to create or delete users.");
}

function assertUser($id) {
	global $user;
	if ($user['id'] != $id) {
		die("You do not have permission to view or edit other users.");
	}
}

function onlyAllowUser($event) {
	$updateUserData = $event->getInfo();
	assertUser($updateUserData['id']);
}

function onlyAllowUser_array($event) {
	$data = $event->getInfo();
	foreach ($data as $id => $userData) {
		assertUser($id);
	}
}

EventCenter::sharedCenter()->addEventListener('db.willSendData', 'onlyAllowUser_array');
EventCenter::sharedCenter()->addEventListener('db.insert', 'deny');
EventCenter::sharedCenter()->addEventListener('db.update', 'onlyAllowUser');
EventCenter::sharedCenter()->addEventListener('db.delete', 'deny');

$table->run();