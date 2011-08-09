<?php

class EventCenter {
	protected static $sharedCenter;
	protected $eventListeners = array();
	
	public static function sharedCenter() {
		if (self::$sharedCenter) {
			return self::$sharedCenter;
		}
		return self::$sharedCenter = new EventCenter();
	}
	
	public function addEventListener($eventName, $callback) {
		if (!isset($this->eventListeners[$eventName])) {
			$this->eventListeners[$eventName] = array();
		}
		$this->eventListeners[$eventName][] = $callback;
	}
	
	public function removeEventListener($eventName, $callback) {
		if (!isset($this->eventListeners[$eventName])) {
			return;
		}
		foreach (array_keys($this->eventListeners[$eventName], $callback) as $key) {
			unset($this->eventListeners[$eventName][$key]);
		}
	}
	
	public function fire($event, $origin=null, $info = null) {
		// either pass one Event object as argument,
		// or the init arguments for an Event object (name, origin, [info])
		
		if (!$event instanceof Event) {
			// create $event
			$event = new Event($event, $origin, $info);
		}
		$name = $event->getName();
		if (isset($this->eventListeners[$name])) {
			foreach ($this->eventListeners[$name] as $callback) {
				call_user_func($callback, $event);
			}
		}
	}
}

class Event {
	protected $name;
	protected $origin;
	protected $info;
	
	public function __construct($name, $origin, $info = null) {
		$this->name = $name;
		$this->origin = $origin;
		$this->info = $info ? $info : array();
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getOrigin() {
		return $this->origin;
	}
	
	public function getInfo() {
		return $this->info;
	}
}
