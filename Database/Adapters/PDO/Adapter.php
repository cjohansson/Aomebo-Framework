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
namespace Aomebo\Database\Adapters\PDO
{

    /**
     * PDO supports following drivers by PHP:
     * CUBRID (PDO)
     * MS SQL Server (PDO)
     * Firebird (PDO)
     * IBM (PDO)
     * Informix (PDO)
     * MySQL (PDO)
     * MS SQL Server (PDO)
     * Oracle (PDO)
     * ODBC and DB2 (PDO)
     * PostgreSQL (PDO)
     * SQLite (PDO)
     * 4D (PDO)
     * 
     * However some functionality is not available from the PDO driver like 
     * getting table column information in a universal way so we have to implement that 
     * ourselves.
     * 
     * @method static \Aomebo\Database\Adapters\PDO\Adapter getInstance()
     */
    final class Adapter extends 
        \Aomebo\Database\Adapters\Base
    {

        /**
         * @var \PDO
         */
        protected $_con;

        /**
         * @param string $host
         * @param string $user
         * @param string $password
         * @param string $database
         * @param array $options
         * @throws \Exception
         * @return bool
         * @link http://php.net/manual/en/pdo.construct.php
         */
        public function connect($host, $user, $password,
            $database, $options)
        {
            
            if (isset($options['dsn'])) {

                $this->_options = $options;
                
                $dbOptions = array();
                if (isset($options['options'])) {
                    $dbOptions = $options['options'];
                }
                
                try {
                
                    $this->_con = new \PDO(
                        $options['dsn'],
                        $user,
                        $password,
                        $dbOptions
                    );
                    
                    if (isset($this->_con)) {
    
                        $this->_connected = true;
                        $this->_selectedDatabase = true;
                        return true;
                        
                    }
                    
                } catch (\Exception $e) {
                    /*
                    Throw new \Exception(
                        sprintf(
                            __('Failed to construct database connection. Error: "%s"'),
                            $e->getMessage()
                        )
                    );
                    */
                }

                $this->_connected = false;
                return false;
                
            } else {
                Throw new \Exception(__('Missing database-engine DNS.'));
            }

        }

        /**
         * @param string $tableName
         * @return bool|int
         * @throws \Exception
         */
        public function getNextInsertId($tableName)
        {
            if (isset($tableName)) {
                if ($resultset = \Aomebo\Database\Adapter::query(
                    'SELECT `AUTO_INCREMENT` FROM `information_schema`.`TABLES` '
                    . 'WHERE `table_schema` = {database} AND `table_name` = {table}',
                    array(
                        'database' => array(
                            'value' => \Aomebo\Database\Adapter::
                            getSelectedDatabase(),
                            'quoted' => true,
                        ),
                        'table' => array(
                            'value' => $tableName,
                            'quoted' => true,
                        ),
                    ))
                ) {
                    $row = $resultset->fetchAssoc();
                    return $row['AUTO_INCREMENT'];
                }
            } else {
                Throw new \Exception(
                    self::systemTranslate(
                        'Invalid parameters'
                    )
                );
            }

            return false;

        }

        /**
         * @param string $charset
         * @throws \Exception
         * @return bool
         * @link http://stackoverflow.com/questions/4361459/php-pdo-charset-set-names
         */
        public function setHandleCharset($charset)
        {
            if ($this->_connected
                && !empty($charset)
            ) {
                if (!$this->_con->query('set names ' . self::escape($charset))) {
                    Throw new \Exception(
                        sprintf(
                            self::systemTranslate(
                                'Could not set database handle charset to: "%s"'
                            ),
                            $charset
                        )
                    );
                } else {
                    return true;
                }
            }
            return false;
        }

        /**
         * @return bool
         */
        public function disconnect()
        {
            if ($this->_connected) {
                $this->_con = null;
                return true;
            }
            return false;
        }

        /**
         * Perform SQL escape.
         *
         * @param mixed $string
         * @throws \Exception
         * @return mixed
         */
        public function escape($string)
        {
            if ($this->_connected) {
                $escaped = $this->_con->quote($string);
                if (isset($escaped)) {
                    return substr($escaped, 1, -1);
                } else {
                    Throw new \Exception(
                        self::systemTranslate(
                            'Failed to perform SQL escape, make sure '
                            . 'there is a database connection'
                        )
                    );
                }
            } else {
                Throw new \Exception(
                    self::systemTranslate(
                        'Can not SQL escape when not connected'
                    )
                );
            }
        }

        /**
         * Performs an SQL-query.
         *
         * @param string $sql
         * @return \PDOStatement
         */
        public function query($sql)
        {
            if ($this->_connected) {
                return $this->_con->query($sql);
            }
            return false;
        }

        /**
         * 
         */
        public function getPrivilegies()
        {
        }

        /**
         * Performs an unbuffered query.
         *
         * @param string $sql
         * @return Resultset|bool
         * @link http://php.net/manual/en/mysqlinfo.concepts.buffering.php
         */
        public function unbufferedQuery($sql)
        {
            if ($this->_connected) {
                if ($this->isMySQL()) {
                    $this->_con->setAttribute(
                        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
                    $result = $this->query($sql);
                    $this->_con->setAttribute(
                        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
                    return $result;
                }                
            }
            return false;
        }

        /**
         * @return bool
         */
        public function isMySQL() { return $this->_isDriverName('mysql'); }

        /**
         * @return bool
         */
        public function isPostGreSQL() { return $this->_isDriverName('postgresql'); }

        /**
         * @return bool
         */
        public function isSQLlite() { return $this->_isDriverName('sqllite'); }

        /**
         * @return mixed
         */
        public function getDriverName()
        {
            return $this->_con->getAttribute(\PDO::ATTR_DRIVER_NAME);
        }

        /**
         * @return bool
         */
        public function hasError()
        {
            if ($this->_connected) {
                return ($this->_con->errorInfo() ? true : false);
            }
            return false;
        }

        /**
         * @return string
         */
        public function getError()
        {
            if ($this->_connected) {
                return $this->_con->errorCode();
            } else {
                return '';
            }
        }

        /**
         * @return \PDO|null
         */
        public function getNativeObject()
        {
            return (isset($this->_con) ? $this->_con : null);
        }

        /**
         * @param string $databaseName
         * @throws \Exception
         * @return bool
         */
        public function databaseExists($databaseName)
        {
            if (!empty($databaseName)) {
                if (\Aomebo\Database\Adapter::query(
                    'SELECT `SCHEMA_NAME` FROM `INFORMATION_SCHEMA`.`SCHEMATA`'
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
                    self::systemTranslate(
                        'Invalid parameters'
                    )
                );
            }
            return false;
        }

        /**
         * @param string $databaseName
         * @throws \Exception
         * @return bool
         */
        public function selectDatabase($databaseName)
        {
            if (!empty($databaseName)) {
                if ($this->_con->query(
                    'USE ' . self::escape($databaseName) . '')
                ) {
                    return true;
                }
            } else {
                Throw new \Exception(
                    self::systemTranslate(
                        'Invalid parameters'
                    )
                );
            }
            return false;
        }

        /**
         * @return string|bool
         */
        public function getSelectedDatabase()
        {
            if ($resultset = \Aomebo\Database\Adapter::query(
                'SELECT DATABASE() AS `database`')
            ) {
                $row = $resultset->fetchAssocAndFree();
                if (isset($row['database'])) {
                    return $row['database'];
                }
            }
            return false;
        }

        /**
         * @param string $tableName
         * @throws \Exception
         * @return bool
         */
        public function tableExists($tableName)
        {
            if (!empty($tableName)) {
                if ($resultset = \Aomebo\Database\Adapter::query(
                    'SELECT COUNT(*) AS `count` FROM `information_schema`.`TABLES` '
                    . 'WHERE `TABLE_SCHEMA` = {database} '
                    . 'AND `TABLE_NAME` = {table}',
                    array(
                        'database' => array(
                            'value' => \Aomebo\Database\Adapter::
                            getSelectedDatabase(),
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
                    self::systemTranslate(
                        'Invalid parameters'
                    )
                );
            }
            return false;
        }

        /**
         * @param string $databaseName
         * @throws \Exception
         * @return bool
         */
        public function createDatabase(
            $databaseName)
        {
            if (!empty($databaseName)) {
                if (\Aomebo\Database\Adapter::query(
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
                    self::systemTranslate(
                        'Invalid parameters'
                    )
                );
            }
            return false;
        }

        /**
         * @return int
         * @link http://php.net/manual/en/pdo.lastinsertid.php
         */
        public function getLastInsertId()
        {
            return $this->_con->lastInsertId();
        }

        /**
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
         * @param bool $useAnsiQuotes
         * @return string
         */
        public function getBackQuoteCharacter($useAnsiQuotes)
        {
            return '`';
        }

        /**
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array $columnsAndValues               array(($column, $value), ... ($column, $value))
         * @return int|bool
         * @throws \Exception
         */
        public function tableAdd($table, $columnsToValues)
        {

            $sql = 'INSERT INTO ' . $table . '(';

            $dataIndex = 0;
            foreach ($columnsToValues as $columnsToValuesAndValue)
            {
                if ($dataIndex > 0) {
                    $sql .= ', ';
                }
                if  (is_a($columnsToValuesAndValue[0],
                    '\Aomebo\Database\Adapters\TableColumn')
                ) {

                    /** @var \Aomebo\Database\Adapters\TableColumn $key */

                    $sql .= $columnsToValuesAndValue[0];

                } else {
                    $sql .= \Aomebo\Database\Adapter::backquote(
                        $columnsToValuesAndValue[0],
                        true
                    );
                }
                $dataIndex++;
            }

            $sql .= ') VALUES(';
            $dataIndex = 0;

            foreach ($columnsToValues as $columnsToValuesAndValue)
            {
                if ($dataIndex > 0) {
                    $sql .= ', ';
                }
                if  (is_a($columnsToValuesAndValue[0],
                    '\Aomebo\Database\Adapters\TableColumn')
                ) {

                    /** @var \Aomebo\Database\Adapters\TableColumn $key */

                    if ($columnsToValuesAndValue[0]->isString) {
                        $sql .= \Aomebo\Database\Adapter::quote(
                            $columnsToValuesAndValue[1],
                            true
                        );
                    } else {
                        $sql .= \Aomebo\Database\Adapter::escape(
                            $columnsToValuesAndValue[1]
                        );
                    }

                } else {
                    $sql .= \Aomebo\Database\Adapter::escape(
                        $columnsToValuesAndValue[1]
                    );
                }
                $dataIndex++;
            }

            $sql .= ')';

            if ($result = \Aomebo\Database\Adapter::query(
                $sql)
            ) {
                return \Aomebo\Database\Adapter::getLastInsertId();
            }

            return false;

        }

        /**
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array $set
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         * @throws \Exception
         */
        public function tableUpdate($table, $set, $where = null, $limit = 1)
        {

            $sql = 'UPDATE ' . $table . ' SET ';
            $dataIndex = 0;

            foreach ($set as $columnAndValue)
            {
                if ($dataIndex > 0) {
                    $sql .= ', ';
                }
                if  (is_a($columnAndValue[0],
                    '\Aomebo\Database\Adapters\TableColumn')
                ) {

                    $sql .= $columnAndValue[0];

                } else {
                    $sql .= \Aomebo\Database\Adapter::backquote(
                        $columnAndValue[0],
                        true
                    );
                }

                $sql .= ' = ';

                if  (is_a($columnAndValue[0],
                    '\Aomebo\Database\Adapters\TableColumn')
                ) {

                    if ($columnAndValue[0]->isString) {
                        $sql .= \Aomebo\Database\Adapter::quote(
                            $columnAndValue[1],
                            true
                        );
                    } else {
                        $sql .= \Aomebo\Database\Adapter::escape(
                            $columnAndValue[1]
                        );
                    }

                } else {
                    $sql .= \Aomebo\Database\Adapter::escape(
                        $columnAndValue[1]
                    );
                }

                $dataIndex++;

            }

            if (isset($where)
                && is_array($where)
                && sizeof($where) > 0
            ) {
                $sql .= self::_generateWhereSubquery($where);
            }

            if (isset($limit)) {
                $sql .= self::_generateLimitSubquery($limit);
            }

            if ($result = \Aomebo\Database\Adapter::query(
                $sql)
            ) {
                return $result;
            }

            return false;

        }

        /**
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         * @throws \Exception
         */
        public function tableDelete($table, $where = null, $limit = 1)
        {

            $sql = 'DELETE FROM ' . $table . ' ';

            if (isset($where)
                && is_array($where)
                && sizeof($where) > 0
            ) {
                $sql .= self::_generateWhereSubquery($where);
            }

            if (isset($limit)) {
                $sql .= self::_generateLimitSubquery($limit);
            }

            if ($result = \Aomebo\Database\Adapter::query(
                $sql)
            ) {
                return $result;
            }

            return false;

        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @return bool
         * @throws \Exception
         */
        public function tableCreate($table)
        {

            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table;

            if ($columns = $table->getColumns()) {

                $sql .= '(';
                $dataIndex = 0;

                foreach ($table->getColumns() as $column)
                {

                    /** @var \Aomebo\Database\Adapters\TableColumn $column */

                    if ($dataIndex > 0) {
                        $sql .= ', ';
                    }

                    $sql .= $column . ' ' . $column->specification;
                    $dataIndex++;

                }

                if ($columnSpecification = $table->getColumnSpecification()) {
                    $sql .= ', ' . $columnSpecification;
                }

                $sql .= ')';

            }

            if ($specification = $table->getSpecification()) {
                $sql .= ' ' . $specification;
            }

            if ($result = \Aomebo\Database\Adapter::query(
                $sql)
            ) {
                return $result;
            }

            return false;

        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @return bool
         * @throws \Exception
         */
        public function tableDrop($table)
        {

            $sql = 'DROP TABLE ' . $table;

            if ($result = \Aomebo\Database\Adapter::query(
                $sql)
            ) {
                return $result;
            }

            return false;

        }

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
        public function tableSelect($table, $columns = null,
            $where = null, $groupBy = null, $orderBy = null,
            $limit = null)
        {

            $sql = 'SELECT ';

            if (isset($columns)
                && is_array($columns)
            ) {

                $dataIndex = 0;

                foreach ($columns as $columnAndValue)
                {
                    if (isset($columnAndValue)) {
                        if ($dataIndex > 0) {
                            $sql .= ', ';
                        }
                        if  (is_a($columnAndValue[0],
                            '\Aomebo\Database\Adapters\TableColumn')
                        ) {

                            $sql .= $columnAndValue[0];

                        } else {
                            $sql .= \Aomebo\Database\Adapter::backquote(
                                $columnAndValue[0],
                                true
                            );
                        }

                        if (isset($columnAndValue[1])) {
                            $sql .= ' AS ' . \Aomebo\Database\Adapter::backquote(
                                $columnAndValue[1],
                                true
                            );
                        }
                    }
                }

            } else {
                $sql .= '*';
            }

            $sql .= ' FROM ' . $table . ' ';

            if (isset($groupBy)) {
                $sql .= self::_generateGroupSubquery($groupBy);
            }

            if (isset($where)) {
                $sql .= self::_generateWhereSubquery($where);
            }

            if (isset($orderBy)) {
                $sql .= self::_generateOrderBySubquery($orderBy);
            }

            if (isset($limit)) {
                $sql .= self::_generateLimitSubquery($limit);
            }

            if ($result = \Aomebo\Database\Adapter::query(
                $sql)
            ) {
                return $result;
            }

            return false;

        }

        /**
         * @return bool
         */
        public function useResult()
        {
            if (isset($this->_con)) {
            }
            return false;
        }

        /**
         * @return bool
         */
        public function storeResult()
        {
            if (isset($this->_con)) {
            }
            return false;
        }

        /**
         * @param \Aomebo\Database\Adapters\Transaction $transaction
         * @return \Aomebo\Database\Adapters\Resultset|bool
         */
        public function executeTransaction($transaction)
        {
            if ($this->beginTransaction()) {
                try {
                    foreach ($transaction->getQueries() as $query)
                    {
                        $this->query($query);
                    }
                    if ($this->commitTransaction()) {
                        return true;
                    }
                } catch (\Exception $e) {
                    $this->rollbackTransaction();
                }
            }
            return false;
        }

        /**
         * @return bool
         * @link http://php.net/manual/en/pdo.begintransaction.php
         */
        public function beginTransaction()
        {
            return ($this->_con->beginTransaction() ? true : false);
        }

        /**
         * @return bool
         * @link http://php.net/manual/en/pdo.commit.php
         */
        public function commitTransaction()
        {
            return ($this->_con->commit() ? true : false);
        }

        /**
         * @return bool
         * @link http://php.net/manual/en/pdo.rollback.php
         */
        public function rollbackTransaction()
        {
            return ($this->_con->rollBack() ? true : false);
        }

        /**
         * @param string $tableName
         * @param string $columnName
         * @return bool
         * @throws \Exception
         */
        public function tableHasColumn($tableName, $columnName)
        {
            if (!empty($tableName)
                && !empty($columnName)
            ) {
                if ($this->isMySQL()) {
                    if ($columns = $this->getTableColumns($tableName)) {
                        foreach ($columns as $column)
                        {
                            if (!empty($column['Field'])
                                && $column['Field'] == $columnName
                            ) {
                                return true;
                            }
                        }
                    }
                } else {
                    
                    // TODO: Implement more support for drivers here
                    
                    Throw new \Exception(
                        self::systemTranslate('This feature has not been implemented for current driver.')
                    );
                }
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
            }
            return false;
        }

        /**
         * @param string $tableName
         * @return array|bool
         * @throws \Exception
         */
        public function getTableColumns($tableName)
        {
            if (!empty($tableName)) {
                
                if ($this->isMySQL()) {
                    if ($resultset = \Aomebo\Database\Adapter::query(
                        sprintf(
                            'SHOW COLUMNS FROM `%s`',
                            $this->escape($tableName)
                        ))
                    ) {
                        return $resultset->fetchAssocAllAndFree();
                    }
                } else if ($this->isPostGreSQL()) {
                    /** @link http://dba.stackexchange.com/questions/22362/how-do-i-list-all-columns-for-a-specified-table */
                    if ($resultset = \Aomebo\Database\Adapter::query(
                        sprintf(
                            'SELECT * FROM information_schema.columns '
                            . 'WHERE `table_schema` = "%s" '
                            . 'AND `table_name` = "%s"',
                            $this->escape($this->getSelectedDatabase()),
                            $this->escape($tableName)
                        ))
                    ) {
                        return $resultset->fetchAssocAllAndFree();
                    }
                } else if ($this->isSQLlite()) {
                    /** @link http://stackoverflow.com/questions/947215/how-to-get-a-list-of-column-names-on-sqlite3-iphone */
                    if ($resultset = \Aomebo\Database\Adapter::query(
                        sprintf(
                            'PRAGMA table_info(%s)',
                            $this->escape($tableName)
                        ))
                    ) {
                        return $resultset->fetchAssocAllAndFree();
                    }
                } else {

                    // TODO: Implement more support for drivers here
                    
                    Throw new \Exception(
                        self::systemTranslate('This feature has not been implemented for current driver.')
                    );
                }
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters'));
            }
            return false;
        }

        /**
         * @internal
         * @param array $where
         * @return string
         * @throws \Exception
         */
        private function _generateWhereSubquery($where)
        {

            $sql = ' WHERE ';
            $dataIndex = 0;

            foreach ($where as $columnAndValue)
            {
                if (isset($columnAndValue)) {
                    if ($dataIndex > 0) {
                        if (!isset($columnAndValue[3])
                            || $columnAndValue[3] == 'AND'
                        ) {
                            $sql .= ' AND ';
                        } else {
                            $sql .= ' ' . $columnAndValue[3] . ' ';
                        }
                    }
                    if  (is_a($columnAndValue[0],
                        '\Aomebo\Database\Adapters\TableColumn')
                    ) {

                        $sql .= $columnAndValue[0];

                    } else {
                        $sql .= \Aomebo\Database\Adapter::backquote(
                            $columnAndValue[0],
                            true
                        );
                    }

                    // Operator
                    if (!isset($columnAndValue[2])
                        || $columnAndValue[2] == '='
                    ) {
                        $sql .= ' = ';
                    } else {
                        $sql .= ' ' . $columnAndValue[2] . ' ';
                    }

                    if  (is_a($columnAndValue[0],
                        '\Aomebo\Database\Adapters\TableColumn')
                    ) {

                        if ($columnAndValue[0]->isString) {
                            $sql .= \Aomebo\Database\Adapter::quote(
                                $columnAndValue[1],
                                true
                            );
                        } else {
                            $sql .= \Aomebo\Database\Adapter::escape(
                                $columnAndValue[1]
                            );
                        }

                    } else {
                        $sql .= \Aomebo\Database\Adapter::escape(
                            $columnAndValue[1]
                        );
                    }

                    $dataIndex++;
                }
            }

            return $sql;

        }

        /**
         * @internal
         * @param array $groupBy
         * @return string
         * @throws \Exception
         */
        private function _generateGroupSubquery($groupBy)
        {

            $sql = ' GROUP BY ';
            $dataIndex = 0;

            foreach ($groupBy as $columnAndValue)
            {
                if (isset($columnAndValue)) {
                    if ($dataIndex > 0) {
                        $sql .= ', ';
                    }
                    if  (is_a($columnAndValue[0],
                        '\Aomebo\Database\Adapters\TableColumn')
                    ) {

                        $sql .= $columnAndValue[0];

                    } else {
                        $sql .= \Aomebo\Database\Adapter::backquote(
                            $columnAndValue[0],
                            true
                        );
                    }

                    $dataIndex++;
                }
            }

            return $sql;

        }

        /**
         * @internal
         * @param int|string $limit
         * @return string
         * @throws \Exception
         */
        private function _generateLimitSubquery($limit)
        {
            return (isset($limit) ?
                ' LIMIT ' . \Aomebo\Database\Adapter::escape(
                    $limit) : '');
        }

        /**
         * @internal
         * @param array $orderBy
         * @return string
         * @throws \Exception
         */
        private function _generateOrderBySubquery($orderBy)
        {

            $sql = ' ORDER BY ';
            $dataIndex = 0;

            foreach ($orderBy as $columnAndValue)
            {
                if (isset($columnAndValue)) {
                    if ($dataIndex > 0) {
                        $sql .= ', ';
                    }
                    if  (is_a($columnAndValue[0],
                        '\Aomebo\Database\Adapters\TableColumn')
                    ) {

                        $sql .= $columnAndValue[0];

                    } else {
                        $sql .= \Aomebo\Database\Adapter::backquote(
                            $columnAndValue[0],
                            true
                        );
                    }

                    // Operator
                    if (!isset($columnAndValue[1])
                        || $columnAndValue[1] == 'ASC'
                    ) {
                        $sql .= ' ASC';
                    } else {
                        $sql .= ' ' . $columnAndValue[1];
                    }

                    $dataIndex++;
                }
            }

            return $sql;

        }

        /**
         * @internal
         * @param string $driverName
         * @return bool
         */
        private function _isDriverName($driverName)
        {
            if ($this->_connected
                && !empty($driverName)
            ) {
                if (stripos($this->getDriverName(),
                    $driverName) !== false
                ) {
                    return true;
                }
            }
            return false;
        }

    }
}
