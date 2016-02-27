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
namespace Aomebo
{

    /**
     *
     */
    class Base
    {

        /**
         *
         */
        public function __construct()
        {
        }

        /**
         *
         */
        public function __destruct()
        {
        }

        /**
         * @static
         * @param string $key
         * @param null|mixed [$default = null]
         * @return mixed
         */
        protected static function _getSetting($key, $default = null)
        {
            $value =\Aomebo\Configuration::getSetting($key, false);
            return ($value !== null ? $value : $default);
        }

        /**
         * @static
         * @param string $message
         * @param string|null [$domain = null]
         * @return string
         */
        public static function systemTranslate($message, $domain = null)
        {
            return \Aomebo\Internationalization\System::systemTranslate(
                $message, $domain);
        }

        /**
         * @return string
         */
        public function getAbsoluteFilename()
        {
            $reflector = new \ReflectionObject($this);
            return $reflector->getFilename();
        }

    }

}
