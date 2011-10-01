<?php

class DPAlterForm extends DP {
	public function act() {
		if (!$this->Dibasic->permissions['alter']) {
			header('HTTP/1.0 403 Forbidden');
			echo '{"error":"Permission denied"}';
			return;
		}
		
		$cols = $this->Dibasic->columns;
		$mods = $this->Dibasic->tableModifications;
		
		$addDefs = array();
		$modifyDefs = array();
		$removeDefs = array();
		
		foreach ($mods['add'] as $name) {
			$addDefs[] = "`$name` {$cols[$name]->dataType} NOT NULL";
		}
		
		foreach ($mods['modify'] as $name) {
			$modifyDefs[] = "MODIFY `$name` {$cols[$name]->dataType} NOT NULL";
		}
		
		foreach ($mods['remove'] as $name) {
			$removeDefs[] = "DROP `$name`";
		}
		
		$alters = implode(',', array_merge($modifyDefs,
			                               $removeDefs,
			                               count($addDefs) ? array('ADD('.implode(',', $addDefs).')') : array())
			);
		
		$query = "ALTER TABLE `{$this->Dibasic->tableName}` $alters";
		
		mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		die('1'); // success
	}
}