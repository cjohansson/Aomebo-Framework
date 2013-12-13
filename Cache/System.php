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
namespace Aomebo\Cache
{

    /**
     * @method static \Aomebo\Cache\System getInstance()
     */
    class System extends \Aomebo\Singleton
    {

        /**
         * @var int
         */
        const FORMAT_RAW = 0;

        /**
         * @var int
         */
        const FORMAT_JSON_ENCODE = 1;

        /**
         * @var int
         */
        const FORMAT_SERIALIZE = 2;

        /**
         *
         */
        public function __construct()
        {
            if (!$this->_isConstructed()) {
                parent::__construct();
                $this->_flagThisConstructed();
            }
        }

        /**
         * @static
         * @param string $parameters
         * @param string|null [$key = null]
         * @param int [$format = self::FORMAT_RAW]
         * @return mixed|bool
         */
        public static function loadCache($parameters,
            $key = null,
            $format = self::FORMAT_RAW)
        {
            if (self::cacheExists($parameters, $key)) {
                if ($cachePath = self::getCachePath($parameters, $key)) {

                    $formattedData = file_get_contents($cachePath);

                    // Format data
                    if ($format == self::FORMAT_JSON_ENCODE) {
                        $data = json_decode($formattedData, true);
                    } else if ($format == self::FORMAT_SERIALIZE) {
                        $data = unserialize($formattedData);
                    } else {
                        $data = $formattedData;
                    }

                    return $data;

                }
            }
            return false;
        }

        /**
         * @static
         * @param string $parameters
         * @param string|null [$key = null]
         * @param mixed|string $data
         * @param int [$format = self::FORMAT_RAW]
         * @return bool
         */
        public static function saveCache($parameters,
            $key = null,
            $data, $format = self::FORMAT_RAW)
        {
            if (isset($data)) {
                if ($cachePath = self::getCachePath($parameters, $key)) {

                    // Make directories if needed
                    \Aomebo\Filesystem::makeDirectories($cachePath);

                    // Format data
                    if ($format == self::FORMAT_JSON_ENCODE) {
                        $formattedData = json_encode($data);
                    } else if ($format == self::FORMAT_SERIALIZE) {
                        $formattedData = serialize($data);
                    } else {
                        $formattedData = $data;
                    }

                    if (!empty($key)) {
                        self::clearCache($parameters, $key);
                    }

                    if (\Aomebo\Filesystem::makeFile(
                        $cachePath,
                        $formattedData)
                    ) {
                        return true;
                    }

                }
            }
            return false;
        }

        /**
         * @param string $parameters
         * @param string|null [$key = null]
         * @return bool
         */
        public static function clearCache($parameters, $key = null)
        {
            if (self::cacheExists($parameters, $key)) {

                // Make directories if needed
                $path = \Aomebo\Application::getCacheDir()
                    . DIRECTORY_SEPARATOR . $parameters;
                if (!is_dir($path)) {
                    \Aomebo\Filesystem::makeDirectories($path);
                }

                if (!isset($key)) {
                    if (\Aomebo\Filesystem::deleteFilesInDirectory($path)) {
                        return true;
                    }
                } else {
                    $path .= DIRECTORY_SEPARATOR . $key;
                    if (\Aomebo\Filesystem::deleteFile($path)) {
                        return true;
                    }
                }

            }

            return false;

        }


        /**
         * @static
         * @param string $parameters
         * @param string|null [$key = null]
         * @return string
         */
        public static function getCachePath($parameters, $key = null)
        {
            $path = \Aomebo\Application::getCacheDir();
            if (!empty($parameters)) {
                if ($explode = explode('/', $parameters)) {
                    foreach ($explode as $parameter)
                    {
                        $path .= DIRECTORY_SEPARATOR . $parameter;
                    }
                }
            }
            if (isset($key)) {
                $path .= DIRECTORY_SEPARATOR . $key;
            }
            return $path;
        }

        /**
         * @static
         * @param string $parameters            Like 'Runtime/Header/Logged-in'
         * @param string|null [$key = null]
         * @return bool
         */
        public static function cacheExists($parameters, $key = null)
        {
            if (!empty($parameters)) {

                $path = \Aomebo\Application::getCacheDir();
                if (!empty($parameters)) {
                    if ($explode = explode('/', $parameters)) {
                        foreach ($explode as $parameter)
                        {
                            $path .= DIRECTORY_SEPARATOR . $parameter;
                            if (!is_dir($path)) {
                                return false;
                            }
                        }
                    }
                }

                if (isset($key)) {
                    $path .= DIRECTORY_SEPARATOR . $key;
                    if (!file_exists($path)
                        || !is_file($path)
                    ) {
                        return false;
                    }
                }

                return true;

            }

            return false;

        }

    }
}
