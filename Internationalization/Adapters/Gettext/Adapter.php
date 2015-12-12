<?php
/**
 * Aomebo - a module-based MVC framework for PHP 5.3 and higher
 *
 * Copyright 2010 - 2015 by Christian Johansson <christian@cvj.se>
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

                // Is data not an array? (compatability with old caches)
                if (!is_array(self::$_languageToTranslations[$locale])) {
                    self::$_languageToTranslations[$locale] = array();
                }

            }
                
            if (sizeof(self::$_languageToTranslations[$locale]) == 0) {

                \Aomebo\Cache\System::clearCache(
                    $cacheParameters,
                    null,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                );

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

            if (!isset(self::$_languageToTranslations[$locale][$textDomain])) {
                self::$_languageToTranslations[$locale][$textDomain] =
                    new \Aomebo\Internationalization\Adapters\Gettext\Translations();
            }

            $accBool = true;

            if (is_dir($location)
                && file_exists(
                    $location . '/' . $textDomain . '-' . $locale . '.mo')
            ) {
                if (!self::_loadEntriesFromFile(
                    $location . '/' . $textDomain . '-' . $locale . '.mo',
                    $locale,
                    $textDomain)
                ) {
                    $accBool = false;
                }
            } else if (is_dir($location . '/' . $locale)
                && file_exists(
                    $location . '/' . $locale . '/' . $textDomain . '.mo')
            ) {
                if (!self::_loadEntriesFromFile(
                    $location . '/' . $locale . '/' . $textDomain . '.mo',
                    $locale,
                    $textDomain)
                ) {
                    $accBool = false;
                }
            } else if (is_dir($location . '/' . $defaultLocale)
                && file_exists($location . '/' . $defaultLocale . '/' . $textDomain . '.mo')
            ) {
                if (!self::_loadEntriesFromFile(
                    $location . '/' . $defaultLocale . '/' . $textDomain . '.mo',
                    $locale,
                    $textDomain)
                ) {
                    $accBool = false;
                }
            } else if (is_dir($location)
                && file_exists($location . '/' . $textDomain . '-' . $defaultLocale . '.mo')
            ) {
                if (!self::_loadEntriesFromFile(
                    $location . '/' . $textDomain . '-' . $defaultLocale . '.mo',
                    $locale,
                    $textDomain)
                ) {
                    $accBool = false;
                }
            } else {
                $accBool = false;
            }
            
            return $accBool;
            
        }

        /**
         * @internal
         * @param string $path
         * @param string $locale
         * @param string $textDomain
         * @return bool
         */
        private static function _loadEntriesFromFile($path, $locale, $textDomain)
        {

            $mo =
                new \Aomebo\Internationalization\Adapters\Gettext\MO();

            if ($mo->import_from_file($path)) {

                $ref = & self::$_languageToTranslations[$locale][$textDomain];
                /** @var \Aomebo\Internationalization\Adapters\Gettext\Translations $ref */

                foreach ($mo->entries as $entry)
                {
                    /** @var \Aomebo\Internationalization\Adapters\Gettext\Translation_Entry $entry */
                    $ref->add_entry($entry);
                }

                return true;

            } else {
                return false;
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
        public function gettext($message)
        {
            return $this->dgettext(
                \Aomebo\Internationalization\System::getTextDomain(),
                $message
            );
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
            if (isset(self::$_translations[$domain])) {
                $ref = & self::$_translations[$domain];
                /** @var \Aomebo\Internationalization\Adapters\Gettext\Translations $ref */
                return $ref->translate($message);
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
         * @param string $singular
         * @param string $plural
         * @param int $count
         * @return string
         * @see ngettext()
         */
        public function ngettext($singular, $plural, $count)
        {
            return self::$_translations->translate_plural($singular, $plural, $count);
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
         * @param string|null [$context = null]
         * @return string
         * @see dcgettext()
         */
        public function dcgettext($domain, $message, $context = null)
        {
            if (isset(self::$_translations[$domain])) {
                $ref = & self::$_translations[$domain];
                /** @var \Aomebo\Internationalization\Adapters\Gettext\Translations $ref */
                return $ref->translate($message, $context);
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
         * @param string $singular
         * @param string $plural
         * @param int $count
         * @return string
         * @see dngettext()
         */
        public function dngettext($domain, $singular, $plural, $count)
        {
            if (isset(self::$_translations[$domain])) {
                $ref = & self::$_translations[$domain];
                /** @var \Aomebo\Internationalization\Adapters\Gettext\Translations $ref */
                return $ref->translate_plural($singular, $plural, $count);
            }
            return ($count > 1 ? $plural : $singular);
        }

        /**
         * Plural version of dcgettext.
         *
         * This function allows you to override the current
         * domain for a single plural message lookup.
         *
         * @static
         * @param string $domain
         * @param string $singular
         * @param string $plural
         * @param int $count
         * @param string|null [$context = null]
         * @return string
         * @see dcngettext()
         */
        public function dcngettext($domain, $singular, $plural, $count, $context = null)
        {
            if (isset(self::$_translations[$domain])) {
                $ref = & self::$_translations[$domain];
                /** @var \Aomebo\Internationalization\Adapters\Gettext\Translations $ref */
                return $ref->translate_plural($singular, $plural, $count, $context);
            }
            return ($count > 1 ? $plural : $singular);
        }

        /**
         * Answers whether adapter has data for a specific text-domain.
         *
         * @param string $domain
         * @return bool
         */
        public function hasEntriesForTextDomain($domain)
        {
            return (!empty($domain)
                && isset(self::$_translations[$domain]->entries)
                && sizeof(self::$_translations[$domain]->entries) > 0
            );
        }

    }

}
