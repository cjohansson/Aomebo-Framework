<?php

require_once(dirname(dirname(dirname(__DIR__)))
    . DIRECTORY_SEPARATOR
    . 'Application.php');

new \Aomebo\Application(
    array(
        \Aomebo\Application::PARAMETER_SITE_PATH =>
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'private',
    )
);
