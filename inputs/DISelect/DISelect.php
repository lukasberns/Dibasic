<?php

class DISelect extends DI {
	protected $originalOptions = array();
	
	public function init() {
		$opts = $this->getOption('options');
		if (!is_array($opts)) {
			die('Please set an array for the option “options” for a DISelect.');
		}
		$opts = array('' => '—') + $opts;
		
		// we need to prefix keys to support numeric keys
		$this->options['options'] = array(); // empty
		foreach ($opts as $k => $v) {
			$this->options['options']['_'.$k] = $v;
		}
		$this->originalOptions = $opts;
	}
	
	public function searchCondition($query) {
		$matches = array();
		
		foreach ($this->originalOptions as $k => $v) {
			if (stripos($v, $query) !== false) {
				$matches[] = $k;
			}
		}
		
		if (count($matches)) {
			return 'IN ("'.implode('","', array_map('mysql_real_escape_string', $matches)).'")';
		}
		
		return parent::searchCondition($query);
	}
}

