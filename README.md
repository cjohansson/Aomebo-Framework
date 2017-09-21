# Aomebo Framework

Aomebo Framework (AF) is a **open-source (OS) object-oriented (OO) PHP: Hypertext Preprocessor (PHP) Model-View-Controller (MVC) framework**.

The framework is built to be compatible with all types of applications (even shell applications) and is fully scalable.
Uses lazy loading to only loads the stuff **you need** for your project and tries to place as few limits as possible on the developer. The main idea of the framework is to enable swift development by using common and easy programming patterns and supporting a wide variety of programming styles.

It only requires PHP 5.3 or later with PHP default modules.

The framework is built with time-, space- and memory-complexity in mind which makes it memory-efficient, CPU-efficient, storage-efficient and fast.

With optional features such as:

* Built-in concurrency-support, supports communication between requests, limit concurrent requests (optional)
* Advanced and easy-to-use queued trigger and filter system (Like Wordpress) (optional)
* Internationalization and localization with adapters and supports multiple simulatenous text-domains and languages (Gettext adapter like Wordpress for .mo and .po files and PHP) (optional)
* Multiple template adapters built-in (Smarty 3, Twig, PHP) (optional)
* Support for Models, Controllers, Modules, Views, Libraries (Centralized MVC, Decentralized MVC or combined MVC design like Zend Framework)
* Dependency-support and automatic CSS and Javascript generation (optional)
* Works out of the box with APC, Xdebug and PHPUnit
* Extensive support for URL-generation and routing (with or without mod_rewrite) (optional)
* Support for different database adapters, preparing, escaping and transactions and to run without database too (MySQLi and PDO) (optional)
* Cache-system for filesystem or database which supports raw, JSON or serialized data (optional)
* Feedback and debug functions (optional)
* Built-in support for dynamic sitemap indexing (optional)
* Built-in support for different session setups (filesystem, native PHP or database) (optional)
* **All this super-fast, even with database-support and cache disabled execution-times around 30ms to a few hundred of miliseconds for large-scale sites**.

The framework has been successfully used for:

* Internationalized and localized sites
* Internationalized and localized communities
* Accounting and bookkeeping software
* Internationalized and localized e-commerce systems
* REST APIs
* Blogs
* Company homepages

The framework is licensed by the Open license LGPL version 3.

## A easy example

This example should take about 15min to implement and should give you a basic site to start with that uses jQuery and Bootstrap via CDN.

1. Let's say we want to store the framework at `/usr/share/aomebo-framework/` and we have our public root at `/var/www/MyWebSite/public/`. We want our application to be located outside of public root at `/var/www/MyWebSite/private/`
2. Clone git repository `git clone https://github.com/cjohansson/Aomebo-Framework.git /usr/share/aomebo-framework/`
3. Create a `index.php` in your public root like this to tell Aomebo Framework where your applications private files are located by passing the `PARAMETER_SITE_PATH` parameter to the Aomebo Application constructor like this

Let's say that this is `/var/www/MyWebSite/public/index.php`
``` php
<?php
require_once('/usr/share/aomebo-framework/Application.php');
new \Aomebo\Application(
    array(
        \Aomebo\Application::PARAMETER_SITE_PATH =>
            '/var/www/MyWebSite/private/',
    )
);
```

3. Create the application

We are going to create **5** modules: *Html*, *Header*, *Footer*, *Wrapper*, *Blog* and **2** client-side dependencies. All paths from now on will be relative to **/var/www/MyWebSite/**. We will be using jQuery, Bootstrap and the Twig template system.

### Dependencies

#### jQuery

`private/Dependencies/jQuery/Dependency.php`

``` php
<?php
/**
 *
 */

/**
 *
 */
namespace Dependencies\jQuery
{

    /**
     *
     */
    class Dependency extends \Aomebo\Associatives\Dependency
    {

        /**
         * @var array
         */
        protected $_options = array(
            'inline.html' => array(
                'mode' => parent::MODE_INLINE,
            ),
        );

    }
}
```

`private/Dependencies/jQuery/inline.html`

``` html
<script src="http://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
```

#### Bootstrap


### Modules

#### Html
`private/Modules/Html/Module.php`

``` php
<?php
/**
 *
 */

/**
 *
 */
namespace Modules\Html
{

    /**
     * @method static \Modules\Html\Module getInstance()
     */
    class Module extends \Aomebo\Runtime\Module implements
        \Aomebo\Runtime\Executable,
        \Aomebo\Runtime\ExecutionParameters,
        \Aomebo\Runtime\Dependent
    {

	    /**
	     * @static
	     * @var string
	     */
	    private static $_title;

        /**
         * @return array|bool
         */
        public function getDependencies()
        {
            return array(
	            new \Aomebo\Associatives\Dependent('jQuery'),
	            new \Aomebo\Associatives\Dependent('Bootstrap'),
            );
        }

        /**
         * @return array|bool
         */
        public function getParameters()
        {
	        return array('title', 'body');
        }

        /**
         * @static
         * @param string $title
         */
        public static function setTitle($title)
        {
	        self::$_title = $title;
        }

        /**
         * @static
         * @return string
         */
        public static function getTitle()
        {
	        return self::$_title;
        }

        /**
         * @return bool|mixed|string
         */
        public function execute()
        {
	        if (empty(self::$_title)) {
		        self::$_title = $this->getField('title');
	        }
	        $view = self::_getTwigView();
	        $view->setFile('views/view.twig');
	        $view->attachVariables(array(
		        'title' => self::$_title,
		        'body' => $this->getField('body'),
	        ));
            return $view->parse();
        }

    }
}
```

`private/Modules/Html/views/view.twig`

``` twig
<html>
    <head>
	<title>{{ title }}</title>
    </head>
    <body>
	{{ body|raw }}
    </body>
</html>
```

`private/Modules/Html/Associatives/script.js`

``` javascript
$(document).ready(function(event) {
console.log('Site started');
});

```

`private/Modules/Html/Associatives/style.css`

``` css
body
{
}
```

#### Header

`private/Modules/Header/Module.php`

``` php
<?php
namespace Modules\Header
{

    /**
     * @method static \Modules\Header\Module getInstance()
     */
    class Module extends \Aomebo\Runtime\Module implements
        \Aomebo\Runtime\Executable
    {

        /**
         * @return bool|mixed|string
         */
        public function execute()
        {
	        $view = self::_getTwigView();
            $view->setFile('views/view.twig');
            $view->attachVariables(array(
	            'title' => \Aomebo\Configuration::getSetting('framework,name'),
	            'version' => \Aomebo\Configuration::getSetting('framework,version'),
            ));
            return $view->parse();
        }

    }
}
```

`private/Modules/Header/views/view.twig`

``` twig
<div id="{{ F }}">
    &copy; {{ year }}
    <br /><a href="{{ website }}" target="_blank">{{ website }}</a>
</div>
```

#### Footer

`private/Modules/Footer/Module.php`

``` php
<?php
namespace Modules\Footer
{

    /**
     * @method static \Modules\Footer\Module getInstance()
     */
    class Module extends \Aomebo\Runtime\Module implements
        \Aomebo\Runtime\Executable
    {

        /**
         * @return string
         */
        public function execute()
        {
            $view = self::_getTwigView();
            $view->setFile('views/view.twig');
            $view->attachVariables(array(
                'website' => \Aomebo\Configuration::getSetting('framework,website'),
                'year' => date('Y'),
            ));
            return $view->parse();
        }

    }

}
```

`private/Modules/Footer/views/view.twig`

``` twig
<div id="{{ F }}" class="text-center">
    &copy; {{ year }}
    <br /><a href="{{ website }}" target="_blank">{{ website }}</a>
</div>
```

`private/Modules/Footer/Associatives/style.css`

``` css
#footer {
    margin: 60px 0 40px;
}
```

#### Wrapper

4. Create some pages

5. Try it

6. Add database connection

7. Try again

8. Create some routes

9. Try again

10. Done
