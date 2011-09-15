<?php

// Force value

Dibasic::import('DIIgnore');

class DIForcedValue extends DIIgnore {
	public function init() {
		parent::init();
		if (!isset($this->options['value'])) {
			trigger_error('Please specify "value" option.', E_USER_ERROR);
		}
	}
	
	public function processData(&$data, $id = -1) {
		$data[$this->columnName] = $this->options['value'];
	}
}

createDummyJavascriptClass('DIForcedValue');
