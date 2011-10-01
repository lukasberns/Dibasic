<?php

class DPDBInterface extends DP {
	public function act() {
		if (isset($_POST['get']) and $this->Dibasic->hasPermission('select')) {
			$this->getData();
			return;
		}
		else if (isset($_POST['insert']) and $this->Dibasic->hasPermission('insert')) {
			$this->insert();
			return;
		}
		else if (isset($_POST['update']) and $this->Dibasic->hasPermission('update')) {
			$this->update();
			return;
		}
		else if (isset($_POST['remove']) and $this->Dibasic->hasPermission('delete')) {
			$this->remove();
			return;
		}
		header('HTTP/1.0 403 Forbidden');
		echo '{"error":"Permission denied"}';
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
			if (!$this->Dibasic->hasPermission('select', $id)) {
				continue; // silently drop entry to not even let know that this exists
			}
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
		$ids = array();
		$key = $this->Dibasic->key;
		$DIs = $this->Dibasic->columns;
		
		foreach ($data as $id => $row) {
			if (!$this->Dibasic->hasPermission('update', $id)) {
				continue; // silently drop
			}
			
			$values = '';
			foreach ($DIs as $name => $DI) {
				if (isset($row[$name])) {
					$DI->processData($row, $id);
				}
			}
			
			$ids[] = intval($id);
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
		
		$json = array();
		
		if (count($ids)) {
			$ids = implode(',', $ids);
			$query = "SELECT * FROM `{$this->Dibasic->tableName}` WHERE `{$key}` IN ($ids);";
			$query_result = mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
			
			while ($row = mysql_fetch_assoc($query_result)) {
				$id = $row[$key];
				$json[$id] = $row;
			}
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
		
		$ids_arr = array();
		foreach (explode(',', $ids) as $id) {
			if (!$this->Dibasic->hasPermission('delete', $id)) {
				continue; // silently drop
			}
			$ids_arr[] = $id;
		}
		$ids = implode(',', $ids_arr);
		
		// fetch data before deleting
		$query = "SELECT * FROM `{$this->Dibasic->tableName}` WHERE `{$key}` IN ($ids);";
		$query_result = mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$json = array();
		while ($row = mysql_fetch_assoc($query_result)) {
			$json[$row[$key]] = $row;
			EventCenter::sharedCenter()->fire('db.delete', $this, $row); // `delete' is present form since it is not deleted yet
		}
		
		// delete
		$query = "DELETE FROM `{$this->Dibasic->tableName}` WHERE `{$key}` IN ($ids)";
		$query_result = mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		
		EventCenter::sharedCenter()->fire('db.deleted', $this, $json);
		
		echo json_encode($ids_arr);
	}
}