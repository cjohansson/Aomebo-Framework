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
namespace Aomebo\Dispatcher
{

    /**
     *
     */
    class Route extends \Aomebo\Base implements \Serializable
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
         * @var \Aomebo\Runtime|string|null
         */
        public $reference = null;

        /**
         * @var string
         */
        public $page = '';

        /**
         * @var \Closure|string|array|null
         */
        public $enablingFunction = null;

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
         * @param \Closure|string|array|null [$enablingFunction = null]
         */
        public function __construct(
            $name = null,
            $regexp = null,
            $sprintf = null,
            $keys = null,
            $method = null,
            $reference = null,
            $page = null,
            $enablingFunction = null)
        {

            parent::__construct();

            if (isset($reference)
                && is_a($reference, '\Aomebo\Runtime')
                && is_a($reference, '\Aomebo\Runtime\Routable')
            ) {
                $this->reference = & $reference;
            } else {

                $backtrace =
                    \Aomebo\Application::getDebugBacktrace(3);

                if (isset($backtrace[2]['object'])
                    && is_a($backtrace[2]['object'], '\Aomebo\Runtime')
                    && is_a($backtrace[2]['object'], '\Aomebo\Runtime\Routable')
                ) {
                    $this->reference =
                        & $backtrace[2]['object'];
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
                $this->_hashKey = self::generateRouteHashKey(
                    $this->keys);
            }
            if (isset($enablingFunction)
                && \Aomebo\Trigger\System::isFunctionReference(
                    $enablingFunction)
            ) {
                $this->enablingFunction = $enablingFunction;
            }
        }

        /**
         * @return string
         */
        public function serialize()
        {
            return serialize(array(
                'name' => $this->name,
                'keys' => $this->keys,
                'keyToValues' => $this->keyToValues,
                'page' => $this->page,
                'reference' => (isset($this->reference) ? get_class($this->reference) : ''),
                'regexp' => $this->regexp,
                'sprintf' => $this->sprintf,
                'values' => $this->values,
                'method' => $this->method,
                'enablingFunction' => $this->enablingFunction,
                '_hashKey' => $this->_hashKey,
            ));
        }

        /**
         * @param string $data
         */
        public function unserialize($data)
        {
            if (!empty($data)) {
                if ($unserializedData = @unserialize($data)) {
                    if (isset($unserializedData['name'])) {
                        $this->name = $unserializedData['name'];
                    }
                    if (isset($unserializedData['keys'])) {
                        $this->keys = $unserializedData['keys'];
                    }
                    if (isset($unserializedData['keyToValues'])) {
                        $this->keyToValues = $unserializedData['keyToValues'];
                    }
                    if (isset($unserializedData['page'])) {
                        $this->page = $unserializedData['page'];
                    }
                    if (isset($unserializedData['regexp'])) {
                        $this->regexp = $unserializedData['regexp'];
                    }
                    if (isset($unserializedData['sprintf'])) {
                        $this->sprintf = $unserializedData['sprintf'];
                    }
                    if (isset($unserializedData['values'])) {
                        $this->values = $unserializedData['values'];
                    }
                    if (isset($unserializedData['method'])) {
                        $this->method = $unserializedData['method'];
                    }
                    if (isset($unserializedData['enablingFunction'])) {
                        $this->enablingFunction = $unserializedData['enablingFunction'];
                    }
                    if (isset($unserializedData['_hashKey'])) {
                        $this->_hashKey = $unserializedData['_hashKey'];
                    }
                    if (!empty($unserializedData['reference'])) {
                        if (class_exists($unserializedData['reference'], false))
                        {
                            $this->reference = \Aomebo\Singleton::getInstance(
                                $unserializedData['reference']
                            );
                        }
                    }
                }
            }
        }

        /**
         * @static
         * @param array $getArray       non-associative array containing keys
         * @return string
         */
        public static function generateRouteHashKey($getArray)
        {
            sort($getArray, SORT_STRING);
            return md5(implode('', $getArray));
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
                    $this->_hashKey = self::generateRouteHashKey(
                        $this->keys);
                }
                return $this->_hashKey;
            }
            return false;
        }

        /**
         * @param string $url
         * @return bool
         */
        public function isMatchingUrl($url)
        {
            if (!empty($url)) {
                if ($this->isValid()) {

                    $enabled = true;

                    // Support for function references enabling/disabling route programmatically
                    if (isset($this->enablingFunction)) {
                        $enabled = \Aomebo\Trigger\System::callFunctionReference(
                            $this->enablingFunction,
                            array($this)
                        );
                    }

                    if ($enabled) {

                        // Does the RegExp match URL?
                        if (preg_match(
                                $this->regexp,
                                $url) === 1
                        ) {
                            return true;
                        }

                    }
                }
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

                    $enabled = true;

                    // Support for function references enabling/disabling route programmatically
                    if (isset($this->enablingFunction)) {
                        $enabled = \Aomebo\Trigger\System::callFunctionReference(
                            $this->enablingFunction,
                            array($this)
                        );
                    }

                    if ($enabled) {

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
                                Throw new \Exception(sprintf(
                                    self::systemTranslate('Invalid regexp "%s" for route named "%s"'),
                                    $this->regexp,
                                    $this->name));
                            }
                        } else if (isset($this->_matches)
                            && (sizeof($this->_matches) - 1) ==
                            sizeof($this->keys)
                        ) {
                            $this->_isMatching = true;
                        }

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
         * @todo Implement clear parameter
         */
        public function buildUri($uriParameters = null,
             $page = null, $clear = false)
        {

            $uri = \Aomebo\Dispatcher\System::getPageBaseUri();

            if (!empty($page)) {
                if (\Aomebo\Dispatcher\System::uriExistsForPage($page)
                    && !\Aomebo\Dispatcher\System::isDefaultPage($page)
                ) {
                    $uri .=
                        \Aomebo\Dispatcher\System::getUriForPage($page)
                        . '/';
                }
            } else if (!empty($this->page)) {
                if (\Aomebo\Dispatcher\System::uriExistsForPage($this->page)
                    && !\Aomebo\Dispatcher\System::isDefaultPage($this->page)
                ) {
                    $uri .=
                        \Aomebo\Dispatcher\System::getUriForPage($this->page)
                        . '/';
                }
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
                            Throw new \Exception(sprintf(
                                self::systemTranslate('Could not find match with index "%s" for route.'),
                                ($keyIndex + 1)));
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
