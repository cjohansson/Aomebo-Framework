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
         * @var string
         */
        const TABLE = 'cache';

        /**
         * @var int
         */
        const CACHE_STORAGE_LOCATION_FILESYSTEM = 0;

        /**
         * @var int
         */
        const CACHE_STORAGE_LOCATION_DATABASE = 1;

        /**
         *
         */
        public function __construct()
        {
            if (!$this->_isConstructed()) {

                parent::__construct();

                if (!self::isInstalled()) {
                    self::install();
                }

                $this->_flagThisConstructed();
            }
        }

        /**
         * @static
         * @return string
         */
        public static function getTable()
        {
            return '{TABLE PREFIX}{SYSTEM TABLE PREFIX}' . self::TABLE;
        }

        /**
         * @static
         * @return string
         */
        public static function isInstalled()
        {
            return \Aomebo\Database\Adapter::tableExists(
                self::getTable());
        }

        /**
         *
         */
        public static function install()
        {

            \Aomebo\Database\Adapter::query(
                'CREATE TABLE IF NOT EXISTS `' . self::getTable() . '`('
                . '`cache_parameters` VARCHAR(200) NOT NULL DEFAULT "",'
                . '`cache_key` VARCHAR(200) NOT NULL DEFAULT "",'
                . '`cache_data` LONGBLOB NOT NULL DEFAULT "",'
                . '`cache_added` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",'
                . '`cache_edited` TIMESTAMP NOT NULL DEFAULT "0000-00-00 00:00:00" ON UPDATE CURRENT_TIMESTAMP, '
                . 'PRIMARY KEY(`cache_parameters`,`cache_key`))'
            );

        }

        /**
         * @static
         * @param string $parameters
         * @param string|null [$key = null]
         * @param int [$format = self::FORMAT_RAW]
         * @param int [$storage = self::CACHE_STORAGE_LOCATION_FILESYSTEM]
         * @return mixed|bool
         */
        public static function loadCache(
            $parameters,
            $key = null,
            $format = self::FORMAT_RAW,
            $storage = self::CACHE_STORAGE_LOCATION_FILESYSTEM)
        {

            if ($storage == self::CACHE_STORAGE_LOCATION_FILESYSTEM) {

                return self::loadCacheInFilesystem(
                    $parameters,
                    $key,
                    $format
                );

            } else if ($storage == self::CACHE_STORAGE_LOCATION_DATABASE) {

                return self::loadCacheInDatabase(
                    $parameters,
                    $key,
                    $format
                );

            }

            return false;

        }

        /**
         * @static
         * @param string $parameters
         * @param string|null [$key = null]
         * @param int [$format = self::FORMAT_RAW]
         * @return mixed|bool
         */
        public static function loadCacheInFilesystem(
            $parameters,
             $key = null,
             $format = self::FORMAT_RAW)
        {

            if (self::cacheExistsInFilesystem($parameters, $key)) {
                if ($cachePath = self::getCachePath($parameters, $key)) {

                    if ($formattedData = \Aomebo\Filesystem::getFileContents(
                        $cachePath, false)
                    ) {

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
            }

            return false;

        }

        /**
         * @static
         * @param string $parameters
         * @param string|null [$key = null]
         * @param int [$format = self::FORMAT_RAW]
         * @return mixed|bool
         */
        public static function loadCacheInDatabase(
            $parameters,
            $key = null,
            $format = self::FORMAT_RAW)
        {

            if (self::cacheExistsInDatabase($parameters, $key)) {

                if (!isset($key)) {

                    if ($resultset = \Aomebo\Database\Adapter::query(
                        'SELECT * FROM `' . self::getTable() . '` '
                        . 'WHERE `cache_parameters` = {parameters}'
                        . 'LIMIT 1',
                        array(
                            'parameters' => array(
                                'value' => $parameters,
                                'quoted' => true,
                            ),
                        ))
                    ) {

                        $row = $resultset->fetchAssocAndFree();

                        if (isset($row['cache_data'])) {

                            $formattedData = $row['cache_data'];

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

                } else {

                    if ($resultset = \Aomebo\Database\Adapter::query(
                        'SELECT * FROM `' . self::getTable() . '` '
                        . 'WHERE `cache_parameters` = {parameters} '
                        . 'AND `cache_key` = {key} '
                        . 'LIMIT 1',
                        array(
                            'parameters' => array(
                                'value' => $parameters,
                                'quoted' => true,
                            ),
                            'key' => array(
                                'value' => $key,
                                'quoted' => true,
                            )
                        ))
                    ) {

                        $row = $resultset->fetchAssocAndFree();

                        if (isset($row['cache_data'])) {

                            $formattedData = $row['cache_data'];

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
         * @param int $storage = self::CACHE_STORAGE_LOCATION_FILESYSTEM
         * @return bool
         */
        public static function saveCache(
            $parameters,
            $key = null,
            $data,
            $format = self::FORMAT_RAW,
            $storage = self::CACHE_STORAGE_LOCATION_FILESYSTEM)
        {

            if ($storage == self::CACHE_STORAGE_LOCATION_FILESYSTEM) {

                return self::saveCacheInFilesystem(
                    $parameters,
                    $key,
                    $data,
                    $format);

            } else if ($storage == self::CACHE_STORAGE_LOCATION_DATABASE) {

                return self::saveCacheInDatabase(
                    $parameters,
                    $key,
                    $data,
                    $format
                );

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
        public static function saveCacheInFilesystem(
            $parameters,
            $key = null,
            $data,
            $format = self::FORMAT_RAW)
        {
            if (isset($data)) {
                if ($cachePath = self::getCachePath(
                    $parameters, $key)
                ) {

                    // Make directories if needed
                    if (\Aomebo\Filesystem::makeDirectories($cachePath, false)) {

                        // Format data
                        if ($format == self::FORMAT_JSON_ENCODE) {
                            $formattedData = json_encode($data);
                        } else if ($format == self::FORMAT_SERIALIZE) {
                            $formattedData = serialize($data);
                        } else {
                            $formattedData = $data;
                        }

                        if (!empty($key)) {
                            self::clearCacheInFilesystem(
                                $parameters,
                                $key
                            );
                        }

                        if (\Aomebo\Filesystem::makeFile(
                            $cachePath,
                            $formattedData,
                            false)
                        ) {
                            return true;
                        }

                    }

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
        public static function saveCacheInDatabase(
            $parameters,
            $key = null,
            $data,
            $format = self::FORMAT_RAW)
        {

            if (isset($data)) {

                // Format data
                if ($format == self::FORMAT_JSON_ENCODE) {
                    $formattedData = json_encode($data);
                } else if ($format == self::FORMAT_SERIALIZE) {
                    $formattedData = serialize($data);
                } else {
                    $formattedData = $data;
                }

                if (!empty($key)) {
                    self::clearCacheInDatabase(
                        $parameters,
                        $key
                    );
                }

                if (\Aomebo\Database\Adapter::query(
                    'INSERT IGNORE INTO `' . self::getTable() . '`('
                    . '`cache_parameters`,`cache_key`,`cache_data`,`cache_added`) '
                    . 'VALUES({parameters},{key},{data},{added})',
                    array(
                        'parameters' => array(
                            'value' => $parameters,
                            'quoted' => true,
                        ),
                        'key' => array(
                            'value' => (!empty($key) ? $key : ''),
                            'quoted' => true,
                        ),
                        'data' => array(
                            'value' => $formattedData,
                            'quoted' => true,
                        ),
                        'added' => 'NOW()',
                    ))
                ) {
                    return true;
                }
            }

            return false;

        }

        /**
         * @param string $parameters
         * @param string|null [$key = null]
         * @param int [$storage = self::CACHE_STORAGE_LOCATION_FILESYSTEM]
         * @return bool
         */
        public static function clearCache(
            $parameters,
            $key = null,
            $storage = self::CACHE_STORAGE_LOCATION_FILESYSTEM)
        {

            if ($storage == self::CACHE_STORAGE_LOCATION_FILESYSTEM) {

                return self::clearCacheInFilesystem(
                    $parameters,
                    $key
                );

            } else if ($storage == self::CACHE_STORAGE_LOCATION_DATABASE) {

                return self::clearCacheInDatabase(
                    $parameters,
                    $key
                );

            }

            return false;

        }

        /**
         * @param string $parameters
         * @param string|null [$key = null]
         * @return bool
         */
        public static function clearCacheInFilesystem(
            $parameters, $key = null)
        {
            if (self::cacheExistsInFilesystem($parameters, $key)) {

                // Make directories if needed
                $path = \Aomebo\Application::getCacheDir()
                    . DIRECTORY_SEPARATOR . $parameters;

                if (!is_dir($path)) {
                    \Aomebo\Filesystem::makeDirectories(
                        $path,
                        false,
                        true
                    );
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
         * @param string $parameters
         * @param string|null [$key = null]
         * @return bool
         */
        public static function clearCacheInDatabase(
            $parameters, $key = null)
        {
            if (self::cacheExistsInDatabase($parameters, $key)) {

                if (!isset($key)) {

                    \Aomebo\Database\Adapter::query(
                        'DELETE FROM `' . self::getTable() . '` '
                        . 'WHERE `cache_parameters` = {parameters}',
                        array(
                            'parameters' => array(
                                'value' => $parameters,
                                'quoted' => true,
                            ),
                        ));

                    return true;

                } else {

                    \Aomebo\Database\Adapter::query(
                        'DELETE FROM `' . self::getTable() . '` '
                        . 'WHERE `cache_parameters` = {parameters} '
                        . 'AND `cache_key` = {key}',
                        array(
                            'parameters' => array(
                                'value' => $parameters,
                                'quoted' => true,
                            ),
                            'key' => array(
                                'value' => $key,
                                'quoted' => true,
                            )
                        ));

                    return true;

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
         * @param int [$storage = self::CACHE_STORAGE_LOCATION_FILESYSTEM]
         * @return bool
         */
        public static function cacheExists($parameters,
            $key = null,
            $storage = self::CACHE_STORAGE_LOCATION_FILESYSTEM)
        {

            if ($storage == self::CACHE_STORAGE_LOCATION_FILESYSTEM) {

                return self::cacheExistsInFilesystem(
                    $parameters,
                    $key
                );

            } else if ($storage == self::CACHE_STORAGE_LOCATION_DATABASE) {

                return self::cacheExistsInDatabase(
                    $parameters,
                    $key
                );

            }

            return false;

        }

        /**
         * @static
         * @param string $parameters            Like 'Runtime/Header/Logged-in'
         * @param string|null [$key = null]
         * @return bool
         */
        public static function cacheExistsInFilesystem(
            $parameters, $key = null)
        {
            if (!empty($parameters)) {

                $path =
                    \Aomebo\Application::getCacheDir();

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

        /**
         * @static
         * @param string $parameters            Like 'Runtime/Header/Logged-in'
         * @param string|null [$key = null]
         * @return bool
         */
        public static function cacheExistsInDatabase(
            $parameters, $key = null)
        {
            if (!empty($parameters)) {

                if (!isset($key)) {

                    if (\Aomebo\Database\Adapter::query(
                        'SELECT * FROM `' . self::getTable() . '` '
                        . 'WHERE `cache_parameters` = {parameters} '
                        . 'LIMIT 1',
                        array(
                            'parameters' => array(
                                'value' => $parameters,
                                'quoted' => true,
                            ),
                        ))
                    ) {
                        return true;
                    }

                } else {

                    if (\Aomebo\Database\Adapter::query(
                        'SELECT * FROM `' . self::getTable() . '` '
                        . 'WHERE `cache_parameters` = {parameters} '
                        . 'AND `cache_key` = {key} '
                        . 'LIMIT 1',
                        array(
                            'parameters' => array(
                                'value' => $parameters,
                                'quoted' => true,
                            ),
                            'key' => array(
                                'value' => $key,
                                'quoted' => true,
                            )
                        ))
                    ) {
                        return true;
                    }

                }

            }

            return false;

        }

    }
}
