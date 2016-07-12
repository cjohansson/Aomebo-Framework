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

            if (\Aomebo\Application::isInhibitedToConstruct(
                $calledClass)
            ) {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate('Class %s may not be constructed at this time in the execution chain. Please adjust your runtime configuration.'),
                        $calledClass
                    )
                );
            }

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
         * @static
         * @param string|null [$className = null]
         * @return Singleton
         */
        public static function getInstance($className = null)
        {

            if (empty($className)) {
                $className = get_called_class();
            }

            if (!self::_isInstanciated($className)) {
                $newObject = new $className();
                if (!self::_isInstanciated($className)) {
                    self::$_instances[$className] = $newObject;
                }
            }

            return self::$_instances[$className];

        }

        /**
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
            return (is_array(self::$_isConstructed)
                && !empty(self::$_isConstructed[$calledClass]));
        }

    }

}
