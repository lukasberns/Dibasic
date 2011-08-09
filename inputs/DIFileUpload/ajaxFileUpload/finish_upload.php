<?php

function finish_upload($data, array $accept=null, $upload_path=null) {
	// pass the data passed by the ajax upload (*sid*|*oldvalue*)
	// this function will move the file to the correct directory,
	// deletes old files if neccessary
	// and returns the new filename
	
	// upload path can be an absolute path
	// or a relative one appended to $upload_dir of upload_settings.inc
	
	
	// to be platform independent about trailing slashes
	$DOCUMENT_ROOT = dirname($_SERVER['DOCUMENT_ROOT'].'/.');
	
	$data = explode('|', $data);
	$sid = ereg_replace('[^a-zA-Z0-9]', '', $data[0]);
	$oldvalue = $data[1];
	
	if (!$accept) {
		$accept = array();
	}
	
	if (!$sid) {
		// the user didn’t upload a new file
		return array('url'=>$oldvalue);
	}
	
	// read the settings
	require(dirname(__FILE__)."/read_settings.php");
	
	if (substr($upload_dir, -1) != '/') {
		$upload_dir .= '/';
	}
	
	if (!file_exists($upload_dir)) {
		die('finish_upload() -- The upload directory doesn’t exist: "'.$upload_dir.'"  Please create it first');
	}
	
	// set the correct $upload_path
	if ($upload_path) {
		if ($upload_path[0] != '/') {
			// relative path
			$upload_path = $upload_dir.$upload_path;
		}
	}
	else {
		$upload_path = $upload_dir;
	}
	
	if (substr($upload_path, -1) != '/') {
		$upload_path .= '/';
	}
	
	if ($sid == 'delete' and $oldvalue) {
		// nothing uploaded and old file should be removed
		
		delete_uploaded_file($oldvalue, $DOCUMENT_ROOT, $upload_path);
		return array('path'=>'','url'=>'','size'=>0);
	}
	
	/*****

		Move uploaded file and clean up temp files
		by tomas.epineer.se
		made changes to fit my needs (Lukas)

	*****/
	
	$file = "{$tmp_dir}/{$sid}_qstring"; // this file stores the query string passed to cgi_dir while uploading
	if (!file_exists($file)) {
		die("finish_upload() -- Error: Upload query file doesn't exist. Maybe the sid is wrong or the jquery upload was pointing to the wrong cgi.\nReceived sid: $sid");
	}
	
	$qstr = join('', file($file));
	unlink($file);
	
	$q = array();
	parse_str($qstr, $q); // stores the query variables inside $q as an array
	
	$files = array();
	$num_files = count($q['file']['name']);
	for ($i = 0; $i < $num_files; $i++) {
		$fn = $q['file']['name'][$i];
		$tmp = $q['file']['tmp_name'][$i];
		if (!file_exists($tmp)) {
			die('finish_upload() - The uploaded file couldn’t be found.');
		}
		$ext = pathinfo($fn, PATHINFO_EXTENSION);
		if (count($accept) and !in_array(strtolower($ext), $accept)) {
			die('finish_upload() - The file you uploaded was not accepted: '.basename($fn)."\nAllowed:".implode(', ', $accept));
		}
		$b_pos = strrpos($fn, '\\');
		$f_pos = strrpos($fn, '/');
		if($b_pos == false and $f_pos == false) {
			$file_name = $fn;
		} else {
			$file_name = substr($fn, max($b_pos,$f_pos)+1);
		}
		/*****
		  	Before moving the file to its final destination, you might want to check that the file
		  	is what you expect it to be, for example check that it really is an image file if you are
		  	building an image uploader.
		******/
		
		// create all not existing directories before moving
		$slashes = array();
		$slashes[0] = 0;
		
		$end_loop = substr_count($upload_path, '/');
		for($j = 1; $j < $end_loop; $j++) {
			$slashes[$j] = strpos($upload_path, '/', $slashes[$j-1]+1);
			$supDir = substr($upload_path, 0, $slashes[$j]);
			if(!file_exists($supDir)) {
				mkdir($supDir);
			}
		}
		
		// the `realpath()` is a fix for those cases when there is a symlink in the upload_path.
		// in those cases retrieving the URL from the path using DOCUMENT_ROOT won’t work correctly
		$upload_path = realpath($upload_path).'/';
		
		if ($oldvalue) {
			// if there was something uploaded before, delete it, as long as it is in the upload directory
			// (usually when you're doing an update)
			
			delete_uploaded_file($oldvalue, $DOCUMENT_ROOT, $upload_path);
		}
		
		$newfile = $upload_path.$file_name;
		$j = 1;
		
		while (file_exists($newfile)) {
			// make sure the new file won’t override an older file
			$newfile = preg_replace('/(\/[^\.]*?)(\-[0-9]+)?(\.[^\/]*)?$/u', '$1-'.$j.'$3', $newfile);
			$j++;
		}
		rename($tmp, $newfile); // move the file
		$files[] = array(
			'size'=>$q['file']['size'][$i],
			'path'=>$newfile,
			 'url'=>substr($newfile, strlen($DOCUMENT_ROOT))
		);
		chmod($newfile, 0744); //change the permission of the file
	}
	
	// clean up the tmp files
	$tmp_files = array('_flength','_postdata','_err','_signal','_qstring');
	foreach($tmp_files as $file) {
		if (file_exists($path = "{$tmp_dir}/{$sid}{$file}")) {
			unlink($path);
		}
	}
	
	return $files[0];
}

function delete_uploaded_file($file, $doc_root, $upload_dir) {
	if (!$file) return;
	
	$path = $doc_root.$file;
	$upload_path = realpath($upload_dir); // resolve symlinks
	
	if (file_exists($path) and stripos($path, $upload_dir) === 0) {
		// case insensitive search to support case insensitive file systems
		unlink($path);
	}
}
