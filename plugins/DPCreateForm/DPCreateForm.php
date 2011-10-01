<?php

Dibasic::import('DIText');

class DPCreateForm extends DP {
	public function act() {
		if (!$this->Dibasic->permissions['create']) {
			header('HTTP/1.0 403 Forbidden');
			echo '{"error":"Permission denied"}';
			return;
		}
		
		$cols = $this->Dibasic->columns;
		$colDefs = array();
		$key = mysql_real_escape_string($_POST['key']);
		
		if (!preg_match('/^[A-Z][A-Z0-9_]*$/i', $key)) {
			die('Invalid input for key. It has to match the regex /[A-Z][A-Z0-9_]*/i');
		}
		
		$colDefs[] = "`$key` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY";
		foreach ($cols as $name => $DI) {
			$colDefs[] = "`$name` $DI->dataType $DI->mysql_extra";
		}
		
		$tableDef = implode(', ', $colDefs);
		$query = "CREATE TABLE `{$this->Dibasic->tableName}` ($tableDef) DEFAULT CHARSET = utf8";
		
		mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		die('1'); // success
	}
}