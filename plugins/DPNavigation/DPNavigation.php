<?php

class DPNavigation extends DP {
	public function act() {
		global $user; // TODO: the use of globals is dangerous
		$userId = (int) $user['id'];
		$tableName = DIBASIC_DB_PREFIX.'pages';
		$permissionsTable = DIBASIC_DB_PREFIX.'page_to_user';
		$q = "SELECT id, title, `group` FROM `$tableName` LEFT JOIN $permissionsTable ON id=page WHERE user=$userId OR file_for_permissionless!='' ORDER BY `order` ASC";
		$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$data = array();
		while ($row = mysql_fetch_assoc($qr)) {
			if (!$row['group']) {
				$data[] = $row;
			}
			else {
				if (!isset($data[$row['group']])) {
					$data[$row['group']] = array();
				}
				$data[$row['group']][] = $row;
			}
		}
		echo json_encode($data);
	}
}