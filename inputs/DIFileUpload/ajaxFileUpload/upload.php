<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// default settings
$max_upload = 5000000;
$tmp_dir = "/tmp";
require(dirname(__FILE__).'/read_settings.php');

if (isset($_GET['test'])) {
	echo "Test";
	exit();
}

$sessionid = $_GET['sid'];
$sessionid = preg_replace('/[^a-zA-Z0-9]/', '', $sessionid);

// don't change the next few lines unless you have a very good reason to.
$error_file     = "$tmp_dir/$sessionid"."_err";
$qstring_file   = "$tmp_dir/$sessionid"."_qstring";

function bye_bye($message, $internal) {
	global $error_file;
	
	// Try to open error file to output message too
	$fh = fopen($error_file, 'w');
	header('HTTP/1.0 500 Internal Server Error');
	if ($fh !== false) {
		// write message to file, so can be read from fileprogress.php
		fwrite($fh, date('c')."\n");
		fwrite($fh, $message."\n");
		fwrite($fh, $internal."\n");
		fclose($fh);
		
		echo $message;
	} else {
		// can't write error file. output alert directly to client
		echo "Encountered error: $message. Also unable to write to error file.";
	}
	exit;
}

// bye_bye("Test error", "Internal message");

$q = array();
$i = 0;
foreach ($_FILES as $nam => $fileInfo) {
	// need to move because the default is removed when the file is deleted
	$newtmp = tempnam($tmp_dir, "AFU");
	$moved = move_uploaded_file($fileInfo['tmp_name'], $newtmp);
	if ($moved === false) {
		bye_bye("Error moving uploaded file to tmp directory", $fileInfo['tmp_name']." -> ".$newtmp);
	}
	$fileInfo['tmp_name'] = $newtmp;
	foreach ($fileInfo as $key => $value) {
		$q['file'][$key][$i] = $value;
	}
	$i++;
}

$writtenBytes = file_put_contents($qstring_file, http_build_query($q));
if ($writtenBytes === false) {
	bye_bye("Could not open qstring file for editing.", $qstring_file);
}

echo 1; // success
