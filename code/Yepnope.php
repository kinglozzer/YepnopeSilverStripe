<?php
/**
* 
*/
class Yepnope extends Requirements {

	/**
	 * Instance of yepnope for storage
	 *
	 * @var Yepnope
	 */
	private static $backend = null;
	
	/**
	 * Returns an instance of Yepnope_Backend
	 *
	 * @return object $backend
	 */
	public static function backend() {
		if(!self::$backend) {
			self::$backend = new Yepnope_Backend();
		}
		return self::$backend;
	}

	/**
	 * Set a custom file to include for 'yepnope', such as modernizr or false if not required
	 *
	 * @param string|boolean $file
	 */
	public static function set_yepnope($file) {
		self::backend()->set_yepnope($file);
	}

	/**
	 * Add files to be output by yepnope
	 *
	 * @param string|array $files
	 * @param string $callback A function called for each resource loaded
	 * @param string $complete A function called once when all resources have been loaded
	 * @param string $id A unique identifier for the test
	 */
	public static function add_files($files, $callback=null, $complete=null, $id=null) {
		self::backend()->add_files($files, $callback, $complete, $id);
	}

	/**
	 * Add a yepnope test
	 *
	 * @param string $test The test to be run
	 * @param string|array $yep File(s) to be loaded if the test passes
	 * @param string|array $nope File(s) to be loaded if the test fails
	 * @param string|array $load File(s) to be loaded regardless of the test outcome
	 * @param string $callback A function called for each resource loaded
	 * @param string $complete A function called once when all resources have been loaded
	 * @param string $id A unique identifier for the test
	 */
	public static function add_test($test, $yep=null, $nope=null, $load=null,
		$callback=null, $complete=null, $id=null
	) {
		self::backend()->add_test($test, $yep, $nope, $load, $callback, $complete, $id);
	}

	/**
	 * Clear a yepnope test
	 *
	 * @param string $id The identifier of the test
	 */
	public static function clear_test($id) {
		self::backend()->clear_test($id);
	}

}

class Yepnope_Backend extends Requirements_Backend {

	protected $yepnopeTests = array();

	protected $yepnopeScript = 'yepnopesilverstripe/javascript/yepnope.1.5.4-min.js';

	public $customScriptID = null;

	public function set_yepnope($file) {
		$this->yepnopeScript = $file;
	}

	public function get_yepnope() {
		return $this->yepnopeScript;
	}

	public function add_files($files, $callback=null, $complete=null, $id=null) {
		if(is_string($files)) $files = array($files);
		$yepnopeTest = array(
			'test' => null,
			'yep' => null,
			'nope' => null,
			'load' => $files,
			'callback' => $callback,
			'complete' => $complete
		);

		$id = ($id) ? $id : $this->generateIdentifier($files);
		
		$this->yepnopeTests[$id] = $yepnopeTest;
		$this->evalYepnope();
	}

	public function generateIdentifier($files) {
		$tmpArray = array();
		foreach ($files as $file) {
			$filename = basename($file);
			$tmpArray[] = $filename;
		}
		return implode('|', $tmpArray);
	}

	public function clear_test($id) {
		$tests = $this->yepnopeTests;
		unset($tests[$id]);
		$this->yepnopeTests = $tests;
		$this->evalYepnope();
	}

	public function add_test($test, $yep=null, $nope=null, $load=null,
		$callback=null, $complete=null, $id=null
	) {
		if ( ! $yep && ! $nope) {
			user_error(
				"Yepnope::add_test(): 
				You need to specify a 'yep' or a 'nope' for your test.", 
				E_USER_ERROR
			);
		}

		if(is_string($yep) || ! $yep) $yep = array($yep);
		if(is_string($nope) || ! $nope) $nope = array($nope);
		if(is_string($load) || ! $load) $load = array($load);

		$yepnopeTest = compact("test", "yep", "nope", "load", "callback", "complete");

		$id = ($id) ? $id : $this->generateIdentifier(array_merge($yep, $nope, $load));
		
		$this->yepnopeTests[$id] = $yepnopeTest;
		$this->evalYepnope();
	}

	public function evalYepnope() {
		if ($yepnope = $this->get_yepnope()) Requirements::javascript($yepnope);
		if ($this->customScriptID) $this->clear($this->customScriptID);

		$str = "yepnope([{\n";	
		$allTests = array();

		foreach ($this->yepnopeTests as $property) {
			$tempArray = array();
			foreach ($property as $name=>$value) {
				if ($value !== null) {
					$tmpStr = "\t" . $name . ": ";
					if (is_array($value)) {
						$tmpStr .= "['" . implode("', '", $value) . "']";
					} else {
						$tmpStr .= $value;
					}
					$tempArray[] = $tmpStr;
				}
			}
			$allTests[] = implode(",\n", $tempArray) . "\n";
		}

		$str .= implode("}, {\n", $allTests) . "}]);";	
		$this->customScriptID = "yepnope-" . time();
		Requirements::customScript($str, $this->customScriptID);
	}

}