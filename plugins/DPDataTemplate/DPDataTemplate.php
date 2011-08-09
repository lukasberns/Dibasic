<?php

class DPDataTemplate extends DP {
	protected $order = array();
	protected $where;
	
	public function init() {
		$this->options['sortOptions'] = array();
	}
	
	public function order($title, $column) /* ... */ {
		// $title is the text to display when the user can choose the order
		
		// $column should be just the column name when ASC
		// and with a "-" prepended when ordering DESC
		// you can specify as much arguments as you want, the first one takes precedence
		
		$args = func_get_args();
		array_shift($args);
		$order = array();
		foreach ($args as $c) {
			$direction = ($c[0] != '-' ? 'ASC' : 'DESC');
			$order[] = '`'.substr($c, $c[0] == '-')."` $direction";
		}
		$this->order[$title] = implode(',', $order);
		
		$this->options['sortOptions'][] = $title;
	}
	
	public function where($column, $value) {
		// $column should be the column name with the operator appended
		// you can specify as many argument tuples as you want
		
		$args = func_get_args();
		$where = array();
		for ($i = 0; $i < floor(count($args)/2); $i++) {
			$where[] = $args[$i*2] . '"' . mysql_real_escape_string($args[$i*2 + 1]) . '"';
		}
		$this->where = implode(' AND ', $where);
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
		if (count($this->order)) {
			if (isset($_GET['sortBy']) and isset($this->options['sortOptions'][$_GET['sortBy']])) {
				$order = $this->order[$this->options['sortOptions'][$_GET['sortBy']]];
			}
			else {
				$order = current($this->order);
			}
			$query .= " ORDER BY $order";
		}
		if ($this->where) {
			$query .= " WHERE $this->where";
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
		if ($this->where) {
			$query .= " WHERE $this->where";
		}
		$query_result = mysql_query($query);
		echo mysql_result($query_result, 0);
		
		die();
	}
}