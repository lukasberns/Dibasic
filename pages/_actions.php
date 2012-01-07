<?php

// _actions.php
// this page displays a log of all actions done on Dibasic
// WARNING: DO NOT modify this page unless you are really sure about what you are doing.
// Dibasic depends on this file

$table = new Dibasic(DIBASIC_DB_PREFIX.'actions');

$table->addPlugin('ActionDetails');

$table->c('name', 'Text', 'Name');
$table->c('author_id', 'ForeignKey', 'Author', array(
	'rules' => 'required',
	'table' => DIBASIC_DB_PREFIX.'users',
	'column' => array('realname', 'username'),
	'order' => array('realname', 'username'),
));
$table->c('page_id', 'ForeignKey', 'Page', array(
	'rules' => 'required',
	'table' => DIBASIC_DB_PREFIX.'pages',
	'column' => 'title',
	'order' => 'order'
));
$table->c('timestamp', 'Timestamp', 'Timestamp', array(
	'setOnUpdate' => false
));

$d = $table->setDataRenderer('DataTable', array(
	'controls' => array('DPActionDetails')
));

$table->run();

