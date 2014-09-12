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
         * @return bool
         */
        public function init()
        {

            $textDomains =
                \Aomebo\Internationalization\System::getTextDomains();

            $locale =
                \Aomebo\Internationalization\System::getLocale();
            $defaultLocale =
                \Aomebo\Internationalization\System::getDefaultLocale();

            self::$_translations =
                new \Aomebo\Internationalization\Adapters\Gettext\Translations();

            foreach ($textDomains as $textDomainName => $array)
            {
                if (is_dir($array[0] . '/' . $locale)) {
                    if ($scandir = scandir($array[0] . '/' . $locale)) {
                        foreach ($scandir as $file)
                        {

                            $path = $array[0] . '/' . $locale . '/' . $file;

                            if (is_file($path)
                                && strtolower(substr($path, -3)) == '.mo'
                            ) {

                                $context = substr($file, 0, strrpos($file, '.'));

                                $mo = new \Aomebo\Internationalization\Adapters\Gettext\MO();
                                if ($mo->import_from_file($path)) {

                                    foreach ($mo->entries as $entry)
                                    {

                                        /** \Aomebo\Internationalization\Adapters\Gettext\Translation_Entry $entry */

                                        $entry->context = $context;
                                        self::$_translations->add_entry($entry);

                                    }

                                }

                            } else {

                                $path = $array[0] . '/' . $defaultLocale . '/' . $file;

                                if (is_file($path)
                                    && strtolower(substr($path, -3)) == '.mo'
                                ) {

                                    $context = substr($file, 0, strrpos($file, '.'));
                                    $mo = new \Aomebo\Internationalization\Adapters\Gettext\MO();

                                    if ($mo->import_from_file($path)) {

                                        foreach ($mo->entries as $entry)
                                        {

                                            /** \Aomebo\Internationalization\Adapters\Gettext\Translation_Entry $entry */

                                            $entry->context = $context;
                                            self::$_translations->add_entry($entry);

                                        }

                                    }

                                }

                            }

                        }
                    }
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
