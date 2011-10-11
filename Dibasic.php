<?php

if ((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || ini_get('magic_quotes_sybase')) {
	foreach($_GET as $k=>$v) $_GET[$k] = stripslashes($v);
	foreach($_POST as $k=>$v) $_POST[$k] = stripslashes($v);
	foreach($_COOKIE as $k=>$v) $_COOKIE[$k] = stripslashes($v);
}

require('util/Events.php');
require('util/Element.php');
require('util/Button.php');
require('util/createDummyJavascriptClass.php');

class Dibasic {
	public $tableName;
	public $key; // primary key of table (set automatically)
	public $tableExists;
	public $tableModifications = array();
	public $tableNeedsModifications = false;
	
	public $baseURL = '.';
	public static $jsDir;
	public static $cssDir;
	
	public $columns = array();
	public $plugins = array();
	public $columnDefinitions = array(); // func_get_args() of ::addColumn()
	public $pluginDefinitions = array();
	
	public $headerFile;
	public $footerFile;
	public $mainTemplate;
	public $dataRenderer;
	
	public $mainStructure; // executed result of mainTemplate
	public $jsonRequest = false;
	
	public $disableFading = true; // this improves the performance a lot
	
	public $permissions = array(
		'select' => true, // pass array of ids to limit select permission to them
		'create' => true,
		'alter' => true,
		'insert' => true,
		'update' => true, // pass array of ids to limit update permission to them. limited by the select permissions as well
		'delete' => true, // pass array of ids to limit delete permission to them. limited by the select permissions as well
	);
	public $deny = array(
		'select' => array(), // array of ids to deny permissions to
		'update' => array(), // array of ids to deny permissions to
		'delete' => array(), // array of ids to deny permissions to
	);
	
	public function __construct($tableName) {
		$this->tableName = $tableName;
		$this->headerFile = DIBASIC_ROOT . '/html/header.php';
		$this->footerFile = DIBASIC_ROOT . '/html/footer.php';
		$this->mainTemplate = DIBASIC_ROOT . '/html/mainTemplate.php';
		
		Dibasic::$jsDir = DIBASIC_URL.'/js/';
		Dibasic::$cssDir = DIBASIC_URL.'/css/';
		
		$this->import('json2.js');
		$this->import('jquery-1.6.4.min.js');
		$this->import('jquery-ui-1.8rc1.custom.min.js');
		$this->import('custom-theme/jquery-ui-1.8rc1.custom.css');
		
		$this->import('util.js');
		$this->import('process.js');
		$this->import('registerUndo.js');
		$this->import('Dibasic.js');
		$this->import('validate/jquery.validate.min.js');
		$this->import('validate/lib/jquery.metadata.js');
		
		$this->import('DI');
		$this->import('DP');
		
		$this->addPlugin('Navigation');
		$this->addPlugin('AutomaticLogout');
		
		$this->addPlugin('DBInterface');
		$this->addPlugin('AddForm');
		$this->addPlugin('UpdateForm');
		$this->addPlugin('DeleteForm');
		
		$this->import('fancybox/jquery.fancybox-1.3.0.js');
		$this->import(DIBASIC_URL.'/js/fancybox/jquery.fancybox-1.3.0.css');
		$this->import('jquery.equalheights.js');
		$this->import('jquery.ba-bbq.min.js');
		
		$this->import('reset.css');
		$this->import('main.css');
		$this->import('buttons.css');
		$this->import('errors.css');
		
		if (isset($_GET['json'])) {
			$this->jsonRequest = true;
		}
	}
	
	public function c() {
		// shorthand for add_column(...)
		$args = func_get_args();
		return call_user_func_array(array($this, 'addColumn'), $args);
	}
	
	public function addColumn($columnName, $className, $title = '', array $options = null) {
		$c = 'DI' . $className;
		Dibasic::import($c);
		$this->columns[$columnName] = new $c($this, $columnName, $title, $options);
		
		$this->columnDefinitions[] = array($columnName, $className, $title, &$this->columns[$columnName]->options);
		
		return $this->columns[$columnName];
	}
	
	public function addPlugin($name, array $options = null) {
		if (isset($this->plugins[$name])) {
			return; // already added
		}
		
		$c = 'DP' . $name;
		Dibasic::import($c);
		$this->plugins[$name] = new $c($this, $options);
		
		$this->pluginDefinitions[] = array($name, &$this->plugins[$name]->options);
		return $this->plugins[$name];
	}
	
	public function setDataRenderer($name, array $options = null) {
		return $this->dataRenderer = $this->addPlugin($name, $options);
	}
	
	public function run() {
		// first check if db table is set up properly
		// then, if no $_GET attribute was specified, this creates the bounding box for everything
		// otherwise it passes the work to the designated classes
		
		foreach ($this->permissions as &$p) {
			if (is_array($p)) {
				$p = array_map('intval', $p);
			}
		}
		
		foreach ($this->deny as &$d) {
			if (is_array($d)) {
				$d = array_map('intval', $d);
			}
		}
		
		if (isset($_GET['permissions'])) {
			echo json_encode($this->permissions);
			return;
		}
		
		if ($this->disableFading) {
			Dibasic::import('disableFading.js');
		}
		
		if (!$this->dataRenderer) {
			$this->setDataRenderer('DataTable');
		}
		
		if ($this->tableName) {
			$this->checkDBTable();
		}
		
		if (!$this->mainStructure) {
			ob_start();
				$Dibasic = $this;
				include($this->mainTemplate);
				$this->mainStructure = ob_get_contents();
				empty($Dibasic);
			ob_end_clean();
		}
		
		if (isset($_GET['action'])) {
			$action = $_GET['action'];
			if (substr($action, 0, 2) == 'DP') {
				$name = substr($action, 2);
				if (isset($this->plugins[$name])) {
					$this->plugins[$name]->act();
				}
			}
			elseif (isset($this->columns[$action])) {
				// DIs
				$this->columns[$action]->act();
			}
			die();
		}
		
		if (!$this->jsonRequest) {
			include($this->headerFile);
		}
		
		if (isset($_GET['display'])) {
			$p = $this->plugins[$_GET['display']];
			if ($p) {
				echo $p->display();
			}
		}
		else {
			$this->json();
		}
		
		$this->import('start.js');
		
		if (!$this->jsonRequest) {
			include($this->footerFile);
		}
	}
	
	public function hasPermission($action, $id = null) {
		if (!isset($this->permissions[$action])) {
			trigger_error('Unknown action', E_USER_ERROR);
			return false;
		}
		
		$p = $this->permissions[$action];
		if (!$p) {
			return false;
		}
		
		if ($id !== null and isset($this->deny[$action]) and in_array(intval($id), $this->deny[$action])) {
			return false;
		}
		
		switch ($action) {
			case 'update':
			case 'delete':
			// you can't update or delete entries you don't have select permission for
			$select = $this->permissions['select'];
			if (!$select) {
				return false;
			}
			
			if ($id !== null and in_array(intval($id), $this->deny['select'])) {
				return false;
			}
			
			if (is_array($select)) {
				// update/delete are stricter than the select permissions
				
				if (is_array($p)) {
					// if 123 is in select but not in update/delete, deny
					// if 123 in not in select but in update/delete, deny as well
					$p = array_intersect($select, $p);
				}
				else {
					// if update/delete = true, limit them to the select permissions
					$p = $select;
				}
			}
			break;
		}
		
		if ($id === null) {
			// generic permission test. might be forbidden for specific ids though
			return $p;
		}
		
		if (is_array($p) and !in_array(intval($id), $p)) {
			return false;
		}
		
		return true;
	}
	
	protected function json() {
		// creates the json data used by the js frontend
		$json = array(
			'columns' => array(),
			'inputs' => array(),
			'plugins' => array()
		);
		
		foreach ($this->columnDefinitions as $def) {
			$DI = $this->columns[$def[0]];
			$DIName = 'DI'.$def[1];
			
			$def['options'] = isset($def[3]) ? $def[3] : array();
			$json['columns'][] = array_merge(array(
				'name' => $def[0],
				'DIName' => $DIName,
				'title' => $def[2],
				'dataType' => $DI->dataType
			), $def['options']);
			
			if (!isset($json['inputs'][$DIName])) {
				$json['inputs'][$DIName] = call_user_func(array($DIName, 'jsonData'));
			}
		}
		
		foreach ($this->pluginDefinitions as $def) {
			$def['options'] = isset($def[1]) ? $def[1] : array();
			$json['plugins'][] = array_merge(array('DPName' => 'DP'.$def[0]), $def['options']);
		}
		
		$json['dataRendererName'] = get_class($this->dataRenderer);
		
		$curlyI = 0;
		
		$json['key'] = $this->key;
		$json['tableName'] = $this->tableName;
		$json['tableExists'] = $this->tableExists;
		$json['tableModifications'] = $this->tableModifications;
		$json['tableNeedsModifications'] = $this->tableNeedsModifications;
		
		$json['mainStructure'] = $this->mainStructure;
		$json['permissions'] = $this->permissions;
		$json['deny'] = $this->deny;
		
		$urlParts = explode('?', $_SERVER['REQUEST_URI']);
		$json['baseUrl'] = $urlParts[0];
		
		$urlParams = isset($urlParts[1]) ? $urlParts[1] : '';
		$json['urlParams'] = array();
		if (strlen($urlParams)) {
			foreach (explode('&', $urlParams) as $part) {
				$parted = explode('=', $part);
				$json['urlParams'][$parted[0]] = isset($parted[1]) ? $parted[1] : '';
			}
		}
		
		echo '<script type="text/javascript">Dibasic = ' . json_encode($json) . '</script>';
	}
	
	public function redirect($url = null, $params = null) {
		if ($params) {
			$args = func_get_args();
			array_shift($args);
			$url = call_user_func_array(array($this, 'getURL'), $args);
		}
		else if (!$url) {
			$url = $this->baseURL;
		}
		header("Location: $url");
		exit();
	}
	
	public function getURL($param = null) {
		// get a url with parameters
		
		$args = array();
		
		if (is_array($param)) {
			$args = $param;
		}
		else {
			$p = func_get_args();
			for ($i = 0; $i < floor(count($p)/2); $i++) {
				$args[$p[$i*2]] = $p[$i*2 + 1];
			}
		}
		
		$url = $this->baseURL;
		
		if (!strpos($url, '?')) {
			$url .= '?';
		}
		
		foreach ($args as $key => $value) {
			$url .= '&' . urlencode($key) . '=' . urlencode($value);
		}
		
		return $url;
	}
	
	protected function checkDBTable() {
		if (!$this->tableName) {
			die('DBTable->tableName is empty');
		}
		
		// first check if the table exists
		$exists_q = mysql_query("SHOW TABLES LIKE '$this->tableName'") or trigger_error(mysql_error(), E_USER_ERROR);
		$this->tableExists = mysql_num_rows($exists_q) > 0;
		
		if (!$this->tableExists) {
			return;
		}
		
		// then check if it needs modifications
		$tableColumns_q = mysql_query("DESCRIBE $this->tableName") or trigger_error(mysql_error(), E_USER_ERROR);
		
		$tableColumns = array();
		$tableDataTypes = array();
		
		$defColumns = array_keys($this->columns); // names of the columns
		$defDataTypes = array();
		
		while ($row = mysql_fetch_assoc($tableColumns_q)) {
			if ($row['Key'] == 'PRI') {
				$this->key = $row['Field'];
			}
			else {
				$tableColumns[] = $row['Field'];
				$tableDataTypes[$row['Field']] = $row['Type'];
			}
		}
		
		foreach ($this->columns as $name => $DI) {
			$defDataTypes[$name] = $DI->dataType;
		}
		
		$this->tableModifications['add'] = array_diff($defColumns, $tableColumns);
		if (isset($_GET['check_datatypes'])) {
			$this->tableModifications['modify'] = array_diff(
													array_keys(array_diff_assoc($defDataTypes, $tableDataTypes)),
													$this->tableModifications['add']
												);
		}
		else {
			$this->tableModifications['modify'] = array();
		}
		$this->tableModifications['remove'] = array_diff($tableColumns, $defColumns);
		
		if (count($this->tableModifications['add']) or count($this->tableModifications['modify']) or count($this->tableModifications['remove'])) {
			$this->tableNeedsModifications = true;
		}
	}
	
	protected static $importedClasses = array();
	protected static $importedPHPFiles = array();
	protected static $javascripts = array();
	protected static $stylesheets = array();
	
	// these are needed to get the loading order right (esp. for js obj inheritance)
	protected static $importing = array();
	protected static $importingDepth = 0;
	
	public static function import($name) {
		self::$importingDepth++;
		$importingNow = array('js'=>array(),'css'=>array());
		self::$importing[] =& $importingNow;
		
		if (substr($name, 0, 11) == 'javascript:') {
			// let’s you insert short javascript code
			$importingNow['js'][] = $name;
		}
		else if (preg_match('/\.[A-Za-z]+$/', $name)) {
			// file
			
			$absolute = $name;
			// prefixing the path with a colon will load the file relative to the callee's directory
			if ($name[0] == ':') {
				$trace = debug_backtrace();
				$calleeDir = dirname($trace[0]['file']);
				$name = substr($name, 1); // strip the colon prefix
				$absolute = $calleeDir.'/'.$name;
				$name = substr($absolute, strlen(DOCUMENT_ROOT));
			}
			
			$ext = substr($name, strrpos($name, '.')+1);
			switch ($ext) {
				case 'js':
					$importingNow['js'][] = $name;
					break;
				case 'css':
					$importingNow['css'][] = $name;
					break;
				case 'php':
					if (!in_array($absolute, self::$importedPHPFiles)) {
						include($absolute);
						self::$importedPHPFiles[] = $absolute;
					}
					break;
			}
		}
		else {
			// class
			if (!in_array($name, self::$importedClasses)) {
				$basenameCandidate = array();
				if (substr($name, 0, 2) == 'DP') {
					// plugin
					$basenameCandidate[] = DIBASIC_SUPERROOT . '/plugins/' . $name;
					$basenameCandidate[] = DIBASIC_ROOT . '/plugins/' . $name;
				}
				else if (substr($name, 0, 2) == 'DI') {
					// input
					$basenameCandidate[] = DIBASIC_SUPERROOT . '/inputs/' . $name;
					$basenameCandidate[] = DIBASIC_ROOT . '/inputs/' . $name;
				}
				else {
					echo 'Couldn’t load ' . $name;
					return;
				}
				
				$imported = false;
				
				foreach ($basenameCandidate as $basename) {
					if (is_file($file = $basename . '.php')) {
						// just a normal php file
						include($file);
						$imported = true;
						break;
					}
					if (is_dir($basename)) {
						// folder, so import everything inside (php, css, js)

						// import the main php file first, to get the correct dependencies
						if (file_exists($file = "$basename/$name.php")) {
							self::import($file);
						}
						
						foreach (array('php', 'js', 'css') as $ext) {
							foreach (glob("$basename/*.$ext") as $file) {
								$filename = basename($file);
								if ($filename[0] == '.' or $filename[0] == '_' or $filename == "$name.php") {
									continue; // skip this or parent folder and files starting with an . or _
								}
								
								if (is_dir($file)) {
									continue; // skip folders
								}
								
								if ($ext == 'js' or $ext == 'css') {
									$file = substr(realpath($file), strlen(DOCUMENT_ROOT));
								}
								
								self::import($file);
							}
						}
						$imported = true;
						break;
					}
				}
				
				if ($imported) {
					self::$importedClasses[] = $name;
				}
				else if (!class_exists($name)) {
					die('Couldn’t import ' . $name);
				}
			}
		}
		
		if (self::$importingDepth == 1) {
			// base level import
			foreach ($importingNow['js'] as $file) {
				if (!in_array($file, self::$javascripts)) {
					self::$javascripts[] = $file;
				}
			}
			foreach ($importingNow['css'] as $file) {
				if (!in_array($file, self::$stylesheets)) {
					self::$stylesheets[] = $file;
				}
			}
		}
		else {
			// deeper import (import inside of import)
			$oneLevelUp =& self::$importing[self::$importingDepth-2];
			$oneLevelUp['js'] = array_merge($oneLevelUp['js'], $importingNow['js']);
			$oneLevelUp['css'] = array_merge($oneLevelUp['css'], $importingNow['css']);
		}
		
		array_pop(self::$importing);
		self::$importingDepth--;
	}
	
	public static function getStylesheets() {
		$links = new DocumentFragment();
		foreach (self::$stylesheets as $url) {
			if ($url[0] != '/' and !preg_match('/^https?:\/\//', $url)) {
				$url = self::$cssDir . $url;
			}
			$links->add(new Element('link', array(
				'rel' => 'stylesheet',
				'href' => $url,
				'type' => 'text/css',
				'charset' => 'utf-8'
			)));
		}
		return $links;
	}
	
	public static function getJavascripts() {
		$scripts = new DocumentFragment();
		foreach (self::$javascripts as $url) {
			if (substr($url, 0, 11) == 'javascript:') {
				$el = new Element('script', array(
					'type' => 'text/javascript',
					'charset' => 'utf-8'
				));
				$el->addChild(substr($url, 11));
				$scripts->add($el);
				empty($cdata);
				empty($el);
				continue;
			}
			
			if ($url[0] != '/' and !preg_match('/^https?:\/\//', $url)) {
				$url = self::$jsDir . $url;
			}
			$scripts->add(new Element('script', array(
				'src' => $url,
				'type' => 'text/javascript',
				'charset' => 'utf-8'
			)));
		}
		return $scripts;
	}
}
