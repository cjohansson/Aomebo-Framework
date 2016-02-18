<?php
/**
 * Url smarty custom plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {url} function plugin
 *
 * Type:     function<br>
 * Name:     url<br>
 *
 * @param array $params
 * @return string|null
 */
function smarty_function_url($params)
{

    $getArray = array();
    foreach ($params as $key => $value)
    {
        if (!empty($key)
            && substr($key, 0, 1) != '_'
        ) {
            $getArray[$key] = $value;
        }
    }

    $page = (!empty($params['_page']) ? $params['_page'] : '');
    $clear = (isset($params['_clear']) ?
        (!empty($params['_clear']) ? true : false)
        : true);
    $default = (!empty($params['_default']) ? true : false);
    $full = (!empty($params['_full']) ? true : false);

    if ($default) {
        if ($full) {
            return \Aomebo\Dispatcher\System::
                buildDefaultFullUri();
        } else {
            return \Aomebo\Dispatcher\System::
                buildDefaultUri();
        }
    } else {
        if ($full) {
            return \Aomebo\Dispatcher\System::
                buildFullUri($getArray, $page, $clear);
        } else {
            return \Aomebo\Dispatcher\System::
                buildUri($getArray, $page, $clear);
        }
    }

}
