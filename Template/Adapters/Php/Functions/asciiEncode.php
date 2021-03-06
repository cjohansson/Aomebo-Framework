<?php

/**
 * This method converts a string to a ASCII.
 *
 * @param string $string
 * @param bool [$toLowerCase = true]
 * @param string [$replaceWith = '_']
 * @return string
 */
function asciiEncode(
    $string, $toLowerCase = true, $replaceWith = '_')
{
    return \Aomebo\Dispatcher\System::formatUriComponent(
        $string, $toLowerCase, $replaceWith);
}
