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
namespace Aomebo\Internationalization\Adapters\Gettext
{

    /**
     * @method static \Aomebo\Internationalization\Adapters\Gettext\Adapter getInstance()
     */
    class Adapter extends \Aomebo\Internationalization\Adapters\Base
    {

        /**
         * @internal
         * @static
         * @var null|\Aomebo\Internationalization\Adapters\Gettext\Translations
         */
        private static $_translations = null;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_languageToTranslations = array();

        /**
         * @internal
         * @static
         * @var null|string
         */
        private static $_currentLocale = null;

        /**
         * @param array|null [$textDomains = null]
         * @param string|null [$locale = null]
         * @param string|null [$defaultLocale = null]
         * @return bool
         */
        public function initLocale($textDomains = null, 
            $locale = null, $defaultLocale = null)
        {
            
            if (!isset($textDomains)) {
                $textDomains =
                    \Aomebo\Internationalization\System::getTextDomains();
            }
            if (!isset($locale)) {
                $locale =
                    \Aomebo\Internationalization\System::getLocale();
            }
            if (!isset($defaultLocale)) {
                $defaultLocale =
                    \Aomebo\Internationalization\System::getDefaultLocale();
            }

            $lastModificationTime = 0;

            foreach ($textDomains as $textDomain => $location)
            {
                $dirtime =
                    \Aomebo\Filesystem::getDirectoryLastModificationTime(
                        $location, 
                        true, 
                        2, 
                        false
                    );
                if ($dirtime > $lastModificationTime)
                {
                    $lastModificationTime = $dirtime;
                }
            }

            $cacheParameters = 'Internationalization/Gettext/' . $locale . '/' . $defaultLocale;
            $cacheKey = $lastModificationTime;
            
            self::$_languageToTranslations[$locale] = array();
            
            if (\Aomebo\Cache\System::cacheExists(
                $cacheParameters,
                $cacheKey,
                \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM)
            ) {

                self::$_languageToTranslations[$locale] =
                    \Aomebo\Cache\System::loadCache(
                        $cacheParameters,
                        $cacheKey,
                        \Aomebo\Cache\System::FORMAT_SERIALIZE,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                );
                
            } else {

                \Aomebo\Cache\System::clearCache(
                    $cacheParameters,
                    null,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                );

                self::$_languageToTranslations[$locale] =
                    new \Aomebo\Internationalization\Adapters\Gettext\Translations();
                
                $this->loadTextDomains($textDomains);

                \Aomebo\Cache\System::saveCache(
                    $cacheParameters,
                    $cacheKey,
                    self::$_languageToTranslations[$locale],
                    \Aomebo\Cache\System::FORMAT_SERIALIZE,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                );

            }
        }

        /**
         * @param string|null [$locale = null]
         * @return bool
         */
        public function setLocale($locale = null)
        {
            if (!isset($locale)) {
                $locale =
                    \Aomebo\Internationalization\System::getLocale();
            }
            if (!isset(self::$_languageToTranslations[$locale])) {
                $this->initLocale(null, $locale, null);
            }
            self::$_translations = & self::$_languageToTranslations[$locale];
            self::$_currentLocale = $locale;
            return true;
        }

        /**
         * @param array $textDomains
         * @param string|null [$locale = null]
         * @param string|null [$defaultLocale = null]
         * @return bool
         */
        public function loadTextDomains($textDomains, 
            $locale = null, 
            $defaultLocale = null)
        {
            
            if (!isset($locale)) {
                $locale =
                    \Aomebo\Internationalization\System::getLocale();
            }
            if (!isset($defaultLocale)) {
                $defaultLocale =
                    \Aomebo\Internationalization\System::getDefaultLocale();
            }

            $accBool = true;
            
            foreach ($textDomains as $textDomain => $location)
            {
                $response = $this->loadTextDomain(
                    $textDomain,
                    $location,
                    $locale,
                    $defaultLocale
                );
                $accBool = ($accBool && $response);
            }
            
            return $accBool;
            
        }

        /**
         * @param string $textDomain
         * @param string $location
         * @param string|null [$locale = null]
         * @param string|null [$defaultLocale = null]
         * @return bool
         */
        public function loadTextDomain($textDomain, $location,  
            $locale = null, 
            $defaultLocale = null)
        {

            if (!isset($locale)) {
                $locale =
                    \Aomebo\Internationalization\System::getLocale();
            }
            if (!isset($defaultLocale)) {
                $defaultLocale =
                    \Aomebo\Internationalization\System::getDefaultLocale();
            }

            $accBool = true;

            if (is_dir($location)
                && file_exists(
                    $location . '/' . $textDomain . '-' . $locale . '.mo')
            ) {

                $path = $location . '/' . $textDomain . '-' . $locale . '.mo';

                $mo = new \Aomebo\Internationalization\Adapters\Gettext\MO();
                if ($mo->import_from_file($path)) {

                    foreach ($mo->entries as $entry)
                    {

                        /** @var \Aomebo\Internationalization\Adapters\Gettext\Translation_Entry $entry */

                        $entry->context = $textDomain;

                        $ref = & self::$_languageToTranslations[$locale];
                        /** @var \Aomebo\Internationalization\Adapters\Gettext\Translations $ref */
                        $ref->add_entry($entry);

                    }

                } else {
                    $accBool = false;
                }
            } else if (is_dir($location . '/' . $locale)
                && file_exists(
                    $location . '/' . $locale . '/' . $textDomain . '.mo')
            ) {

                $path = $location . '/' . $locale . '/' . $textDomain . '.mo';

                $mo = new \Aomebo\Internationalization\Adapters\Gettext\MO();
                if ($mo->import_from_file($path)) {

                    foreach ($mo->entries as $entry)
                    {

                        /** @var \Aomebo\Internationalization\Adapters\Gettext\Translation_Entry $entry */

                        $entry->context = $textDomain;
                        
                        $ref = & self::$_languageToTranslations[$locale];
                        /** @var \Aomebo\Internationalization\Adapters\Gettext\Translations $ref */
                        $ref->add_entry($entry);

                    }

                } else {
                    $accBool = false;
                }
            } else if (is_dir($location . '/' . $defaultLocale)
                && file_exists($location . '/' . $defaultLocale . '/' . $textDomain . '.mo')
            ) {
                
                $path = $location . '/' . $defaultLocale . '/' . $textDomain . '.mo';

                $mo = new \Aomebo\Internationalization\Adapters\Gettext\MO();

                if ($mo->import_from_file($path)) {

                    foreach ($mo->entries as $entry)
                    {

                        /** @var \Aomebo\Internationalization\Adapters\Gettext\Translation_Entry $entry */

                        $entry->context = $textDomain;

                        $ref = & self::$_languageToTranslations[$locale];
                        /** @var \Aomebo\Internationalization\Adapters\Gettext\Translations $ref */
                        $ref->add_entry($entry);

                    }

                } else {
                    $accBool = false;
                }
            } else if (is_dir($location)
                && file_exists($location . '/' . $textDomain . '-' . $defaultLocale . '.mo')
            ) {

                $path = $location . '/' . $textDomain . '-' . $defaultLocale . '.mo';

                $mo = new \Aomebo\Internationalization\Adapters\Gettext\MO();

                if ($mo->import_from_file($path)) {

                    foreach ($mo->entries as $entry)
                    {

                        /** @var \Aomebo\Internationalization\Adapters\Gettext\Translation_Entry $entry */

                        $entry->context = $textDomain;

                        $ref = & self::$_languageToTranslations[$locale];
                        /** @var \Aomebo\Internationalization\Adapters\Gettext\Translations $ref */
                        $ref->add_entry($entry);

                    }

                } else {
                    $accBool = false;
                }
            } else {
                $accBool = false;
            }
            
            return $accBool;
            
        }

        /**
         * Lookup a message in the current domain.
         *
         * @static
         * @param string $message
         * @return string
         * @see gettext()
         */
        public function gettext($message)
        {
            return self::$_translations->translate($message);
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
        public function dgettext($domain, $message)
        {
            return self::$_translations->translate($message, $domain);
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
        public function ngettext($msgid1, $msgid2, $n)
        {
            return self::$_translations->translate_plural($msgid1, $msgid2, $n);
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
        public function dcgettext($domain, $message, $category)
        {
            return self::$_translations->translate($message, $domain);
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
        public function dngettext($domain, $msgid1, $msgid2, $n)
        {
            return self::$_translations->translate_plural($msgid1, $msgid2, $n, $domain);
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
        public function dcngettext($domain, $msgid1, $msgid2, $n, $category)
        {
            return self::$_translations->translate_plural($msgid1, $msgid2, $n, $domain);
        }

    }

}
