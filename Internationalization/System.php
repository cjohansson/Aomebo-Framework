<?php
/**
 * Aomebo - a module-based MVC framework for PHP 5.3+
 *
 * Copyright (C) 2010+ Christian Johansson <christian@cvj.se>
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
 * @see http://www.aomebo.org
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
         * @static
         * @var bool
         */
        private static $_enabled = false;

        /**
         * @static
         * @var string
         */
        private static $_locale = '';

        /**
         * @static
         * @var array
         */
        private static $_textDomains = array();

        /**
         * @static
         * @var string
         */
        private static $_adapter = '';

        /**
         * @static
         * @var array
         */
        private static $_adapters = array();

        /**
         * @static
         * @var \Aomebo\Internationalization\Adapters\Base|null
         */
        private static $_adapterClass;

        /**
         * @throws \Exception
         */
        public function __construct()
        {

            parent::__construct();

            if (!$this->_isConstructed()) {

                self::setEnabled(
                    \Aomebo\Configuration::getSetting('internationalization,enabled'));
                self::setLocale(
                    \Aomebo\Configuration::getSetting('internationalization,locale'));

                $textDomains = array();

                if ($systemTextDomains =
                    \Aomebo\Configuration::getSetting(
                        'internationalization,system text domains')
                ) {
                    foreach ($systemTextDomains as $key => $array)
                    {
                        if (isset($array)
                            && is_array($array)
                            && isset($array[0], $array[1])
                            && is_dir(_SYSTEM_ROOT_ . $array[0])
                        ) {
                            $textDomains[$key] = array(
                                _SYSTEM_ROOT_ . $array[0],
                                $array[1],
                            );
                        } else {
                            Throw new \Exception(
                                'Invalid internationalization "'
                                . print_r($array, true) . '"'
                            );
                        }
                    }
                }

                if ($siteTextDomains =
                    \Aomebo\Configuration::getSetting(
                        'internationalization,site text domains')
                ) {
                    foreach ($siteTextDomains as $key => $array)
                    {
                        if (isset($array)
                            && is_array($array)
                            && isset($array[0], $array[1])
                            && is_dir(_PRIVATE_ROOT_ . $array[0])
                        ) {
                            $textDomains[$key] = array(
                                _PRIVATE_ROOT_ . $array[0],
                                $array[1]
                            );
                        } else {
                            Throw new \Exception(
                                'Invalid internationalization "'
                                . print_r($array, true) . '"'
                            );
                        }
                    }
                }

                if (sizeof($textDomains) > 0) {
                    self::setTextDomains($textDomains);
                }

                self::setAdapter(
                    \Aomebo\Configuration::getSetting('internationalization,adapter'));

                $this->_flagThisConstructed();

                if (self::isEnabled()) {
                    self::_init();
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
            return '';
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
            return '';
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
            return '';
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
            return '';
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
            return '';
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
            return '';
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
         */
        public static function setLocale($locale)
        {
            self::$_locale = $locale;
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
         * @param array $textDomains
         */
        public static function setTextDomains($textDomains)
        {
            foreach ($textDomains as $key => $location)
            {
                self::$_textDomains[$key] = $location;
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
         * @return bool
         */
        public static function setTextDomain($domain)
        {
            if (isset(self::$_adapterClass)) {
                self::$_adapterClass->setDomain($domain);
                return true;
            }
            return false;
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
         * @throws \Exception
         * @return bool
         */
        private static function _init()
        {

            self::_loadAdapters();

            if (self::adapterExists(self::$_adapter)) {

                $className = '\\Aomebo\\Internationalization\\Adapters\\'
                    . self::$_adapters[self::$_adapter] . '\\Adapter';

                /** @var \Aomebo\Internationalization\Adapters\Base $classObj */
                $classObj = new $className();
                $classObj->init();

                self::$_adapterClass = $classObj;

                return true;

            } else {
                Throw new \Exception(
                    'Adapter "' . self::$_adapter . '" does not exists in system,'
                    . ' in ' . __FILE__);
            }
        }

        /**
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
         * @static
         * @return string
         */
        private static function _getAdaptersDirectory()
        {
            return __DIR__ . DIRECTORY_SEPARATOR . 'Adapters';
        }

    }

}
