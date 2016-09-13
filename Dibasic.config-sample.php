<?php

// config file for Dibasic
// rename this file to Dibasic.config.php and edit its contents



// connect to db server and select the db
mysql_connect('DATABASE_HOST', 'DATABASE_USER', 'DATABASE_PASSWORD');
mysql_select_db('DATABASE_NAME');
mysql_query('SET NAMES utf8');

// set mb language to unicode
mb_language("uni");
mb_internal_encoding("utf-8");
mb_http_input("auto");
mb_http_output("utf-8");

// a prefix that will be added to the internal tables dibasic uses
define('DIBASIC_DB_PREFIX', 'Dibasic_');


// don't change the following (filesystem paths don't include the trailing slash, urls do)
define('DOCUMENT_ROOT', preg_replace('|'.preg_quote($_SERVER['SCRIPT_NAME']).'$|', '', $_SERVER['SCRIPT_FILENAME']));
define('DIBASIC_URL', substr(DIBASIC_ROOT, strlen(DOCUMENT_ROOT)).'/');
define('DIBASIC_SUPERURL', substr(DIBASIC_SUPERROOT, strlen(DOCUMENT_ROOT)).'/');
define('COOKIE_DIR', DIBASIC_SUPERURL); // where to save the cookies into
define('COOKIE_NAME', 'dibasic_session'); // name of cookie used by dibasic. change if it clashes with another cookie
