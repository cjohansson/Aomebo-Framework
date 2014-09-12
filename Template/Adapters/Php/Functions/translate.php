<?php

/**
 * @param array|null [$getArray = null]
 * @param string [$page = '']
 * @param bool [$clear = false]
 * @return string
 */
function uri($getArray = null, $page = '', $clear = false)
{
    $dispatcher =
        \Aomebo\Dispatcher\System::getInstance();
    return $dispatcher->buildUri($getArray, $page, $clear);
}
