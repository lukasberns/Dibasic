<?php

class DI {
	public $Dibasic;
	public $columnName;
	public $title;
	public $value = ''; // value saved in database (for updating etc.)
	public $options = array();
	
	// note: has to be equal to the type name mysql would return when doing 'DESCRIBE tableName'
	public $dataType = 'varchar(255)';
	
	// will be appended after the column name on creation of the table
	public $mysql_extra = 'NOT NULL';
	
	public function __construct($Dibasic, $columnName, $title = '', array $options = null) {
		// refrain from overwriting the constructor, use init() instead
		$this->Dibasic = $Dibasic;
		$this->columnName = $columnName;
		$this->title = $title;
		if ($options) {
			$this->options = $options;
		}
		if (isset($this->options['dataType'])) {
			$this->dataType = $this->options['dataType'];
		}
		$this->init();
	}
	
	protected function init() {
		// do stuff after $Dibasic and $options have been set
	}
	
	public function processData(&$data, $id = -1) {
		// $data is all received data
		$value = &$data[$this->columnName];
		
		// remove control chars except HT, NL, CR (09,0A,0D)
		// $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value);
		
		// validate and convert it here if necessary before saving to db
		if (!$this->isValid($value)) {
			die("The value given for “{$this->columnName}” was invalid: {$value}");
		}
	}
	
	public function willSendData(&$data) {
		// $data will be sent to the js frontend
		// modify it here if, e.g. you need to obfuscate it (see DISecureText)
		// $value = &$data[$this->columnName];
	}
	
	public function isValid($value) {
		$rules = $this->getOption('rules');
		if (is_array($rules)) {
			// complex stuff
			// TODO: implement
		}
		else {
			// required, email etc.
			switch ($rules) {
				case 'required':
					return strlen($value) > 0;
			}
		}
		return true;
	}
	
	public function getOption($name) {
		return isset($this->options[$name]) ? $this->options[$name] : null;
	}
	
	public static function jsonData() {
		// json to pass to javascript frontend
		// returning an array will add it to Dibasic.inputs.DI***
		return null;
	}
	
	public function act() {
		// called if this DI is referenced via ?action=columnName
	}
	
	/**
	 * What condition to use when searching
	 * @param string $query Word that is being searched for (already trimmed etc.)
	 * @return string Where condition (without the column name)
	 */
	public function searchCondition($query) {
		return 'LIKE "%'.mysql_real_escape_string($query).'%"';
	}
}