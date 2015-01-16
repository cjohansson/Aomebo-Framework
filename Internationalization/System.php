<?php
/**
 * Aomebo - a module-based MVC framework for PHP 5.3 and higher
 *
 * Copyright 2010 - 2014 by Christian Johansson <christian@cvj.se>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 * @license LGPL version 3
 * @see http://www.aomebo.org/ or https://github.com/cjohansson/Aomebo-Framework
 */

/**
 *
 */
namespace Aomebo\Internationalization
{

    /**
     * @method static \Aomebo\Internationalization\System getInstance()
     */
    class System extends \Aomebo\Singleton
    {

        /**
         * @internal
         * @static
         * @var bool|null
         */
        private static $_enabled = null;

        /**
         * @internal
         * @static
         * @var string|null
         */
        private static $_locale = null;

        /**
         * @internal
         * @static
         * @var string|null
         */
        private static $_defaultLocale = null;

        /**
         * @internal
         * @static
         * @var array|null
         */
        private static $_textDomains = null;

        /**
         * @internal
         * @static
         * @var string|null
         */
        private static $_defaultSystemTextDomain = null;

        /**
         * @internal
         * @static
         * @var string|null
         */
        private static $_defaultSiteTextDomain = null;


        /**
         * @internal
         * @static
         * @var string|null
         */
        private static $_adapter = null;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_adapters = array();

        /**
         * @internal
         * @static
         * @var \Aomebo\Internationalization\Adapters\Base|null
         */
        private static $_adapterClass = null;

        /**
         * @internal
         * @static
         * @var string|null
         */
        private static $_textDomain = null;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_loadedTextDomains = false;

        /**
         * @throws \Exception
         */
        public function __construct()
        {

            parent::__construct();

            if (!$this->_isConstructed()) {

                \Aomebo\Trigger\System::processTriggers(
                    \Aomebo\Trigger\System::TRIGGER_KEY_BEFORE_INTERNATIONALIZATION_LOAD
                );

                if (!isset(self::$_enabled)) {
                    self::setEnabled(
                        \Aomebo\Configuration::getSetting('internationalization,enabled'));
                }
                if (!isset(self::$_locale)) {
                    self::setLocale(
                        \Aomebo\Configuration::getSetting('internationalization,locale'));
                }
                if (!isset(self::$_defaultLocale)) {
                    self::setDefaultLocale(
                        \Aomebo\Configuration::getSetting('internationalization,default locale'));
                }
                if (!isset(self::$_defaultSystemTextDomain)) {
                    self::setSystemDefaultTextDomain(
                        \Aomebo\Configuration::getSetting('internationalization,default system text domain'));
                }
                if (!isset(self::$_defaultSiteTextDomain)) {
                    self::setSiteDefaultTextDomain(
                        \Aomebo\Configuration::getSetting('internationalization,default site text domain'));
                }

                if (!isset(self::$_textDomains)) {

                    $textDomains = array();

                    if ($systemTextDomains =
                        \Aomebo\Configuration::getSetting(
                            'internationalization,system text domains')
                    ) {
                        foreach ($systemTextDomains as $path)
                        {

                            if (is_array($path)
                                && isset($path[0])
                            ) {
                                $path = $path[0];
                            }

                            if (isset($path)
                                && !is_array($path)
                            ) {

                                $domainPath = _SYSTEM_ROOT_ . $path;

                                if (is_dir($domainPath)) {
                                    $textDomains[] = $domainPath;
                                } else {
                                    Throw new \Exception(
                                        'Invalid internationalization "'
                                        . $path . '", '
                                        . 'no directory found at "'
                                        . $domainPath . '".'
                                    );
                                }

                            }
                        }
                    }

                    if ($siteTextDomains =
                        \Aomebo\Configuration::getSetting(
                            'internationalization,site text domains')
                    ) {
                        foreach ($siteTextDomains as $path)
                        {

                            if (is_array($path)
                                && isset($path[0])
                            ) {
                                $path = $path[0];
                            }

                            if (isset($path)
                                && !is_array($path)
                            ) {

                                $domainPath = _SITE_ROOT_ . $path;

                                if (is_dir($domainPath)) {
                                    $textDomains[] = $domainPath;
                                } else {
                                    Throw new \Exception(
                                        sprintf(
                                            __('Invalid internationalization "%s", '
                                            . 'no directory found at "%s".'),
                                            $path,
                                            $domainPath
                                        )
                                    );
                                }

                            }
                        }
                    }

                    if (sizeof($textDomains) > 0) {
                        self::setTextDomains($textDomains);
                    }

                }

                if (!isset(self::$_adapter)) {
                    self::setAdapter(
                        \Aomebo\Configuration::getSetting('internationalization,adapter'));
                }

                \Aomebo\Trigger\System::processTriggers(
                    \Aomebo\Trigger\System::TRIGGER_KEY_AFTER_INTERNATIONALIZATION_LOAD
                );

                $this->_flagThisConstructed();

                if (self::isEnabled()) {
                    self::init();
                }

            }
        }

        /**
         * Lookup a message in the current domain.
         *
         * @static
         * @param string $message
         * @return string
         * @see gettext()
         */
        public static function gettext($message)
        {
            if (self::$_adapterClass) {
                return self::$_adapterClass->gettext($message);
            }
            return $message;
        }

        /**
         * @static
         * @param string $directory
         * @return array
         */
        public static function getLocalesFromDirectory($directory)
        {
            $locales = array();
            if (is_dir($directory)) {
                if ($items = scandir($directory)) {
                    foreach ($items as $item)
                    {
                        if ($item != '.'
                            && $item != '..'
                            && is_dir($directory . '/' . $item)
                        ) {
                            if (preg_match(
                                '/^[a-z]{2}\_[A-Z]{2}$/',
                                $item) === 1
                            ) {
                                $locales[] = $item;
                            }
                        }
                    }
                }
            }
            return $locales;
        }

        /**
         * @static
         * @param string $message
         * @param string|null [$domain = null]
         * @return string
         * @see gettext()
         */
        public static function siteTranslate($message, $domain = null)
        {
            if (!isset($domain)) {
                $domain = self::$_defaultSiteTextDomain;
            }
            if ($triggerMessage = \Aomebo\Trigger\System::processTriggers(
                \Aomebo\Trigger\System::TRIGGER_KEY_INTERNATIONALIZATION_TRANSLATE,
                array($message, $domain))
            ) {
                return $triggerMessage;
            }
            if (self::$_adapterClass) {
                return self::$_adapterClass->dgettext($domain, $message);
            }
            return $message;
        }

        /**
         * @static
         * @param string $message
         * @param string|null [$domain = null]
         * @return string
         * @see gettext()
         */
        public static function systemTranslate($message, $domain = null)
        {
            if (!isset($domain)) {
                $domain = self::$_defaultSystemTextDomain;
            }
            if ($triggerMessage = \Aomebo\Trigger\System::processTriggers(
                \Aomebo\Trigger\System::TRIGGER_KEY_INTERNATIONALIZATION_TRANSLATE,
                array($message, $domain))
            ) {
                return $triggerMessage;
            }
            if (self::$_adapterClass) {
                return self::$_adapterClass->dgettext($domain, $message);
            }
            return $message;
        }

        /**
         * Override the current domain.
         *
         * The dgettext() function allows you to override the current
         * domain for a single message lookup.
         *
         * @param string $domain
         * @param string $message
         * @return string
         * @see dgettext()
         */
        public static function dgettext($domain, $message)
        {
            if (self::$_adapterClass) {
                return self::$_adapterClass->dgettext(
                    $domain, $message);
            }
            return $message;
        }

        /**
         * Plural version of gettext.
         *
         * The plural version of gettext(). Some languages have more than
         * one form for plural messages dependent on the count.
         *
         * @static
         * @param string $msgid1
         * @param string $msgid2
         * @param int $n
         * @return string
         * @see ngettext()
         */
        public static function ngettext($msgid1, $msgid2, $n)
        {
            if (self::$_adapterClass) {
                return self::$_adapterClass->ngettext(
                    $msgid1, $msgid2, $n);
            }
            return ($n > 1 ? $msgid2 : $msgid1);
        }

        /**
         * Overrides the domain for a single lookup.
         *
         * This function allows you to override the current
         * domain for a single message lookup.
         *
         * @static
         * @param string $domain
         * @param string $message
         * @param int $category
         * @return string
         * @see dcgettext()
         */
        public static function dcgettext($domain, $message, $category)
        {
            if (self::$_adapterClass) {
                return self::$_adapterClass->dcgettext(
                    $domain, $message, $category);
            }
            return $message;
        }

        /**
         * Plural version of dgettext.
         *
         * The dngettext() function allows you to override
         * the current domain for a single plural message lookup.
         *
         * @static
         * @param string $domain
         * @param string $msgid1
         * @param string $msgid2
         * @param int $n
         * @return string
         * @see dngettext()
         */
        public static function dngettext($domain, $msgid1,
            $msgid2, $n)
        {
            if (self::$_adapterClass) {
                return self::$_adapterClass->dngettext(
                    $domain, $msgid1, $msgid2, $n);
            }
            return ($n > 1 ? $msgid2 : $msgid1);
        }

        /**
         * Plural version of dcgettext.
         *
         * This function allows you to override the current
         * domain for a single plural message lookup.
         *
         * @static
         * @param string $domain
         * @param string $msgid1
         * @param string $msgid2
         * @param int $n
         * @param int $category
         * @return string
         * @see dcngettext()
         */
        public static function dcngettext($domain, $msgid1,
            $msgid2, $n, $category)
        {
            if (self::$_adapterClass) {
                return self::$_adapterClass->dcngettext(
                    $domain, $msgid1, $msgid2, $n, $category);
            }
            return ($n > 1 ? $msgid2 : $msgid1);
        }

        /**
         * @static
         * @param bool $enabled
         */
        public static function setEnabled($enabled)
        {
            self::$_enabled = (!empty($enabled));
        }

        /**
         * @static
         * @return bool
         */
        public static function isEnabled()
        {
            return (!empty(self::$_enabled));
        }

        /**
         * @static
         * @param string $locale
         * @return bool
         */
        public static function setLocale($locale)
        {
            
            self::$_locale = $locale;
            
            // Are text-domains loaded already?
            if (self::$_loadedTextDomains) {
                return self::$_adapterClass->setLocale($locale);
            } else {
                return true;
            }
            
        }

        /**
         * @static
         * @param string $textDomain
         */
        public static function setSystemDefaultTextDomain($textDomain)
        {
            self::$_defaultSystemTextDomain = $textDomain;
        }

        /**
         * @static
         * @return string
         */
        public static function getSystemDefaultTextDomain()
        {
            return self::$_defaultSystemTextDomain;
        }

        /**
         * @static
         * @param string $textDomain
         */
        public static function setSiteDefaultTextDomain($textDomain)
        {
            self::$_defaultSiteTextDomain = $textDomain;
        }

        /**
         * @static
         * @return string
         */
        public static function getSiteDefaultTextDomain()
        {
            return self::$_defaultSiteTextDomain;
        }

        /**
         * @static
         * @param string $defaultLocale
         */
        public static function setDefaultLocale($defaultLocale)
        {
            self::$_defaultLocale = $defaultLocale;
        }

        /**
         * @static
         * @return string
         */
        public static function getLocale()
        {
            return self::$_locale;
        }

        /**
         * @static
         * @return string
         */
        public static function getDefaultLocale()
        {
            return self::$_defaultLocale;
        }

        /**
         * @static
         * @param string $domain
         * @param string $location
         */
        public static function addTextDomain($domain, $location)
        {
            
            self::$_textDomains[$domain] = $location;
            
            // Are textdomains loaded already?
            if (self::$_loadedTextDomains) {                
                self::$_adapterClass->loadTextDomain($domain, $location);
            }
            
        }

        /**
         * @static
         * @param array $textDomains
         */
        public static function setTextDomains($textDomains)
        {
            foreach ($textDomains as $domain => $location)
            {
                self::addTextDomain($domain, $location);
            }
        }

        /**
         * @static
         * @param string $domain
         * @return bool
         */
        public static function textDomainExists($domain)
        {
            if (!empty($domain)
                && isset(self::$_textDomains[$domain])
            ) {
                return true;
            }
            return false;
        }

        /**
         * @static
         * @return array
         */
        public static function getTextDomains()
        {
            return self::$_textDomains;
        }

        /**
         * @static
         * @return string
         */
        public static function getTextDomain()
        {
            return self::$_textDomain;
        }

        /**
         * @static
         * @param string $adapter
         */
        public static function setAdapter($adapter)
        {
            self::$_adapter = strtolower($adapter);
        }

        /**
         * @static
         * @return string
         */
        public static function getAdapter()
        {
            return self::$_adapter;
        }

        /**
         * @static
         * @return array
         */
        public static function getAdapters()
        {
            return self::$_adapters;
        }

        /**
         * @static
         * @param string $domain
         */
        public static function setTextDomain($domain)
        {
            self::$_textDomain = $domain;
        }

        /**
         * @static
         * @param string $adapter
         * @return bool
         */
        public static function adapterExists($adapter)
        {
            return (isset(self::$_adapters[$adapter]));
        }

        /**
         * @static
         */
        public static function init()
        {

            self::_loadAdapters();

            if (self::adapterExists(self::$_adapter)) {

                $className = '\\Aomebo\\Internationalization\\Adapters\\'
                    . self::$_adapters[self::$_adapter] . '\\Adapter';

                try
                {

                    /** @var \Aomebo\Internationalization\Adapters\Base $classObj */
                    $classObj = new $className();
                    $classObj->initLocale();
                    $classObj->setLocale(self::getLocale());

                    self::$_adapterClass = $classObj;
                    self::$_loadedTextDomains = true;

                } catch (\Exception $e) {

                    \Aomebo\FeedBack\Debug::output(
                        sprintf(
                            __('Failed to init internationalization adapter "%s", error: "%s".'),
                            self::$_adapter,
                            $e->getMessage()
                        ),
                        false,
                        true
                    );

                }

            }
        }

        /**
         * @internal
         * @static
         */
        private static function _loadAdapters()
        {
            $dir = self::_getAdaptersDirectory();
            if ($scandir = scandir($dir)) {
                foreach ($scandir as $item)
                {
                    $relPath =
                        $dir . DIRECTORY_SEPARATOR . $item;
                    if ($item != '.'
                        && $item != '..'
                        && is_dir($relPath)
                    ) {

                        $adapterPath =
                            $relPath . DIRECTORY_SEPARATOR . 'Adapter.php';

                        if (file_exists($adapterPath)
                            && is_file($adapterPath)
                        ) {
                            $cisName = strtolower($item);
                            self::$_adapters[$cisName] = $item;
                        }

                    }

                }
            }
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getAdaptersDirectory()
        {
            return __DIR__ . DIRECTORY_SEPARATOR . 'Adapters';
        }

    }

}

/**
 * Global namespace
 */
namespace
{

    if (!function_exists('__')) {

        /**
         * @param string $message
         * @param string|null [$domain = null]
         * @param string|null [$category = null]
         * @return string
         */
        function __($message, $domain = null, $category = null)
        {
            return \Aomebo\Internationalization\System::siteTranslate(
                $message,
                $domain
            );
        }

    }

    if (!function_exists('translate')) {

        /**
         * @param string $message
         * @param string|null [$domain = null]
         * @param string|null [$category = null]
         * @return string
         */
        function translate($message, $domain = null, $category = null)
        {
            return \Aomebo\Internationalization\System::siteTranslate(
                $message,
                $domain
            );
        }

    }

    if (!function_exists('t')) {

        /**
         * @param string $message
         * @param string|null [$domain = null]
         * @param string|null [$category = null]
         * @return string
         */
        function t($message, $domain = null, $category = null)
        {
            return \Aomebo\Internationalization\System::siteTranslate(
                $message,
                $domain
            );
        }

    }

    if (!function_exists('_e')) {

        /**
         * @param string $message
         * @param string|null [$domain = null]
         * @param string|null [$category = null]
         * @return string
         */
        function _e($message, $domain = null, $category = null)
        {
            echo \Aomebo\Internationalization\System::siteTranslate(
                $message,
                $domain
            );
        }

    }

}
