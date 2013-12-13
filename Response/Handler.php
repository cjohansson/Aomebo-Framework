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
namespace Aomebo\Response
{

    /**
     * @method static \Aomebo\Response\Handler getInstance()
     */
    class Handler extends \Aomebo\Singleton
    {

        /**
         * @var string
         */
        const RESPONSES_DIR = 'Responses';

        /**
         * Contains all loaded response-types.
         *
         * @internal
         * @static
         * @var array
         */
        private static $_types = array();

        /**
         * Contains the current response type if any.
         *
         * @internal
         * @static
         * @var \Aomebo\Response\Type|null
         */
        private static $_response = null;

        /**
         *
         */
        public function __construct()
        {
            if (!self::_isConstructed()) {

                self::_load();
                self::_parseRequest();
                self::_flagThisConstructed();

            }
        }

        /**
         * @static
         * @throws \Exception
         */
        public static function respond()
        {
            if (self::hasResponse()) {
                self::$_response->respond();
            } else {
                Throw new \Exception('No response exists');
            }
        }

        /**
         * @static
         * @return bool
         */
        public static function hasResponse()
        {
            return (isset(self::$_response));
        }

        /**
         * Parses the current request and tries to determine
         * if their is any response that matches request.
         *
         * @internal
         * @static
         * @return bool
         */
        private static function _parseRequest()
        {

            foreach (self::$_types as & $type)
            {

                /** @var \Aomebo\Response\Type $type */
                if ($type->isValidRequest()) {
                    self::$_response = $type;
                    return true;
                }

            }

            return false;
        }

        /**
         * @internal
         */
        private static function _load()
        {

            $dir = self::_getResponsesDir();
            $dirItems = scandir($dir);
            self::$_types = array();

            foreach ($dirItems as $dirItem)
            {

                if (!empty($dirItem)
                        && $dirItem != '..'
                        && $dirItem != '.'
                        && stripos($dirItem, '.php') !== false
                ) {


                    $itemName = substr($dirItem, 0, strpos($dirItem, '.'));
                    $itemClassName =
                            '\\Aomebo\\Response\\Responses\\' . $itemName;
                    $path = $dir . DIRECTORY_SEPARATOR . $dirItem;

                    require_once($path);

                    if (class_exists($itemClassName, false)) {
                        self::$_types[] = new $itemClassName();
                    }

                }
            }
        }

        /**
         * @static
         * @return string
         */
        private static function _getResponsesDir()
        {

            $path =
                    __DIR__ . DIRECTORY_SEPARATOR . self::RESPONSES_DIR;

            if (!is_dir($path)) {
                \Aomebo\Filesystem::makeDirectory($path);
            }

            return $path;
        }

    }

}
