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
    class Gettext_Translations extends Translations
    {

        /**
         * @var array
         */
        private $_nplurals = array();

        /**
         * @var null|string
         */
        private $_gettext_select_plural_form = null;

        /**
         * The gettext implementation of select_plural_form.
         *
         * It lives in this class, because there are more than one descendand, which will use it and
         * they can't share it effectively.
         *
         * @param int $count
         * @return mixed
         */
        public function gettext_select_plural_form($count)
        {
            if (!isset($this->_gettext_select_plural_form) || is_null($this->_gettext_select_plural_form)) {
                list( $nplurals, $expression ) = $this->nplurals_and_expression_from_header($this->get_header('Plural-Forms'));
                $this->_nplurals = $nplurals;
                $this->_gettext_select_plural_form = $this->make_plural_form_function($nplurals, $expression);
            }
            return call_user_func($this->_gettext_select_plural_form, $count);
        }

        /**
         * @param string $header
         * @return array
         */
        public function nplurals_and_expression_from_header($header)
        {
            if (preg_match('/^\s*nplurals\s*=\s*(\d+)\s*;\s+plural\s*=\s*(.+)$/', $header, $matches)) {
                $nplurals = (int)$matches[1];
                $expression = trim($this->parenthesize_plural_exression($matches[2]));
                return array($nplurals, $expression);
            } else {
                return array(2, 'n != 1');
            }
        }

        /**
         * Makes a function, which will return the right translation index, according to the
         * plural forms header
         *
         * @param bool $nplurals
         * @param string $expression
         * @return string
         */
        public function make_plural_form_function($nplurals, $expression)
        {
            $expression = str_replace('n', '$n', $expression);
            $func_body = "
                \$index = (int)($expression);
                return (\$index < $nplurals)? \$index : $nplurals - 1;";
            return create_function('$n', $func_body);
        }

        /**
         * Adds parantheses to the inner parts of ternary operators in
         * plural expressions, because PHP evaluates ternary oerators from left to right
         *
         * @param string $expression        the expression without parentheses
         * @return string
         */
        public function parenthesize_plural_exression($expression)
        {
            $expression .= ';';
            $res = '';
            $depth = 0;
            for ($i = 0; $i < strlen($expression); ++$i) {
                $char = $expression[$i];
                switch ($char) {
                    case '?':
                        $res .= ' ? (';
                        $depth++;
                        break;
                    case ':':
                        $res .= ') : (';
                        break;
                    case ';':
                        $res .= str_repeat(')', $depth) . ';';
                        $depth= 0;
                        break;
                    default:
                        $res .= $char;
                }
            }
            return rtrim($res, ';');
        }

        /**
         * @param array $translation
         * @return array
         */
        public function make_headers($translation)
        {
            $headers = array();
            // sometimes \ns are used instead of real new lines
            $translation = str_replace('\n', "\n", $translation);
            $lines = explode("\n", $translation);
            foreach($lines as $line) {
                $parts = explode(':', $line, 2);
                if (!isset($parts[1])) continue;
                $headers[trim($parts[0])] = trim($parts[1]);
            }
            return $headers;
        }

        /**
         * @param string $header
         * @param string $value
         */
        public function set_header($header, $value)
        {
            parent::set_header($header, $value);
            if ('Plural-Forms' == $header) {
                list( $nplurals, $expression ) = $this->nplurals_and_expression_from_header($this->get_header('Plural-Forms'));
                $this->_nplurals = $nplurals;
                $this->_gettext_select_plural_form = $this->make_plural_form_function($nplurals, $expression);
            }
        }

    }

}
