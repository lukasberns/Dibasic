<?php

// DIIgnore: Does nothing
// add these fields at the end of the table

class DIIgnore extends DI {
	public function init() {
		if (isset($this->options['dataType'])) {
			$this->dataType = $this->options['dataType'];
		}
	}
}

