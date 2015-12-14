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
namespace Aomebo\Interpreter\Adapters\Xml
{

    /**
     *
     */
    final class Adapter extends \Aomebo\Interpreter\Adapters\Base
    {

        /**
         * @var string
         */
        protected $_fileSuffix = '.xml';

        /**
         * @param string $data
         * @return string
         */
        public function applyDefaultEncapsulation($data)
        {
            return '<?xml version="1.0" encoding="UTF-8" ?>'
                . '<root>' . $data . '</root>';
        }

        /**
         * @param string $data
         * @throws \Exception
         * @return array|bool|mixed
         */
        public function process($data)
        {
            if (function_exists('simplexml_load_string')
                && function_exists('libxml_use_internal_errors')
            ) {
                libxml_use_internal_errors(true);
                if ($processed = simplexml_load_string($data,
                    null, LIBXML_NOEMPTYTAG)
                ) {
                    $array = $this->_toArray($processed);
                    return $array;
                } else {
                    Throw new \Exception(sprintf(
                        self::systemTranslate(
                            'The xml is malformed (%s).'
                        ),
                        print_r(libxml_get_errors(), true)
                    ));
                }
            }
            return false;
        }

        /**
         * @internal
         * @param \object $data
         * @return array|string
         */
        private function _toArray($data)
        {
            if ($data->count() > 0)
            {
                $children = $data->children();
                $array = array();

                foreach ($children as $child)
                {

                    /** @var \SimpleXmlElement $child */

                    if ($child->count() > 0) {
                        $array[] = array(
                            parent::FIELD_KEY => $child->getName(),
                            parent::FIELD_VALUE => $this->_toArray($child),
                        );
                    } else {
                        $array[] = array(
                            parent::FIELD_KEY => $child->getName(),
                            parent::FIELD_VALUE => strval($child),
                        );
                    }
                }
                return $array;
            } else {
                return strval($data);
            }
        }

    }
}
