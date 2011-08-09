<?php

Dibasic::import('DICheckbox');

class DPManageUserPermissions extends DP {
	public function init() {
		/* Get pages and generate permissions editor */
		
		$pagesTable = DIBASIC_DB_PREFIX.'pages';
		$q = "SELECT * FROM $pagesTable ORDER BY `order` ASC";
		$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
		
		$this->options['pageTitles'] = array();
		$this->options['pageDefaultPermissions'] = array();
		
		while ($r = mysql_fetch_assoc($qr)) {
			$title = $r['group'] ? $r['group']." » " : '';
			$title .= $r['title'];
			
			$this->options['pageTitles'][$r['id']] = $title;
			$this->options['pageDefaultPermissions'][$r['id']] = (int) $r['can_open_by_default'];
		}
	}
	
	public function insertHere() {
		// this adds the permissions chooser at this position in the form
		$this->options['position'] = count($this->Dibasic->columns);
	}
	
	public function act() {
		$permissionsTable = DIBASIC_DB_PREFIX.'page_to_user';
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			// get the permissions for the user

			$id = intval(isset($_GET['id']) && $_GET['id'] !== '' ? $_GET['id'] : die('Please set GET param “id”'));

			$q = "SELECT * FROM $permissionsTable WHERE user = '$id'";
			$qr = mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);

			$pages = array();
			while ($r = mysql_fetch_assoc($qr)) {
				$pages[] = (int) $r['page'];
			}

			echo json_encode($pages);
		}
		else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// save permissions
			
			$id = intval(isset($_GET['id']) && $_GET['id'] !== '' ? $_GET['id'] : die('Please set GET param “id”'));
			
			$q = "DELETE FROM $permissionsTable WHERE user = $id";
			mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
			
			foreach ($this->options['pageTitles'] as $pageId => $title) {
				if (isset($_POST[$pageId]) and $_POST[$pageId] == 'true') {
					$q = "INSERT INTO $permissionsTable (user, page) VALUES ($id, $pageId)";
					mysql_query($q) or trigger_error(mysql_error(), E_USER_ERROR);
				}
			}
			
			die();
		}
	}
}