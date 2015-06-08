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
namespace Aomebo\Database\Adapters
{

    /**
     * @method static \Aomebo\Database\Adapters\Table getInstance()
     */
    class Table extends \Aomebo\Singleton
    {

        /**
         * @var string
         */
        protected $_name = '';

        /**
         * @var string
         */
        protected $_specification = '';

        /**
         * @var string
         */
        protected $_columnSpecification = '';

        /**
         * @param string [$name = '']
         * @param string [$specification = '']
         * @param string [$columnSpecification = '']
         * @throws \Exception
         */
        public function __construct($name = '', $specification = '',
            $columnSpecification = '')
        {

            if (!self::_isConstructed()) {
                parent::__construct();
                self::_flagThisConstructed();
            }

            $this->_name = $name;
            $this->_specification = $specification;
            $this->_columnSpecification = $columnSpecification;

        }

        /**
         * @param array $columnsAndValues       array(($column, $value), ... ($column, $value))
         * @throws \Exception
         * @return int|bool                     Insert id (if available or true) or false
         */
        public function add($columnsAndValues)
        {
            return \Aomebo\Database\Adapter::tableAdd(
                $this,
                $columnsAndValues
            );
        }

        /**
         * @return bool|int
         */
        public function getNextInsertId()
        {
            return \Aomebo\Database\Adapter::getNextInsertId(
                $this
            );
        }

        /**
         * @param array $set
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         */
        public function update($set, $where = null, $limit = 1)
        {
            return \Aomebo\Database\Adapter::tableUpdate(
                $this,
                $set,
                $where,
                $limit
            );
        }

        /**
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         */
        public function delete($where = null, $limit = 1)
        {
            return \Aomebo\Database\Adapter::tableDelete(
                $this,
                $where,
                $limit
            );
        }

        /**
         * @param array|null [$columns = null]
         * @param array|null [$where = null]
         * @param array|null [$groupBy = null]
         * @param array|null [$orderBy = null]
         * @param int|null [$limit = null]
         * @return \Aomebo\Database\Adapters\Resultset|bool
         */
        public function select($columns = null, $where = null,
            $groupBy = null, $orderBy = null, $limit = null)
        {
            return \Aomebo\Database\Adapter::tableSelect(
                $this,
                $columns,
                $where,
                $groupBy,
                $orderBy,
                $limit
            );
        }

        /**
         * @return bool
         */
        public function create()
        {
            return \Aomebo\Database\Adapter::tableCreate(
                $this
            );
        }

        /**
         * @return bool
         */
        public function drop()
        {
            return \Aomebo\Database\Adapter::tableDrop(
                $this
            );
        }

        /**
         * @return array
         */
        public function getColumns()
        {
            $fields = get_object_vars($this);
            $newFields = array();
            foreach ($fields as $key => & $value)
            {
                if (substr($key, 0, 1) != '_') {
                    $newFields[$key] = & $value;
                }
            }
            if (sizeof($newFields) > 0) {
                return $newFields;
            }
            return false;
        }

        /**
         * @return bool
         */
        public function exists()
        {
            return \Aomebo\Database\Adapter::tableExists(
                $this
            );
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->_name;
        }

        /**
         * @return string
         */
        public function getPrefixedName()
        {
            return '{TABLE PREFIX}' . $this->_name;
        }

        /**
         * @return string
         */
        public function getSpecification()
        {
            return $this->_specification;
        }

        /**
         * @return string
         */
        public function getColumnSpecification()
        {
            return $this->_columnSpecification;
        }

        /**
         * @return array
         * @throws \Exception
         */
        public function getTableColumns()
        {
            return \Aomebo\Database\Adapter::getTableColumns(
                $this->getPrefixedName()
            );
        }

        /**
         * @param string $columnName
         * @return bool
         */
        public function hasTableColumn($columnName)
        {
            return \Aomebo\Database\Adapter::tableHasColumn(
                $this->getPrefixedName(),
                $columnName
            );
        }

        /**
         * @return string
         */
        public function __toString()
        {
            return \Aomebo\Database\Adapter::backquote(
                $this->getPrefixedName()
            );
        }

    }

}
