<?php

/**
 * @param string $message
 * @param string|null [$domain = null]
 * @return string
 */
function t($message, $domain = null)
{

    $defaultDomain =
        \Aomebo\Internationalization\System::getSiteDefaultTextDomain();

    if (!empty($domain)
        && $domain != $defaultDomain
    ) {
        return \Aomebo\Internationalization\System::dgettext($domain, $message);
    } else {
        return \Aomebo\Internationalization\System::dgettext($defaultDomain, $message);
    }

}
