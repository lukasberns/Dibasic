<?php

// DISignature
// Provide the username as option:username and this class will append it if someone edits the data

class DISignature extends DI {
	public function processData(&$data, $id=-1) {
		$username = $this->getOption('username');
		if (!$username) {
			global $user;
			if (is_array($user)) {
				if (isset($user['realname']) and $user['realname']) {
					$username = $user['realname'];
				}
				else if (isset($user['username']) and $user['username']) {
					$username = $user['username'];
				}
				else {
					return;
				}
			}
			else {
				return;
			}
		}
		
		$val =& $data[$this->columnName];
		if ($val == '') { $val = $username; return; }
		
		$editors = explode(', ', $val);
		if (in_array($username, $editors)) { return; }
		
		$editors[] = $username;
		$val = implode(', ', $editors);
	}
}

