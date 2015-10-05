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
namespace Aomebo\Internationalization\Adapters\Gettext
{

    /**
     *
     */
    abstract class POMO_Reader
    {

        /**
         * @var string
         */
        public $endian = 'little';


        /**
         * @var string
         */
        public $_post = '';

        /**
         *
         */
        public function __construct()
        {
            $this->is_overloaded = ((ini_get("mbstring.func_overload") & 2) != 0) && function_exists('mb_substr');
            $this->_pos = 0;
        }

        /**
         * Sets the endianness of the file.
         *
         * @param $endian string 'big' or 'little'
         */
        public function setEndian($endian)
        {
            $this->endian = $endian;
        }

        /**
         * @param int $bytes
         * @return string
         */
        abstract public function read($bytes);

        /**
         * @param $pos
         * @return bool
         */
        abstract public function seekto($pos);

        /**
         * @return string
         */
        abstract public function read_all();

        /**
         * Reads a 32bit Integer from the Stream
         *
         * @return mixed The integer, corresponding to the next 32 bits from
         * 	the stream of false if there are not enough bytes or on error
         */
        public function readint32()
        {
            $bytes = $this->read(4);
            if (4 != $this->strlen($bytes))
                return false;
            $endian_letter = ('big' == $this->endian)? 'N' : 'V';
            $int = unpack($endian_letter, $bytes);
            return array_shift($int);
        }

        /**
         * Reads an array of 32-bit Integers from the Stream
         *
         * @param int $count How many elements should be read
         * @return mixed Array of integers or false if there isn't
         * 	enough data or on error
         */
        public function readint32array($count)
        {
            $bytes = $this->read(4 * $count);
            if (4*$count != $this->strlen($bytes))
                return false;
            $endian_letter = ('big' == $this->endian)? 'N' : 'V';
            return unpack($endian_letter.$count, $bytes);
        }


        /**
         * @param $string
         * @param $start
         * @param $length
         * @return string
         */
        public function substr($string, $start, $length)
        {
            if ($this->is_overloaded) {
                return mb_substr($string, $start, $length, 'ascii');
            } else {
                return substr($string, $start, $length);
            }
        }

        /**
         * @param $string
         * @return int
         */
        public function strlen($string)
        {
            if ($this->is_overloaded) {
                return mb_strlen($string, 'ascii');
            } else {
                return strlen($string);
            }
        }

        /**
         * @param $string
         * @param $chunk_size
         * @return array
         */
        public function str_split($string, $chunk_size)
        {
            if (!function_exists('str_split')) {
                $length = $this->strlen($string);
                $out = array();
                for ($i = 0; $i < $length; $i += $chunk_size)
                    $out[] = $this->substr($string, $i, $chunk_size);
                return $out;
            } else {
                return str_split( $string, $chunk_size );
            }
        }

        /**
         * @return mixed
         */
        public function pos()
        {
            return $this->_pos;
        }

        /**
         * @return bool
         */
        public function is_resource()
        {
            return true;
        }

        /**
         * @return bool
         */
        public function close()
        {
            return true;
        }
    }

}
