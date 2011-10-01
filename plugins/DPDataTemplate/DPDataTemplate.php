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
	
	public function where($title, $condition = '' /* , $replacements, ... */) {
		// $title will be displayed if you call this function more than once, so you can choose
		// $condition should be the SQL where condition (without the WHERE)
		// this will be sprintf formatted, with all replacements being escaped.
		// e.g. ->where('Apples', 'type = "%s"', 'Apple')
		// if you only specify the title, all results will be displayed
		// this function is just for display preferences, so it doesn't work for display permission management
		
		$replacements = array_splice(func_get_args(), 2);
		$this->where[$title] = vsprintf($condition, $replacements);
		$this->options['filterOptions'][] = $title;
	}
	
	public function act() {
		if (!$this->Dibasic->permissions['select']) {
			header('HTTP/1.0 403 Forbidden');
			echo '{"error":"Permission denied"}';
			return;
		}
		
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
		$where = array();
		
		if (count($this->where)) {
			if (isset($_GET['filterBy']) and isset($this->options['filterOptions'][$_GET['filterBy']])) {
				$w = $this->where[$this->options['filterOptions'][$_GET['filterBy']]];
				if ($w) { $where[] = $w; }
			}
			else {
				$w = current($this->where);
				if ($w) { $where[] = $w; }
			}
		}
		
		if (isset($_GET['search'])) {
			$search = trim(mb_convert_kana($_GET['search'], 's')); // zen-kaku space to han-kaku space
			if ($search) {
				$parts = preg_split('/\s+/', $search);
				$and = array();
				foreach ($parts as $p) {
					$or = array();
					foreach ($this->Dibasic->columns as $col => $def) {
						$or[] = "`$col` LIKE '%".mysql_real_escape_string($p)."%'";
					}
					if (count($or)) {
						$and[] = '('.implode(' OR ', $or).')';
					}
				}
				if (count($and)) {
					$where[] = implode(' AND ', $and);
				}
			}
		}
		
		if (is_array($this->Dibasic->permissions['select'])) {
			$where[] = "`{$this->Dibasic->key}` IN (".implode(',',array_map('intval', $this->Dibasic->permissions['select'])).")";
		}
		
		if (count($where)) {
			return ' WHERE '.implode(' AND ', $where);
		}
		return '';
	}
}