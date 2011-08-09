<?php

Dibasic::import('DIText');

class DIUniqueText extends DIText {
	public $mysql_extra = 'UNIQUE NOT NULL';
	
	public function init() {
		$rules = array(
			'required'=>true
		);
		
		if (is_array($this->getOption('rules'))) {
			$this->options['rules'] = array_merge($rules, $this->options['rules']);
		}
		else {
			$this->options['rules'] = $rules;
		}
	}
	
	public function act() {
		if (isset($_GET[$this->columnName])) {
			$lookup = $_GET[$this->columnName];
			$id = isset($_GET['id']) ? intval($_GET['id']) : 'NULL';
			$query = "SELECT `$this->columnName` FROM `{$this->Dibasic->tableName}` WHERE `{$this->Dibasic->key}` != $id AND `$this->columnName` = '".mysql_real_escape_string($lookup)."' LIMIT 1";
			$result = mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
			
			if (mysql_num_rows($result)) {
				echo '"Already taken"';
			}
			else {
				echo 'true';
			}
		}
	}
}
