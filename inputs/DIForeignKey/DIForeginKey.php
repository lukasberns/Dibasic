<?php

Dibasic::import('DISelect');

class DIForeignKey extends DISelect {
	public $dataType = 'int(11)';
	
	public function init() {
		// fill the options array
		
		if (!$this->getOption('table')) {
			trigger_error('Please provide the "table" option for DIForeignKey', E_USER_ERROR);
		}
		if (!$this->getOption('column')) {
			trigger_error('Please provide the "column" option for DIForeignKey', E_USER_ERROR);
		}
		
		$this->table = $this->getOption('table');
		$order = $this->getOption('order');
		if (!$order) {
			$order = "$this->table.id";
		}
		$this->order = self::parseOrderColumn($order);
		
		$columns = $this->getOption('column');
		if (!is_array($columns)) {
			$columns = array($columns);
		}
		$this->columns = $columns;
		
		$this->options['options'] = $this->getSelectOptions();
		parent::init();
	}
	
	private static function parseOrderColumn($order) {
		if (!is_array($order)) {
			$order = array($order);
		}
		
		$q = array();
		foreach ($order as $one) {
			if ($one[0] == '-') {
				$direction = 'DESC';
				$column = substr($one, 1);
			}
			else {
				$direction = 'ASC';
				$column = $one;
			}
			
			$q[] = "`$column` $direction";
		}
		return implode(', ', $q);
	}
	
	public function escapeColumnNames($col) {
		if (is_array($col)) {
			$col = $col[0]; // second element is callback in this case
		}
		
		$col = preg_replace('/[^0-9a-z\-_\.]/i', '', $col);
		return '`'.str_replace('.', '`.`', $col).'` AS `'.str_replace('.', '.', $col).'`';
	}
	
	public function getSelectOptions() {
		$exists_q = mysql_query("SHOW TABLES LIKE '$this->table'") or trigger_error(mysql_error(), E_USER_ERROR);
		$tableExists = mysql_num_rows($exists_q) > 0;
		
		if (!$tableExists) {
			// don't stop program even if foreign table does not exist
			// otherwise it's very annoying when the foreign table is the same as the local table
			// you won't be able to create the local table in the first place
			return array( 0 => "DIForeignKey ($this->columnName): Table $this->table does not exist.");
		}
		
		$select = array_merge(array("$this->table.id"), $this->columns);
		$select = implode(',', array_map(array($this, 'escapeColumnNames'), $select));
		
		$join = $this->getOption('join');
		if ($join) {
			$join = "JOIN $join";
		}
		
		$where = $this->getOption('where');
		if ($where) {
			if (is_array($where)) {
				$replacements = array_slice($where, 1);
				$where = vsprintf($where[0], $replacements);
			}
			$where = ' WHERE '.$where;
		}
		
		$q = "SELECT $select FROM `$this->table` $join $where ORDER BY $this->order";
		$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$options = array();
		
		$rules = $this->getOption('rules');
		if (strpos($rules, 'required') === false or (is_array($rules) and in_array('required', $rules))) {
			$options[0] = '— none —';
		}
		
		while ($r = mysql_fetch_assoc($qr)) {
			$v = array();
			$missing = array();
			foreach ($this->columns as $col) {
				$callback = '';
				if (is_array($col)) {
					list($col, $callback) = $col;
				}
				
				if (!isset($r[$col])) {
					$missing [] = $col;
				}
				else if ($callback) {
					$v[] = call_user_func($callback, $r[$col]);
				}
				else {
					$v[] = $r[$col];
				}
				
				if (count($missing)) {
					return array(0 => 'The column(s) "'.implode('", "', $missing).'" provided by the "column" option was/were not found in the table '.$this->table);
				}
			}
			$options[$r["$this->table.id"]] = implode(', ', $v);
		}
		
		return $options;
	}
	
	public function act() {
		// refresh
		$pre_options = $this->getSelectOptions();
		
		// we need to prefix keys to support numeric keys
		$options = array();
		foreach ($pre_options as $k => $v) {
			$options['_'.$k] = $v;
		}
		echo json_encode($options);
	}
}