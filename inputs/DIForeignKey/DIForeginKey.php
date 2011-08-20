<?php

Dibasic::import('DISelect');

class DIForeignKey extends DISelect {
	public function init() {
		// fill the options array
		
		if (!$this->getOption('table')) {
			trigger_error('Please provide the "table" option for DIForeignKey', E_USER_ERROR);
		}
		if (!$this->getOption('column')) {
			trigger_error('Please provide the "column" option for DIForeignKey', E_USER_ERROR);
		}
		
		$order = $this->getOption('order');
		if (!$order) {
			$order = 'id';
		}
		$order = self::parseOrderColumn($order);
		
		$table = $this->getOption('table');
		$column = $this->getOption('column');
		if (!is_array($column)) {
			$column = array($column);
		}
		
		$q = "SELECT * FROM `$table` ORDER BY $order";
		$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$options = array();
		while ($r = mysql_fetch_assoc($qr)) {
			$v = array();
			foreach ($column as $col) {
				if (!isset($r[$col])) {
					trigger_error('The column "'.$col.'" provided by the "column" option was not found in the table', E_USER_ERROR);
				}
				
				$v[] = $r[$col];
			}
			$options[$r['id']] = implode(', ', $v);
		}
		
		$this->options['options'] = $options;
		
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
}