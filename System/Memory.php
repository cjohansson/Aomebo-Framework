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
namespace Aomebo\System
{

    /**
     * @method static \Aomebo\System\Memory getInstance()
     */
    class Memory extends \Aomebo\Singleton
    {

        /**
         * This method checks if available memory on server is
         * enough to run this request.
         *
         * @static
         * @return bool
         */
        public static function systemHasEnoughMemory()
        {

            $required =
                \Aomebo\Configuration::getSetting('application,memory required');
            $free =
                self::getSystemFreeMemory();
            return $free >= $required;

        }

        /**
         * @static
         * @return int      Memory limit in mega-bytes
         */
        public static function getSystemMemoryLimit()
        {

            $memoryLimit = ini_get('memory_limit');
            if ($memoryLimit == '-1') {
                $memoryLimitInMegaBytes = 1024*1024*1024;
            } else if (stripos($memoryLimit, 'K') !== false)
            {
                  $memoryLimitRaw = (int) str_ireplace('K', '', $memoryLimit);
                  $memoryLimitInMegaBytes = (int) ($memoryLimitRaw / 1024);
            } else if (stripos($memoryLimit, 'M') !== false
            ) {
                $memoryLimitInMegaBytes = (int) str_ireplace('M', '', $memoryLimit);
            } else if (stripos($memoryLimit, 'G') !== false
            ) {
                $memoryLimitRaw = (int) str_ireplace('M', '', $memoryLimit);
                $memoryLimitInMegaBytes = (int) ($memoryLimitRaw * 1024);
            } else {
                $memoryLimitInMegaBytes = 0;
            }
            return $memoryLimitInMegaBytes;

        }

        /**
         * @static
         * @return int      Memory usage in mega-bytes.
         */
        public static function getSystemMemoryUsage()
        {

            $usageInBytes = memory_get_usage();

            // Convert to mega bytes
            $usage = ($usageInBytes / 1024 / 1024);

            return (int) $usage;

        }

        /**
         * @static
         * @return int      Memory peak-usage in mega-bytes
         */
        public static function getSystemMemoryPeakUsage()
        {

            $usageInBytes = memory_get_peak_usage();

            // Convert to mega bytes
            $usage = ($usageInBytes / 1024 / 1024);

            return (int) $usage;

        }

        /**
         * @static
         * @return int      Free memory in mega-bytes.
         */
        public static function getSystemFreeMemory()
        {
            return self::getSystemMemoryLimit()
                - self::getSystemMemoryUsage();
        }

    }
}
