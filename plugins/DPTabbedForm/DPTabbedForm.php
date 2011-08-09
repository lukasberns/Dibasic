<?php

Dibasic::import('DPAddForm');

class DPTabbedForm extends DP {
	public function init() {
		$this->options['tabs'] = array();
	}
	
	public function addTab($name) {
		// this wraps every following DI into this tab, until it reaches another DPTabbedForm::tab(...) or the end
		$this->options['tabs'][] = array('name'=>$name, 'startAt'=>count($this->Dibasic->columns));
	}
}