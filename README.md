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
class Page_Controller extends ContentController {

	public function init() {
		parent::init();
	
		$myFiles = array(
			'themes/yourtheme/js/filea.js',
			'themes/yourtheme/js/fileb.js'
		);

		/* Will log 'loaded file' to console twice, once for each resource loaded */
		Yepnope::add_files($myFiles, "console.log('loaded file')");

		/* Will log 'loaded file' to console once, after both resources have been loaded */
		Yepnope::add_files($myFiles, null, "console.log('loaded file')");

		/* Will log 'loaded file' twice and 'loaded all files' once to the console */
		Yepnope::add_files($myFiles, "console.log('loaded file')", "console.log('loaded all files')");
	}

}
```

###Tests:###
You can make use of yepnope's _test_ functionality with the `add_test()` function. Arguments must be passed in the following order:

```php
Yepnope::add_test($test, $yep=null, $nope=null, $load=null, $callback=null, $complete=null);
```

* `$test` - The test to be evaluated
* `$yep` - The file (or array of files) to be loaded if the test returns _true_
* `$nope` - The file (or array of files) to be loaded if the test returns _false_
* `$load` - The file (or array of files) to be loaded regardless of whether the test reuturns true or false
* `$callback` - The function to be called after each resource is loaded
* `$complete` - The function to be called once after all resources are loaded
* `$id` - The unique identifier for the test - see below

Example test:

```php
class Page_Controller extends ContentController {

	public function init() {
		parent::init();
	
		Yepnope::add_test(
			'Modernizr.geolocation',			// Test if geolocation functionality exists
			'regular-styles.css',				// If it does (test returns true), load regular style
			'geolocation-polyfill.js',			// If it doesn't (test returns false), load extra file
			'standardfunctions.js',				// Load these files regardless of test outcome
			"console.log('loaded file')",		// Call this function upon loading each resource
			"console.log('all files loaded')"	// Call this function when all files have been loaded
		);
	}

}
```

The lists of files passed to the function can be either strings, arrays or `null` (for example, if no extra files are needed for the 'yep' argument).

###Clear files & tests:###
Both the `add_files()` and `add_test()` methods take an optional, last argument (4th and 7th arguments respectively) for a unique identifier. This identifier can then be used to remove a test on certain pages. In the example below, let's assume you have files you want to load on every page _except_ pages with the 'ContactPage' page type:

```php
// Page.php
class Page_Controller extends ContentController {

	public function init() {
		parent::init();
	
		$myFiles = array(
			'themes/yourtheme/js/filea.js',
			'themes/yourtheme/js/fileb.js'
		);

		Yepnope::add_files($myFiles, null, null, 'MyFiles'); // Set the id 'MyFiles'
	}

}

// ContactPage.php
class ContactPage_Controller extends Page_Controller {

	public function init() {
		parent::init();

		Yepnope::clear_test('MyFiles'); // Clear the test with id 'MyFiles'
	}

}
```

###Resource callback labels:###
Since version 1.5 of yepnope.js, keys to be used in callbacks are automatically generated from the basename of the file. If you require keys to be used in your callback functions, please use these generated keys as per the example below:

```js
yepnope({
	load: ["https:/­/my-cdn.com/jquery.min.js?v=1.7.1", "https:/­/my-cdn.com/jquery-ui.min.js?v=1.8.16"],
	callback: {
		"jquery.min.js": function () {
			console.log("jquery loaded!");
		},
		"jquery-ui.min.js": function () {
			console.log("jquery-ui loaded!");
		}
	}
});
```

###Customise:###
If you're already using yepnope or modernizr and don't need to include yepnope.js, you can remove the requirement (or set it to use a different script) with the following:

```php
class Page_Controller extends ContentController {

	public function init() {
		parent::init();
		
		Yepnope::set_yepnope(false) // Removes built-in requirement for yepnope.js script
		Yepnope::set_yepnope('themes/yourtheme/js/modernizr.min.js'); // Set to use a custom script
	}

}
```

If you wish to specify a custom error timeout length (yepnope's default is 10 seconds) you can use the `set_timeout()` method. Note that the time is set in milliseconds:

```php
class Page_Controller extends ContentController {

	public function init() {
		parent::init();
		
		Yepnope::add_files('themes/yourtheme/js/yourjavascript.js');
		Yepnope::set_timeout(2000); // Sets error timeout to be 2 seconds
	}

}
```

###Tips:###
If your _tests_, _callback_ functions or _complete_ functions are quite long, then putting them in a PHP file is ugly and hard to maintain. One alternative is to store the raw javascript in a template (be sure to do a _?flush=1_ after creating any templates) and load it using the following method:

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