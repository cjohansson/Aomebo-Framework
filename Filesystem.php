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
     * @method static \Aomebo\Filesystem getInstance()
     */
    class Filesystem extends Singleton
    {

        /**
         * @internal
         * @static
         * @var string|null
         */
        private static $_chmodOct = null;

        /**
         * @internal
         * @static
         * @var int|null
         */
        private static $_chmodDec = null;

        /**
         * @internal
         * @static
         * @var array|null
         */
        private static $_baseDirs = null;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_diremTimes = array();

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_recDiremTimes = array();

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_filemtimesCache = array();

        /**
         * @static
         * @param string $path
         * @return bool
         */
        public static function isPathInBasedir($path)
        {
            if (!empty($path)) {
                if ($baseDirs = self::getBasedirs())
                {
                    foreach ($baseDirs as $baseDir)
                    {
                        if (strlen($path) >= strlen($baseDir)) {
                            if (substr($path, 0, strlen($baseDir)) ==
                                $baseDir
                            ) {
                                return true;
                            }
                        }
                    }
                }
            }
            return false;
        }

        /**
         * @static
         * @return bool|array
         */
        public static function getBasedirs()
        {
            if (!isset(self::$_baseDirs)) {
                if (\Aomebo\Configuration::isLoaded()) {
                    self::$_baseDirs = \Aomebo\Configuration::getSetting('paths,basedirs');
                } else {
                    return false;
                }
            }
            return self::$_baseDirs;
        }

        /**
         * @static
         * @param string $absolutePath
         * @param bool [$throwExceptions = true]
         * @param bool [$trailingIsDirectory = false]
         * @throws \Exception
         * @return bool
         */
        public static function makeDirectories($absolutePath,
            $throwExceptions = true,
            $trailingIsDirectory = false)
        {
            $accBool = true;
            if (\Aomebo\Application::isWritingnabled()) {
                if ($components = explode(
                    DIRECTORY_SEPARATOR, $absolutePath)
                ) {

                    $path = '';
                    $pathIndex = 0;
                    $pathSize = count($components);

                    foreach ($components as $component)
                    {
                        if ($component !== '') {
                            if ($pathIndex < $pathSize - 1
                                || $trailingIsDirectory
                            ) {
    
                                if ($pathIndex > 0) {
                                    $path .= DIRECTORY_SEPARATOR;
                                }
    
                                $path .= $component;
    
                                if (\Aomebo\Configuration::isLoaded()
                                    && self::isPathInBasedir($path)
                                ) {
    
                                    if (!is_dir($path)) {
                                        if (!self::makeDirectory(
                                            $path,
                                            $throwExceptions)
                                        ) {
                                            $accBool = false;
                                        }
                                    }
    
                                }
    
                            }
                        }
    
                        $pathIndex++;
    
                    }
    
                }
            }

            return $accBool;

        }

        /**
         * @static
         * @param string $filename
         * @param bool [$validate = true]
         * @return int|bool
         */
        public static function getFileLastModificationTime($filename, $validate = true)
        {
            if (!isset(self::$_filemtimesCache[$filename])) {
                self::$_filemtimesCache[$filename] = false;
                if (!$validate
                    || (!\Aomebo\Configuration::isLoaded()
                        || self::isPathInBasedir($filename))
                ) {
                    if ($filemtime = @filemtime($filename)) {
                        self::$_filemtimesCache[$filename] = $filemtime;
                    }
                }
            }
            return self::$_filemtimesCache[$filename];
        }

        /**
         * @static
         * @param string $directory
         * @param bool [$recursive = true]
         * @param int|bool [$maxDepth = 2]
         * @param bool [$validate = true]
         * @return int|bool
         */
        public static function getDirectoryLastModificationTime($directory,
            $recursive = true, $maxDepth = 2, $validate = true)
        {
            if (!$validate
                || (!\Aomebo\Configuration::isLoaded()
                    || self::isPathInBasedir($directory))
            ) {
                if (($recursive
                    && !isset(self::$_recDiremTimes[$directory][$maxDepth]))
                    || (!$recursive
                        && !isset(self::$_diremTimes[$directory][$maxDepth]))
                ) {

                    if (is_dir($directory)) {

                        $diremtime = 0;
                        if ($subitems = scandir($directory)) {

                            foreach ($subitems as $subitem)
                            {

                                // Is not parent directory?
                                if ($subitem != '..') {

                                    $path = $directory . '/' . $subitem;
                                    $subitemtime = 0;

                                    // Is it a directory?
                                    if (is_dir($path)) {

                                        // Is it current directory?
                                        if ($subitem == '.') {

                                            if ($filemtime = self::getFileLastModificationTime(
                                                $path,
                                                false)
                                            ) {
                                                $subitemtime = $filemtime;
                                            }

                                        // Are we doing recursive?
                                        } else if ($recursive
                                            && ($maxDepth === false
                                            || $maxDepth > 0)
                                        ) {
                                            $subitemtime = self::getDirectoryLastModificationTime(
                                                $path,
                                                $recursive,
                                                ($maxDepth === false ? false : $maxDepth - 1),
                                                false
                                            );
                                        }

                                    // Is it a file?
                                    } else if (is_file($path)) {
                                        $subitemtime = self::getFileLastModificationTime(
                                            $path, false
                                        );
                                    }

                                    // Is modification time above last maximum?
                                    if ($subitemtime > $diremtime) {
                                        $diremtime = $subitemtime;
                                    }

                                }
                            }

                        }

                        if ($recursive) {
                            self::$_recDiremTimes[$directory][$maxDepth] = $diremtime;
                        } else {
                            self::$_diremTimes[$directory][$maxDepth] = $diremtime;
                        }

                    } else {
                        if ($recursive) {
                            self::$_recDiremTimes[$directory][$maxDepth] = false;
                        } else {
                            self::$_diremTimes[$directory][$maxDepth] = false;
                        }
                    }
                }

                if ($recursive) {
                    return self::$_recDiremTimes[$directory][$maxDepth];
                } else {
                    return self::$_diremTimes[$directory][$maxDepth];
                }

            }

            return false;

        }

        /**
         * @static
         * @param string $absolutePath
         * @param bool [$throwExceptions = true]
         * @throws \Exception
         * @return bool
         */
        public static function makeDirectory($absolutePath,
            $throwExceptions = true)
        {
            if (\Aomebo\Configuration::isLoaded()
                && \Aomebo\Application::isWritingnabled()
                && self::isPathInBasedir($absolutePath)
            ) {
                if (is_dir($absolutePath)) {
                    return true;
                } else {
                    try {

                        if (mkdir($absolutePath)) {

                            self::applyPermissions(
                                $absolutePath,
                                $throwExceptions
                            );

                            return true;

                        } else {

                            if ($throwExceptions) {
                                Throw new \Exception(
                                    sprintf(
                                        self::systemTranslate(
                                            'Could not make directory: "%s"'
                                        ),
                                        $absolutePath
                                    )
                                );
                            }

                        }

                    } catch (\Exception $e) {

                        if ($throwExceptions) {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate('Could not make directory: "%s"'),
                                    $absolutePath
                                )
                            );
                        }

                    }
                }
            }

            return false;

        }

        /**
         * @param string $absolutePath
         * @param bool [$throwException = true]
         * @return string
         * @throws \Exception
         */
        public static function getFileContents($absolutePath,
            $throwException = true)
        {

            if (!\Aomebo\Configuration::isLoaded()
                || self::isPathInBasedir($absolutePath)
            ) {
                if (!empty($absolutePath)
                    && file_exists($absolutePath)
                ) {
                    try {
                        return file_get_contents($absolutePath);
                    } catch (\Exception $e) {
                        if ($throwException) {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate('Opening file: "%s" returned error: "%s"'),
                                    $absolutePath,
                                    $e->getMessage()
                                )
                            );
                        }
                        return false;
                    }

                } else {
                    if ($throwException) {
                        Throw new \Exception(
                            self::systemTranslate('Invalid parameters')
                        );
                    }
                }
            }
            return '';
        }

        /**
         * @static
         * @param string $absolutePath
         * @param int [$size = 0]                             Filesize in bytes.
         * @return bool
         */
        public static function truncateFile($absolutePath, $size = 0)
        {
            if (\Aomebo\Configuration::isLoaded()
                && \Aomebo\Application::isWritingnabled()
                && self::isPathInBasedir($absolutePath)
            ) {
                if (!empty($absolutePath)
                    && isset($size)
                ) {
                    if (file_exists($absolutePath)) {
                        if (filesize($absolutePath) > $size) {
                            if ($file = fopen($absolutePath, 'br+')) {
                                if (flock($file, LOCK_EX)) {
                                    if (ftruncate($file, (int) $size)) {
                                        if (flock($file, LOCK_UN)) {
                                            return true;
                                        }
                                    }
                                    flock($file, LOCK_UN);
                                }
                                fclose($file);
                            }
                        } else {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        /**
         * @static
         * @param string $absolutePath
         * @param string [$contents = '']
         * @param bool [$throwExceptions = true]
         * @throws \Exception
         * @return bool
         */
        public static function makeFile($absolutePath, $contents = '',
            $throwExceptions = true)
        {
            if (\Aomebo\Configuration::isLoaded()
                && \Aomebo\Application::isWritingnabled()
                && self::isPathInBasedir($absolutePath)
            ) {
                self::makeDirectories($absolutePath);

                if (self::_writeFile(
                    $absolutePath,
                    $contents,
                    null,
                    true,
                    $throwExceptions)
                ) {
                    return true;
                }
            }
            return false;

        }

        /**
         * @static
         * @param string $absolutePath
         * @return bool
         */
        public static function deleteFile($absolutePath)
        {
            if (\Aomebo\Configuration::isLoaded()
                && \Aomebo\Application::isWritingnabled()
                && self::isPathInBasedir($absolutePath)
            ) {
                if (unlink($absolutePath)) {
                    self::_clearCache();
                    return true;
                }
            }
            return false;
        }

        /**
         * @static
         * @param string $absolutePath
         * @return bool
         */
        public static function deleteFilesInDirectory($absolutePath)
        {
            if (\Aomebo\Configuration::isLoaded()
                && \Aomebo\Application::isWritingnabled()
                && self::isPathInBasedir($absolutePath)
            ) {
                if (!empty($absolutePath)) {
                    if (self::hasItemsInDirectory($absolutePath))
                    {

                        $items = scandir($absolutePath);

                        foreach ($items as $item)
                        {
                            if ($item != '.'
                                && $item != '..'
                            ) {
                                $absoluteItem =
                                    $absolutePath . DIRECTORY_SEPARATOR . $item;
                                if (is_file($absoluteItem)) {
                                    self::deleteFile($absoluteItem);
                                } else if (is_dir($absoluteItem)) {
                                    self::deleteDirectory($absoluteItem, true);
                                }
                            }
                        }

                        return true;

                    }
                }
            }
            return false;
        }

        /**
         * @static
         * @param string $absolutePath
         * @return bool
         */
        public static function hasItemsInDirectory($absolutePath)
        {
            if (!\Aomebo\Configuration::isLoaded()
                || self::isPathInBasedir($absolutePath)
            ) {
                if (!empty($absolutePath)) {
                    if (is_dir($absolutePath)) {
                        $items = scandir($absolutePath);
                        if (isset($items)
                            && is_array($items)
                            && count($items) > 2
                        ) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        /**
         *
         */
        public function __construct()
        {
            if (!$this->_isConstructed()) {
                parent::__construct();
                self::loadCache();
            }
        }

        /**
         *
         */
        public function __destruct()
        {
            $this->saveCache();
        }

        /**
         * @static
         * @param string $absolutePath
         * @param bool [$deleteFilesInDirectory = false]
         * @return bool
         */
        public static function deleteDirectory($absolutePath,
            $deleteFilesInDirectory = false)
        {
            if (\Aomebo\Configuration::isLoaded()
                && \Aomebo\Application::isWritingnabled()
                && self::isPathInBasedir($absolutePath)
            ) {
                if (!empty($absolutePath)) {
                    if (is_dir($absolutePath)) {

                        if (self::hasItemsInDirectory($absolutePath)) {
                            if ($deleteFilesInDirectory) {
                                self::deleteFilesInDirectory($absolutePath);
                                if (rmdir($absolutePath)) {
                                    return true;
                                }
                            }
                        } else {
                            if (rmdir($absolutePath)) {
                                return true;
                            }
                        }
                    }
                }
            }
            return false;
        }

        /**
         * @static
         * @param string $absolutePath
         * @param string [$contents = '']
         * @return bool
         */
        public static function appendFile($absolutePath, $contents = '')
        {
            if (\Aomebo\Configuration::isLoaded()
                && \Aomebo\Application::isWritingnabled()
                && self::isPathInBasedir($absolutePath)
            ) {
                if (self::_writeFile($absolutePath, $contents, null, false)) {
                    self::_clearCache();
                    return true;
                }
            }
            return false;
        }

        /**
         * @static
         * @param string $path
         * @param bool [$throwExceptions = true]
         * @param string|null [$chmod = null]
         * @throws \Exception
         */
        public static function applyPermissions($path,
            $throwExceptions = true, $chmod = null)
        {
            if (\Aomebo\Configuration::isLoaded()
                && \Aomebo\Application::isWritingnabled()
                && self::isPathInBasedir($path)
            ) {

                // Is a custom chmod specified?
                if (isset($chmod)) {
                    if (strlen($chmod) == 3) {
                        $octChmod = octdec('0' . $chmod);
                    } else if (strlen($chmod) == 4) {
                        $octChmod = octdec($chmod);
                    } else {
                        if ($throwExceptions) {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate('Invalid file mode %s specified.'),
                                    $chmod
                                )
                            );
                        } else {
                            $octChmod = self::_getChmod();
                        }
                    }
                } else {
                    $octChmod = self::_getChmod();
                }

                if (!chmod($path, $octChmod)) {
                    if ($throwExceptions) {
                        Throw new \Exception(
                            sprintf(
                                self::systemTranslate(
                                    'Could not set chmod for file "%s" to oct: "%s".'
                                ),
                                $path,
                                $octChmod
                            )
                        );
                    }
                }

                if (self::isSystemSuperUser()) {

                    // Get configuration
                    $ownerUserName =
                        \Aomebo\Configuration::getSetting(
                            'paths,file owner username');
                    $ownerGroupName =
                        \Aomebo\Configuration::getSetting(
                            'paths,file owner groupname');

                    // Set owner
                    if (!chown($path, $ownerUserName)) {
                        if ($throwExceptions) {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate(
                                        'Could not set owner username to "%s" '
                                        . 'for "%s"'
                                    ),
                                    $ownerUserName,
                                    $path
                                )
                            );
                        }
                    }

                    // Set group
                    if (!chgrp($path, $ownerGroupName)) {
                        if ($throwExceptions) {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate(
                                        'Could not set owner group-name permissions to "%s" for "%s"'
                                    ),
                                    $ownerGroupName,
                                    $path
                                )
                            );
                        }
                    }

                }

                self::_clearCache();
            }
        }

        /**
         * @static
         * @return string|bool
         */
        public static function getSystemUser()
        {
            if (\Aomebo\Application::isExecEnabled()) {
                $return = shell_exec('whoami');
                return $return;
            } else {
                return false;
            }
        }

        /**
         * @static
         */
        public static function loadCache()
        {
            $cacheExpiration = \Aomebo\Configuration::
                getSetting('framework,fileemtime_expiration');

            if ($cacheExpiration > 0) {
                $cacheParameters = 'Filesystem/Filemtimes';
                $cacheKey = $cacheExpiration . '-'
                    . floor(time() / $cacheExpiration);

                if (\Aomebo\Cache\System::cacheExists(
                    $cacheParameters,
                    $cacheKey)
                ) {
                    self::$_filemtimesCache = \Aomebo\Cache\System::loadCache(
                        $cacheParameters,
                        $cacheKey,
                        \Aomebo\Cache\System::FORMAT_JSON_ENCODE
                    );
                }
            }
        }

        /**
         * @static
         */
        public static function saveCache()
        {

            $cacheExpiration = \Aomebo\Configuration::
                getSetting('framework,fileemtime_expiration');

            if ($cacheExpiration > 0) {

                $cacheParameters = 'Filesystem/Filemtimes';
                $cacheKey = $cacheExpiration . '-'
                    . floor(time() / $cacheExpiration);

                if (!\Aomebo\Cache\System::cacheExists(
                    $cacheParameters,
                    $cacheKey)
                ) {

                    \Aomebo\Cache\System::clearCache($cacheParameters);
                    \Aomebo\Cache\System::saveCache(
                        $cacheParameters,
                        $cacheKey,
                        self::$_filemtimesCache,
                        \Aomebo\Cache\System::FORMAT_JSON_ENCODE
                    );

                }

            }

        }

        /**
         * @static
         * @return bool
         */
        public static function isSystemSuperUser()
        {
            if ($user = self::getSystemUser()) {
                return ($user === 'root');
            }
            return false;
        }

        /**
         * @internal
         * @static
         */
        private static function _clearCache()
        {
            clearstatcache();
        }

        /**
         * @internal
         * @static
         * @param string $absolutePath
         * @param string [$contents = '']
         * @param null|string [$chmod = null]
         * @param bool [$truncate = true]
         * @param bool [$throwExceptions = true]
         * @throws \Exception
         * @return bool
         */
        private static function _writeFile($absolutePath, $contents = '',
            $chmod = null, $truncate = true, $throwExceptions = true)
        {
            if (\Aomebo\Configuration::isLoaded()
                && \Aomebo\Application::isWritingnabled()
                && self::isPathInBasedir($absolutePath)
            ) {
                if ($file = fopen($absolutePath, 'ab+')) {
                    if (flock($file, LOCK_EX)) {
                        if ($truncate) {
                            ftruncate($file, 0);
                        }
                        if (isset($contents)) {
                            fwrite($file, $contents);
                        }
                        fflush($file);
                        flock($file, LOCK_UN);
                        fclose($file);
                        self::applyPermissions($absolutePath, $throwExceptions, $chmod);
                        return true;
                    }
                    fclose($file);
                }
            }
            return false;
        }

        /**
         * @internal
         * @static
         * @throws \Exception
         * @return int
         */
        private static function _getChmod()
        {

            if (!isset(self::$_chmodDec)) {

                if (!isset(self::$_chmodOct)) {

                    $defaultChmod = \Aomebo\Configuration::getSetting(
                        'paths,default file mod');

                    if (strlen($defaultChmod) == 3) {
                        $defaultChmod = '0' . $defaultChmod;
                    } else if (strlen($defaultChmod) != 4) {
                        Throw new \Exception(
                            self::systemTranslate(
                                'Invalid default file mode specified in configuration')
                        );
                    }

                    self::$_chmodOct = $defaultChmod;

                }

                self::$_chmodDec = octdec(self::$_chmodOct);

            }

            return self::$_chmodDec;

        }

    }
}
