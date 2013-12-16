<?php
/**
* Aomebo - a module-based MVC framework for PHP 5.3+
*
* Copyright (C) 2010+ Christian Johansson <christian@cvj.se>
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
* @see http://www.aomebo.org
*/

/**
 *
 */
namespace Aomebo\Trigger
{

    /**
     * @method static \Aomebo\Trigger\System getInstance()
     */
    class System extends \Aomebo\Singleton
    {

        /**
         * @var string
         */
        const TRIGGER_KEY_BEFORE_DISPATCH = 'af_before_dispatch';

        /**
         * @var string
         */
        const TRIGGER_KEY_AFTER_DISPATCH = 'af_after_dispatch';

        /**
         * @var string
         */
        const TRIGGER_KEY_BEFORE_INTERPRETATION = 'af_before_interpretation';

        /**
         * @var string
         */
        const TRIGGER_KEY_AFTER_INTERPRETATION = 'af_after_interpretation';

        /**
         * @var string
         */
        const TRIGGER_KEY_BEFORE_PRESENTATION = 'af_before_presentation';

        /**
         * @var string
         */
        const TRIGGER_KEY_AFTER_PRESENTATION = 'af_after_presentation';

        /**
         * @var string
         */
        const TRIGGER_KEY_GET_ASSOCIATIVES_DATA = 'af_get_associatives_data';

        /**
         * @var string
         */
        const TRIGGER_KEY_ASSOCIATIVES_PARSER = 'af_associatives_parser';

        /**
         * @var string
         */
        const TRIGGER_KEY_DATABASE_QUERY = 'af_database_query';

        /**
         * @var string
         */
        const TRIGGER_KEY_DATABASE_CONNECTION_SUCCESS = 'af_database_connection_success';

        /**
         * @var string
         */
        const TRIGGER_KEY_DATABASE_CONNECTION_FAIL = 'af_database_connection_fail';

        /**
         * @var string
         */
        const TRIGGER_KEY_DATABASE_SELECTED_SUCCESS = 'af_database_selected_database_success';

        /**
         * @var string
         */
        const TRIGGER_KEY_DATABASE_SELECTED_FAIL = 'af_database_selected_database_fail';

        /**
         * @static
         * @var array
         */
        private static $_triggers = array();

        /**
         *
         */
        public function __construct()
        {
            parent::__construct();
            if (!$this->_isConstructed()) {
            }
        }

        /**
         * @static
         * @param string $key
         * @param \Closure|string|array $ref
         * @return bool
         */
        public static function addTrigger($key, $ref)
        {
            if (isset($key, $ref)) {
                if (self::isFunctionReference($ref)) {
                    if (!isset(self::$_triggers[$key])) {
                        self::$_triggers[$key] = array();
                    }
                    self::$_triggers[$key][] = $ref;
                    return true;
                }
            }
            return false;
        }

        /**
         * @static
         * @param string $key
         * @return bool
         */
        public static function hasTriggers($key)
        {
            if (self::getTriggers($key)) {
                return true;
            }
            return false;
        }

        /**
         * @static
         * @param string $key
         * @return array|bool
         */
        public static function getTriggers($key)
        {
            if (isset($key, self::$_triggers[$key])
                && is_array(self::$_triggers[$key])
                && sizeof(self::$_triggers[$key]) > 0
            ) {
                return self::$_triggers[$key];
            }
            return false;
        }

        /**
         * @static
         * @param string $key
         * @param array|null [$args = null]
         * @return string|bool
         */
        public static function processTriggers($key, $args = null)
        {

            if ($triggers = self::getTriggers($key)) {

                $output = '';

                // Process arguments
                if (func_num_args() > 2) {

                    $args = func_get_args();
                    $newArgs = array();
                    $count = 0;
                    foreach ($args as $arg)
                    {
                        if ($count > 0) {
                            $newArgs[] = $arg;
                        }
                        $count++;
                    }
                    $args = $newArgs;

                } else if (isset($args)
                    && !is_array($args)
                ) {
                    $args = array($args);
                }

                foreach ($triggers as $trigger) {
                    $output .= self::callFunctionReference($trigger, $args);
                }

                return $output;

            }

            return false;

        }

        /**
         * @static
         * @param \Closure|string|array $ref
         * @return bool
         */
        public static function isFunctionReference($ref)
        {
            if (isset($ref)) {
                if (is_object($ref)) {
                    if (is_a($ref, 'Closure')) {
                        return true;
                    }
                } else if (is_array($ref)) {
                    if (sizeof($ref) == 2) {
                        if ((is_object($ref[0])
                            || is_string($ref[0]))
                            && is_string($ref[1])
                        ) {
                            if (method_exists($ref[0], $ref[1])) {
                                return true;
                            }
                        }
                    }
                } else if (is_string($ref)) {
                    if (function_exists($ref)) {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * @static
         * @param \Closure|string|array $ref
         * @param array|null [$args = null]
         * @return mixed
         */
        public static function callFunctionReference($ref, $args = null)
        {
            if (isset($ref)) {
                if (is_object($ref)) {
                    if (is_a($ref, 'Closure')) {

                        /** @var \Closure $ref */

                        if (isset($args)
                            && is_array($args)
                            && sizeof($args) > 0
                        ) {
                            return call_user_func_array($ref, $args);
                        } else {
                            return call_user_func($ref);
                        }

                    }
                } else if (is_array($ref)) {
                    if (sizeof($ref) == 2) {
                        if ((is_object($ref[0])
                            || is_string($ref[0]))
                            && is_string($ref[1])
                        ) {
                            if (method_exists($ref[0], $ref[1])) {
                                if (isset($args)
                                    && is_array($args)
                                    && sizeof($args) > 0
                                ) {
                                    return call_user_func_array($ref, $args);
                                } else {
                                    return call_user_func($ref);
                                }
                            }
                        }
                    }
                } else if (is_string($ref)) {
                    if (function_exists($ref)) {
                        if (isset($args)
                            && is_array($args)
                            && sizeof($args) > 0
                        ) {
                            return call_user_func_array($ref, $args);
                        } else {
                            return call_user_func($ref);
                        }
                    }
                }
            }
            return false;
        }

    }

}
