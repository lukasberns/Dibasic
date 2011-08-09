<?php

// the data will be hashed on client side along with a salt

Dibasic::import('DIText');
Dibasic::import('jshash-2.2/md5-min.js');

class DISecureText extends DI {
	public $dataType = 'char(32)';
	
	protected $saltColumnName;
	protected $extraHashColumnName;
	protected $extraSaltColumnName1;
	protected $extraSaltColumnName2;
	
	public function init() {
		$this->saltColumnName = $this->getOption('saltColumnName');
		$this->extraHashColumnName = $this->getOption('extraHashColumnName');
		$this->extraSaltColumnName1 = $this->getOption('extraSaltColumnName1');
		$this->extraSaltColumnName2 = $this->getOption('extraSaltColumnName2');
		
		// implied requiredness
		// this setting is actually done in javascript, hence changing this doesn’t do anything
		$this->options['required'] = true;
		
		
		if (!$this->saltColumnName) {
			die('Please specify the option "saltColumnName". The salt of the hash will be stored in this column. This class will add it to the table automatically. You might as well be interested in setting the options "extraHashColumnName", "extraSaltColumnName1" and "extraSaltColumnName2".');
		}
		$this->Dibasic->c($this->saltColumnName, 'Ignore', '', array('dataType' => 'char(32)'));
		
		
		if ($this->extraHashColumnName && $this->extraSaltColumnName1 && $this->extraSaltColumnName2) {
			$this->Dibasic->c($this->extraHashColumnName, 'Ignore', '', array('dataType' => 'char(32)'));
			$this->Dibasic->c($this->extraSaltColumnName1, 'Ignore', '', array('dataType' => 'char(32)'));
			$this->Dibasic->c($this->extraSaltColumnName2, 'Ignore', '', array('dataType' => 'char(32)'));
		}
		else if ($this->extraHashColumnName && $this->extraSaltColumnName1 && $this->extraSaltColumnName2) {
			// you need to specify all if you want the functionality
			die("DISecureText::init() ($this->columnName) error: If the extra hashing is wanted, all three column names, “extraHashColumnName”, “extraSaltColumnName1” and “extraSaltColumnName2” have to be specified.");
		}
	}
	
	public function willSendData(&$data) {
		// $data will be sent to the js frontend
		$value = &$data[$this->columnName];
		$value = strlen($value) > 0; // only return whether there is data saved or not
	}
	
	public function processData(&$data, $id=-1) {
		if ($data[$this->columnName] == 'old') {
			// when the user chose to use the old password while updating
			unset($data[$this->columnName]);
			unset($data[$this->saltColumnName]);
			
			if ($this->extraHashColumnName) {
				unset($data[$this->extraHashColumnName]);
				unset($data[$this->extraSaltColumnName1]);
				unset($data[$this->extraSaltColumnName2]);
			}
			return;
		}
		
		$json = json_decode($data[$this->columnName]);
		
		$data[$this->saltColumnName] = $json->salt;
		$data[$this->columnName] = $json->hash;
		
		if ($this->extraHashColumnName) {
			$data[$this->extraHashColumnName] = $json->extraHash;
			$data[$this->extraSaltColumnName1] = $json->extraSalt1;
			$data[$this->extraSaltColumnName2] = $json->extraSalt2;
		}
	}
}

