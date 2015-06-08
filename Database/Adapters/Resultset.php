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
     * 
     */
    abstract class Resultset extends \Aomebo\Base
    {

        /**
         * This field contains pointer to a resultset.
         *
         * @var \Aomebo\Database\Adapters\Resultset
         */
        protected $_resultset;

        /**
         * @var bool
         */
        protected $_unbuffered;

        /**
         * @var string
         */
        protected $_sql = '';

        /**
         * @param \Aomebo\Database\Adapters\Resultset $resultset
         * @param bool [$unbuffered = false]
         * @param string [$sql = '']
         * @throws \Exception
         * @param bool $unbuffered
         */
        public function __construct($resultset, $unbuffered = false, $sql = '')
        {
            if ($this->_isValid($resultset)) {
                $this->_resultset = $resultset;
                $this->_unbuffered = $unbuffered;
                $this->_sql = $sql;
                parent::__construct();
            } else {
                Throw new \Exception(
                    sprintf(
                        'Invalid resultset "%s".'
                    ),
                    print_r($resultset, true)
                );
            }
        }

        /**
         * This method clears recent resultset from memory.
         *
         * @return mixed
         */
        abstract public function free();

        /**
         * This method returns number of rows in resultset.
         *
         * @return int|bool
         */
        abstract public function numRows();

        /**
         * This method returns the fields in the resultset.
         * 
         * @return array|bool
         */
        abstract public function fetchFields();

        /**
         * This method returns whether a field exists or not.
         * 
         * @param string $fieldName
         * @param string [$tableName = '']
         * @return bool
         * @throws \Exception
         */
        abstract public function hasField($fieldName, $tableName = '');

        /**
         * Return a row from resultset as associative array.
         *
         * @param int [$limit = 0]
         * @return array|bool
         */
        abstract public function fetchAssoc($limit = 0);

        /**
         * Return a row from resultset as associative
         * array and free result.
         *
         * @param int [$limit = 0]
         * @return array|bool
         */
        abstract public function fetchAssocAndFree($limit = 0);

        /**
         * Return all rows from resultset as associative arrays.
         *
         * @return array|bool
         */
        abstract public function fetchAssocAll();

        /**
         * Return all rows from resultset as associative
         * arrays and free result.
         *
         * @return array|bool
         */
        abstract public function fetchAssocAllAndFree();

        /**
         * Return a row from resultset as object.
         *
         * @param int [$limit = 0]
         * @return array|bool
         */
        abstract public function fetchObject($limit = 0);

        /**
         * Return a row from resultset as object
         * and free result.
         *
         * @param int [$limit = 0]
         * @return array|bool
         */
        abstract public function fetchObjectAndFree($limit = 0);

        /**
         * Return all rows from resultset as objects.
         *
         * @return array|bool
         */
        abstract public function fetchObjectAll();

        /**
         * Return all rows from resultset as array of
         * objects and free result.
         *
         * @return array|bool
         */
        abstract public function fetchObjectAllAndFree();

        /**
         * @param mixed $resultset
         * @return bool
         */
        abstract protected function _isValid($resultset);

        /**
         * @return string
         */
        public function getSql()
        {
            return $this->_sql;
        }

        /**
         * @return bool
         */
        public function isUnbuffered()
        {
            return (isset($this->_unbuffered)
                && $this->_unbuffered === true);
        }

        /**
         * @internal
         */
        public function __destruct()
        {
            unset($this->_resultset, $this->_unbuffered, $this->_sql, $this);
        }

    }
}
