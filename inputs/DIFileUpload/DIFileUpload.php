<?php

Dibasic::import(':ajaxFileUpload/jquery.ajaxFileUpload.js');
Dibasic::import(':ajaxFileUpload/jquery.ajaxFileUpload.css');
Dibasic::import(':ajaxFileUpload/finish_upload.php');

if (!defined('CONFIG_UPLOAD_DIR')) {
	define('CONFIG_UPLOAD_DIR', DIBASIC_SUPERROOT.'/uploaded');
}

class DIFileUpload extends DI {
	public static $tmp_dir;
	public static $upload_dir;
	
	public static $upload_cgi;
	public static $fileprogress_php;
	
	public $sizeColumnName = ''; // if you want to store the size of the uploaded file
	
	public function init() {
		EventCenter::sharedCenter()->addEventListener('db.delete', array($this, 'deleteFile'));
	}
	
	public static function loadSettings() {
		$basedir = dirname(__FILE__).'/ajaxFileUpload';
		$urlDir = substr($basedir, strlen(DOCUMENT_ROOT));
		
		if (!defined('CONFIG_UPLOAD_DIR')) {
			trigger_error('CONFIG_UPLOAD_DIR was not defined. DIFileUpload and all its subclasses require this constant.', E_USER_ERROR);
		}
		self::$upload_dir = dirname(CONFIG_UPLOAD_DIR.'/.'); // remove trailing slash
		
		self::$upload_cgi = "$urlDir/upload.cgi";
		self::$fileprogress_php = "$urlDir/fileprogress.php";
	}
	
	public static function jsonData() {
		return array(
			'uploadCGI' => self::$upload_cgi,
			'fileprogressPHP' => self::$fileprogress_php
		);
	}
	
	public function processData(&$data) {
		$value = &$data[$this->columnName];
		$info = finish_upload($value, self::$upload_dir, null, "{$this->Dibasic->tableName}/{$this->columnName}");
		$value = $info['url'];
		if ($this->sizeColumnName && isset($info['size'])) {
			$data[$this->sizeColumnName] = $info['size'];
		}
	}
	
	public function deleteFile($event) {
		$info = $event->getInfo();
		if (isset($data[$this->columnName][0])) {
			$file = DOCUMENT_ROOT.$data[$this->columnName];
			if (file_exists($file)) {
				unlink($file);
			}
		}
	}
}

DIFileUpload::loadSettings();
