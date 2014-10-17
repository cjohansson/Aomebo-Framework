<?php

global $configuration;

$configuration = array (
  'framework' => 
  array (
    'use runtime cache' => true,
    'use dependencies cache' => true,
    'use associatives cache' => true,
  ),
  'dispatch' => 
  array (
    'uri pages' => 
    array (
      'index' => 'index',
      '404_file_not_found' => 'file_not_found',
      'index.php?XDEBUG_SESSION_START=oqMeo1k2031' => 'index',
      'index.php?XDEBUG_SESSION_STOP=oqMeo1k2031' => 'index',
    ),
    'pages uri' => 
    array (
      'index' => 'index',
      'file_not_found' => '404_file_not_found',
    ),
    'default page' => 'index',
    'file not found page' => 'file_not_found',
    'allow only associatives request with matching referer' => false,
  ),
  'database' => 
  array (
    'host' => 'localhost',
    'username' => 'aomebo',
    'password' => 'A0m3b0',
    'database' => 'aomebo_testing',
    'site table prefix' => 'site_',
  ),
  'feedback' => 
  array (
    'error reporting' => 32767,
    'display errors' => true,
    'display startup errors' => true,
    'log errors' => true,
    'error log' => 'Logs/errors.txt',
    'debug mode' => false,
    'display runtime exceptions' => true,
  ),
  'session' => 
  array (
    'always use session' => true,
  ),
  'site' => 
  array (
    'salt' => 'mkamkem2ok2o1k',
    'title' => 'Aomebo Framework Setup',
    'title delimiter' => ' - ',
    'title direction' => 'prepend',
    'slogan' => 'The MVC framework',
    'shortcut icon' => 'images/shortcut-icon.ico',
    'show generator' => false,
    'description' => '',
    'keywords' => 'etc',
    'default time-zone' => 'Europe/Stockholm',
    'session-handler' => '',
    'protocol' => 'http',
    'protocol version' => '1.1,',
    'server name' => 'aomebo.cvj.se',
    'mod_rewrite' => true,
  ),
  'internationalization' => 
  array (
    'enabled' => true,
    'locale' => 'en_US',
    'site text domains' => 
    array (
      'site' => 
      array (
        0 => 'Language',
        1 => 'UTF-8',
      ),
    ),
    'default site text domain' => 'site',
  ),
  'output' => 
  array (
    'minify stylesheets' => true,
    'minify javascripts' => false,
  ),
  'paths' => 
  array (
    'basedirs' => 
    array (
      0 => '/var/www/aomebo.cvj.se/GUI/Setup/',
    ),
  ),
);
