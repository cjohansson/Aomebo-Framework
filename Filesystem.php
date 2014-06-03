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
namespace Aomebo
{

    /**
     * @method static \Aomebo\Filesystem getInstance()
     */
    class Filesystem extends Singleton
    {

        /**
         * @internal
         * @var string|null
         */
        private static $_chmodOct = null;

        /**
         * @internal
         * @var int|null
         */
        private static $_chmodDec = null;

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

            if ($components = explode(
                DIRECTORY_SEPARATOR, $absolutePath)
            ) {

                $path = '';
                $pathIndex = 0;
                $pathSize = sizeof($components);

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

                    $pathIndex++;

                }

            }

            return $accBool;

        }

        /**
         * @static
         * @param string $file
         * @return int|bool
         */
        public static function getFileLastModificationTime($filename)
        {
            try {
                if ($filemtime = filemtime($filename)) {
                    return $filemtime;
                }
            } catch (\Exception $e) {}
            return false;
        }

        /**
         * @static
         * @param string $directory
         * @return int|bool
         */
        public static function getDirectoryLastModificationTime($directory)
        {
            if (substr($directory, -2) != '/.') {
                if (substr($directory, -1) == '/') {
                    $directory .= '.';
                } else {
                    $directory .= '/.';
                }
            }
            try {
                if ($diremtime = filemtime($directory)) {
                    return $diremtime;
                }
            } catch (\Exception $e) {}
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
                                'Could not make directory: '
                                    . '"' . $absolutePath . '"');
                        }

                    }

                } catch (\Exception $e) {

                    if ($throwExceptions) {
                        Throw new \Exception(
                            'Could not make directory: '
                                . '"' . $absolutePath . '"');
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

            if (!empty($absolutePath)
                && file_exists($absolutePath)
            ) {

                if ($file = fopen($absolutePath, 'ab+')) {
                    if (flock($file, LOCK_SH)) {
                        $fileContents =
                            file_get_contents($absolutePath);
                        flock($file, LOCK_UN);
                        fclose($file);
                        return $fileContents;
                    }
                }

            } else {
                if ($throwException) {
                    Throw new \Exception('Invalid parameters');
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

            return false;

        }

        /**
         * @static
         * @param string $absolutePath
         * @return bool
         */
        public static function deleteFile($absolutePath)
        {
            if (unlink($absolutePath)) {
                self::_clearCache();
                return true;
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
            return false;
        }

        /**
         * @static
         * @param string $absolutePath
         * @return bool
         */
        public static function hasItemsInDirectory($absolutePath)
        {
            if (!empty($absolutePath)) {
                if (is_dir($absolutePath)) {
                    $items = scandir($absolutePath);
                    if (isset($items)
                        && is_array($items)
                        && sizeof($items) > 2
                    ) {
                        return true;
                    }
                }
            }
            return false;
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
            if (self::_writeFile($absolutePath, $contents, null, false)) {
                self::_clearCache();
                return true;
            }
            return false;
        }

        /**
         * @static
         * @param string $path
         * @param bool [$throwExceptions = true]
         * @throws \Exception
         */
        public static function applyPermissions($path, $throwExceptions = true)
        {

            if (!chmod($path, self::_getChmod())) {
                if ($throwExceptions) {
                    Throw new \Exception(
                        'Could not set chmod for file "' . $path . '" '
                        . 'to oct: ' . self::$_chmodOct
                        . ', dec: ' . self::$_chmodDec);
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
                            'Could not set owner username to "' . $ownerUserName
                                . '" for "' . $path . '" in ' . __FUNCTION__
                                . ' in ' . __FILE__);
                    }
                }

                // Set group
                if (!chgrp($path, $ownerGroupName)) {
                    if ($throwExceptions) {
                        Throw new \Exception(
                            'Could not set owner groupnamegroup permissions to "' . $ownerGroupName
                                . '" for "' . $path . '" in ' . __FUNCTION__
                                . ' in ' . __FILE__);
                    }
                }

            }

            self::_clearCache();

        }

        /**
         * @static
         * @return string
         */
        public static function getSystemUser()
        {
            $return = exec('whoami');
            return $return;
        }


        /**
         * @static
         * @return bool
         */
        public static function isSystemSuperUser()
        {
            return (self::getSystemUser() === 'root');
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
                    self::applyPermissions($absolutePath, $throwExceptions);
                    return true;
                }
                fclose($file);
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
                            'Invalid default file mode specified in configuration');
                    }

                    self::$_chmodOct = $defaultChmod;

                }

                self::$_chmodDec = octdec(self::$_chmodOct);

            }

            return self::$_chmodDec;

        }

    }
}
