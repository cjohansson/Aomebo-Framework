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
namespace Aomebo\Associatives
{


    /**
     *
     */
    class Dependent extends \Aomebo\Base
    {

        /**
         * @var int
         */
        const TYPE_ASSOCIATIVE = 0;

        /**
         * @var int
         */
        const TYPE_MODEL = 1;

        /**
         * @var int
         */
        const TYPE_MODULE = 2;

        /**
         * @var int
         */
        const TYPE_CONTROLLER = 3;

        /**
         * Case insensitive name of depedent.
         *
         * @var string
         */
        public $name;

        /**
         * Type of depedent.
         *
         * @var int
         */
        public $type = self::TYPE_ASSOCIATIVE;

        /**
         * @param string|null [$name = null]
         * @param int|null [$type = null]
         */
        public function __construct($name = null, $type = null)
        {
            if (isset($name)) {
                $this->name = $name;
            }
            if (isset($type)) {
                $this->type = $type;
            }
        }

        /**
         * @return bool
         */
        public function isValid()
        {
            return ($this->_isValidType($this->type)
                && !empty($this->name));
        }

        /**
         * @return bool
         */
        public function isAvailable()
        {
        }

        /**
         * @param int $type
         * @return bool
         */
        private function _isValidType($type)
        {
            return (isset($type)
                && ($type == self::TYPE_ASSOCIATIVE
                || $type == self::TYPE_CONTROLLER
                || $type == self::TYPE_MODEL
                || $type == self::TYPE_MODULE));
        }

    }

}
