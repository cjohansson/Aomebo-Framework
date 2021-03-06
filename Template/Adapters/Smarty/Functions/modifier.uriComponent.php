<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsModifier
 */

/**
 * Smarty Uri Component modifier plugin
 *
 * Type:     modifier
 * Name:     escape
 * Purpose:  format uri component
 *
 * @see http://www.aomebo.org/ or https://github.com/cjohansson/Aomebo-Framework
 * @param string $string
 * @param bool [$toLowerCase = true]
 * @param string [$replaceWith = '_']
 * @return string                           modified string
 */
function smarty_modifier_uriComponent(
    $string, $toLowerCase = true, $replaceWith = '-')
{
    return \Aomebo\Dispatcher\System::formatUriComponent(
        $string, $toLowerCase, $replaceWith, \Smarty::$_CHARSET);
}
