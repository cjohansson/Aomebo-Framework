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
namespace Aomebo\Database\Adapters\PDO
{

    /**
     * 
     */
    final class Resultset extends 
        \Aomebo\Database\Adapters\Resultset
    {

        /**
         * Holds the native resultset object.
         *
         * @internal
         * @var \PDOStatement|null
         */
        protected $_resultset;

        /**
         * Number of rows.
         *
         * @return boolean|int
         */
        public function numRows()
        {
            if (isset($this->_resultset)
                && !$this->isUnbuffered()
            ) {
                return $this->_resultset->rowCount();
            } else {
                return false;
            }
        }

        /**
         * 
         */
        public function free()
        {
            if (isset($this->_resultset)) {
                $this->_resultset->closeCursor();
            }
        }

        /**
         * Get row as associative array.
         *
         * @param int [$limit = 0]
         * @return array|boolean
         */
        public function fetchAssoc($limit = 0)
        {
            if (isset($this->_resultset)) {
                if ($limit > 0) {
                    $result = array();
                    for ($i = 0; $i < $limit; $i++)
                    {
                        if ($row = $this->_resultset->fetch(
                            \PDO::FETCH_ASSOC
                        )) {
                            $result[] = $row;
                        } else {
                            break;
                        }
                    }
                    return $result;
                } else {
                    return $this->_resultset->fetch(
                        \PDO::FETCH_ASSOC
                    );
                }
            } else {
                return false;
            }
        }

        /**
         * Get row as associative array and free resultset.
         *
         * @param int [$limit = 0]
         * @return array|bool
         */
        public function fetchAssocAndFree($limit = 0)
        {
            if ($result = $this->fetchAssoc($limit)) {
                $this->free();
                return $result;
            }
            return false;
        }

        /**
         * Get all rows as an associative arrays inside numerical array.
         *
         * @return array|bool
         */
        public function fetchAssocAll()
        {
            if (isset($this->_resultset)) {
                $total = array();
                while ($row =
                    $this->_resultset->fetch(\PDO::FETCH_ASSOC)
                ) {
                    $total[] = $row;
                }
                return $total;
            } else {
                return false;
            }
        }

        /**
         * Get all rows as associative arrays in a numeric array
         * then free resultset.
         *
         * @return array|bool
         */
        public function fetchAssocAllAndFree()
        {
            if ($result = $this->fetchAssocAll()) {
                $this->free();
                return $result;
            }
            return false;
        }

        /**
         * Return a row from resultset as object.
         *
         * @param int [$limit = 0]
         * @return array|bool
         */
        public function fetchObject($limit = 0)
        {
            if (isset($this->_resultset)) {
                if ($limit > 0) {
                    $result = array();
                    for ($i = 0; $i < $limit; $i++)
                    {
                        if ($row = $this->_resultset->fetch(
                            \PDO::FETCH_OBJ)
                        ) {
                            $result[] = $row;
                        } else {
                            break;
                        }
                    }
                    return $result;
                } else {
                    return $this->_resultset->fetch(\PDO::FETCH_OBJ);
                }
            } else {
                return false;
            }
        }

        /**
         * Return a row from resultset as object
         * and free result.
         *
         * @param int [$limit = 0]
         * @return array|bool
         */
        public function fetchObjectAndFree($limit = 0)
        {
            if ($result = $this->fetchObject($limit)) {
                $this->free();
                return $result;
            }
            return false;
        }

        /**
         * Return all rows from resultset as objects.
         *
         * @return array|bool
         */
        public function fetchObjectAll()
        {
            if (isset($this->_resultset)) {
                $total = array();
                while ($row =
                    $this->_resultset->fetch(\PDO::FETCH_OBJ)
                ) {
                    $total[] = $row;
                }
                return $total;
            } else {
                return false;
            }
        }

        /**
         * Return all rows from resultset as array of
         * objects and free result.
         *
         * @return array|bool
         */
        public function fetchObjectAllAndFree()
        {
            if ($result = $this->fetchObjectAll()) {
                $this->free();
                return $result;
            }
            return false;
        }

        /**
         * This method returns the fields in the resultset.
         *
         * @return array|bool
         */
        public function fetchFields()
        {
            if (isset($this->_resultset)) {
                $columns = array();
                for ($i = 0; $i < $this->_resultset->columnCount(); $i++)
                {
                    $col = $this->_resultset->getColumnMeta($i);
                    $columns[] = $col;
                }
                if (sizeof($columns) > 0) {
                    return $columns;
                }
            }
            return false;
        }

        /**
         * This method returns whether a field exists or not.
         *
         * @param string $fieldName
         * @param string [$tableName = '']
         * @return bool
         * @throws \Exception
         */
        public function hasField($fieldName, $tableName = '')
        {
            if (!empty($fieldName)) {
                if ($columns = $this->fetchFields()) {
                    foreach ($columns as $column)
                    {
                        if (isset($column['name'])
                            && $column['name'] == $fieldName
                            && (empty($tableName)
                                || $column['table'] == $tableName)
                        ) {
                            return true;
                        }
                    }
                }
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
            }
            return false;
        }

        /**
         * @internal
         * @param mixed $resultset
         * @return bool
         */
        protected function _isValid($resultset)
        {
            if (isset($resultset)
                && is_a($resultset, '\PDOStatement')
            )  {
                return true;
            }
            return false;
        }

    }
}
