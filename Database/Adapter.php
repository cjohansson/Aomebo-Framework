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
namespace Aomebo\Database
{

    /**
     * @method static \Aomebo\Database\Adapter getInstance()
     */
    class Adapter extends \Aomebo\Singleton
    {

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_host = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_username = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_password = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_database = '';

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_options = array();

        /**
         * This flag indicates whether we are connected to database.
         *
         * @internal
         * @static
         * @var bool|null
         */
        private static $_connected = null;

        /**
         * This is a pointer to our default database adapter.
         *
         * @internal
         * @static
         * @var \Aomebo\Database\Adapters\Base|null
         */
        private static $_object = null;

        /**
         * This is a pointer to our resultset class.
         *
         * @internal
         * @static
         * @var \Aomebo\Database\Adapters\Resultset|null
         */
        private static $_resultsetClass = null;

        /**
         * This variable holds last SQL.
         *
         * @internal
         * @static
         * @var static string
         */
        private static $_lastSql = '';

        /**
         * This array hold default keys to replace in query.
         *
         * @internal
         * @static
         * @var array|null
         */
        private static $_replaceKeys = null;

        /**
         * This array hold default values to replace in query.
         *
         * @internal
         * @static
         * @var array|null
         */
        private static $_replaceValues = null;

        /**
         * Associative array to keep track on what replace
         * keys exists.
         *
         * @internal
         * @static
         * @var array|null
         */
        private static $_replaceKeysList = null;

        /**
         * This variable holds our default quote character.
         *
         * @internal
         * @static
         * @var string
         */
        private static $_quoteChar = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_backQuoteChar = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_lastError = null;

        /**
         * @internal
         * @static
         * @var bool|null
         */
        private static $_useDatabase = false;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_queries = array();

        /**
         * @internal
         * @static
         * @var int
         */
        private static $_queryCount = 0;

        /**
         * @var string
         */
        const QUERY_VALUE_QUOTATION_QUOTED = 'quoted';

        /**
         * @var string
         */
        const QUERY_VALUE_QUOTATION_BACKQUOTED = 'backquoted';

        /**
         * @var string
         */
        const QUERY_VALUE_UNESCAPED = 'raw';

        /**
         * @var string
         */
        const QUERY_VALUE = 'value';

        /**
         * @throws \Exception
         */
        public function __construct()
        {
            if (!self::_isConstructed()) {
                parent::__construct();
                $databaseConfiguration =
                    \Aomebo\Configuration::getSetting('database');
                if (!empty($databaseConfiguration['host'])
                    && !empty($databaseConfiguration['username'])
                    && !empty($databaseConfiguration['database'])
                ) {
                    if (self::connect(
                        $databaseConfiguration['host'],
                        $databaseConfiguration['username'],
                        $databaseConfiguration['password'],
                        $databaseConfiguration['database'],
                        (isset($databaseConfiguration['options']) ? $databaseConfiguration['options'] : null))
                    ) {
                        self::_flagThisConstructed();
                    } else {
                        self::_flagThisConstructed();
                        \Aomebo\Trigger\System::processTriggers(
                            \Aomebo\Trigger\System::TRIGGER_KEY_DATABASE_CONNECTION_FAIL);
                        Throw new \Exception(
                            sprintf(
                                self::systemTranslate(
                                    'Could not connect to database server in "%s" in "%s". Check your configuration.'
                                ),
                                __METHOD__,
                                __FILE__
                            )
                        );
                    }
                } else {
                    self::_flagThisConstructed();
                }
            }
        }

        /**
         * @static
         * @return bool|string
         */
        public static function getDatabase()
        {
            self::_instanciate();
            return (!empty(self::$_database) ? self::$_database : false);
        }

        /**
         * @static
         * @return int
         */
        public static function getQueryCount()
        {
            self::_instanciate();
            return self::$_queryCount;
        }

        /**
         * @static
         * @return array
         */
        public static function getQueries()
        {
            self::_instanciate();
            return self::$_queries;
        }

        /**
         *
         */
        public function __destruct()
        {
            if ($this->isConnected()) {
                $this->disconnect();
            }
        }

        /**
         * @static
         * @return bool
         */
        public static function useDatabase()
        {
            self::_instanciate();
            return !empty(self::$_useDatabase);
        }

        /**
         * @static
         * @return bool
         */
        public static function useDatabaseAndIsConnected()
        {
            self::_instanciate();
            return (
                self::useDatabase()
                && self::isConnected()
            );
        }

        /**
         * @static
         * @return mixed|null
         */
        public static function getNativeAdapter()
        {
            self::_instanciate();            
            return (isset(self::$_object) ? self::$_object->getNativeObject() : null);
        }

        /**
         * @internal
         * @static
         * @param string $databaseName
         * @throws \Exception
         * @return bool
         */
        public static function selectDatabase($databaseName)
        {
            self::_instanciate();
            if (self::$_object->selectDatabase($databaseName)) {
                if ($selectedDatabase = self::getSelectedDatabase()) {
                    if ($selectedDatabase == $databaseName) {
                        return true;
                    } else {
                        Throw new \Exception(
                            sprintf(
                                self::systemTranslate(
                                    'Selected database "%s" does not match requested database "%s" in "%s" in "%s"'
                                ),
                                $selectedDatabase,
                                $databaseName,
                                __METHOD__,
                                __FILE__
                            )
                        );
                    }
                } else {
                    Throw new \Exception(
                        sprintf(
                            self::systemTranslate(
                                'Failed to get selected database in "%s" in "%s"'
                            ),
                            __METHOD__,
                            __FILE__
                        )
                    );
                }
            }
            return false;
        }

        /**
         * @static
         * @return string|bool
         */
        public static function getSelectedDatabase()
        {
            self::_instanciate();
            return (self::isConnected() ? self::$_object->getSelectedDatabase() : false);
        }

        /**
         * This method returns whether we are connected
         * to database or not.
         *
         * @static
         * @return bool
         */
        public static function isConnected()
        {
            self::_instanciate();
            return (!empty(self::$_connected) ? true : false);
        }

        /**
         * @static
         * @param mixed $value
         * @throws \Aomebo\Exceptions\InvalidParametersException
         * @return mixed
         */
        public static function escape($value)
        {
            self::_instanciate();
            if (isset($value)
                && !is_array($value)
                && self::isConnected()
            ) {
                return self::$_object->escape($value);
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * This method returns our last run SQL query.
         *
         * @static
         * @return string|bool
         */
        public static function getLastSql()
        {
            self::_instanciate();
            if (!empty(self::$_lastSql)) {
                return self::$_lastSql;
            }
            return false;
        }

        /**
         * @static
         * @return bool
         */
        public static function useResult()
        {
            self::_instanciate();
            if (isset(self::$_object)) {
                return self::$_object->useResult();
            }
            return false;
        }

        /**
         * @static
         * @return bool
         */
        public static function storeResult()
        {
            self::_instanciate();
            if (isset(self::$_object)) {
                return self::$_object->storeResult();
            }
            return false;
        }

        /**
         * This method returns our last insert id.
         *
         * @static
         * @return int|bool
         */
        public static function getLastInsertId()
        {
            self::_instanciate();
            if (isset(self::$_object)) {
                return self::$_object->getLastInsertId();
            }
            return false;
        }

        /**
         * @static
         * @return string|bool
         */
        public static function getLastError()
        {
            self::_instanciate();
            return (!empty(self::$_lastError) ? self::$_lastError : false);
        }

        /**
         * @static
         * @param mixed $value
         * @param bool [$escape = true]
         * @return string
         */
        public static function quote($value, $escape = true)
        {
            self::_instanciate();
            return self::$_quoteChar
                . (!empty($escape) ? self::escape((string) $value) : (string) $value)
                . self::$_quoteChar;
        }

        /**
         * @static
         * @param mixed $value
         * @param bool $escape
         * @return string
         */
        public static function backquote($value, $escape = true)
        {
            self::_instanciate();
            return self::$_backQuoteChar
                . (!empty($escape) ? self::escape((string) $value) : (string) $value)
                . self::$_backQuoteChar;
        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array $columnsAndValues
         * @return int|bool
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function tableAdd($table, $columnsAndValues)
        {
            self::_instanciate();
            if (isset($table, $columnsAndValues)
                && is_array($columnsAndValues)
                && count($columnsAndValues) > 0
            ) {
                return self::$_object->tableAdd(
                    $table,
                    $columnsAndValues
                );
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array $set
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function tableUpdate($table, $set, $where = null, $limit = 1)
        {
            self::_instanciate();
            if (isset($table, $set)
                && is_array($set)
                && count($set) > 0
            ) {
                return self::$_object->tableUpdate(
                    $table,
                    $set,
                    $where,
                    $limit
                );
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array|null [$columns = null]
         * @param array|null [$where = null]
         * @param array|null [$groupBy = null]
         * @param array|null [$orderBy = null]
         * @param int|null [$limit = null]
         * @return \Aomebo\Database\Adapters\Resultset|bool
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function tableSelect($table, $columns = null,
            $where = null, $groupBy = null, $orderBy = null,
            $limit = null)
        {
            self::_instanciate();
            if (isset($table)) {
                return self::$_object->tableSelect(
                    $table,
                    $columns,
                    $where,
                    $groupBy,
                    $orderBy,
                    $limit
                );
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }


        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @return bool
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function tableCreate($table)
        {
            self::_instanciate();
            if (isset($table)) {
                return self::$_object->tableCreate(
                    $table
                );
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @return bool
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function tableDrop($table)
        {
            self::_instanciate();
            if (isset($table)) {
                return self::$_object->tableDrop(
                    $table
                );
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function tableDelete($table, $where = null, $limit = 1)
        {
            self::_instanciate();
            if (isset($table)) {
                return self::$_object->tableDelete(
                    $table,
                    $where,
                    $limit
                );
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * This method returns whether or not table exists.
         *
         * @static
         * @param string|\Aomebo\Database\Adapters\Table $rawTableName
         * @return bool
         */
        public static function tableExists($rawTableName)
        {
            self::_instanciate();
            if (self::isConnected()) {

                if (is_a($rawTableName,
                    '\Aomebo\Database\Adapters\Table')
                ) {
                    $rawTableName = '{TABLE PREFIX}' . $rawTableName->getName();
                }

                $tableName = str_replace(
                    self::$_replaceKeys,
                    self::$_replaceValues,
                    $rawTableName);

                if (self::$_object->tableExists(
                    $tableName)
                ) {
                    return true;
                }

            }

            return false;

        }

        /**
         * This method returns whether or not table exists.
         *
         * @static
         * @param string|\Aomebo\Database\Adapters\Table $rawTableName
         * @return bool|int
         */
        public static function getNextInsertId($rawTableName)
        {
            self::_instanciate();
            if (self::isConnected()) {

                if (is_a($rawTableName,
                    '\Aomebo\Database\Adapters\Table')
                ) {
                    $rawTableName = '{TABLE PREFIX}' . $rawTableName->getName();
                }

                $tableName = str_replace(
                    self::$_replaceKeys,
                    self::$_replaceValues,
                    $rawTableName
                );

                if ($insertId = self::$_object->getNextInsertId(
                    $tableName)
                ) {
                    return $insertId;
                }

            }
            return false;
        }

        /**
         * @static
         * @param mixed $value
         * @param string|bool [$quotation = false]
         * @param bool [$escaped = true]
         * @return array
         */
        public static function getQueryValue(
            $value,
            $quotation = false,
            $escaped = true)
        {
            self::_instanciate();
            $queryValue = array(
                'value' => $value,
            );
            if (!empty($quotation)) {
                if ($quotation == self::QUERY_VALUE_QUOTATION_QUOTED) {
                    $queryValue[self::QUERY_VALUE_QUOTATION_QUOTED] = true;
                } else if ($quotation == self::QUERY_VALUE_QUOTATION_BACKQUOTED) {
                    $queryValue[self::QUERY_VALUE_QUOTATION_BACKQUOTED] = true;
                }
            }
            if (empty($escaped)) {
                $queryValue[self::QUERY_VALUE_UNESCAPED] = true;
            }
            return $queryValue;
        }

        /**
         * Prepares a SQL (multiple or single) query via the vsprintf format.
         *
         * Example:
         * $result = \Aomebo\Database\Adapter::
         *      preparef('SELECT * FROM `my_table` WHERE `a` = "%s" AND `b` = %d ORDER BY `name`', array("dog", 3));
         *
         * Another example:
         * $result = \Aomebo\Database\Adapter::
         *      preparef('SELECT * FROM `my_table` WHERE `a` = "%s" AND `b` = %d ORDER BY `name`', "dog", 3);
         *
         * @static
         * @param string $sql
         * @param array|null [$values = null]
         * @param ... [$value = null]
         * @throws \Exception
         * @see vsprintf()
         * @return string|bool
         */
        public static function preparef($sql, $values = null)
        {
            self::_instanciate();
            if (self::isConnected()) {

                $newValues = array();

                if (isset($values)) {
                    if (is_array($values)) {
                        foreach ($values as $value)
                        {
                            $newValues[] = self::escape($value);
                        }
                    } else {

                        $newValues[] = self::escape($values);

                        if ($args = func_get_args()) {
                            $argsCount = count($args);
                            if ($argsCount > 2) {
                                for ($i = 2; $i < $argsCount; $i++)
                                {
                                    $newValues[] = self::escape($args[$i]);
                                }
                            }
                        }

                    }
                }

                $query = trim($sql);

                if (count($newValues) > 0) {
                    $query = vsprintf($query, $newValues);
                }

                if (!empty($query)) {
                    return $query;
                }

            }

            return false;

        }

        /**
         * Prepare and then Execute SQL Query.
         *
         * Example:
         * $result = \Aomebo\Database\Adapter::
         *      queryf('SELECT * FROM `my_table` WHERE `a` = "%s" AND `b` = %d ORDER BY `name`', array("dog", 3));
         *
         * @static
         * @param string $sql
         * @param array|null [$values = null]
         * @param bool [$unbuffered = false]
         * @param bool [$throwExceptionOnFailure = true]
         * @throws \Exception
         * @see vsprintf()
         * @return \Aomebo\Database\Adapters\Resultset|bool
         */
        public static function queryf($sql, $values = null,
             $unbuffered = false, $throwExceptionOnFailure = true)
        {
            self::_instanciate();
            if (self::isConnected()) {

                $query = self::preparef(
                    $sql,
                    $values
                );

                // Do we have any triggers?
                if ($newSql = \Aomebo\Trigger\System::processTriggers(
                    \Aomebo\Trigger\System::TRIGGER_KEY_DATABASE_QUERY,
                    $query)
                ) {
                    $query = $newSql;
                }

                if (!empty($query)) {

                    $sqlKey = strtoupper(trim(substr($query, 0, stripos($query, ' '))));
                    self::$_lastSql = $query;

                    return self::execute(
                        $query,
                        $unbuffered,
                        $sqlKey,
                        1,
                        $throwExceptionOnFailure
                    );

                } else {
                    if (!empty($sql)) {

                        Throw new \Exception(
                            sprintf(
                                self::systemTranslate(
                                    'SQL: "%s" evaluated into empty query in %s'
                                ),
                                print_r($sql, true),
                                __FUNCTION__
                            )
                        );

                    }
                }

                return true;

            } else {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate(
                            "Can't query '%s' when database connection hasn't been established."
                        ),
                        $sql
                    )
                );
            }
        }

        /**
         * Performs all SQL (multiple or single) queries.
         *
         * Example:
         * $result = \Aomebo\Database\Adapter::
         *      query('SELECT * FROM `my_table` WHERE `a` = "{animal}" AND `b` = {age} ORDER BY `name`', array('name' => 'dog', 'age' => 3));
         *
         * @static
         * @param string $sql
         * @param array|null [$values = null]
         * @throws \Exception
         * @return string|bool
         */
        public static function prepare($sql, $values = null)
        {
            self::_instanciate();
            if (self::isConnected()) {

                if (isset($values)
                    && is_array($values)
                ) {
                    $valuesCount = count($values);
                } else {
                    $valuesCount = 0;
                }

                $rawQuery = trim($sql);
                $query = str_replace(
                    self::$_replaceKeys,
                    self::$_replaceValues,
                    $rawQuery
                );

                if ($valuesCount > 0) {
                    reset($values);
                    foreach ($values as $key => $valueArray)
                    {
                        if (isset($valueArray)) {
                            if (is_array($valueArray)) {
                                if (isset($valueArray[self::QUERY_VALUE])) {

                                    if (!empty($valueArray[self::QUERY_VALUE_QUOTATION_QUOTED])) {

                                        $replaceWith = self::quote(
                                            $valueArray[self::QUERY_VALUE],
                                            empty($valueArray[self::QUERY_VALUE_UNESCAPED])
                                        );

                                    } else if (!empty($valueArray[self::QUERY_VALUE_QUOTATION_BACKQUOTED])) {

                                        $replaceWith = self::backquote(
                                            $valueArray[self::QUERY_VALUE],
                                            empty($valueArray[self::QUERY_VALUE_UNESCAPED])
                                        );

                                    } else if (empty($valueArray[self::QUERY_VALUE_UNESCAPED])) {

                                        $replaceWith = 
                                            self::escape($valueArray[self::QUERY_VALUE]);

                                    } else {

                                        $replaceWith = $valueArray[self::QUERY_VALUE];

                                    }

                                    $query = str_replace(
                                        self::formatQueryReplaceKey($key),
                                        $replaceWith,
                                        $query
                                    );

                                } else if (!empty($valueArray[self::QUERY_VALUE_QUOTATION_QUOTED])) {

                                    $query = str_replace(
                                        self::formatQueryReplaceKey($key),
                                        self::query('', false),
                                        $query
                                    );

                                } else if (!empty($valueArray[self::QUERY_VALUE_QUOTATION_BACKQUOTED])) {

                                    $query = str_replace(
                                        self::formatQueryReplaceKey($key),
                                        self::backquote('', false),
                                        $query
                                    );

                                }
                            } else {

                                $query = str_replace(
                                    self::formatQueryReplaceKey($key),
                                    self::escape($valueArray),
                                    $query
                                );

                            }
                        }
                    }
                }

                if (!empty($query)) {
                    return $query;
                }

            }

            return false;

        }

        /**
         * Prepare and then execute SQL query.
         *
         * Example:
         * $result = \Aomebo\Database\Adapter::
         *      query('SELECT * FROM `my_table` WHERE `a` = "{animal}" AND `b` = {age} ORDER BY `name`', array('name' => 'dog', 'age' => 3));
         *
         * @static
         * @param string $sql
         * @param array|null [$values = null]
         * @param bool [$unbuffered = false]
         * @param bool [$throwExceptionOnFailure = true]
         * @throws \Exception
         * @return \Aomebo\Database\Adapters\Resultset|bool
         */
        public static function query($sql, $values = null,
            $unbuffered = false, $throwExceptionOnFailure = true)
        {
            self::_instanciate();
            if (self::isConnected()) {

                $query = self::prepare(
                    $sql,
                    $values
                );

                // Do we have any triggers?
                if ($newSql = \Aomebo\Trigger\System::processTriggers(
                    \Aomebo\Trigger\System::TRIGGER_KEY_DATABASE_QUERY,
                    $query)
                ) {
                    $query = $newSql;
                }

                if (!empty($query)) {

                    $sqlKey = strtoupper(trim(
                        substr($query, 0, stripos($query, ' '))));
                    self::$_lastSql = $query;

                    return self::execute(
                        $query,
                        $unbuffered,
                        $sqlKey,
                        1,
                        $throwExceptionOnFailure
                    );

                } else {
                    if (!empty($sql)) {
                        Throw new \Exception(
                            sprintf(
                                self::systemTranslate(
                                    'SQL: "%s" evaluated into empty query in %s'
                                ),
                                print_r($sql, true),
                                __FUNCTION__
                            )
                        );
                    }
                }

                return true;

            } else {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate(
                            "Can't query '%s' when database connection hasn't been established."
                        ),
                        $sql
                    )
                );
            }
        }

        /**
         * This method closes database connection.
         *
         * @static
         * @return bool
         */
        public static function disconnect()
        {
            self::_instanciate();
            if (self::isConnected()) {
                if (self::$_object->disconnect()) {
                    self::$_connected = false;
                    return true;
                }
            }
            return false;
        }

        /**
         * @static
         * @param mixed $key
         * @param bool [$escape = true]
         * @throws \Aomebo\Exceptions\InvalidParametersException
         * @return string
         */
        public static function formatQueryReplaceKey($key,
            $escape = true)
        {
            self::_instanciate();
            if (!empty($key)) {
                return '{' . strtolower(
                    (!empty($escape) ? self::escape((string) $key) : (string) $key))
                    . '}';
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * @static
         * @param mixed $key
         * @param bool [$escape = true]
         * @throws \Aomebo\Exceptions\InvalidParametersException
         * @return string
         */
        public static function formatQuerySystemReplaceKey($key,
            $escape = true)
        {
            self::_instanciate();
            if (!empty($key)) {
                return '{' . strtoupper(
                    (!empty($escape) ? self::escape((string) $key) : (string) $key))
                . '}';
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * @static
         * @return bool
         */
        public static function beginTransaction()
        {
            self::_instanciate();
            if (self::isConnected()) {
                return self::$_object->beginTransaction();
            }
            return false;
        }

        /**
         * @static
         * @return bool
         */
        public static function commitTransaction()
        {
            self::_instanciate();
            if (self::isConnected()) {
                return self::$_object->commitTransaction();
            }
            return false;
        }

        /**
         * @static
         * @return bool
         */
        public static function rollbackTransaction()
        {
            self::_instanciate();
            if (self::isConnected()) {
                return self::$_object->rollbackTransaction();
            }
            return false;
        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Transaction $transaction
         * @return \Aomebo\Database\Adapters\Resultset|bool
         */
        public static function executeTransaction($transaction)
        {
            self::_instanciate();
            if (self::isConnected()) {
                if (isset($transaction)
                    && is_a($transaction,
                        '\Aomebo\Database\Adapters\Transaction')
                ) {
                    return self::$_object->executeTransaction($transaction);
                }
            }
            return false;
        }

        /**
         * @static
         * @return bool
         */
        public static function lostConnection()
        {
            if (self::isConnected()) {
                if (self::execute('SELECT 1', false, '', 1, false, false)) {
                    return false;
                } else {
                    self::$_database = '';
                    return true;
                }
            }
            return false;
        }

        /**
         * Reconnects connection
         * @static
         * @param int|null [$iterations = null]
         * @param int|null [$delay = null]
         * @return bool
         */
        public static function reconnect($iterations = null, $delay = null)
        {
            if (!isset($iterations)) {
                $iterations = \Aomebo\Configuration::getSetting(
                    'database,reconnect max retries');
            }
            if (!isset($delay)) {
                $delay = \Aomebo\Configuration::getSetting(
                    'database,reconnect retry delay');
            }
            for ($i = 0; $i < $iterations; $i++)
            {
                sleep($delay);
                try {
                    if (self::connect(self::$_host, self::$_username,
                                      self::$_password, self::$_database,
                                      self::$_options, true, true)
                    ) {
                        return true;
                    }
                } catch (\Exception $e) {
                    \Aomebo\Feedback\Debug::log(sprintf(self::systemTranslate(
                        'Failed to reconnect in iteration %d of %d, error: "%s"',
                        ($i + 1),
                        $iterations,
                        $e->getMessage()
                    )));
                }
            }
            return false;
        }

        /**
         * Execute a SQL query.
         *
         * @internal
         * @static
         * @param string $sql                   WARNING! No escaping is done on this SQL
         * @param bool [$unbuffered = false]
         * @param string [$sqlKey = '']
         * @param int [$queryCount = 1]
         * @param bool [$throwExceptionOnFailure = true]
         * @param null|bool [$reconnect = null]
         * @throws \Exception
         * @return Adapters\Resultset|bool
         */
        public static function execute($sql,
            $unbuffered = false,
            $sqlKey = '', $queryCount = 1,
                                       $throwExceptionOnFailure = true,
                                       $reconnect = null)
        {
            self::_instanciate();
            if (self::isConnected()) {

                self::$_queries[] = $sql;
                self::$_queryCount++;

                if ($queryCount === 1) {

                    if ($unbuffered) {
                        $result = self::$_object->unbufferedQuery($sql);
                    } else {
                        $result = self::$_object->query($sql);
                    }

                    if ($result) {
                        if ($result === true) {
                            return true;
                        } else {

                            /** @var \Aomebo\Database\Adapters\Resultset $resultset  */
                            $resultset = new self::$_resultsetClass(
                                $result,
                                $unbuffered,
                                $sql
                            );

                            if (!$unbuffered
                                && $sqlKey === 'SELECT'
                                && $resultset->numRows() == 0
                            ) {
                                return false;
                            } else {
                                return $resultset;
                            }
                        }
                    } else {

                        if (self::$_object->hasError()) {
                            self::$_lastError = self::$_object->getError();

                            if (\Aomebo\Configuration::getSetting('database,reconnect')
                                && (!isset($reconnect) || !empty($reconnect))
                                && self::lostConnection()
                            ) {
                                \Aomebo\Feedback\Debug::output(
                                    sprintf(
                                        self::systemTranslate(
                                            'Query: "%s" returned error: "%s". Reconnecting..'
                                        ),
                                        $sql,
                                        self::$_object->getError()
                                    )
                                );
                                if (self::reconnect()) {
                                    return self::execute(
                                        $sql, $unbuffered, $sqlKey, $queryCount,
                                        $throwExceptionOnFailure, $reconnect);
                                } else if ($throwExceptionOnFailure) {
                                    Throw new \Exception(self::systemTranslate(
                                        'Failed to reconnect database connection.'
                                    ));
                                }

                            } else if ($throwExceptionOnFailure) {
                                Throw new \Exception(
                                    sprintf(
                                        self::systemTranslate(
                                            'Query: "%s" returned error: "%s"'
                                        ),
                                        $sql,
                                        self::$_object->getError()
                                    )
                                );
                            }
                        }

                    }
                } else {
                    if ($unbuffered) {
                        $result = self::$_object->unbufferedQuery($sql);
                    } else {
                        $result = self::$_object->query($sql);
                    }
                    if ($result) {
                        if ($result !== true) {

                            /** @var \Aomebo\Database\Adapters\Resultset $resultset  */
                            $resultset = new self::$_resultsetClass(
                                $result,
                                $unbuffered,
                                $sql
                            );
                            $resultset->free();
                        }
                    } else {

                        if (self::$_object->hasError()) {
                            self::$_lastError =
                                self::$_object->getError();

                            if (\Aomebo\Configuration::getSetting('database,reconnect')
                                && (!isset($reconnect) || !empty($reconnect))
                                && self::lostConnection()
                            ) {
                                \Aomebo\Feedback\Debug::output(
                                    sprintf(
                                        self::systemTranslate(
                                            'Query: "%s" returned error: "%s". Reconnecting..'
                                        ),
                                        $sql,
                                        self::$_object->getError()
                                    )
                                );
                                if (self::reconnect()) {
                                    return self::execute(
                                        $sql, $unbuffered, $sqlKey, $queryCount,
                                        $throwExceptionOnFailure, $reconnect);
                                } else if ($throwExceptionOnFailure) {
                                    Throw new \Exception(self::systemTranslate(
                                        'Failed to reconnect database connection.'
                                    ));
                                }

                            } else if ($throwExceptionOnFailure) {
                                Throw new \Exception(
                                    sprintf(
                                        self::systemTranslate(
                                            'Query: "%s" returned error: "%s"'
                                        ),
                                        $sql,
                                        self::$_object->getError()
                                    )
                                );
                            }
                        }

                    }
                }
            } else {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate(
                            "Can't query '%s' when database connection hasn't been established."
                        ),
                        $sql
                    )
                );
            }
            return false;
        }

        /**
         * This method tries to establish a database connection.
         *
         * @static
         * @param string $host
         * @param string $username
         * @param string $password
         * @param string $database
         * @param array|null [$options = null]
         * @param bool [$select = true]
         * @param bool [$throwExceptionOnFailure = true]
         * @throws \Exception
         * @return bool
         */
        public static function connect($host, $username,
            $password, $database, $options = null,
            $select = true, $throwExceptionOnFailure = true)
        {

            self::_instanciate();
            self::$_connected = false;
            self::$_useDatabase = false;
            self::$_database = '';
            self::$_replaceKeys = array();
            self::$_replaceValues = array();
            self::$_replaceKeysList = array();

            $dbTypeName = ucfirst(strtolower(
                \Aomebo\Configuration::getSetting('database,adapter')));
            $dbClass = '\\Aomebo\\Database\\Adapters\\' . $dbTypeName . '\\Adapter';
            $resultsetClass = '\\Aomebo\\Database\\Adapters\\' . $dbTypeName  . '\\Resultset';

            if (class_exists($dbClass, true)
                && class_exists($resultsetClass, true)
            ) {

                self::$_object = new $dbClass();
                self::$_resultsetClass = $resultsetClass;
                self::$_lastError = '';

                /** @var \Aomebo\Database\Adapters\Base $dbObject  */
                $dbObject = & self::$_object;

                $userAnsiQuotes = \Aomebo\Configuration::getSetting('database,ansi quotes');

                self::$_quoteChar = $dbObject->getQuoteCharacter($userAnsiQuotes);
                self::$_backQuoteChar = $dbObject->getBackQuoteCharacter($userAnsiQuotes);

                if ($dbObject->connect(
                    $host,
                    $username,
                    $password,
                    $database,
                    $options)
                ) {

                    // Save configuration if we need to reconnect
                    self::$_host = $host;
                    self::$_username = $username;
                    self::$_password = $password;
                    self::$_options = $options;

                    // Add system prefixes
                    self::addSystemReplaceKey(
                        'SYSTEM TABLE PREFIX',
                        \Aomebo\Configuration::getSetting('database,system table prefix'));
                    self::addSystemReplaceKey(
                        'SITE TABLE PREFIX',
                        \Aomebo\Configuration::getSetting('database,site table prefix'));
                    self::addSystemReplaceKey(
                        'TABLE PREFIX',
                        \Aomebo\Configuration::getSetting('database,site table prefix'));
                    self::addSystemReplaceKey(
                        'DATA CHARSET',
                        \Aomebo\Configuration::getSetting('database,data charset'));
                    self::addSystemReplaceKey(
                        'COLLATE CHARSET',
                        \Aomebo\Configuration::getSetting('database,collate charset'));
                    self::addSystemReplaceKey(
                        'STORAGE ENGINE',
                        \Aomebo\Configuration::getSetting('database,storage engine'));

                    // Any optional replace-keys specified?
                    if (isset($options)
                        && is_array($options)
                        && count($options) > 0
                    ) {

                        // Iterate through replace-keys
                        foreach ($options as $key => $value)
                        {
                            if (!empty($key)
                                && !empty($value)
                            ) {
                                self::addReplaceKey($key, $value);
                            }
                        }

                    }

                    self::$_connected = true;
                    self::$_useDatabase = true;

                    $dbObject->setHandleCharset(
                        \Aomebo\Configuration::getSetting(
                            'database,handle charset'));

                    \Aomebo\Trigger\System::processTriggers(
                        \Aomebo\Trigger\System::TRIGGER_KEY_DATABASE_CONNECTION_SUCCESS);

                    if (\Aomebo\Configuration::getSetting('database,create database')) {
                        if (!self::_isInstalled($database)) {
                            if (!self::_install($database)) {
                                if ($throwExceptionOnFailure) {
                                    Throw new \Exception(
                                        sprintf(
                                            self::systemTranslate('Could not install database in %s in %s'),
                                            __METHOD__,
                                            __FILE__
                                        )
                                    );
                                }
                                return false;
                            }
                        }
                    }

                    // Should select and database is not selected already?
                    if (!empty($select)
                        && !$dbObject->hasSelectedDatabase()
                    ) {
                        if (self::selectDatabase($database)) {
                            self::$_database = $database;
                            \Aomebo\Trigger\System::processTriggers(
                                \Aomebo\Trigger\System::TRIGGER_KEY_DATABASE_SELECTED_SUCCESS);
                        } else {
                            \Aomebo\Trigger\System::processTriggers(
                                \Aomebo\Trigger\System::TRIGGER_KEY_DATABASE_SELECTED_FAIL);
                            if ($throwExceptionOnFailure) {
                                Throw new \Exception(
                                    sprintf(
                                        self::systemTranslate('Could not select database in %s in %s'),
                                        __METHOD__,
                                        __FILE__)
                                );
                            }
                            return false;
                        }
                    } else if ($dbObject->hasSelectedDatabase()) {
                        self::$_database = $dbObject->getSelectedDatabase();
                    }
                    return true;

                } else {

                    if ($throwExceptionOnFailure) {
                        Throw new \Exception(
                            sprintf(
                                self::systemTranslate('Could not connect using: "%s", error: "%s"'),
                                print_r(\Aomebo\Configuration::getSetting('database'), true),
                                $dbObject->getError()
                            )
                        );
                    }

                    return false;

                }
            } else {

                if ($throwExceptionOnFailure) {
                    Throw new \Exception(
                        sprintf(
                            self::systemTranslate(
                                'Could not find Database adapter class or database resultset class: %s, %s'
                            ),
                            $dbClass,
                            $resultsetClass
                        )
                    );
                }

                return false;

            }
        }

        /**
         * @static
         * @param string $tableName
         * @return array
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function getTableColumns($tableName)
        {
            self::_instanciate();
            if (!empty($tableName)) {
                if (self::isConnected()) {
                    return self::$_object->getTableColumns($tableName);
                }
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
            return false;
        }

        /**
         * @static
         * @param string $tableName
         * @param string $columnName
         * @return bool
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function tableHasColumn($tableName, $columnName)
        {
            self::_instanciate();
            if (!empty($tableName)
                && !empty($columnName)
            ) {
                if (self::isConnected()) {
                    return self::$_object->tableHasColumn(
                        $tableName,
                        $columnName
                    );
                }
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
            return false;
        }

        /**
         * @static
         * @param string $key
         * @param string $value
         * @return bool
         */
        public static function addReplaceKey($key, $value)
        {
            self::_instanciate();
            if (!empty($key)
                && !empty($value)
            ) {
                $replaceKey = self::formatQueryReplaceKey(
                    $key,
                    false
                );
                if (!isset(self::$_replaceKeysList[$replaceKey])) {
                    self::$_replaceKeys[] =
                        $replaceKey;
                    self::$_replaceValues[] =
                        $value;
                    self::$_replaceKeysList[$replaceKey] =
                        $value;
                }
            }
            return false;
        }

        /**
         * @internal
         * @static
         * @param string $key
         * @param string $value
         * @return bool
         */
        public static function addSystemReplaceKey($key, $value)
        {
            self::_instanciate();
            if (!empty($key)
                && !empty($value)
            ) {

                $replaceKey = self::formatQuerySystemReplaceKey(
                    $key,
                    false
                );

                // This key is new?
                if (!isset(self::$_replaceKeysList[$replaceKey])) {

                    self::$_replaceKeys[] =
                        $replaceKey;
                    self::$_replaceValues[] =
                        $value;
                    self::$_replaceKeysList[$replaceKey] =
                        $value;

                }

                // Key contains space?
                if (strpos($key, ' ') !== false) {

                    $additionalReplaceKey =
                        self::formatQuerySystemReplaceKey(
                            str_replace(' ', '_', $key), false);

                    // This key is new?
                    if (!isset(self::$_replaceKeysList
                        [$additionalReplaceKey])
                    ) {

                        // Add key with underscores
                        self::$_replaceKeys[] =
                            $additionalReplaceKey;
                        self::$_replaceValues[] =
                            $value;
                        self::$_replaceKeysList[$additionalReplaceKey] =
                            $value;

                    }

                }


            }

            return false;

        }

        /**
         * @internal
         * @static
         * @param string $database
         * @return bool
         */
        private static function _isInstalled($database)
        {
            self::_instanciate();
            if (self::isConnected()) {
                if (self::$_object->databaseExists($database)) {
                    return true;
                }
            }
            return false;
        }

        /**
         * @internal
         * @static
         * @param string $database
         * @return bool
         */
        private static function _install($database)
        {
            self::_instanciate();
            if (self::isConnected()) {
                if (\Aomebo\Configuration::getSetting(
                    'database,create database')
                ) {
                    if (self::$_object->createDatabase($database)) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
            return false;
        }

        /**
         * @internal
         * @static
         */
        private static function _instanciate()
        {
            if (!self::_isConstructed()) {
                self::getInstance(__CLASS__);
            }
        }

    }
}
