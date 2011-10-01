<?php

// _ownUser.php
// this page is for users who can’t view the _users.php page
// WARNING: DO NOT modify this page unless you are really sure about what you are doing.
// Dibasic depends on this file

$table = new Dibasic(DIBASIC_DB_PREFIX.'users');

$table->permissions['select'] = array($user['id']); // implied for update as well
$table->permissions['create'] = false;
$table->permissions['alter'] = false;
$table->permissions['insert'] = false;
$table->permissions['delete'] = false;

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

$d->where('Only me', 'id = "%d"', $user['id']);


$table->mainStructure = <<<TEMPLATE

{{= Dibasic.DPNavigation.widget() }}

<div>
{{= Dibasic.dataRenderer.widget() }}
</div>

TEMPLATE;

$table->run();