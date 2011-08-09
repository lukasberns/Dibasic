<?php

// by default this sets the modification-date
// by setting ->setOnUpdate to false, this will act as a post-date

class DITimestamp extends DI {
	public $dataType = 'datetime';
	public $setOnUpdate = true;
	
	public function init() {
		$setOnUpdate = $this->getOption('setOnUpdate');
		if ($setOnUpdate !== null) {
			$this->setOnUpdate = $setOnUpdate;
		}
	}
	
	public function processData(&$data, $id = -1) {
		// TODO: It would be much more eleagnt if we could use the MYSQL function CURRENT_TIMESTAMP here
		if ($this->setOnUpdate || $id == -1) {
			$data[$this->columnName] = gmdate('Y-m-d H:i:s');
		}
		else {
			unset($data[$this->columnName]); // leave the value as it is
		}
	}
}

