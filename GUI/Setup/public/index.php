<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

require_once(dirname(dirname(dirname(__DIR__)))
    . DIRECTORY_SEPARATOR
    . 'Application.php');

new \Aomebo\Application(
    array(
        \Aomebo\Application::PARAMETER_SITE_PATH =>
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'private',
    )
);
