<?php

// _actions.php
// this page displays a log of all actions done on Dibasic
// WARNING: DO NOT modify this page unless you are really sure about what you are doing.
// Dibasic depends on this file

$table = new Dibasic(DIBASIC_DB_PREFIX.'actions');

$table->c('name', 'Text', 'Name');
$table->c('author', 'Text', 'Author', array(
	'table'=>DIBASIC_DB_PREFIX.'users',
	'column'=>array('username', 'realname'),
	'required'=>'required'
));
$table->c('timestamp', 'Timestamp', 'Timestamp', array(
	'setOnUpdate' => false
));

$table->run();