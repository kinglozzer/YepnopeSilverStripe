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
	 */
	public static function add_files($files, $callback=null, $complete=null) {
		self::backend()->add_files($files, $callback, $complete);
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
	 */
	public static function add_test($test, $yep=null, $nope=null, $load=null, $callback=null, $complete=null) {
		self::backend()->add_test($test, $yep, $nope, $load, $callback, $complete);
	}

}

class Yepnope_Backend extends Requirements_Backend {

	//protected $yepnopeFiles = array();

	protected $yepnopeTests = array();

	protected $yepnopeScript = 'yepnopesilverstripe/javascript/yepnope.1.5.4-min.js';

	public $customScriptID = null;

	public function set_yepnope($file) {
		$this->yepnopeScript = $file;
	}

	public function get_yepnope() {
		return $this->yepnopeScript;
	}

	public function add_files($files, $callback=null, $complete=null) {
		if(is_string($files)) $files = array($files);
		$yepnopeTest = array(
			'test' => null,
			'yep' => null,
			'nope' => null,
			'load' => $files,
			'callback' => $callback,
			'complete' => $complete
		);
		
		$this->yepnopeTests[] = $yepnopeTest;
		$this->evalYepnope();
	}

	public function add_test($test, $yep=null, $nope=null, $load=null, $callback=null, $complete=null) {
		if ( ! $yep && ! $nope) {
			user_error(
				"Yepnope::add_test(): 
				You need to specify a 'yep' or a 'nope' for your test.", 
				E_USER_ERROR
			);
		}

		if(is_string($yep)) $yep = array($yep);
		if(is_string($nope)) $nope = array($nope);
		if(is_string($load)) $nope = array($load);

		$yepnopeTest = compact("test", "yep", "nope", "load", "callback", "complete");
		
		$this->yepnopeTests[] = $yepnopeTest;
		$this->evalYepnope();
	}

	public function evalYepnope() {
		if ($yepnope = $this->get_yepnope()) Requirements::javascript($yepnope);
		if ($this->customScriptID) $this->clear($this->customScriptID);

		$str = "yepnope([{\n";	
		$allYepnopeTests = array();

		foreach ($this->yepnopeTests as $test) {
			$testStr = '';
			if ($test['test']) {
				$testStr .= "test: " . $test['test'];
				if (isset($test['yep'])) {
					$testStr .= ",\nyep: ['";
					$testStr .= implode("', '", $test['yep']);
					$testStr .= "']";
				}
				if (isset($test['nope'])) {
					$testStr .= ",\nnope: ['";
					$testStr .= implode("', '", $test['nope']);
					$testStr .= "']";
				}
				$testStr .= ",\n";
			}
			if (isset($test['load'])) {
				$testStr .= "load: ['";
				$testStr .= implode("', '", $test['load']);
				$testStr .= "']";
			}
			if (isset($test['callback'])) {
				$testStr .= ",\ncallback: " . $test['callback'];
			}
			if (isset($test['complete'])) {
				$testStr .= ",\ncomplete: " . $test['complete'];
			}
			$allYepnopeTests[] = $testStr .= "\n";
		}

		$str .= implode("}, {\n", $allYepnopeTests) . "}]);";
		
		$this->customScriptID = time();
		Requirements::customScript($str, $this->customScriptID);
	}

}