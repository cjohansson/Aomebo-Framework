<?php

/**
 * @param array|null [$getArray = null]
 * @param string [$page = '']
 * @param bool [$clear = true]
 * @return string
 */
function uri($getArray = null, $page = '', $clear = true)
{
    return \Aomebo\Dispatcher\System::buildUri(
        $getArray, $page, $clear);
}
