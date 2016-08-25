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

## Setup

1. Clone framework anywhere on your server `git clone https://github.com/cjohansson/Aomebo-Framework.git /usr/share/aomebo-framework/`
2. Create a `index.php` in your public root like this and tell Aomebo Framework where your applications private files are located.

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

4. Create some pages

5. Try it

6. Add database connection

7. Try again
