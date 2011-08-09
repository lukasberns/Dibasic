<?php

// if you’re only extending server side functionality of a DI or a DP,
// you might want a dummy js class created automatically for you

function createDummyJavascriptClass($className) {
	if ($parent = get_parent_class($className)) {
		Dibasic::import("javascript:Class('$className', $parent, {});");
	}
	else {
		Dibasic::import("javascript:Class('$className', {});");
	}
}

