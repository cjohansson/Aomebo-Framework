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
namespace Aomebo\Database\Adapters\Mysqli
{

    /**
     * @internal
     * @method static \Aomebo\Database\Adapters\Mysqli\Adapter getInstance()
     */
    final class Adapter extends \Aomebo\Database\Adapters\Base
    {

        /**
         * @internal
         * @var \mysqli
         */
        protected $_con;

        /**
         * @internal
         * @param string $host
         * @param string $user
         * @param string $password
         * @param string $database
         * @param array $options
         * @throws \Exception
         * @return bool
         */
        public function connect($host, $user, $password,
            $database, $options)
        {

            $this->_options = $options;
            $this->_con = new \mysqli($host, $user, $password);

            if ($this->_con->connect_error
                || mysqli_connect_errno()
            ) {
                $this->_connected = false;
                return false;
            } else {
                $this->_connected = true;
                return true;
            }

        }

        /**
         * @param string $charset
         * @throws \Exception
         * @return bool
         */
        public function setHandleCharset($charset)
        {
            if ($this->_connected
                && !empty($charset)
            ) {
                if (!$this->_con->set_charset($charset)) {
                    Throw new \Exception(
                        'Could not set database handle charset to: "'
                        . $charset . '"');
                } else {
                    return true;
                }
            }
            return false;
        }

        /**
         * @internal
         * @return bool
         */
        public function disconnect()
        {
            if ($this->_connected) {
                if ($this->_con->close()) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Perform SQL escape.
         *
         * @internal
         * @param mixed $string
         * @throws \Exception
         * @return mixed
         */
        public function escape($string)
        {
            if ($this->_connected) {
                $escaped =
                    $this->_con->real_escape_string($string);
                if (isset($escaped)) {
                    return $escaped;
                } else {
                    if ($escaped = mysqli_real_escape_string($this->_con, $string)) {
                        return $escaped;
                    } else {
                        Throw new \Exception(
                            'Failed to perform SQL escape in ' . __METHOD__
                            . ' in ' . __FILE__ . ', make sure there is a database '
                            . 'connection');
                    }
                }
            } else {
                Throw new \Exception(
                    'Can not SQL escape when not connected in '
                    . __METHOD__ . ' in ' . __FILE__);
            }
        }

        /**
         * Performs an SQL-query.
         *
         * @internal
         * @param string $sql
         * @return Resultset|bool
         */
        public function query($sql)
        {
            if ($this->_connected) {
                return $this->_con->query($sql);
            }
            return false;
        }

        /**
         * @internal
         */
        public function getPrivilegies()
        {
        }

        /**
         * Performs an unbuffered query.
         *
         * @internal
         * @param string $sql
         * @return Resultset|bool
         */
        public function unbufferedQuery($sql)
        {
            if ($this->_connected) {
                if ($this->_con->real_query($sql)) {
                    return $this->_con->use_result();
                }
            }
            return false;
        }

        /**
         * @internal
         * @return bool
         */
        public function hasError()
        {
            if ($this->_connected) {
                return !empty($this->_con->error);
            }
            return false;
        }

        /**
         * @internal
         * @return string
         */
        public function getError()
        {
            if ($this->_connected) {
                return $this->_con->error;
            } else {
                return '';
            }
        }

        /**
         * @internal
         * @param string $databaseName
         * @throws \Exception
         * @return bool
         */
        public function databaseExists($databaseName)
        {
            if (!empty($databaseName)) {
                $dba = \Aomebo\Database\Adapter::getInstance();
                if ($dba->query('SELECT `SCHEMA_NAME` FROM `INFORMATION_SCHEMA`.`SCHEMATA`'
                    . ' WHERE `SCHEMA_NAME` = {name}',
                    array(
                        'name' => array(
                            'value' => $databaseName,
                            'quoted' => true
                        ),
                    ))
                ) {
                    return true;
                }
            } else {
                Throw new \Exception(
                    'Invalid parameters for ' . __FUNCTION__);
            }
            return false;
        }

        /**
         * @internal
         * @param string $databaseName
         * @throws \Exception
         * @return bool
         */
        public function selectDatabase($databaseName)
        {
            if (!empty($databaseName)) {
                if ($this->_con->select_db($databaseName)) {
                    return true;
                }
            } else {
                Throw new \Exception(
                    'Invalid parameters for '
                    . __METHOD__ . ' in ' . __FILE__);
            }
            return false;
        }

        /**
         * @internal
         * @return string|bool
         */
        public function getSelectedDatabase()
        {
            $dba =
                \Aomebo\Database\Adapter::getInstance();
            if ($resultset = $dba->query('SELECT DATABASE() AS `database`')) {
                $row = $resultset->fetchAssocAndFree();
                if (isset($row['database'])) {
                    return $row['database'];
                }
            }
            return false;
        }

        /**
         * @internal
         * @param string $tableName
         * @throws \Exception
         * @return bool
         */
        public function tableExists($tableName)
        {
            if (!empty($tableName)) {

                if ($resultset = \Aomebo\Database\Adapter::query(
                    'SELECT COUNT(*) AS `count` FROM `information_schema`.`tables` '
                    . 'WHERE `table_schema` = {database} AND `table_name` = {table}',
                    array(
                        'database' => array(
                            'value' => \Aomebo\Configuration::getSetting(
                                'database,database'),
                            'quoted' => true,
                        ),
                        'table' => array(
                            'value' => $tableName,
                            'quoted' => true,
                        ),
                    ))
                ) {
                    $row = $resultset->fetchAssoc();
                    if ($row['count'] > 0) {
                        return true;
                    }
                }

            } else {
                Throw new \Exception(
                    'Invalid parameters for ' . __FUNCTION__);
            }
            return false;
        }

        /**
         * @internal
         * @param string $databaseName
         * @throws \Exception
         * @return bool
         */
        public function createDatabase(
            $databaseName)
        {
            if (!empty($databaseName)) {
                $dba =
                    \Aomebo\Database\Adapter::getInstance();
                if ($dba->query(
                    'CREATE DATABASE IF NOT EXISTS `{database}` '
                    . 'DEFAULT CHARSET="{DATA CHARSET}" '
                    . 'DEFAULT COLLATE="{COLLATE CHARSET}"',
                    array(
                        'database' => array(
                            'value' => $databaseName,
                            'quoted' => false,
                        ),
                    ))
                ) {
                    return true;
                }
            } else {
                Throw new \Exception(
                    'Invalid parameters for ' . __FUNCTION__);
            }
            return false;
        }

        /**
         * @internal
         * @return int
         */
        public function getLastInsertId()
        {
            return $this->_con->insert_id;
        }

        /**
         * @internal
         * @param bool $useAnsiQuotes
         * @return string
         */
        public function getQuoteCharacter($useAnsiQuotes)
        {
            if ($useAnsiQuotes) {
                return "'";
            } else {
                return '"';
            }
        }

        /**
         * @internal
         * @param bool $useAnsiQuotes
         * @return string
         */
        public function getBackQuoteCharacter($useAnsiQuotes)
        {
            return '`';
        }

    }
}
