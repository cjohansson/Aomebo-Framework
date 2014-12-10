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
namespace Aomebo\Database
{

    /**
     * @method static \Aomebo\Database\Adapter getInstance()
     */
    class Adapter extends \Aomebo\Singleton
    {

        /**
         * This flag indicates whether we are connected to database.
         *
         * @internal
         * @static
         * @var bool|null
         */
        private static $_connected = false;

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
        private static $_useDatabase = null;

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

                    self::$_useDatabase = true;

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
                                    'Could not connect to database server in '
                                    . '%s in %s. Check your configuration.'
                                ),
                                __METHOD__,
                                __FILE__
                            )
                        );

                    }

                } else {

                    self::$_useDatabase = false;
                    self::_flagThisConstructed();

                }
            }
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

            return (
                self::useDatabase() && self::isConnected()
            );

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
                                    'Selected database "%s" '
                                    . 'does not match requested database '
                                    . '"%s" in %s in %s'
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
                                'Failed to get selected database in %s in %s'
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
            return
                (self::isConnected() ? self::$_object->getSelectedDatabase() : false);
        }

        /**
         * This method returns whether we are connected
         * to database or not.
         *
         * @internal
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
         * @throws \Exception
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
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
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
         * This method returns our last insert id.
         *
         * @static
         * @return int|bool
         */
        public static function getLastInsertId()
        {

            if (!self::_isConstructed()) {
                self::getInstance();
            }

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
            return
                (!empty(self::$_lastError) ? self::$_lastError : false);
        }

        /**
         * @static
         * @param mixed $value
         * @param bool [$escape = true]
         * @return string
         */
        public static function quote($value, $escape = true)
        {
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
            return
                self::$_backQuoteChar
                . (!empty($escape) ? self::escape((string) $value) : (string) $value)
                . self::$_backQuoteChar;
        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array $columnsAndValues
         * @return int|bool
         * @throws \Exception
         */
        public static function tableAdd($table, $columnsAndValues)
        {
            if (isset($table, $columnsAndValues)
                && is_array($columnsAndValues)
                && sizeof($columnsAndValues) > 0
            ) {
                return self::$_object->tableAdd(
                    $table,
                    $columnsAndValues
                );
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
            }
        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array $set
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         * @throws \Exception
         */
        public static function tableUpdate($table, $set, $where = null, $limit = 1)
        {
            if (isset($table, $set)
                && is_array($set)
                && sizeof($set) > 0
            ) {
                return self::$_object->tableUpdate(
                    $table,
                    $set,
                    $where,
                    $limit
                );
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
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
         * @throws \Exception
         */
        public static function tableSelect($table, $columns = null,
            $where = null, $groupBy = null, $orderBy = null,
            $limit = null)
        {
            if (isset($table)
            ) {
                return self::$_object->tableSelect(
                    $table,
                    $columns,
                    $where,
                    $groupBy,
                    $orderBy,
                    $limit
                );
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
            }
        }


        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @return bool
         * @throws \Exception
         */
        public static function tableCreate($table)
        {
            if (isset($table)
            ) {
                return self::$_object->tableCreate(
                    $table
                );
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
            }
        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @return bool
         * @throws \Exception
         */
        public static function tableDrop($table)
        {
            if (isset($table)
            ) {
                return self::$_object->tableDrop(
                    $table
                );
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
            }
        }

        /**
         * @static
         * @param \Aomebo\Database\Adapters\Table $table
         * @param array|null [$where = null]
         * @param int|null [$limit = 1]
         * @return bool
         * @throws \Exception
         */
        public static function tableDelete($table, $where = null, $limit = 1)
        {
            if (isset($table)) {
                return self::$_object->tableDelete(
                    $table,
                    $where,
                    $limit
                );
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
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
         * Performs all SQL (multiple or single) queries via the vsprintf format.
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
         * @param bool [$allowMultipleQueries = false]
         * @throws \Exception
         * @see vsprintf()
         * @return \Aomebo\Database\Adapters\Resultset|bool
         */
        public static function queryf($sql, $values = null,
             $unbuffered = false, $throwExceptionOnFailure = true,
             $allowMultipleQueries = false)
        {
            if (self::isConnected()) {

                // Do we have any triggers?
                if ($newSql = \Aomebo\Trigger\System::processTriggers(
                    \Aomebo\Trigger\System::TRIGGER_KEY_DATABASE_QUERY,
                    $sql)
                ) {
                    $sql = $newSql;
                }

                if ($allowMultipleQueries) {

                    $queries = explode(';', $sql);
                    if (is_array($queries)) {
                        $queryCount = sizeof($queries);
                    } else {
                        $queries = array($sql);
                        $queryCount = (int) 1;
                    }

                } else {
                    $queries = array($sql);
                    $queryCount = (int) 1;
                }

                if (isset($values)
                    && is_array($values)
                ) {
                    $valuesCount = sizeof($values);
                    foreach ($values as & $value)
                    {
                        $value = self::escape($value);
                    }
                } else {
                    $values = array();
                    if (isset($value)) {
                        $values = self::escape($value);
                    }
                    $valuesCount = 0;
                }

                foreach ($queries as $rawQuery)
                {

                    $query = trim($rawQuery);

                    if ($valuesCount > 0) {
                        $query = vsprintf($query, $values);
                    }

                    if (!empty($query)) {

                        $sqlKey = strtoupper(trim(substr($query, 0, stripos($query, ' '))));
                        self::$_lastSql = $query;

                        if ($queryCount === 1) {
                            return self::_query($query, $unbuffered, $sqlKey, $queryCount, $throwExceptionOnFailure);
                        } else {
                            self::_query($query, $unbuffered, $sqlKey, $queryCount, $throwExceptionOnFailure);
                        }

                    } else {
                        if (!empty($rawQuery)) {

                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate(
                                        'SQL: "%s" evaluated into empty query in %s'
                                    ),
                                    print_r($rawQuery, true),
                                    __FUNCTION__
                                )
                            );

                        }
                    }
                }

                return true;

            } else {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate('Can\'t query "%s" when database '
                            . 'connection hasn\'t been established. '
                            . 'Database adapter constructed: ' .
                            (self::_isConstructed() ? 'YES' : 'NO')),
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
         * @param bool [$unbuffered = false]
         * @param bool [$throwExceptionOnFailure = true]
         * @param bool [$allowMultipleQueries = false]
         * @throws \Exception
         * @return \Aomebo\Database\Adapters\Resultset|bool
         */
        public static function query($sql, $values = null,
            $unbuffered = false, $throwExceptionOnFailure = true,
            $allowMultipleQueries = false)
        {
            if (self::isConnected()) {

                // Do we have any triggers?
                if ($newSql = \Aomebo\Trigger\System::processTriggers(
                    \Aomebo\Trigger\System::TRIGGER_KEY_DATABASE_QUERY,
                    $sql)
                ) {
                    $sql = $newSql;
                }

                if ($allowMultipleQueries) {

                    $queries = explode(';', $sql);
                    if (is_array($queries)) {
                        $queryCount = sizeof($queries);
                    } else {
                        $queries = array($sql);
                        $queryCount = (int) 1;
                    }

                } else {
                    $queries = array($sql);
                    $queryCount = (int) 1;
                }

                if (isset($values)
                    && is_array($values)
                ) {
                    $valuesCount = sizeof($values);
                } else {
                    $valuesCount = 0;
                }

                foreach ($queries as $rawQuery)
                {
                    $rawQuery = trim($rawQuery);
                    $query = str_replace(
                        self::$_replaceKeys,
                        self::$_replaceValues,
                        $rawQuery);

                    if ($valuesCount > 0) {
                        reset($values);
                        foreach ($values as $key => $valueArray)
                        {
                            if (isset($valueArray)) {
                                if (is_array($valueArray)) {
                                    if (isset($valueArray[self::QUERY_VALUE])) {

                                        if (!empty($valueArray[self::QUERY_VALUE_QUOTATION_QUOTED])) {

                                            $replaceWith = self::quote($valueArray[self::QUERY_VALUE],
                                                empty($valueArray[self::QUERY_VALUE_UNESCAPED])
                                            );

                                        } else if (!empty($valueArray[self::QUERY_VALUE_QUOTATION_BACKQUOTED])) {

                                            $replaceWith = self::backquote(
                                                $valueArray[self::QUERY_VALUE],
                                                empty($valueArray[self::QUERY_VALUE_UNESCAPED])
                                            );

                                        } else if (empty($valueArray[self::QUERY_VALUE_UNESCAPED])) {

                                            $replaceWith = self::escape($valueArray[self::QUERY_VALUE]);

                                        } else {

                                            $replaceWith = $valueArray[self::QUERY_VALUE];

                                        }

                                        $query = str_replace(
                                            self::formatQueryReplaceKey($key),
                                            $replaceWith,
                                            $query);

                                    } else if (!empty($valueArray[self::QUERY_VALUE_QUOTATION_QUOTED])) {

                                        $query = str_replace(
                                            self::formatQueryReplaceKey($key),
                                            self::query('', false),
                                            $query);

                                    } else if (!empty($valueArray[self::QUERY_VALUE_QUOTATION_BACKQUOTED])) {

                                        $query = str_replace(
                                            self::formatQueryReplaceKey($key),
                                            self::backquote('', false),
                                            $query);

                                    }
                                } else {

                                    $query = str_replace(
                                        self::formatQueryReplaceKey($key),
                                        self::escape($valueArray),
                                        $query);

                                }
                            }
                        }
                    }

                    if (!empty($query)) {

                        $sqlKey = strtoupper(trim(
                            substr($query, 0, stripos($query, ' '))));
                        self::$_lastSql = $query;

                        if ($queryCount === 1) {
                            return self::_query($query, $unbuffered, $sqlKey, $queryCount, $throwExceptionOnFailure);
                        } else {
                            self::_query($query, $unbuffered, $sqlKey, $queryCount, $throwExceptionOnFailure);
                        }

                    } else {
                        if (!empty($rawQuery)) {

                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate(
                                        'SQL: "%s" evaluated into empty query in %s'
                                    ),
                                    print_r($rawQuery, true),
                                    __FUNCTION__
                                )
                            );

                        }
                    }
                }

                return true;

            } else {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate('Can\'t query "%s" when database '
                        . 'connection hasn\'t been established. '
                        . 'Database adapter constructed: ' .
                        (self::_isConstructed() ? 'YES' : 'NO')),
                        $sql
                    )
                );
            }
        }

        /**
         * This method closes database connection.
         *
         * @internal
         * @static
         * @return bool
         */
        public static function disconnect()
        {
            if (self::isConnected()) {
                if (self::$_object->disconnect()) {
                    return true;
                }
            }
            return false;
        }

        /**
         * @static
         * @param mixed $key
         * @param bool [$escape = true]
         * @throws \Exception
         * @return string
         */
        public static function formatQueryReplaceKey($key,
            $escape = true)
        {
            if (!empty($key)) {
                return '{' . strtolower(
                    (!empty($escape) ? self::escape((string) $key)
                    : (string) $key))
                    . '}';
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
            }
        }

        /**
         * @internal
         * @static
         * @param mixed $key
         * @param bool [$escape = true]
         * @throws \Exception
         * @return string
         */
        public static function formatQuerySystemReplaceKey($key,
            $escape = true)
        {
            if (!empty($key)) {
                return '{' . strtoupper(
                    (!empty($escape) ? self::escape((string) $key)
                        : (string) $key))
                . '}';
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
            }
        }

        /**
         * Perform a single SQL query.
         *
         * @internal
         * @static
         * @param string $sql
         * @param bool [$unbuffered = false]
         * @param string [$sqlKey = '']
         * @param int [$queryCount = 1]
         * @param bool [$throwExceptionOnFailure = true]
         * @throws \Exception
         * @return Adapters\Resultset|bool
         */
        private static function _query($sql, $unbuffered = false,
            $sqlKey = '', $queryCount = 1, $throwExceptionOnFailure = true)
        {
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

                        if ($throwExceptionOnFailure) {

                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate('Query:' . "<p>\n%s</p>\n returned error:<p>\n"
                                    . "<p>\n%s</p>"),
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

                        if ($throwExceptionOnFailure) {

                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate('Query:' . "<p>\n%s</p>\n returned error:<p>\n"
                                        . "<p>\n%s</p>"),
                                    $sql,
                                    self::$_object->getError()
                                )
                            );

                        }
                    }

                }
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

            if (isset($transaction)
                && is_a($transaction, '\Aomebo\Database\Adapters\Transaction')
            ) {

            }

            return false;

        }

        /**
         * This method tries to establish a database connection.
         *
         * @internal
         * @static
         * @param string $host
         * @param string $username
         * @param string $password
         * @param string $database
         * @param array|null [$options = null]
         * @param bool [$select = true]
         * @throws \Exception
         * @return bool
         */
        public static function connect($host, $username,
            $password, $database, $options = null, $select = true)
        {

            self::$_connected = false;
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
                        && sizeof($options) > 0
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

                    $dbObject->setHandleCharset(
                        \Aomebo\Configuration::getSetting(
                            'database,handle charset'));

                    \Aomebo\Trigger\System::processTriggers(
                        \Aomebo\Trigger\System::TRIGGER_KEY_DATABASE_CONNECTION_SUCCESS);

                    if (!self::_isInstalled($database)) {
                        if (!self::_install($database)) {

                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate('Could not install database in %s in %s'),
                                    __METHOD__,
                                    __FILE__
                                )
                            );

                        }
                    }

                    if (!empty($select)) {
                        if (self::selectDatabase($database)) {

                            \Aomebo\Trigger\System::processTriggers(
                                \Aomebo\Trigger\System::TRIGGER_KEY_DATABASE_SELECTED_SUCCESS);

                        } else {

                            \Aomebo\Trigger\System::processTriggers(
                                \Aomebo\Trigger\System::TRIGGER_KEY_DATABASE_SELECTED_FAIL);

                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate('Could not select database in %s in %s'),
                                    __METHOD__,
                                    __FILE__)
                            );
                        }
                    }

                    return true;

                } else {
                    Throw new \Exception(
                        sprintf(
                            self::systemTranslate('Could not connect using: "%s"'),
                            print_r(\Aomebo\Configuration::getSetting('database'), true)
                        )
                    );
                }
            } else {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate(
                            'Could not find Database adapter class or database resultset class: '
                            . '%s, %s'),
                        $dbClass,
                        $resultsetClass
                    )
                );
            }

        }

        /**
         * @static
         * @param string $key
         * @param string $value
         * @return bool
         */
        public static function addReplaceKey($key, $value)
        {
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
