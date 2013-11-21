<?php
/**
 * Holds a Yepnope test - facilitates getting/setting of parameters for the test
 */

class YepnopeTestObject extends Object {

	public $id;
	protected $test;
	protected $yep;
	protected $nope;
	protected $load;
	protected $callback;
	protected $complete;

	/**
	 * @return void
	 */
	public function __construct($id, $test, $yep, $nope, $load, $callback, $complete) {
		$this->id = $id;
		$this->test = $test;
		$this->yep = $yep;
		$this->nope = $nope;
		$this->load = $load;
		$this->callback = $callback;
		$this->complete = $complete;
	}

	/**
	 * Cast this object to an array
	 * 
	 * @return array
	 */
	public function toArray() {
		return array(
			'id' => $this->id,
			'test' => $this->test,
			'yep' => $this->yep,
			'nope' => $this->nope,
			'load' => $this->load,
			'callback' => $this->callback,
			'complete' => $this->complete
		);
	}

	/**
	 * Magic method to allow getting/setting of properties
	 * 
	 * @return mixed
	 */
	public function __call($method, $arguments) {
		$action = substr($method, 0, 3);

		if (in_array($action, array('get', 'set'))) {
			$property = strtolower(substr($method, 3));
			$allowedProperties = array('id', 'test', 'yep', 'nope', 'load', 'callback', 'complete');

			if (in_array($property, $allowedProperties)) {
				if ($action === 'get') {
					return $this->$property;
				} else {
					$this->$property = $arguments[0];
					return null;
				}
			}
		}

		return parent::__call($method, $arguments);
	}

}