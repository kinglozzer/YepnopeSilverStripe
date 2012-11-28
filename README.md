#YepnopeSilverStripe#

A module to allow loading of Javascript & CSS using the conditional loader [yepnopejs](http://yepnopejs.com).

By:
Loz Calver & Colin Richardson - [Bigfork Ltd](http://www.bigfork.co.uk/).

##About yepnope:##

_"yepnope is an asynchronous conditional resource loader that's super-fast, and allows you to load only the scripts that your users need."_

##Installation:##

Simply clone or download this repository and put it in a folder called 'yepnopesilverstripe' in your SilverStripe installation folder, then run `dev/build`.

##Examples:##

###Add files:###
The simplest way to add a file, or multiple files, whether they're Javascript or CSS, is to use the `add_files()` function in your `Page_Controller`:

```php
class Page_Controller extends ContentController {

	public function init() {
		parent::init();
		
		/* Add a single file */
		Yepnope::add_files('themes/yourtheme/js/yourjavascript.js');

		/* Add an array of files */
		Yepnope::add_files(
			array(
				'themes/yourtheme/css/adminstyles.css',
				'themes/yourtheme/css/extrauserstyles.css'
			)
		);
	}

}
```

###Callbacks:###
You can specify a _callback_ function (called for each file that's loaded) and/or a _complete_ function (called after all files have been loaded) by passing second and third arguments to the `add_files()` function:

```php
class HomePage_Controller extends Page_Controller {

	public function init() {
		parent::init();
	
		/* Will log 'loaded file' to console twice, once for each resource loaded */
		Yepnope::add_files(
			array(
				'themes/yourtheme/js/filea.js',
				'themes/yourtheme/js/fileb.js'
			),
			"console.log('loaded file')"
		);

		/* Will log 'loaded file' to console once, after both resources have been loaded */
		Yepnope::add_files(
			array(
				'themes/yourtheme/js/filea.js',
				'themes/yourtheme/js/fileb.js'
			),
			null,
			"console.log('loaded file')"
		);
	}

}
```

###Tests:###
You can make use of yepnope's _test_ functionality with the `add_test()` function. Arguments must be passed in the following order:

```php
Yepnope::add_test($test, $yep=null, $nope=null, $load=null, $callback=null, $complete=null)
```

* `$test` - The test to be evaluated
* `$yep` - The file (or array of files) to be loaded if the test returns _true_
* `$nope` - The file (or array of files) to be loaded if the test returns _false_
* `$callback` - The function to be called after each resource is loaded
* `$complete` - The function to be called once after all resources are loaded

Example tests:

```php
class HomePage_Controller extends Page_Controller {

	public function init() {
		parent::init();
	
		Yepnope::add_test(
			'Modernizr.geolocation',			// Test if geolocation functionality exists
			'regular-styles.css',				// If it does (test returns true), load regular styles
			array(								// If it doesn't (test returns false), load extra files
				'modified-styles.css',
				'geolocation-polyfill.js'
			),
			'standardfunctions.js',				// Load these files regardless of test outcome
			"console.log('loaded file')",		// Call this function upon loading each resource
			"console.log('all files loaded')"	// Call this function when all files have been loaded
		);
	}

}
```

###Customise:###
If you're already using yepnope or modernizr and don't need to include yepnope.js, you can remove the requirement (or set it to use a different script) with the following:

```php
class Page_Controller extends ContentController {

	public function init() {
		parent::init();
		
		Yepnope::set_yepnope(false) // Removes built-in requirement for yepnope.js script
		Yepnope::set_yepnope('themes/yourtheme/js/modernizr.min.js'); // Sets requirement to use a custom script
	}

}
```

###Tips:###
If your _tests_, _callback_ functions or _complete_ functions are quite long, then putting them in a PHP file is ugly and hard to maintain. One alternative is to store the raw javascript in a template (be sure to do a _?flush=1_) and load it using the following method:

```php
class Page_Controller extends ContentController {

	public function init() {
		parent::init();
		
		Yepnope::add_files('themes/simple/javascript/somescript.js', $this->renderWith('MyCallback'));
	}

}
```

MyCallback.ss would then contain your raw Javascript (not wrapped in any HTML tags or anything).

##TODO##

* Add support for prefixes (!css, !timeout etc)
* General cleaning, tidying and refactoring of code
* More testing