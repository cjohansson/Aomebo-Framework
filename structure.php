<?php

global $configuration;

$configuration = array(
    'framework' =>
        array(
            'name' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Aomebo Framework',
                ),
            'version' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => '1.0',
                ),
            'website' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'http://www.aomebo.org',
                ),
            'contact' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'info@aomebo.org',
                ),
            'show credits' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'show statistics' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'use runtime cache' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'use dependencies cache' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'use associatives cache' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'auto-install' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),      
        ),
    'interpreter' => 
        array(
            'convert_xml_pages_to_php' => 
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
        ),
    'indexing' =>
        array(
            'expiration days' =>
                array(
                    'type' => 'integer',
                    'required' => true,
                    'default' => 180,
                ),
            'enabled' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'index query string uris using mod_rewrite' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'index query string uris' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'save content' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
        ),
    'application' =>
        array(
            'memory required' =>
                array(
                    'type' => 'integer',
                    'required' => true,
                    'default' => 4,
                ),
            'maximum concurrent requests' =>
                array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 0,
                ),
            'maximum concurrent requests period' =>
                array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 10,
                ),
            'auto-install all runtimes' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'auto-install specific runtimes' =>
                array(
                    'type' => 'array',
                    'required' => true,
                    'default' => array(),
                ),            
        ),
    'paths' =>
        array(
            'resources dir' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Resources',
                ),
            'resources dir is absolute' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'uploads dir' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Uploads',
                ),
            'pages dir' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Pages',
                ),
            'uploads dir is absolute' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'associatives dir' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Associatives',
                ),
            'default file mod' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => '770',
                ),
            'file owner username' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'www-data',
                ),
            'file owner groupname' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'www-data',
                ),
            'runtime site directories' =>
                array(
                    'type' => 'array',
                    'required' => true,
                    'default' =>
                        array(
                            0 => 'Modules',
                            1 => 'Models',
                            2 => 'Controllers',
                        ),
                ),
            'runtime public directories' =>
                array(
                    'type' => 'array',
                    'required' => true,
                    'default' =>
                        array(),
                ),
            'create runtime directories' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'create associatives directories' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'basedirs' =>
                array(
                    'type' => 'array',
                    'required' => true,
                    'default' =>
                        array(
                            0 => '/',
                        ),
                ),
        ),
    'internationalization' =>
        array(
            'enabled' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'locale' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'en_US',
                ),
            'default locale' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'en_US',
                ),
            'system text domains' =>
                array(
                    'type' => 'array',
                    'required' => false,
                    'default' =>
                        array(
                            'framework' => 'Locale',
                        ),
                ),
            'site text domains' =>
                array(
                    'type' => 'array',
                    'required' => false,
                    'default' =>
                        array(),
                ),
            'adapter' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Gettext',
                ),
            'default system text domain' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'framework',
                ),
            'default site text domain' =>
                array(
                    'type' => 'string',
                    'required' => false,
                    'default' => 'site',
                ),
        ),
    'output' =>
        array(
            'format' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'chunk size' =>
                array(
                    'type' => 'unsigned integer',
                    'required' => true,
                    'default' => 512,
                ),
            'character set' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'utf-8',
                ),
            'linebreak character' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => "\n",
                ),
            'tab character' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => '    ',
                ),
            'language' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'en',
                ),
            'doctype' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'xhtml 1.0 strict',
                ),
            'default dateformat' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Y-m-d H:i:s',
                ),
            'show credits' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'show statistics' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'module cache' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'indexing enabled' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'minify javascripts' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'minify stylesheets' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'associatives cache' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'add resources cache tag' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'add resources statistics tag' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'add resources sections' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'autoload failure triggers exception' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'favicon directs to site shortcut icon' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'output headers in shell mode' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'mime' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'text/html',
                ),
        ),
    'settings' =>
        array(
            'ajax mode' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'ajax',
                ),
            'associatives mode' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'associatives',
                ),
        ),
    'feedback' =>
        array(
            'error reporting' =>
                array(
                    'type' => 'unsigned integer',
                    'required' => true,
                    'default' => 32767,
                ),
            'display errors' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'display startup errors' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'log errors' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'error log' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'error_log.txt',
                ),
            'debug mode' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'include backtrace' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'backtrace limit' =>
                array(
                    'type' => 'integer',
                    'required' => true,
                    'default' => 0,
                ),
            'halt on runtime exceptions' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'halt on runtime construct exceptions' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'log runtime exceptions' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'display runtime exceptions' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'truncate error log' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'truncate error log size' =>
                array(
                    'type' => 'integer',
                    'required' => true,
                    'default' => 1048576,
                ),
            'dump environment variables' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
        ),
    'session' =>
        array(
            'table' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'sessions',
                ),
            'blocks table' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'sessions_blocks_data',
                ),
            'cookie key' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'aoe',
                ),
            'cookie delimiter' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => '_',
                ),
            'cookie path' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => '/',
                ),
            'expires' =>
                array(
                    'type' => 'unsigned integer',
                    'required' => true,
                    'default' => 1800,
                ),
            'handler' =>
                array(
                    'type' => 'string',
                    'required' => false,
                    'default' => '',
                ),
            'always use session' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'renew existing session' =>
                array(
                    'normal' =>
                        array(
                            'type' => 'boolean',
                            'required' => true,
                            'default' => true,
                        ),
                    'ajax' =>
                        array(
                            'type' => 'boolean',
                            'required' => true,
                            'default' => false,
                        ),
                ),
            'garbage collect on page requests' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => false,
                ),
            'garbage collect on shell requests' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
        ),
    'dispatch' =>
        array(
            'pages uri' =>
                array(
                    'type' => 'associative array',
                    'required' => true,
                    'default' =>
                        array(
                            'index' => 'index.php',
                        ),
                ),
            'uri pages' =>
                array(
                    'type' => 'associative array',
                    'required' => true,
                    'default' => array(
                        'home' => 'index',
                    ),
                ),
            'default page' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'index',
                ),
            'page adapter' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Xml',
                ),
            'file not found page' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'file_not_found',
                ),
            'redirect to file not found page' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'error page' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'error.html',
                ),
            'allow shell requests' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'allow only associatives request with matching referer' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'allow ajax get requests' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'allow ajax post requests' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'page syntax regexp' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => '/^([a-zA-Z\\_\\-]+)$/',
                ),
            'use default page for uris starting with question-mark' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
            'use default page for invalid page syntax uris' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => false,
                ),
        ),
    'site' =>
        array(
            'class path' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Site.php',
                ),
            'salt' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'oiwe012ake',
                ),
            'title' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'New Aomebo Framework Application',
                ),
            'title delimiter' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => ' - ',
                ),
            'title direction' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'prepend',
                ),
            'slogan' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Because it\'s worth it',
                ),
            'shortcut icon' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'shortcut_icon.ico',
                ),
            'show generator' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => true,
                ),
            'description' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Here you\'ll find information about this brand new site',
                ),
            'keywords' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'new site, aomebo site',
                ),
            'default time-zone' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'Europe/London',
                ),
            'protocol' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'http',
                ),
            'protocol version' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => '1.1',
                ),
            'server name' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'localhost',
                ),
            'mod_rewrite' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
        ),
    'templates' =>
        array(
            'variables' =>
                array(
                    'type' => 'array',
                    'required' => false,
                    'default' =>
                        array(),
                ),
        ),
    'database' =>
        array(
            'create database' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'system table prefix' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'aomebo_',
                ),
            'site table prefix' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'site_',
                ),
            'adapter' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'mysqli',
                ),
            'version' =>
                array(
                    'type' => 'string',
                    'required' => false,
                    'default' => 5,
                ),
            'storage engine' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'MyISAM',
                ),
            'host' =>
                array(
                    'type' => 'string',
                    'required' => false,
                    'default' => 'localhost',
                ),
            'username' =>
                array(
                    'type' => 'string',
                    'required' => false,
                    'default' => '',
                ),
            'password' =>
                array(
                    'type' => 'string',
                    'required' => false,
                    'default' => '',
                ),
            'database' =>
                array(
                    'type' => 'string',
                    'required' => false,
                    'default' => '',
                ),
            'options' =>
                array(
                    'type' => 'array',
                    'required' => false,
                ),
            'ansi quotes' =>
                array(
                    'type' => 'boolean',
                    'required' => true,
                    'default' => false,
                ),
            'data charset' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'utf8',
                ),
            'collate charset' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'utf8_general_ci',
                ),
            'handle charset' =>
                array(
                    'type' => 'string',
                    'required' => true,
                    'default' => 'utf8',
                ),
        ),
    'cache' =>
        array(
            'expiration time' =>
                array(
                    'type' => 'unsigned integer',
                    'required' => true,
                    'default' => 2592000,
                ),
            'garbage collect on page requests' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => false,
                ),
            'garbage collect on shell requests' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
            'garbage collect limit' =>
                array(
                    'type' => 'unsigned integer',
                    'required' => true,
                    'default' => 500,
                ),
            'garbage collect minimum interval' =>
                array(
                    'type' => 'integer',
                    'required' => true,
                    'default' => 9600,
                ),
        ),
    'responses' =>
        array(
            'associatives' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
            'favicon' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
            'page' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
            'shell' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
            'test' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
            'bootstrap' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
            'php' =>
                array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
        ),
        'php_responses' => array(
            'type' => 'array',
            'required' => false,
            'default' => array(),
        ),
);

