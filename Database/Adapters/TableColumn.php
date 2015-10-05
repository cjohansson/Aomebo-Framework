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
namespace Aomebo\Database\Adapters
{

    /**
     *
     */
    class TableColumn
    {

        /**
         * @var string
         */
        public $name = '';

        /**
         * @var string
         */
        public $specification = '';

        /**
         * @var bool
         */
        public $isString = false;

        /**
         * @param string [$name = '']
         * @param string [$specification = '']
         * @param bool [$isString = false]
         */
        public function __construct($name = '',
            $specification = '',
            $isString = false)
        {
            $this->name = $name;
            $this->specification = $specification;
            $this->isString = $isString;
        }

        /**
         *
         */
        public function __toString()
        {
            return \Aomebo\Database\Adapter::backquote(
                $this->name
            );
        }

    }
}
