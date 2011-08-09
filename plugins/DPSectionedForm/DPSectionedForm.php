<?php

class DPSectionedForm extends DP {
	public function init() {
		$this->options['titles'] = array();
	}
	
	public function addTitle($text) {
		// this adds the title $text in the current position
		$this->options['titles'][] = array('text'=>$text, 'position'=>count($this->Dibasic->columns));
	}
}