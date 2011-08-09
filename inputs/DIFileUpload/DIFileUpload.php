<?php

Dibasic::import(':ajaxFileUpload/jquery.ajaxFileUpload.js');
Dibasic::import(':ajaxFileUpload/jquery.ajaxFileUpload.css');
Dibasic::import(':ajaxFileUpload/finish_upload.php');

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
		require("$basedir/read_settings.php");
		$urlDir = substr($basedir, strlen(DOCUMENT_ROOT));
		
		self::$tmp_dir = $tmp_dir;
		self::$upload_dir = $upload_dir;
		
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
		$info = finish_upload($value, null, "{$this->Dibasic->tableName}/{$this->columnName}");
		$value = $info['url'];
		if ($this->sizeColumnName && isset($info['size'])) {
			$data[$this->sizeColumnName] = $info['size'];
		}
	}
	
	public function deleteFile($event) {
		$info = $event->getInfo();
		foreach ($info as $id => $data) {
			if (isset($data[$this->columnName][0])) {
				$file = $_SERVER['DOCUMENT_ROOT'].$data[$this->columnName];
				if (file_exists($file)) {
					unlink($file);
				}
			}
		}
	}
}

DIFileUpload::loadSettings();
