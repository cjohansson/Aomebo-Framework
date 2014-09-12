<?php

/**
 * @param string $message
 * @param string|null [$domain = null]
 * @return string
 */
function translate($message, $domain = null)
{

    $defaultDomain =
        \Aomebo\Internationalization\System::getTextDomain();

    if (!empty($domain)
        && $domain != $defaultDomain
    ) {
        return \Aomebo\Internationalization\System::dgettext($domain, $message);
    } else {
        return \Aomebo\Internationalization\System::gettext($message);
    }

}
