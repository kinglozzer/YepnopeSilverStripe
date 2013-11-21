<?php
/**
 * The main Yepnope class - all function calls invoke Yepnope_Backend in the same way
 * that the Requirements class does
 */

class Yepnope extends Requirements {

	/**
	 * Instance of yepnope for storage
	 * 
	 * @var Yepnope_Backend|null
	 */
	private static $backend = null;

	/**
	 * Returns an instance of Yepnope_Backend
	 *
	 * @return Yepnope_Backend $backend
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
	 * @return void
	 */
	public static function set_yepnope($file) {
		self::backend()->set_yepnope($file);
	}

	/**
	 * Set whether we should automatically evaluate Yepnope
	 * 
	 * @return void
	 */
	public static function set_automatically_evaluate($bool) {
		self::backend()->set_automatically_evaluate($bool);
	}

	/**
	 * Return whether we should automatically evaluate Yepnope. Used in YepnopeControllerExtension
	 * 
	 * @return boolean
	 */
	public static function get_automatically_evaluate() {
		return self::backend()->get_automatically_evaluate();
	}

	/**
	 * Add files to be output by yepnope
	 *
	 * @param string|array $files
	 * @param string|null $callback A function called for each resource loaded
	 * @param string|null $complete A function called once when all resources have been loaded
	 * @param string|null $id A unique identifier for the test
	 * @return void
	 */
	public static function add_files($files, $callback=null, $complete=null, $id=null) {
		self::backend()->add_files($files, $callback, $complete, $id);
	}

	/**
	 * Add a yepnope test
	 *
	 * @param string $test The test to be run
	 * @param string|array|null $yep File(s) to be loaded if the test passes
	 * @param string|array|null $nope File(s) to be loaded if the test fails
	 * @param string|array|null $load File(s) to be loaded regardless of the test outcome
	 * @param string|null $callback A function called for each resource loaded
	 * @param string|null $complete A function called once when all resources have been loaded
	 * @param string|null $id A unique identifier for the test
	 * @return void
	 */
	public static function add_test($test, $yep=null, $nope=null, $load=null,
		$callback=null, $complete=null, $id=null
	) {
		self::backend()->add_test($test, $yep, $nope, $load, $callback, $complete, $id);
	}

	/**
	 * Get a yepnope test object
	 * 
	 * @param string $id The identifier of the test
	 * @return YepnopeTestObject|null
	 */
	public static function get_test($id) {
		return self::backend()->get_test($id);
	}

	/**
	 * Clear a yepnope test
	 *
	 * @param string $id The identifier of the test
	 * @return void
	 */
	public static function clear_test($id) {
		self::backend()->clear_test($id);
	}

	/**
	 * Set error timeout length in milliseconds
	 *
	 * @param int $ms The time in milliseconds for error timeout
	 * @return void
	 */
	public static function set_timeout($ms) {
		self::backend()->set_timeout($ms);
	}

	/**
	 * Evaluate Yepnope tests specified and set Requirements::customScript() with the
	 * produced JavaScript
	 * 
	 * @return void
	 */
	public static function eval_yepnope($customScriptID = 'yepnope') {
		self::backend()->evalYepnope($customScriptID);
	}

}

/**
 * The Yepnope equivalent of Requirements_Backend. All the actual logic takes place here.
 */
class Yepnope_Backend extends Requirements_Backend {

	/** 
	 * @var ArrayList
	 */
	protected $yepnopeTests;

	/**
	 * The location of the yepnope script, or false if not required
	 * 
	 * @var boolean|string
	 */
	protected $yepnopeScript = false;

	/**
	 * @var boolean
	 */
	protected $automaticallyEvaluate = true;

	/**
	 * The time in milliseconds for yepnope error timeout, or false to leave default
	 * 
	 * @var boolean|string
	 */
	protected $yepnopeTimeout = false;

	/**
	 * Use __construct() for setting default path as you can't concatenate in properties
	 * 
	 * @return self
	 */
	public function __construct() {
		$this->yepnopeScript = YEPNOPESILVERSTRIPE_BASE . '/javascript/yepnope.1.5.4-min.js';
		$this->yepnopeTests = new ArrayList();
	}

	/**
	 * @param string $file
	 * @return void
	 */
	public function set_yepnope($file) {
		$this->yepnopeScript = $file;
	}

	/**
	 * @return string|bool
	 */
	public function get_yepnope() {
		return $this->yepnopeScript;
	}

	/** 
	 * @return void
	 */
	public function set_automatically_evaluate($bool) {
		$this->automaticallyEvaluate = (bool) $bool;
	}

	/** 
	 * @return boolean
	 */
	public function get_automatically_evaluate() {
		return $this->automaticallyEvaluate;
	}

	/**
	 * @param int $ms
	 * @return void
	 */
	public function set_timeout($ms) {
		$this->yepnopeTimeout = (int) $ms;
	}

	/**
	 * @return string|bool
	 */
	public function get_timeout() {
		return $this->yepnopeTimeout;
	}

	/**
	 * @param string|array $files
	 * @param string|null $callback A function called for each resource loaded
	 * @param string|null $complete A function called once when all resources have been loaded
	 * @param string|null $id A unique identifier for the test
	 * @return void
	 */
	public function add_files($files, $callback=null, $complete=null, $id=null) {
		if(is_string($files)) $files = array($files);

		$id = ($id) ? $id : $this->generateIdentifier($files);
		$testObject = YepnopeTestObject::create($id, null, null, null, $files, $callback, $complete);

		$this->yepnopeTests->push($testObject);
	}

	/**
	 * Generates an identifier for a test from a list of files
	 *
	 * @param array $files
	 * @return string $id The ID string
	 */
	public function generateIdentifier($files) {
		$tmpArray = array();
		foreach ($files as $file) {
			if ($filename = basename($file)) $tmpArray[] = $filename;
		}
		return implode('|', $tmpArray);
	}

	/**
	 * @param string $id
	 * @return YepnopeTestObject|null
	 */
	public function get_test($id) {
		return $this->yepnopeTests->find('id', $id);
	}

	/**
	 * @param string $id
	 * @return void
	 */
	public function clear_test($id) {
		$this->yepnopeTests = $this->yepnopeTests->exclude('id', $id);
	}

	/**
	 * @param string $test The test to be run
	 * @param string|array|null $yep File(s) to be loaded if the test passes
	 * @param string|array|null $nope File(s) to be loaded if the test fails
	 * @param string|array|null $load File(s) to be loaded regardless of the test outcome
	 * @param string|null $callback A function called for each resource loaded
	 * @param string|null $complete A function called once when all resources have been loaded
	 * @param string|null $id A unique identifier for the test
	 * @return void
	 */
	public function add_test($test, $yep=null, $nope=null, $load=null,
		$callback=null, $complete=null, $id=null
	) {
		if ( ! $yep && ! $nope) {
			throw new InvalidArgumentException("Yepnope::add_test(): You need to specify a 'yep' or"
				. " a 'nope' for your test.");
		}

		$yep = (array) $yep;
		$nope = (array) $nope;
		$load = (array) $load;

		$id = ($id) ? $id : $this->generateIdentifier(array_merge($yep, $nope, $load));
		$testObject = YepnopeTestObject::create($id, $test, $yep, $nope, $load, $callback, $complete);

		$this->yepnopeTests->push($testObject);
	}

	/**
	 * Evaluate yepnope conditions and build Javascript to be output in template
	 *
	 * @return void
	 */
	public function evalYepnope($customScriptID) {
		$str = "";

		if ($yepnope = $this->get_yepnope()) Requirements::javascript($yepnope);
		if ($timeout = $this->get_timeout()) $str .= "yepnope.errorTimeout = " . $timeout . ";\n";

		$str .= "yepnope([{\n";
		$allTests = array();
		
		foreach ($this->yepnopeTests->toArray() as $testObject) {
			$tempArray = array();
			foreach ($testObject->toArray() as $name => $value) {
				if ( ! empty($value) && $name !== 'id') {
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

		Requirements::customScript($str, $customScriptID);
	}

}