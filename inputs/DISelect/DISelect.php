<?php

class DISelect extends DI {
	public function init() {
		if (!is_array($this->getOption('options'))) {
			die('Please set an array for the option “options” for a DISelect.');
		}
	}
}

