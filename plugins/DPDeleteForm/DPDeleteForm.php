<?php

class DPDeleteForm extends DP {
	public function act() {
		if (isset($_GET['id'])) {
			$this->check();
		}
		else {
			$this->delete();
		}
	}
	
	protected function check() {
		$id = (int) $_GET['id'];
		$query = "SELECT * FROM {$this->Dibasic->tableName} WHERE {$this->Dibasic->key} = '$id'";
		$query_result = mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		$row = mysql_fetch_assoc($query_result);
		if ($row) {
			die('{"id":'.$id.'}');
		}
		else {
			die('Entry does not exist.');
		}
	}
	
	protected function delete() {
		$id = (int) $_POST['id'];
		$query = "DELETE FROM {$this->Dibasic->tableName} WHERE {$this->Dibasic->key} = '$id'";
		mysql_query($query) or trigger_error(mysql_error(), E_USER_ERROR);
		die('{"id":'.$id.'}');
	}
}