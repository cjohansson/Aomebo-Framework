<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsModifier
 */

/**
 * Smart Ascii Encode modifier plugin
 *
 * Type:     modifier
 * Name:     escape
 * Purpose:  escape string for output
 *
 * @see http://www.aomebo.org/ or https://github.com/cjohansson/Aomebo-Framework
 * @param string  $string
 * @param bool [$toLowerCase = true]
 * @param string [$replaceWith = '_']
 * @return string           modified string
 */
function smarty_modifier_asciiEncode(
    $string, $toLowerCase = true, $replaceWith = '_')
{
    return \Aomebo\Dispatcher\System::formatUriComponent(
        $string, $toLowerCase, $replaceWith, \Smarty::$_CHARSET);
}
