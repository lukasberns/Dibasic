<?php

class DPDBInterface extends DP {
	public function act() {
		if (isset($_POST['get'])) {
			$this->getData();
		}
		else if (isset($_POST['insert'])) {
			$this->insert();
		}
		else if (isset($_POST['update'])) {
			$this->update();
		}
		else if (isset($_POST['remove'])) {
			$this->remove();
		}
	}
	
	protected function getData() {
		$ids = $_POST['get'];
		if (preg_match('/\d+\-\d+/', $ids)) {
			// range
			$condition = 'BETWEEN ' . str_replace('-', ' AND ', $ids);
			$ids_arr = call_user_func_array('range', explode('-', $ids));
		}
		else if (preg_match('/\d+(,\d+)*/', $ids)) {
			// list
			$condition = "IN ($ids)";
			$ids_arr = explode(',', $ids);
		}
		else {
			// TODO: return proper HTTP status
			die('Invalid input');
		}
		$key = $this->Dibasic->key;
		$query = "SELECT * FROM `{$this->Dibasic->tableName}` WHERE `{$key}` {$condition}";
		$query_result = mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$data = array();
		while ($row = mysql_fetch_assoc($query_result)) {
			$id = $row[$key];
			foreach ($row as $k=>$v) {
				if (isset($this->Dibasic->columns[$k])) {
					$this->Dibasic->columns[$k]->willSendData($row);
				}
				else if ($k != $key) {
					// because it might include sensitive information
					unset($row[$k]);
				}
			}
			$data[$id] = $row;
			unset($ids_arr[array_pop(array_keys($ids_arr, $id))]); // remove id from $ids_arr
		}
		foreach ($ids_arr as $id) {
			$data[$id] = null; // to show the js frontend that these entries do not exist
		}
		
		EventCenter::sharedCenter()->fire('db.willSendData', $this, $data);
		
		echo json_encode($data);
	}
	
	protected function insert() {
		$data = json_decode($_POST['insert'], true);
		
		$DIs = $this->Dibasic->columns;
		$cols = array(); $vals = array();
		foreach ($DIs as $DI) {
			$DI->processData($data);
		}
		
		EventCenter::sharedCenter()->fire('db.insert', $this, $data);
		
		$data = array_intersect_key($data, $DIs); // security
		
		foreach ($data as $col => $val) {
			$cols[] = mysql_real_escape_string($col);
			$vals[] = mysql_real_escape_string($val);
		}
		
		$cols = implode('`,`', $cols);
		$vals = implode("', '", $vals);
		
		$key = $this->Dibasic->key;
		
		$query = "INSERT INTO `{$this->Dibasic->tableName}` (`$cols`) VALUES ('$vals');";
		mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$query = "SELECT * FROM `{$this->Dibasic->tableName}` WHERE `{$key}` = LAST_INSERT_ID();";
		$query_result = mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$row = mysql_fetch_assoc($query_result);
		$json = array();
		$json[$row[$key]] = $row;
		EventCenter::sharedCenter()->fire('db.inserted', $this, $json);
		echo json_encode($row);
		die();
	}
	
	protected function update() {
		$data = json_decode($_POST['update'], true);
		$key = $this->Dibasic->key;
		$DIs = $this->Dibasic->columns;
		
		foreach ($data as $id => $row) {
			$values = '';
			foreach ($DIs as $name => $DI) {
				if (isset($row[$name])) {
					$DI->processData($row, $id);
				}
			}
			
			$row[$key] = $id;
			EventCenter::sharedCenter()->fire('db.update', $this, $row);
			
			$row = array_intersect_key($row, $this->Dibasic->columns); // security
			foreach ($row as $col => $val) {
				if ($col == $key) { continue; }
				if ($values) { $values .= ', '; }
				$values .= '`'.mysql_real_escape_string($col).'`="'.mysql_real_escape_string($val).'"';
			}
			
			$query = "UPDATE `{$this->Dibasic->tableName}` SET $values WHERE `$key` = ".(int)$id." LIMIT 1;";
			mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		}
		
		$ids = implode(',', array_map('intval', array_keys($data)));
		$query = "SELECT * FROM `{$this->Dibasic->tableName}` WHERE `{$key}` IN ($ids);";
		$query_result = mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$json = array();
		while ($row = mysql_fetch_assoc($query_result)) {
			$json[$row[$key]] = $row;
		}
		
		
		EventCenter::sharedCenter()->fire('db.updated', $this, $json);
		echo json_encode($json);
		die();
	}
	
	protected function remove() {
		$ids = $_POST['remove'];
		$key = $this->Dibasic->key;
		if (!preg_match('/\d+(,\d+)*/', $ids)) {
			die('Invalid input');
		}
		
		$ids_arr = array_map('intval', explode(',', $ids));
		
		// fetch data before deleting
		$query = "SELECT * FROM `{$this->Dibasic->tableName}` WHERE `{$key}` IN ($ids);";
		$query_result = mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$json = array();
		while ($row = mysql_fetch_assoc($query_result)) {
			$json[$row[$key]] = $row;
		}
		EventCenter::sharedCenter()->fire('db.delete', $this, $json); // `delete' is present form since it is not deleted yet
		
		// delete
		$query = "DELETE FROM `{$this->Dibasic->tableName}` WHERE `{$key}` IN ($ids)";
		$query_result = mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		
		EventCenter::sharedCenter()->fire('db.deleted', $this);
		
		echo json_encode($ids_arr);
	}
}