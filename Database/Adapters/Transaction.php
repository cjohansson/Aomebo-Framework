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
     * @internal
     * @method static \Aomebo\Database\Adapters\Transaction getInstance()
     */
    class Transaction extends \Aomebo\Singleton
    {

        /**
         * @var array
         */
        protected $_queries = array();

        /**
         * @param string|array [$queries = null]
         */
        public function __construct($queries = null)
        {

            if (!self::_isConstructed()) {
                parent::__construct();
                self::_flagThisConstructed();
            }

            if (isset($queries)) {
                if (is_array($queries)) {
                    $this->_queries = $queries;
                } else {
                    $this->_queries[] = $queries;
                }
            }

        }

        /**
         * @return array
         */
        public function getQueries()
        {
            return $this->_queries;
        }

        /**
         * @param string $query
         * @throws \Exception
         * @return int|bool                     Insert id (if available or true) or false
         */
        public function add($query)
        {
            $this->_queries[] = $query;
        }

        /**
         * @return bool
         */
        public function execute()
        {
            return \Aomebo\Database\Adapter::executeTransaction($this);
        }

    }

}
