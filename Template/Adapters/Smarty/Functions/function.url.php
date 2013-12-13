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
    $clear = (!empty($params['_clear']) ? true : false);
    $default = (!empty($params['_default']) ? true : false);
    $full = (!empty($params['_full']) ? true : false);

    /** @var \Aomebo\Dispatcher\System $dispatcher  */
    $dispatcher = \Aomebo\Dispatcher\System::getInstance();

    if ($default) {
        if ($full) {
            return $dispatcher->buildDefaultFullUri();
        } else {
            return $dispatcher->buildDefaultUri();
        }
    } else {
        if ($full) {
            return $dispatcher->buildFullUri($getArray, $page, $clear);
        } else {
            return $dispatcher->buildUri($getArray, $page, $clear);
        }
    }

}
