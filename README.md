# Aomebo Framework

Aomebo Framework (AF) is a **open-source (OS) object-oriented (OO) PHP MVC (Model-View-Controller) framework**.

The framework is built to be compatible with all types of applications (even shell applications) and is fully scalable.
Only loads the stuff **you need** for your project and tries to place as few limits as possible on the developer.

The framework is built with time-, space- and memory-complexity in mind which makes it memory-efficient, CPU-efficient, storage-efficient and fast.

With optional features such as:

* Built-in concurrency-support, supports communication between requests, limit concurrent requests and load-queues (optional)
* Advanced and easy-to-use trigger and filter system (Like Wordpress) (optional)
* Internationalization and localization with adapters and supports multiple simulatenous text-domains (Gettext adapter like Wordpress for .mo and .po files and PHP) (optional)
* Multiple template adapters (Smarty 3, Twig, PHP) (optional)
* Support for Models, Controllers, Modules, Views, Libraries (Centralized MVC, Decentralized MVC or combined MVC design like Zend Framework)
* Dependency-support and automatic CSS and Javascript generation (optional)
* Built-in support for PHPUnit and Xdebug (optional)
* Works out of the box with APC
* Extensive support for URL-generation and routing (with or without mod_rewrite) (optional)
* Support for different database adapters, preparing, escaping and transactions and to run without database too (MySQLi and PDO) (optional)
* Cache-system for filesystem or database which supports raw, JSON or serialized data (optional)
* Feedback and debug functions (optional)
* Built-in support for dynamic indexing (optional)
* Built-in support for different session setups (filesystem, native PHP or database) (optional)
* **All this super-fast, even with database-support execution-times in a few hundred of miliseconds for large-scale sites**.

The framework has been successfully used for:

* Internationalized and localized sites.
* Internationalized and localized communities.
* Accounting and bookkeeping software.
* Internationalized and localized e-commerce systems.
* REST APIs.
* Blogs.
* Company homepages.

The framework is licensed by the Open license LGPL version 3.

## A easy example

1. Let's say we want to have the framework at `/usr/share/aomebo-framework/` and we have our public root at `/var/www/MyWebSite/public/`, we want our application to be located outside of public root at `/var/www/MyWebSite/private/`
2. Clone git repository `git clone https://github.com/cjohansson/Aomebo-Framework.git /usr/share/aomebo-framework/`
3. Create a `index.php` in your public root like this and tell Aomebo Framework where your applications private files are located by passing the `PARAMETER_SITE_PATH` parameter to the Aomebo Application constructor.

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

3. Create some modules

### Html
`/var/www/MyWebSite/private/Modules/Html/Module.php`

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
                new \Aomebo\Associatives\Dependent('jQuery'));
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

`/var/www/MyWebSite/private/Modules/Html/views/view.twig`

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

4. Create some pages

5. Try it

6. Add database connection

7. Try again

8. Create some routes
