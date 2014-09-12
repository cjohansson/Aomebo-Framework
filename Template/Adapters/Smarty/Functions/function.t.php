<?php
/**
 * T smarty custom plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {t} function plugin
 *
 * Type:     function<br>
 * Name:     t<br>
 *
 * @param array $params
 * @return string|null
 */
function smarty_function_t($params)
{

    $defaultDomain =
        \Aomebo\Internationalization\System::getSiteDefaultTextDomain();

    if (is_array($params)
        && isset($params['message'])
    ) {

        $message = $params['message'];
        $domain = $defaultDomain;

        if (isset($params['domain'])) {
            $domain = $params['domain'];
        }

        if ($domain != $defaultDomain) {
            return \Aomebo\Internationalization\System::dgettext($domain, $message);
        } else {
            return \Aomebo\Internationalization\System::dgettext($defaultDomain, $message);
        }

    } else {
        return \Aomebo\Internationalization\System::dgettext($defaultDomain, $params);
    }

}
