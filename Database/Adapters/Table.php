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
namespace Aomebo\Database\Adapters
{

    /**
     * @internal
     */
    abstract class Table extends \Aomebo\Base
    {

        /**
         * @var \Aomebo\Database\Adapters\TableColumns|null
         */
        private $_tableColumns;

        /**
         * @param \Aomebo\Database\Adapters\TableColumns|null [$tableColumns = null]
         * @throws \Exception
         */
        public function __construct($tableColumns = null)
        {

            if (isset($tableColumns)
                && is_a($tableColumns, '\Aomebo\Database\Adapters\TableColumns')
            ) {
                $this->_tableColumns = $tableColumns;
            } else {
                Throw new \Exception(
                    'Invalid parameters for constructor');
            }

        }

        /**
         * @param array $data
         * @throws \Exception
         * @return int|bool                     Insert id (if available or true) or false
         */
        public function add($data)
        {

            if (isset($data)
                && is_array($data)
                && sizeof($data) > 0
            ) {
            } else {
                Throw new \Exception('Invalid parameters');
            }

            return false;

        }

        /**
         * @param int|array $id
         * @param array $data
         * @throws \Exception
         * @return bool
         */
        public function update($where, $data)
        {
            if (isset($where, $data)
                && is_array($data)
                && sizeof($data) > 0
            ) {
            } else {
                Throw new \Exception('Invalid parameters');
            }
            return false;
        }

    }

}