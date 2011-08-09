<?php

Dibasic::import('DITime');

class DITimeInterval extends DITime {
	protected $endColumnName;
	protected $endColumnTitle;
	
	public function init() {
		$this->endColumnName = $this->getOption('endColumnName');
		if (!$this->endColumnName) {
			trigger_error('Please specify the option endColumnName', E_USER_ERROR);
		}
		$this->endColumnName = $this->getOption('endColumnName');
		if (!$this->endColumnTitle) {
			$this->endColumnTitle = 'To';
		}
		$this->Dibasic->c($this->endColumnName, 'Time', 'To', $this->options);
	}
}

