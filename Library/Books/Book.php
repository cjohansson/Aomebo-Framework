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
namespace Aomebo\Library\Books
{

    /**
     *
     */
    abstract class Book extends \Aomebo\Base
    {

        /**
         * This method loads library into scope.
         *
         * @return bool
         */
        abstract public function load();

        /**
         * This method requires a file relative to child.
         *
         * @internal
         * @param string $filename [$absoluteDir = __DIR__]
         * @param string $absoluteDir
         * @throws \Exception
         * @return bool
         */
        protected static function _require($filename,
            $absoluteDir = __DIR__)
        {
            if (isset($filename)) {
                $root = $absoluteDir;
                if (file_exists($root . '/' . $filename)) {
                    try {
                        require_once($root . '/' . $filename);
                        return true;
                    } catch (\Exception $e) {
                        return false;
                    }
                }
            } else {
                Throw new \Exception(
                    'Invalid parameteters for ' . __FILE__);
            }
            return false;
        }

    }
}
