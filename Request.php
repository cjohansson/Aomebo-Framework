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
namespace Aomebo
{

    /**
     * @method static \Aomebo\Request getInstance()
     */
    class Request extends \Aomebo\Singleton
    {

        /**
         * @static
         * @var string
         */
        public static $uri;

        /**
         * @static
         * @var string
         */
        public static $protocol;

        /**
         * @static
         * @var float
         */
        public static $protocolVersion;

        /**
         * @static
         * @var string
         */
        public static $requestUri;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $queryString;

        /**
         * @static
         * @var string
         */
        public static $method;

        /**
         *
         */
        public function __construct()
        {
            parent::__construct();
            if (!$this->_isConstructed()) {
                self::_parseProtocol();
                self::_parseRequest();
                $this->_flagThisConstructed();
            }
        }

        /**
         * This method parses server-protocol data.
         *
         * @internal
         * @static
         */
        private static function _parseProtocol()
        {

            if (isset($_SERVER['SERVER_PROTOCOL'])) {
                $serverProtocolData = $_SERVER['SERVER_PROTOCOL'];
                $serverProtocol =
                    substr($serverProtocolData, 0, strpos($serverProtocolData, '/'));
                $serverProtocolVersion =
                    substr($serverProtocolData, strpos($serverProtocolData, '/') + 1);
            } else {
                $serverProtocol =
                    \Aomebo\Configuration::getSetting('site,protocol');
                $serverProtocolVersion =
                    \Aomebo\Configuration::getSetting('site,protocol version');
            }

            if (strtoupper($serverProtocol) == 'HTTP') {
                self::$protocol = 'HTTP';
            } else if (strtoupper($serverProtocol) == 'HTTPS') {
                self::$protocol = 'HTTPS';
            }

            self::$protocolVersion = (float) $serverProtocolVersion;
        }

        /**
         * This method parses uri to find adress
         * and query-string data.
         *
         * @internal
         * @static
         */
        private static function _parseRequest()
        {
            /**
             * $_SERVER['REQUEST_URI'] is not a safe variable.
             *
             * @see http://security.stackexchange.com/questions/32299/is-server-a-safe-source-of-data-in-php
             */
            if (isset($_SERVER['REQUEST_URI'])
                && substr($_SERVER['REQUEST_URI'], 0, 1) == '/'
            ) {
                self::$uri = substr(
                    urldecode($_SERVER['REQUEST_URI']),
                    strlen(_PUBLIC_EXTERNAL_ROOT_)
                );
            } else if (isset($_SERVER['SCRIPT_NAME'])
                       && substr($_SERVER['SCRIPT_NAME'], 0, 1) == '/'
            ) {
                self::$uri = basename($_SERVER['SCRIPT_NAME']);
            } else {
                self::$uri = 'index.php';
            }

            if ($strrpos = strpos(self::$uri, '/')) {
                self::$requestUri = substr(self::$uri, 0, $strrpos);
                self::$queryString = substr(self::$uri, ($strrpos + 1));
            } else {
                self::$requestUri = self::$uri;
                self::$queryString = '';
            }

            if (empty($_SERVER['REQUEST_METHOD'])) {
                self::$method = 'GET';
            } else {
                self::$method = strtoupper($_SERVER['REQUEST_METHOD']);
            }
        }

    }
}