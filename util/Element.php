<?php

/*

note: "xyz" means (array, Element or string)

Element {
	@public
	
	string	$name
	array	$attributes
	array	$children
	mixed	$metadata
	
	void	__construct(string $name [, array $attributes] [, xyz $children])
	string	getAttr(string $name)
	void	setAttr(string $name, string $value)
	string	generate()
}

DocumentFragment {
	@public
	
	array	$elements
	
	void	__construct(xyz $elements)
	void	add(xyz $element)
	string	generate()
}

*/

class Element {
	public $name;
	public $attributes = array();
	public $children = array();
	public $metadata;
	
	public function __construct($name, array $attributes=null, $children=null) {
		$this->name = $name;
		if ($attributes) {
			$this->attributes = $attributes;
		}
		if ($children) {
			$this->addChild($children);
		}
	}
	
	public function getAttr($name) {
		return $this->attributes[$name];
	}
	
	public function setAttr($name, $value) {
		$this->attributes[$name] = $value;
	}
	
	public function addChild($child) {
		if (is_array($child)) {
			$this->children = array_merge($this->children, $child);
		}
		else if (get_class($child) == 'DocumentFragment') {
			$this->children = array_merge($this->children, $child->elements);
		}
		else {
			$this->children[] = $child;
		}
	}
	
	public function __toString() {
		return $this->generate();
	}
	
	public function generate() {
		// create HTML code
		
		$attributes = $this->attributes;
		
		if (is_array($this->metadata)) {
			if (!isset($attributes['class'])) {
				$attributes['class'] = '';
			}
			$attributes['class'] .= ' ' . json_encode($this->metadata);
		}
		
		$html = '<' . $this->name;
		foreach ($attributes as $name => $value) {
			$html .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
		}
		
		if (!in_array($this->name, array('img', 'input', 'br', 'hr', 'link', 'meta'))) {
			$html .= '>';
		
			foreach ($this->children as $child) {
				if (is_string($child)) {
					$html .= htmlspecialchars($child);
				}
				else {
					$html .= $child->generate();
				}
			}
		
			$html .= '</' . $this->name . '>';
		}
		else {
			// self closing
			$html .= ' />';
		}
		return $html;
	}
}

class DocumentFragment {
	public $elements = array();
	
	public function __construct($elements=null) {
		if ($elements) {
			if (is_array($elements)) {
				$this->elements = $elements;
			}
			else {
				$this->elements[] = $elements;
			}
		}
	}
	
	public function add($el) {
		if (is_array($el)) {
			$this->elements = array_merge($this->elements, $el);
		}
		else {
			$this->elements[] = $el;
		}
	}
	
	public function __toString() {
		return $this->generate();
	}
	
	public function generate() {
		$html = '';
		foreach ($this->elements as $el) {
			if (is_string($el)) {
				$html .= $el;
			}
			else {
				$html .= $el->generate();
			}
		}
		return $html;
	}
}
