<?php

class DISelect extends DI {
	public function init() {
		$opts = $this->getOption('options');
		if (!is_array($opts)) {
			die('Please set an array for the option “options” for a DISelect.');
		}
		
		// we need to prefix keys to support numeric keys
		$this->options['options'] = array(); // empty
		foreach ($opts as $k => $v) {
			$this->options['options']['_'.$k] = $v;
		}
		
		if (isset($this->options['default'])) {
			$this->options['default'] = '_'.$this->options['default'];
		}
	}
	
	public function processData(&$data, $id = -1) {
		super::processData($data, $id);
		
		// $data is all received data
		$value = &$data[$this->columnName];
		$value = substr($value, 1); // remove "_" prefix
	}
	
	public function willSendData(&$data) {
		// $data will be sent to the js frontend
		// modify it here if, e.g. you need to obfuscate it (see DISecureText)
		$value = &$data[$this->columnName];
		$value = '_'.$value;
	}
}

