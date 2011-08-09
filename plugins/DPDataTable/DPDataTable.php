<?php

Dibasic::import('DPDataTemplate');

class DPDataTable extends DPDataTemplate {
	public function init() {
		if (!isset($this->options['columns'])) {
			// generate columns automatically
			$cols = array();
			foreach ($this->Dibasic->columns as $col) {
				$cols[] = $col->columnName;
			}
			$this->options['columns'] = $cols;
		}
	}
}