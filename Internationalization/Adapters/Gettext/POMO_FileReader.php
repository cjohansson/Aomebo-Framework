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
namespace Aomebo\Internationalization\Adapters\Gettext
{

    /**
     *
     */
    class POMO_FileReader extends POMO_Reader
    {

        /**
         * @var \resource|null
         */
        public $_f = null;

        /**
         * @param string $filename
         */
        public function __construct($filename)
        {
            parent::__construct();
            $this->_f = fopen($filename, 'rb');
        }

        /**
         * @param int $bytes
         * @return string
         */
        public function read($bytes)
        {
            return fread($this->_f, $bytes);
        }

        /**
         * @param $pos
         * @return bool
         */
        public function seekto($pos)
        {
            if (-1 == fseek($this->_f, $pos, SEEK_SET)) {
                return false;
            }
            $this->_pos = $pos;
            return true;
        }

        /**
         * @return bool
         */
        public function is_resource()
        {
            return is_resource($this->_f);
        }

        /**
         * @return bool
         */
        public function feof()
        {
            return feof($this->_f);
        }

        /**
         * @return bool
         */
        public function close()
        {
            return fclose($this->_f);
        }

        /**
         * @return string
         */
        public function read_all()
        {
            $all = '';
            while ( !$this->feof() )
                $all .= $this->read(4096);
            return $all;
        }

    }

}
