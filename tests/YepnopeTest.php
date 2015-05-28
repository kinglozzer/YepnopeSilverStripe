<?php

class YepnopeTest extends SapphireTest {

	public function setUp() {
		Requirements::clear();

		// Reset Yepnope::$backend to null for each test
		$yep = new Yepnope();
		$reflection = new ReflectionClass('Yepnope');
		$backendProp = $reflection->getProperty('backend');
		$backendProp->setAccessible(true);
		$backendProp->setValue($yep, null);
	}
	
	public function testSetters() {
		$backend = Yepnope::backend();
		Yepnope::set_yepnope('foo.js');
		Yepnope::set_automatically_evaluate(false);
		Yepnope::set_timeout(10000);


		$this->assertInstanceOf('Yepnope_Backend', $backend);
		$this->assertEquals('foo.js', $backend->get_yepnope());
		$this->assertFalse($backend->get_automatically_evaluate());
		$this->assertEquals(10000, $backend->get_timeout());
	}

	public function testAddFiles() {
		$files = array('1.js', '2.js');
		$callback = 'function() { callback(); }';
		$complete = 'function() { complete(); }';
		Yepnope::add_files($files, $callback, $complete);
		Yepnope::eval_yepnope();

		$scripts = Requirements::get_custom_scripts();
		
		$expects = <<<JS
yepnope([
    {
        "load": [
            "1.js",
            "2.js"
        ],
        "callback": function() { callback(); },
        "complete": function() { complete(); }
    }
]);
JS;

		$this->assertContains($expects, $scripts);
	}

	public function testClearTest() {
		$files1 = array('1.js', '2.js');
		$files2 = array('3.js', '4.js');
		Yepnope::add_files($files1, null, null, 'ClearMe');
		Yepnope::add_files($files2, null, null, 'ButNotMe');
		Yepnope::clear_test('ClearMe');
		Yepnope::eval_yepnope();

		$scripts = Requirements::get_custom_scripts();
		
		$contains = <<<JS
        "load": [
            "3.js",
            "4.js"
        ]
JS;

		$notContains = <<<JS
        "load": [
            "1.js",
            "2.js"
        ]
JS;

		$this->assertContains($contains, $scripts);
		$this->assertNotContains($notContains, $scripts);
	}

	public function testAddTest() {
		$test = "window.JSON";
		$yep = array('yep.js');
		$nope = array('nope.js');
		$load = array('always-load.js');
		$callback = 'function() { callback(); }';
		$complete = 'function() { complete(); }';
		Yepnope::add_test($test, $yep, $nope, $load, $callback, $complete);
		Yepnope::eval_yepnope();

		$scripts = Requirements::get_custom_scripts();
		
		$expects = <<<JS
yepnope([
    {
        "test": window.JSON,
        "yep": [
            "yep.js"
        ],
        "nope": [
            "nope.js"
        ],
        "load": [
            "always-load.js"
        ],
        "callback": function() { callback(); },
        "complete": function() { complete(); }
    }
]);
JS;
		
		$this->assertContains($expects, $scripts);

		$test2 = "Modernizr.geolocation";
		$yep2 = array('hasGeo.js');
		$nope2 = array('geoPolyfill.js');
		$load2 = array('geo-app.js');
		$callback2 = 'function() { geoLoaded(); }';
		$complete2 = 'function() { geoComplete(); }';
		Yepnope::add_test($test2, $yep2, $nope2, $load2, $callback2, $complete2);
		Yepnope::eval_yepnope();

		$scripts = Requirements::get_custom_scripts();

		$expects = <<<JS
yepnope([
    {
        "test": window.JSON,
        "yep": [
            "yep.js"
        ],
        "nope": [
            "nope.js"
        ],
        "load": [
            "always-load.js"
        ],
        "callback": function() { callback(); },
        "complete": function() { complete(); }
    },
    {
        "test": Modernizr.geolocation,
        "yep": [
            "hasGeo.js"
        ],
        "nope": [
            "geoPolyfill.js"
        ],
        "load": [
            "geo-app.js"
        ],
        "callback": function() { geoLoaded(); },
        "complete": function() { geoComplete(); }
    }
]);
JS;

		$this->assertContains($expects, $scripts);
	}

	public function testGetTest() {
		$files = array('1.js', '2.js');
		$callback = 'function() { callback(); }';
		$complete = 'function() { complete(); }';
		Yepnope::add_files($files, $callback, $complete, 'test-id');

		$test = Yepnope::get_test('test-id');

		$this->assertInstanceOf('YepnopeTestObject', $test);
	}

	public function testNoTestsDoesNotIncludeYepnope() {
		Yepnope::eval_yepnope();
		$scripts = Requirements::get_custom_scripts();
		$javascripts = Requirements::backend()->get_javascript();

		$this->assertEquals('', $scripts, 'YepNope output found, even though no test were added');
		$this->assertEquals(array(), $javascripts, 'YepNope lib was included, even though no tests were added');
	}

}