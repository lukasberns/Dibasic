<?php

class DPDataTemplate extends DP {
	protected $order = array();
	protected $where = array();
	
	public function init() {
		$this->options['sortOptions'] = array();
		$this->options['filterOptions'] = array();
	}
	
	public function order($title /*, $column1, $column2, ... */) {
		// $title is the text to display when the user can choose the order (optional if only one column is used)
		
		// $column should be just the column name when ASC
		// and with a "-" prepended when ordering DESC
		// you can specify as much arguments as you want, the first one takes precedence
		
		$args = func_get_args();
		array_shift($args);
		
		if (count($args) == 0) {
			$args = array($title);
		}
		
		$order = array();
		foreach ($args as $c) {
			$direction = ($c[0] != '-' ? 'ASC' : 'DESC');
			$order[] = '`'.substr($c, $c[0] == '-')."` $direction";
		}
		$this->order[$title] = implode(',', $order);
		
		$this->options['sortOptions'][] = $title;
	}
	
	public function where($title /* , $column1, $value1, ... */) {
		// $title will be displayed if you call this function more than once, so you can choose
		// $column should be the column name with the operator appended
		// the value type matters. so 1 !== "1"
		// you can specify as many argument tuples as you want
		// if you only specify the title, all results will be displayed
		// this function is just for display preferences, so it doesn't work for display permission management
		
		$args = func_get_args();
		$where = array();
		for ($i = 0, $l = floor((count($args)-1)/2); $i < $l; $i++) {
			$where[] = $args[$i*2 + 1] . '"' . mysql_real_escape_string($args[$i*2 + 2]) . '"';
		}
		
		$this->where[$title] = implode(' AND ', $where);
		$this->options['filterOptions'][] = $title;
	}
	
	public function act() {
		if (isset($_GET['getData'])) {
			$this->getData();
		}
		else if (isset($_GET['getTotalCount'])) {
			$this->getTotalCount();
		}
	}
	
	public function getData() {
		// returns ids
		$query = "SELECT `{$this->Dibasic->key}` FROM `{$this->Dibasic->tableName}`";
		$query .= $this->getWhereCondition();
		if (count($this->order)) {
			if (isset($_GET['sortBy']) and isset($this->options['sortOptions'][$_GET['sortBy']])) {
				$order = $this->order[$this->options['sortOptions'][$_GET['sortBy']]];
			}
			else {
				$order = current($this->order);
			}
			$query .= " ORDER BY $order";
		}
		if (isset($_GET['dataPage']) and isset($_GET['perpage'])) {
			$page = (int) $_GET['dataPage'] - 1;
			$perpage = (int) $_GET['perpage'];
			$offsetStart = $perpage * $page;
			$query .= " LIMIT $offsetStart,$perpage";
		}
		$query_result = mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$data = array();
		while ($row = mysql_fetch_row($query_result)) {
			$data[] = (int)$row[0];
		}
		echo json_encode($data);
		die();
	}
	
	public function getTotalCount() {
		// will return the total number of entries
		
		$query = "SELECT COUNT(*) FROM {$this->Dibasic->tableName}";
		$query .= $this->getWhereCondition();
		$query_result = mysql_query($query);
		echo mysql_result($query_result, 0);
		
		die();
	}
	
	public function getWhereCondition() {
		if (count($this->where)) {
			if (isset($_GET['filterBy']) and isset($this->options['filterOptions'][$_GET['filterBy']])) {
				$where = $this->where[$this->options['filterOptions'][$_GET['filterBy']]];
			}
			else {
				$where = current($this->where);
			}
			if ($where) {
				return " WHERE $where";
			}
		}
		return '';
	}
}