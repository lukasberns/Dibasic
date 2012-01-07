<?php

class DPActionDetails extends DP {
	public function act() {
		if (!isset($_GET['action_id'])) {
			header('HTTP/1.0 400 Bad Request');
			die('Please provide an "action_id"');
		}
		
		$action_id = intval($_GET['action_id']);
		$log = DIBASIC_DB_PREFIX.'log';
		$q = "SELECT * FROM `$log` WHERE action_id = '$action_id' ORDER BY id ASC";
		$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$json = array();
		
		while ($r = mysql_fetch_assoc($qr)) {
			if (!isset($json[$r['table']])) {
				$json[$r['table']] = array();
			}
			
			$table = &$json[$r['table']];
			if (!isset($table[$r['table_id']])) {
				$table[$r['table_id']] = array();
			}
			
			$value_q = sprintf(
				"SELECT value
				FROM `$log`
				WHERE `table` = '%s'
				AND table_id = '%s'
				AND `key` = '%s'
				AND id < '%s'
				ORDER BY id DESC
				LIMIT 1",
				mysql_real_escape_string($r['table']),
				mysql_real_escape_string($r['table_id']),
				mysql_real_escape_string($r['key']),
				mysql_real_escape_string($r['id'])
			);
			$value_qr = mysql_query($value_q) or trigger_error(mysql_error(), E_USER_ERROR);
			$value = mysql_num_rows($value_qr) ? mysql_result($value_qr, 0) : null;
			$r['old'] = $value;
			$r['changed'] = true;
			
			$table[$r['table_id']][] = $r;
		}
		
		foreach ($json as $tableName => &$changedRows) {
			$q = "DESCRIBE `$tableName`";
			$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
			$columns = array();
			
			while ($r = mysql_fetch_assoc($qr)) {
				$columns[] = $r['Field'];
			}
			
			foreach ($changedRows as $id => &$changedValues) {
				$columnsWithChanges = array();
				$biggestChangeId = 0;
				foreach ($changedValues as $change) {
					$columnsWithChanges[] = $change['key'];
					$biggestChangeId = $change['id'];
				}
				
				$missing = array_diff($columns, $columnsWithChanges);
				foreach ($missing as $c) {
					$q = sprintf(
						"SELECT value
						FROM `$log`
						WHERE `table` = '%s'
						AND table_id = '%s'
						AND `key` = '%s'
						AND id < '%s'
						ORDER BY id DESC
						LIMIT 1",
						mysql_real_escape_string($tableName),
						mysql_real_escape_string($id),
						mysql_real_escape_string($c),
						mysql_real_escape_string($biggestChangeId)
					);
					$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
					$value = mysql_num_rows($qr) ? mysql_result($qr, 0) : null;
					
					$changedValues[] = array(
						'changed' => false,
						'key' => $c,
						'old' => $value,
						'value' => $value
					);
				}
			}
		}
		
		echo json_encode($json);
	}
}
