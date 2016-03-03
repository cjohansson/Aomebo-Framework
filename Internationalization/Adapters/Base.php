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
         * @param array $textDomain
         * @param string $location
         * @param string|null [$locale = null]
         * @param string|null [$defaultLocale = null]
         * @return bool
         */
        abstract public function loadTextDomain($textDomain,
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
         * @param string $singular
         * @param string $plural
         * @param int $count
         * @return string
         * @see ngettext()
         */
        abstract public function ngettext($singular, $plural, $count);

        /**
         * Overrides the domain for a single contextual lookup.
         *
         * This function allows you to override the current
         * domain for a single contextual message lookup.
         *
         * @static
         * @param string $domain
         * @param string $message
         * @param string|null [$context = null]
         * @return string
         * @see dcgettext()
         */
        abstract public function dcgettext($domain, $message, $context = null);

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
        abstract public function dngettext($domain, $singular, $plural, $count);

        /**
         * Plural version of dcgettext.
         *
         * This function allows you to override the current
         * domain for a single contextual plural message lookup.
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
        abstract public function dcngettext($domain, $singular, $plural, $count, $context = null);

        /**
         * Answers whether adapter has data for a specific text-domain.
         *
         * @param string $domain
         * @return bool
         */
        abstract public function hasEntriesForTextDomain($domain);

    }

}
