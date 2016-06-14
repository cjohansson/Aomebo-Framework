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
    class POMO_StringReader extends POMO_Reader
    {

        /**
         * @var string
         */
        public $_str = '';

        /**
         * @var null|int
         */
        public $_pos = null;

        /**
         * @param string $str
         */
        public function __construct($str = '')
        {
            parent::__construct();
            $this->_str = $str;
            $this->_pos = 0;
        }

        /**
         * @param $bytes
         * @return string
         */
        public function read($bytes)
        {
            $data = $this->substr($this->_str, $this->_pos, $bytes);
            $this->_pos += $bytes;
            if ($this->strlen($this->_str) < $this->_pos) $this->_pos = $this->strlen($this->_str);
            return $data;
        }

        /**
         * @param $pos
         * @return int
         */
        public function seekto($pos)
        {
            $this->_pos = $pos;
            if ($this->strlen($this->_str) < $this->_pos) $this->_pos = $this->strlen($this->_str);
            return $this->_pos;
        }

        /**
         * @return int
         */
        public function length()
        {
            return $this->strlen($this->_str);
        }

        /**
         * @return string
         */
        public function read_all()
        {
            return $this->substr($this->_str, $this->_pos, $this->strlen($this->_str));
        }

    }

}
