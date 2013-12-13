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
namespace Aomebo\Dispatcher
{

    /**
     *
     */
    class Route extends \Aomebo\Base
    {

        /**
         * @var string
         */
        public $name = '';

        /**
         * Use to match uri's.
         *
         * @var string
         */
        public $regexp = '';

        /**
         * Used to build uri's.
         *
         * @var string
         */
        public $sprintf = '';

        /**
         * In order of appearance in regexp matches.
         *
         * @var array
         */
        public $keys = array();

        /**
         * Runtime method (optional).
         *
         * @var string
         */
        public $method = '';

        /**
         * In order of appearance in regexp matches.
         *
         * @var array
         */
        public $values = array();

        /**
         * @var array
         */
        public $keyToValues = array();

        /**
         * @var \Aomebo\Runtime|null
         */
        public $reference = null;

        /**
         * @var string
         */
        public $page = '';

        /**
         * @internal
         * @var string|null
         */
        private $_hashKey = null;

        /**
         * @internal
         * @var array|null
         */
        private $_matches = null;

        /**
         * @internal
         * @var bool|null
         */
        private $_isMatching = null;

        /**
         * @param string|null [$name = null]
         * @param string|null [$regexp = null]
         * @param string|null [$sprintf = null]
         * @param array|null [$keys = null]
         * @param string|null [$method = null]
         * @param \Aomebo\Runtime|null [$reference = null]
         * @param string|null [$page = null]
         */
        public function __construct(
            $name = null,
            $regexp = null,
            $sprintf = null,
            $keys = null,
            $method = null,
            $reference = null,
            $page = null)
        {

            parent::__construct();

            if (isset($reference)
                && is_a($reference, '\Aomebo\Runtime')
                && is_a($reference, '\Aomebo\Runtime\Routable')
            ) {
                $this->reference = & $reference;
            } else {

                /**
                 * @see http://www.php.net/function.debug-backtrace
                 */
                if (phpversion() >= '5.3.6') {
                    $backtrace = debug_backtrace(
                        DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
                } else {
                    $backtrace = debug_backtrace(true);
                }

                if (isset($backtrace[1]['object'])
                    && is_a($backtrace[1]['object'], '\Aomebo\Runtime')
                    && is_a($backtrace[1]['object'], '\Aomebo\Runtime\Routable')
                ) {
                    $this->reference = & $backtrace[1]['object'];
                }
            }
            if (!empty($name)) {
                $this->name = $name;
            }
            if (!empty($regexp)) {
                $this->regexp = $regexp;
            }
            if (!empty($sprintf)) {
                $this->sprintf = $sprintf;
            }
            if (isset($keys)
                && is_array($keys)
                && sizeof($keys) > 0
            ) {
                $this->keys = $keys;
            }
            if (!empty($method)
                && isset($this->reference)
                && method_exists($this->reference, $method)
            ) {
                $this->method = $method;
            }
            if (isset($page)) {
                $this->page = $page;
            }
            if ($this->isValid()) {
                $dispatcher =
                    \Aomebo\Dispatcher\System::getInstance();
                $this->_hashKey = $dispatcher->generateRouteHashKey(
                    $this->keys);
            }

        }

        /**
         * @return bool
         */
        public function isValid()
        {
            return (!empty($this->regexp)
                && !empty($this->sprintf)
                && isset($this->keys)
                && is_array($this->keys)
                && sizeof($this->keys) > 0);
        }

        /**
         * @return string
         */
        public function getHashKey()
        {
            if ($this->isValid()) {
                if (empty($this->_hashKey)) {
                    $dispatcher =
                        \Aomebo\Dispatcher\System::getInstance();
                    $this->_hashKey = $dispatcher->generateRouteHashKey(
                        $this->keys);
                }
                return $this->_hashKey;
            }
            return false;
        }

        /**
         * @throws \Exception
         * @return array|bool               Matches or false
         */
        public function isMatching()
        {
            if ($this->isValid()) {

                if (!isset($this->_isMatching)) {

                    $queryString =
                        \Aomebo\Dispatcher\System::getQueryString();

                    // Doesn't the RegExp match current uri?
                    if (@preg_match(
                        $this->regexp,
                        $queryString,
                        $this->_matches) === false
                    ) {
                        $this->_isMatching = false;
                        if ($this->_matches === false) {
                            Throw new \Exception(
                                'Invalid regexp "' . $this->regexp . '" '
                                . 'for route named "' . $this->name . '"');
                        }
                    } else if (isset($this->_matches)
                        && (sizeof($this->_matches) - 1) ==
                            sizeof($this->keys)
                    ) {
                        $this->_isMatching = true;
                    }
                }

                return $this->_isMatching;

            } else {
                return false;
            }
        }

        /**
         * @param array|null [$uriParameters = null]
         * @param string|null [$page = null]
         * @param bool [$clear = false]
         * @return string
         */
        public function buildUri($uriParameters = null,
            $page = null, $clear = false)
        {

            $uri = \Aomebo\Dispatcher\System::getPageBaseUri();

            if (!empty($page)
                && \Aomebo\Dispatcher\System::uriExistsForPage($page)
            ) {
                $uri .=
                    \Aomebo\Dispatcher\System::getUriForPage($page)
                    . '/';
            } else if (!empty($this->page)
                && \Aomebo\Dispatcher\System::uriExistsForPage($this->page)
            ) {
                $uri .=
                    \Aomebo\Dispatcher\System::getUriForPage($this->page)
                    . '/';
            } else if (!\Aomebo\Dispatcher\System::isCurrentPageDefaultPage()) {
                $uri .=
                    \Aomebo\Dispatcher\System::getUriForPage(
                        \Aomebo\Dispatcher\System::getPage())
                    . '/';
            }

            // Re-order array keys order to match the specified route.
            $newGetArray = array();

            foreach ($this->keys as $key)
            {
                $newGetArray[$key] = $uriParameters[$key];
            }

            $uri .= vsprintf($this->sprintf, $newGetArray);

            return $uri;

        }

        /**
         * @throws \Exception
         * @return array|bool               Matches or false
         */
        public function buildGetValues()
        {
            if ($this->isValid()) {
                if (!empty($this->_isMatching)) {

                    $this->values = array();
                    $this->keyToValues = array();

                    // Iterate through GET-keys
                    foreach ($this->keys as $keyIndex => $keyName)
                    {

                        // Does route have this parameter?
                        if (isset($this->_matches[$keyIndex + 1])) {

                            $value = & $this->_matches[$keyIndex + 1];

                            // Set GET-data
                            $_GET[$keyName] = $value;
                            $this->keyToValues[$keyName] = $value;
                            $this->values[] = $value;

                        // Otherwise - error
                        } else {
                            Throw new \Exception(
                                'Could not find match with index "'
                                . ($keyIndex + 1) . '" for route.'
                            );
                        }

                    }

                    return true;

                }
            }

            return false;

        }

        /**
         * @return bool
         */
        public function hasValues()
        {
            return (isset($this->values)
                && is_array($this->values)
                && sizeof($this->values) > 0);
        }

        /**
         * @return string|null
         */
        public function execute()
        {
            if ($this->hasValues()) {
                return call_user_func_array(
                    array($this->reference, $this->method),
                    $this->values);
            } else {
                return call_user_func(
                    array($this->reference, $this->method));
            }
        }

    }

}
