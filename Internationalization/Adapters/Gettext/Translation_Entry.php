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
    class Translation_Entry
    {

        /**
         * @var bool
         */
        public $is_plural = false;

        /**
         * @var mixed
         */
        public $context = null;

        /**
         * @var null
         */
        public $singular = null;

        /**
         * @var null
         */
        public $plural = null;

        /**
         * @var array
         */
        public $translations = array();

        /**
         * @var string
         */
        public $translator_comments = '';

        /**
         * @var string
         */
        public $extracted_comments = '';

        /**
         * @var array
         */
        public $references = array();

        /**
         * @var array
         */
        public $flags = array();

        /**
         * @param array $args associative array, support following keys:
         * 	- singular (string) -- the string to translate, if omitted and empty entry will be created
         * 	- plural (string) -- the plural form of the string, setting this will set {@link $is_plural} to true
         * 	- translations (array) -- translations of the string and possibly -- its plural forms
         * 	- context (string) -- a string differentiating two equal strings used in different contexts
         * 	- translator_comments (string) -- comments left by translators
         * 	- extracted_comments (string) -- comments left by developers
         * 	- references (array) -- places in the code this strings is used, in relative_to_root_path/file.php:linenum form
         * 	- flags (array) -- flags like php-format
         */
        public function __construct($args=array())
        {
            // if no singular -- empty object
            if (!isset($args['singular'])) {
                return;
            }
            // get member variable values from args hash
            foreach ($args as $varname => $value) {
                $this->$varname = $value;
            }
            if (isset($args['plural'])) $this->is_plural = true;
            if (!is_array($this->translations)) $this->translations = array();
            if (!is_array($this->references)) $this->references = array();
            if (!is_array($this->flags)) $this->flags = array();
        }

        /**
         * @return string|bool
         */
        public function key()
        {
            if (is_null($this->singular)) {
                return false;
            } else {
                return is_null($this->context)? $this->singular : $this->context.chr(4).$this->singular;
            }
        }

        /**
         * @param $other
         */
        public function merge_with(&$other)
        {
            $this->flags = array_unique(array_merge($this->flags, $other->flags));
            $this->references = array_unique(array_merge($this->references, $other->references));
            if ($this->extracted_comments != $other->extracted_comments) {
                $this->extracted_comments .= $other->extracted_comments;
            }
        }
    }

}
