<?php
# PHP File Uploader with progress bar Version 2.0
# Based on progress.php, a contrib to Megaupload, by Mike Hodgson.
# Changed for use with AJAX by Tomas Larsson
# http://tomas.epineer.se/

# Licence:
# The contents of this file are subject to the Mozilla Public
# License Version 1.1 (the "License"); you may not use this file
# except in compliance with the License. You may obtain a copy of
# the License at http://www.mozilla.org/MPL/
# 
# Software distributed under this License is distributed on an "AS
# IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or
# implied. See the License for the specific language governing
# rights and limitations under the License.
#
# Changed a bit to output data in JSON (Lukas Berns http://rand1-365.blogspot.com)

require_once("read_settings.php");

// disable any kind of caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

function bye_bye($msg) {
	header("HTTP/1.1 500 Internal Server Error");
	echo "$msg";
	exit;
}

if(!isset($_REQUEST['sid'])) {
	bye_bye("No sid received.");	
}
$sessionid = $_REQUEST['sid'];

$info_file = "$tmp_dir/$sessionid"."_flength";
$data_file = "$tmp_dir/$sessionid"."_postdata";
$error_file = "$tmp_dir/$sessionid"."_err";
$signal_file = "$tmp_dir/$sessionid"."_signal";
$qstring_file = "$tmp_dir/$sessionid"."_qstring";

# Send error code if error file exists
if(file_exists($error_file)) {
	$mes = file_get_contents($error_file);
	bye_bye($mes);
}

$current_size = $total_size = $percent_done = 0;
$started = TRUE;
if ($fp = @fopen($info_file,"r")) {
		$fd = fread($fp,1000);
		fclose($fp);
		$total_size = $fd;
} else {
	$started = FALSE;
}

if ($started) {
	$current_size = @filesize($data_file);
	$percent_done = floor(($current_size / $total_size) * 100);
	
	if ($percent_done == 100) {
		// 100 stops the upload, but we have to wait until the _qstring and the _signal files are created
		if (!(file_exists($signal_file) and file_exists($qstring_file))) {
			$percent_done = 99;
		}
	}
}

/*****************  Upload Speed   ***********************/

function parseSize($size) {
	$i=0;
	$iec = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	while (($size/1024)>1) {
		$size=$size/1024;
		$i++;
	}
	return(round($size,1).' '.$iec[$i]);
}

$info_file_time = 0;
if(file_exists($info_file))
	$info_file_time = filectime($info_file);

$data_file_time = 0;
if(file_exists($data_file))
	$data_file_time = filectime($data_file);

$upload_time = $data_file_time - $info_file_time;
$speed = parseSize($upload_time ? $current_size / $upload_time : ' - B') . '/s';

$remaining_time = ($current_size and $upload_time) ? ($total_size - $current_size) / ($current_size / $upload_time) : ' - ';

/****************   end Upload Speed   **********************/

if (isset($_REQUEST['json'])) {
	echo json_encode(array(
		'percentDone' => $percent_done,
		'speed' => $speed,
		'currentSize' => $current_size,
		'totalSize' => $total_size,
		'elapsedTime' => $upload_time,
		'remainingTime' => $remaining_time,
		'sid' => $sessionid
	));
}
else
	echo $percent_done;
?>
