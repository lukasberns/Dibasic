<?php

class DISelect extends DI {
	public function init() {
		$opts = $this->getOption('options');
		if (!is_array($opts)) {
			die('Please set an array for the option “options” for a DISelect.');
		}
		
		// we need to prefix keys to support numeric keys
		$this->options['options'] = array(); // empty
		foreach ($opts as $k => $v) {
			$this->options['options']['_'.$k] = $v;
		}
	}
}

