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
     * @method static \Aomebo\Database\Adapters\Base getInstance()
     */
    abstract class Base extends \Aomebo\Singleton
    {

        /**
         * @var mixed
         */
        protected $_con;

        /**
         * @var array
         */
        protected $_options;

        /**
         * @var bool
         */
        protected $_connected = false;

        /**
         * @var bool
         */
        protected $_selectedDatabase = false;

        /**
         * @abstract
         * @param string $host
         * @param string $user
         * @param string $password
         * @param string $database
         * @param array $options
         * @return bool
         */
        abstract public function connect($host, $user, $password,
            $database, $options);

        /**
         * @param string $useAnsiQuotes
         * @return string
         */
        abstract public function getQuoteCharacter($useAnsiQuotes);

        /**
         * @param string $useAnsiQuotes
         * @return string
         */
        abstract public function getBackQuoteCharacter($useAnsiQuotes);

        /**
         * @param string $tableName
         * @return array|bool
         * @throws \Exception
         */
        abstract public function getTableColumns($tableName);

        /**
         * @param string $tableName
         * @param string $columnName
         * @return bool
         * @throws \Exception
         */
        abstract public function tableHasColumn($tableName, $columnName);

        /**
         * @abstract
         * @param mixed $string
         * @return mixed
         */
        abstract public function escape($string);

        /**
         * @abstract
         * @return boolean
         */
        abstract public function disconnect();

        /**
         * @abstract
         * @param string $sql
         * @return Resultset|boolean
         */
        abstract public function query($sql);

        /**
         * @return bool
         */
        abstract public function useResult();

        /**
         * @return bool
         */
        abstract public function storeResult();

        /**
         * @abstract
         * @param string $sql
         * @throws \Exception
         * @return Resultset|bool
         */
        abstract public function unbufferedQuery($sql);

        /**
         * @abstract
         * @param string $databaseName
         * @throws \Exception
         * @return boolean
         */
        abstract public function databaseExists($databaseName);

        /**
         * @abstract
         * @param string $databaseName
         * @throws \Exception
         * @return boolean
         */
        abstract public function createDatabase($databaseName);

        /**
         * @abstract
         * @param string $databaseName
         * @throws \Exception
         * @return boolean
         */
        abstract public function selectDatabase($databaseName);

        /**
         * @abstract
         * @return string|bool
         */
        abstract public function getSelectedDatabase();

        /**
         * @abstract
         * @param string $tableName
         * @throws \Exception
         * @return boolean
         */
        abstract public function tableExists($tableName);

        /**
         * This method tries to determine if database
         * connection has privilegies to check if tables exists,
         * create tables, check if database exists and create databases.
         */
        abstract public function getPrivilegies();

        /**
         * @abstract
         * @return int
         */
        abstract public function getLastInsertId();

        /**
         * @abstract
         * @param string $charset
         * @return bool
         */
        abstract public function setHandleCharset($charset);

        /**
         * @abstract
         * @return bool
         */
        abstract public function hasError();

        /**
         * @return string
         */
        abstract public function getError();

        /**
         * @param string $tableName
         * @return string|bool
         * @throws \Exception
         */
        abstract public function getNextInsertId($tableName);

        /**
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array $columnsToValues
         * @return int|bool
         * @throws \Exception
         */
        abstract public function tableAdd($table, $columnsToValues);

        /**
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array $set
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         * @throws \Exception
         */
        abstract public function tableUpdate($table, $set, $where = null, $limit = 1);

        /**
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         * @throws \Exception
         */
        abstract public function tableDelete($table, $where = null, $limit = 1);

        /**
         * @param \Aomebo\Database\Adapters\Table $table
         * @throws \Exception
         */
        abstract public function tableCreate($table);

        /**
         * @param \Aomebo\Database\Adapters\Table $table
         * @throws \Exception
         */
        abstract public function tableDrop($table);

        /**
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array|null [$columns = null]
         * @param array|null [$where = null]
         * @param array|null [$groupBy = null]
         * @param array|null [$orderBy = null]
         * @param int|null [$limit = null]
         * @return \Aomebo\Database\Adapters\Resultset|bool
         * @throws \Exception
         */
        abstract public function tableSelect($table, $columns = null,
            $where = null, $groupBy = null, $orderBy = null, $limit = null);

        /**
         * @param \Aomebo\Database\Adapters\Transaction $transaction
         * @return bool
         */
        abstract public function executeTransaction($transaction);

        /**
         * @return bool
         */
        abstract public function beginTransaction();

        /**
         * @return bool
         */
        abstract public function commitTransaction();

        /**
         * @return bool
         */
        abstract public function rollbackTransaction();

        /**
         * @return mixed|null
         */
        abstract public function getNativeObject();

        /**
         * @return bool
         */
        public function isConnected()
        {
            return (!empty($this->_connected));
        }

        /**
         * @return bool
         */
        public function hasSelectedDatabase()
        {
            return (!empty($this->_selectedDatabase));
        }

        /**
         * @internal
         */
        public function __destruct()
        {
            unset(
                $this->_con,
                $this->_options,
                $this->_connected
            );
            unset($this);
        }

    }
}
