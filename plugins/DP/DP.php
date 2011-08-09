<?php

class DP {
	public $Dibasic;
	public $options = array();
	
	public function __construct($Dibasic, array $options = null) {
		// don't overwrite constructor, use init() instead
		$this->Dibasic = $Dibasic;
		if ($options) {
			$this->options = $options;
		}
		$this->init();
	}
	
	protected function init() {
		// do stuff after $Dibasic and $options have been set
	}
	
	public function act() {
		// do stuff when this class was called in $_GET['action']
	}
}