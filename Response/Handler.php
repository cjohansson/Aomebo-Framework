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
                try {
                    self::$_response->respond();
                } catch (\Exception $e) {}
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
         * @static
         * @param \Aomebo\Response\type $a
         * @param \Aomebo\Response\type $b
         * @return int
         */
        public static function compareResponses($a, $b)
        {
            if ($a->getPriority() > $b->getPriority()) {
                return -1;
            } else if ($a->getPriority() == $b->getPriority()) {
                return 0;
            } else {
                return 1;
            }
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
         * @static
         */
        private static function _load()
        {

            self::$_types = array();

            $lastEmTime = 0;
            $diremTime = \Aomebo\Filesystem::getDirectoryLastModificationTime(
                self::_getResponsesDir());
            if ($diremTime > $lastEmTime) {
                $lastEmTime = $diremTime;
            }
            $diremTime = \Aomebo\Filesystem::getDirectoryLastModificationTime(
                self::_getResponsesSiteDir());
            if ($diremTime > $lastEmTime) {
                $lastEmTime = $diremTime;
            }

            $cacheParameters = 'Response/Responses';
            $cacheKey = md5('lastmod=' . $lastEmTime);

            if (\Aomebo\Cache\System::cacheExists(
                $cacheParameters,
                $cacheKey,
                \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM)
            ) {

                if (!self::$_types = \Aomebo\Cache\System::loadCache(
                    $cacheParameters,
                    $cacheKey,
                    \Aomebo\Cache\System::FORMAT_SERIALIZE,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    )
                ) {
                    self::_loadResponsesInDirectory(self::_getResponsesDir());
                    self::_loadResponsesInDirectory(self::_getResponsesSiteDir());
                }

            } else {

                \Aomebo\Cache\System::clearCache(
                    $cacheParameters,
                    null,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                );

                self::_loadResponsesInDirectory(self::_getResponsesDir());
                self::_loadResponsesInDirectory(self::_getResponsesSiteDir());

                \Aomebo\Cache\System::saveCache(
                    $cacheParameters,
                    $cacheKey,
                    self::$_types,
                    \Aomebo\Cache\System::FORMAT_SERIALIZE,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                );

            }

            // Sort responses based on priority here.
            usort(self::$_types, '\Aomebo\Response\Handler::compareResponses');

        }

        /**
         * @internal
         * @static
         * @param string $directory
         */
        private static function _loadResponsesInDirectory($directory)
        {
            if (!empty($directory)
                && is_dir($directory)
            ) {

                $dirItems = scandir($directory);

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
                        $path = $directory . DIRECTORY_SEPARATOR . $dirItem;

                        $configName = strtolower($itemName);

                        // Is response enabled in config?
                        if (\Aomebo\Configuration::getSetting(
                            'responses,' . $configName)
                        ) {

                            try {

                                require_once($path);

                                if (class_exists($itemClassName, false)) {

                                    self::$_types[] = new $itemClassName();

                                }

                            } catch (\Exception $e) {}

                        }

                    }
                }
            }
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getResponsesDir()
        {

            $path = __DIR__ . DIRECTORY_SEPARATOR . self::RESPONSES_DIR;

            if (!is_dir($path)) {
                \Aomebo\Filesystem::makeDirectory($path);
            }

            return $path;
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getResponsesSiteDir()
        {

            $path = _SYSTEM_SITE_ROOT_ . DIRECTORY_SEPARATOR . self::RESPONSES_DIR;

            if (!is_dir($path)) {
                \Aomebo\Filesystem::makeDirectory($path);
            }

            return $path;
        }

    }

}
