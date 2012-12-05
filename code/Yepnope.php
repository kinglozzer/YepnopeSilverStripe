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

	/**
	 * Set error timeout length in milliseconds
	 *
	 * @var int $ms The time in milliseconds for error timeout
	 */
	public static function set_timeout($ms) {
		self::backend()->set_timeout($ms);
	}

}

class Yepnope_Backend extends Requirements_Backend {

	/**
	 * An array of yepnope tests.
	 *
	 * Standard yepnope 'loads' are also contained in this array, with the 'test' key
	 * set to null. Tests are stored in the following format:
	 *
	 * array(
	 *	'test' => $test,
	 *	'yep' => $yep,
	 *	'nope' => $nope,
	 *	'load' => $load,
	 *	'callback' => $callback,
	 *	'complete' => $complete,
	 *	'id' => $id
	 * );
	 */
	protected $yepnopeTests = array();

	/**
	 * The location of the yepnope script, or false if not required
	 */
	protected $yepnopeScript = 'YepnopeSilverStripe/javascript/yepnope.1.5.4-min.js';

	/**
	 * The time in milliseconds for yepnope error timeout, or false to leave default
	 */
	protected $yepnopeTimeout = false;

	/**
	 * The script ID of the Requirements::customScript() added
	 *
	 * Used to wipe existing yepnope scripts to avoid duplication of files
	 */
	public $customScriptID = null;

	public function set_yepnope($file) {
		$this->yepnopeScript = $file;
	}

	/**
	 * Get the path to the yepnope script
	 *
	 * @return string|bool
	 */
	public function get_yepnope() {
		return $this->yepnopeScript;
	}

	public function set_timeout($ms) {
		$this->yepnopeTimeout = (int) $ms;
	}

	/**
	 * Get the error timeout length
	 *
	 * @return string|bool
	 */
	public function get_timeout() {
		return $this->yepnopeTimeout;
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

	/**
	 * Generates an identifier for a test from a list of files
	 *
	 * @return str $id The ID string
	 */
	public function generateIdentifier($files) {
		$tmpArray = array();
		foreach ($files as $file) {
			if ($filename = basename($file)) $tmpArray[] = $filename;
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

	/**
	 * Evaluate yepnope conditions and build Javascript to be output in template
	 *
	 * The script wipes any existing yepnope scripts (to avoid duplication) by calling
	 * Requirements_Backend::clear($id) - where $id is the script's unique identifier
	 * in the format "yepnope-(current time)"
	 *
	 * @return void
	 */
	public function evalYepnope() {
		$str = "";

		if ($yepnope = $this->get_yepnope()) Requirements::javascript($yepnope);
		if ($timeout = $this->get_timeout()) $str .= "yepnope.errorTimeout = " . $timeout . ";\n";
		if ($this->customScriptID) $this->clear($this->customScriptID);

		$str .= "yepnope([{\n";
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