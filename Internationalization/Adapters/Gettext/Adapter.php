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
namespace Aomebo\Internationalization\Adapters\PhpGettext
{

    /**
     * @method static \Aomebo\Internationalization\Adapters\PhpGettext\Adapter getInstance()
     * @see https://launchpad.net/php-gettext/
     */
    class Adapter extends \Aomebo\Internationalization\Adapters\Base
    {

        /**
         * @var string
         */
        const LIBRARY_DIR = 'php-gettext-1.0.11';

        /**
         * @return bool
         */
        public function init()
        {

            require_once(__DIR__ . DIRECTORY_SEPARATOR
                . self::LIBRARY_DIR . DIRECTORY_SEPARATOR . 'gettext.inc');

            $internationalizationSystem =
                \Aomebo\Internationalization\System::getInstance();

            $locale =
                $internationalizationSystem::getLocale();

            // Set language to locale
            T_setlocale(LC_ALL, $locale);

            $textDomains =
                $internationalizationSystem::getTextDomains();

            foreach ($textDomains as $textDomainName => $array)
            {
                T_bindtextdomain($textDomainName, $array[0]);
                T_bind_textdomain_codeset($textDomainName, $array[1]);
            }

        }

        /**
         * @param string $domain
         * @return bool
         */
        public function setDomain($domain)
        {
            if (!empty($domain)) {
                T_textdomain($domain);
            }
            return false;
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
            return T_gettext($message);
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
            return T_dgettext($domain, $message);
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
            return T_ngettext($msgid1, $msgid2, $n);
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
            return T_dcgettext($domain, $message, $category);
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
            return T_dngettext($domain, $msgid1, $msgid2, $n);
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
            return T_dcngettext($domain, $msgid1, $msgid2, $n, $category);
        }

    }

}
