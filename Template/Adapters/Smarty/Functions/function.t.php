<?php
/**
 * Translate smarty custom plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {translate} function plugin
 *
 * Type:     function<br>
 * Name:     translate<br>
 *
 * @param array $params
 * @return string|null
 */
function smarty_function_translate($params)
{

    $defaultDomain =
        \Aomebo\Internationalization\System::getTextDomain();

    if (is_array($params)
        && isset($params['message'])
    ) {

        $message = $params['message'];
        $domain = $defaultDomain;

        if (isset($params['domain'])) {
            $domain = $params['domain'];
        }

        if ($domain == $defaultDomain) {
            return \Aomebo\Internationalization\System::gettext($message);
        } else {
            return \Aomebo\Internationalization\System::dgettext($domain, $message);
        }

    } else {
        return \Aomebo\Internationalization\System::gettext($params);
    }

}
