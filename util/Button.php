<?php

function Button($value, $url) {
	return new Element('a', array(
		'href' => $url,
		'class' => 'open-in-fancybox button'
	), $value);
	
	return new Element('form', array(
		'action' => $url,
		'method' => 'post'
	), new Element('input', array(
		'type' => 'submit',
		'value' => $value
	)));
}