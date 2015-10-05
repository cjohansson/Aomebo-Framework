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
     *
     */
    class NOOP_Translations
    {

        /**
         * @var array
         */
        var $entries = array();

        /**
         * @var array
         */
        var $headers = array();

        /**
         * @param string $entry
         * @return bool
         */
        public function add_entry($entry)
        {
            return true;
        }

        /**
         * @param string $header
         * @param string $value
         */
        public function set_header($header, $value) {}

        /**
         * @param array $headers
         */
        public function set_headers($headers) {}

        /**
         * @param string $header
         * @return bool
         */
        public function get_header($header)
        {
            return false;
        }

        /**
         * @param array $entry
         * @return bool
         */
        public function translate_entry(&$entry)
        {
            return false;
        }

        /**
         * @param $singular
         * @param null $context
         * @return mixed
         */
        public function translate($singular, $context = null)
        {
            return $singular;
        }

        /**
         * @param $count
         * @return int
         */
        public function select_plural_form($count) {
            return 1 == $count? 0 : 1;
        }

        /**
         * @return int
         */
        public function get_plural_forms_count()
        {
            return 2;
        }

        /**
         * @param $singular
         * @param $plural
         * @param $count
         * @param null $context
         * @return mixed
         */
        public function translate_plural($singular, $plural, $count, $context = null)
        {
            return 1 == $count? $singular : $plural;
        }

        /**
         * @param $other
         */
        public function merge_with(&$other) {}

    }

}
