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

	public static function set_modernizr($file) {
		self::backend()->set_modernizr($file);
	}

	/**
	 * Add a file to be output by yepnope
	 *
	 * @param string $file
	 */
	public static function add_file($file) {
		self::backend()->add_file($file);
	}

	/**
	 * Clear either a single or all requirements.
	 * Caution: Clearing single rules works only with customCSS and customScript if you specified a {@uniquenessID}. 
	 * 
	 * See {@link Requirements_Backend::clear()}
	 * 
	 * @param $file String
	 */
	public static function clear($fileOrID = null) {
		self::backend()->clear($fileOrID);
	}

}

class Yepnope_Backend extends Requirements_Backend {

	protected $yepnopeFiles = array();

	protected $modernizr = 'yepnopesilverstripe/javascript/yepnope.1.5.4-min.js';

	public function set_modernizr($path) {
		$this->modernizr = $path;
	}

	public function get_modernizr() {
		return $this->modernizr;
	}

	public function add_file($file) {
		Requirements::javascript($this->get_modernizr());

		$this->yepnopeFiles[$file] = true;

		$this->evalYepnope();
	}

	/**
	 * Clear either a single or all requirements.
	 * Caution: Clearing single rules works only with customCSS and customScript if you specified a {@uniquenessID}. 
	 * 
	 * @param string $fileOrID
	 */
	public function clear($fileOrID = null) {
		$arrayList = array('javascript','css', 'customScript', 'customCSS', 'customHeadTags', 'yepnopeFiles');
		if($fileOrID) {
			foreach($arrayList as $type) {
				if(isset($this->{$type}[$fileOrID])) {
					$this->disabled[$type][$fileOrID] = $this->{$type}[$fileOrID];
					unset($this->{$type}[$fileOrID]);
				}
			}
		} else {
			foreach ($arrayList as $type) {
				$this->disabled[$type] = $this->{$type};
				$this->{$type} = array();
			}
		}
		$this->evalYepnope();
	}

	public function evalYepnope() {
		$str = 'yepnope([';

		$i = 1;
		$count = count($this->yepnopeFiles);
		foreach ($this->yepnopeFiles as $file => $dummy) {
			$path = $this->path_for_file($file);

			$str .= ($i == $count) ? "'$path'" : "'$path',";

			$i++;
		}

		$str .= '])';
		
		Requirements::customScript($str, time());
	}

}