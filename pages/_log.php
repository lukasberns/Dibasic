<?php

// _log.php
// this page displays a log of all values changed on Dibasic
// WARNING: DO NOT modify this page unless you are really sure about what you are doing.
// Dibasic depends on this file

$table = new Dibasic(DIBASIC_DB_PREFIX.'log');

$table->c('action_id', 'Text', 'Action');
$table->c('table', 'Text', 'Table');
$table->c('table_id', 'Text', 'id');
$table->c('key', 'Text', 'Key');
$table->c('value', 'Text', 'Value');

$table->run();