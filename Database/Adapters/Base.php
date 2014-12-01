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
     * @internal
     * @method static \Aomebo\Database\Adapters\Base getInstance()
     */
    abstract class Base extends \Aomebo\Singleton
    {

        /**
         * @internal
         * @var mixed
         */
        protected $_con;

        /**
         * @internal
         * @var array
         */
        protected $_options;

        /**
         * @internal
         * @var bool
         */
        protected $_connected;

        /**
         * @internal
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
         * @internal
         * @param string $useAnsiQuotes
         * @return string
         */
        abstract public function getQuoteCharacter($useAnsiQuotes);

        /**
         * @internal
         * @param string $useAnsiQuotes
         * @return string
         */
        abstract public function getBackQuoteCharacter($useAnsiQuotes);

        /**
         * @internal
         * @abstract
         * @param mixed $string
         * @return mixed
         */
        abstract public function escape($string);

        /**
         * @internal
         * @abstract
         * @return boolean
         */
        abstract public function disconnect();

        /**
         * @internal
         * @abstract
         * @param string $sql
         * @return Resultset|boolean
         */
        abstract public function query($sql);

        /**
         * @internal
         * @abstract
         * @param string $sql
         * @throws \Exception
         * @return Resultset|bool
         */
        abstract public function unbufferedQuery($sql);

        /**
         * @internal
         * @abstract
         * @param string $databaseName
         * @throws \Exception
         * @return boolean
         */
        abstract public function databaseExists($databaseName);

        /**
         * @internal
         * @abstract
         * @param string $databaseName
         * @throws \Exception
         * @return boolean
         */
        abstract public function createDatabase($databaseName);

        /**
         * @internal
         * @abstract
         * @param string $databaseName
         * @throws \Exception
         * @return boolean
         */
        abstract public function selectDatabase($databaseName);

        /**
         * @internal
         * @abstract
         * @return string|bool
         */
        abstract public function getSelectedDatabase();

        /**
         * @internal
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
         *
         * @internal
         */
        abstract public function getPrivilegies();

        /**
         * @internal
         * @abstract
         * @return int
         */
        abstract public function getLastInsertId();

        /**
         * @internal
         * @abstract
         * @param string $charset
         * @return bool
         */
        abstract public function setHandleCharset($charset);

        /**
         * @internal
         * @abstract
         * @return bool
         */
        abstract public function hasError();

        /**
         * @internal
         * @return string
         */
        abstract public function getError();

        /**
         * @internal
         * @param string $tableName
         * @return string|bool
         * @throws \Exception
         */
        abstract public function getNextInsertId($tableName);

        /**
         * @internal
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array $columnsToValues
         * @return int|bool
         * @throws \Exception
         */
        abstract public function tableAdd($table, $columnsToValues);

        /**
         * @internal
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array $set
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         * @throws \Exception
         */
        abstract public function tableUpdate($table, $set, $where = null, $limit = 1);

        /**
         * @internal
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         * @throws \Exception
         */
        abstract public function tableDelete($table, $where = null, $limit = 1);

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @throws \Exception
         */
        abstract public function tableCreate($table);

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @throws \Exception
         */
        abstract public function tableDrop($table);

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
