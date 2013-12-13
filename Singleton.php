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
namespace Aomebo
{

    /**
     *
     */
    class Singleton extends \Aomebo\Base
    {

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_instances;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_isConstructed;

        /**
         *
         */
        public function __construct()
        {

            parent::__construct();

            $calledClass = get_called_class();

            if (!is_array(self::$_instances)) {
                self::$_instances = array();
            }

            if (!is_array(self::$_isConstructed)) {
                self::$_isConstructed = array();
            }

            if (!self::_isInstanciated($calledClass)) {
                self::$_instances[$calledClass] = & $this;
            }

        }

        /**
         *
         */
        public function __destruct()
        {
            parent::__destruct();
        }

        /**
         * @internal
         * @static
         * @return Singleton
         */
        public static function getInstance()
        {
            $calledClass = get_called_class();

            if (!self::_isInstanciated($calledClass)) {
                $newObject = new $calledClass();
                if (!self::_isInstanciated($calledClass)) {
                    self::$_instances[$calledClass] = $newObject;
                }
            }

            return self::$_instances[$calledClass];
        }

        /**
         * @internal
         * @static
         * @param string|null [$className = null]
         * @return bool
         */
        protected static function _isInstanciated($className = null)
        {
            $calledClass = (!empty($className) ? $className : get_called_class());
            return isset(self::$_instances[$calledClass]);
        }

        /**
         * @static
         * @param string|null [$className = null]
         */
        protected static function _flagThisConstructed($className = null)
        {
            $calledClass = (!empty($className) ? $className : get_called_class());
            self::$_isConstructed[$calledClass] = true;
        }

        /**
         * @static
         * @param string|null [$className = null]
         * @return bool
         */
        protected static function _isConstructed($className = null)
        {
            $calledClass = (!empty($className) ? $className : get_called_class());
            return (!empty(self::$_isConstructed[$calledClass]));
        }

    }

}
