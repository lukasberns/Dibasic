<?php

// DIReorder

Dibasic::import('DISelect');

class DIReorder extends DI {
	public $dataType = 'int(11)';
	
	public function processData(&$data) {
		if (!isset($data[$this->columnName])) {
			return;
		}
		$val =& $data[$this->columnName];
		if ($val == 'MAX' || $val == 'MIN') {
			$q = "SELECT $val(`$this->columnName`) FROM `{$this->Dibasic->tableName}`";
			$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
			$val = mysql_result($qr, 0) + ($val == 'MAX' ? 1 : -1);
		}
	}
	
	public function act() {
		$id = $_GET['id']-0;
		$amount = $_GET['move']-0;
		$oldvalue = $_GET['oldvalue']-0;
		
		for ($o = 1; $o <= abs($amount); $o++) {
			
			if ($amount < 0) {
				$adesc = 'DESC';
				$operator = '<';
			}
			else if ($amount > 0) {
				$adesc = 'ASC';
				$operator = '>';
			}
			else {
				return;
			}
			
			$q = "SELECT id, `$this->columnName` FROM `{$this->Dibasic->tableName}` WHERE `$this->columnName` $operator '$oldvalue' ORDER BY `$this->columnName` $adesc LIMIT 1";
			$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
			
			echo json_encode(mysql_fetch_assoc($qr));
		}
	}
}

