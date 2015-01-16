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
namespace Aomebo\Internationalization\Adapters
{

    /**
     * @method static \Aomebo\Internationalization\Adapters\Base getInstance()
     */
    abstract class Base extends \Aomebo\Singleton
    {

        /**
         * @param array|null [$textDomains = null]
         * @param string|null [$locale = null]
         * @param string|null [$defaultLocale = null]
         * @return bool
         */
        abstract public function initLocale($textDomains = null, 
            $locale = null, $defaultLocale = null);

        /**
         * @param array $textDomains
         * @param string|null [$locale = null]
         * @param string|null [$defaultLocale = null]
         * @return bool
         */
        abstract public function loadTextDomains($textDomains, 
            $locale = null, $defaultLocale = null);

        /**
         * @param array $textDomains
         * @param string $location
         * @param string|null [$locale = null]
         * @param string|null [$defaultLocale = null]
         * @return bool
         */
        abstract public function loadTextDomain($textDomains,
            $location, $locale = null, $defaultLocale = null);

        /**
         * @param string|null [$locale = null]
         * @return bool
         */
        abstract public function setLocale($locale = null);

        /**
         * Lookup a message in the current domain.
         *
         * @static
         * @param string $message
         * @return string
         * @see gettext()
         */
        abstract public function gettext($message);

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
        abstract public function dgettext($domain, $message);

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
        abstract public function ngettext($msgid1, $msgid2, $n);

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
        abstract public function dcgettext($domain, $message, $category);

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
        abstract public function dngettext($domain, $msgid1, $msgid2, $n);

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
        abstract public function dcngettext($domain, $msgid1, $msgid2, $n, $category);

    }

}
