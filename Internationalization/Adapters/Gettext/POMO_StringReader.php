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
namespace Aomebo\Internationalization\Adapters\Gettext
{

    /**
     *
     */
    class POMO_Reader
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
        public function POMO_Reader()
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
         * @param integer count How many elements should be read
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

if ( !class_exists( 'POMO_FileReader' ) ):
class POMO_FileReader extends POMO_Reader {
	function POMO_FileReader($filename) {
		parent::POMO_Reader();
		$this->_f = fopen($filename, 'rb');
	}

	function read($bytes) {
		return fread($this->_f, $bytes);
	}

	function seekto($pos) {
		if ( -1 == fseek($this->_f, $pos, SEEK_SET)) {
			return false;
		}
		$this->_pos = $pos;
		return true;
	}

	function is_resource() {
		return is_resource($this->_f);
	}

	function feof() {
		return feof($this->_f);
	}

	function close() {
		return fclose($this->_f);
	}

	function read_all() {
		$all = '';
		while ( !$this->feof() )
			$all .= $this->read(4096);
		return $all;
	}
}
endif;

if ( !class_exists( 'POMO_StringReader' ) ):
/**
 * Provides file-like methods for manipulating a string instead
 * of a physical file.
 */
class POMO_StringReader extends POMO_Reader {

	var $_str = '';

	function POMO_StringReader($str = '') {
		parent::POMO_Reader();
		$this->_str = $str;
		$this->_pos = 0;
	}


	function read($bytes) {
		$data = $this->substr($this->_str, $this->_pos, $bytes);
		$this->_pos += $bytes;
		if ($this->strlen($this->_str) < $this->_pos) $this->_pos = $this->strlen($this->_str);
		return $data;
	}

	function seekto($pos) {
		$this->_pos = $pos;
		if ($this->strlen($this->_str) < $this->_pos) $this->_pos = $this->strlen($this->_str);
		return $this->_pos;
	}

	function length() {
		return $this->strlen($this->_str);
	}

	function read_all() {
		return $this->substr($this->_str, $this->_pos, $this->strlen($this->_str));
	}

}
endif;

if ( !class_exists( 'POMO_CachedFileReader' ) ):
/**
 * Reads the contents of the file in the beginning.
 */
class POMO_CachedFileReader extends POMO_StringReader {
	function POMO_CachedFileReader($filename) {
		parent::POMO_StringReader();
		$this->_str = file_get_contents($filename);
		if (false === $this->_str)
			return false;
		$this->_pos = 0;
	}
}
endif;

if ( !class_exists( 'POMO_CachedIntFileReader' ) ):
/**
 * Reads the contents of the file in the beginning.
 */
class POMO_CachedIntFileReader extends POMO_CachedFileReader {
	function POMO_CachedIntFileReader($filename) {
		parent::POMO_CachedFileReader($filename);
	}
}
endif;
