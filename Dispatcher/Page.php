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
namespace Aomebo\Dispatcher
{

    /**
     * Every page will be stored as a Page object in memory. 
     * The page object holds information about page-name and page-contents.
     * 
     * The page object can be executed to start the execution of it's contents.
     * 
     * @todo Implement this
     */
    class Page extends \Aomebo\Base
    {

        /**
         * @internal
         * @var string
         */
        private $_name = '';

        /**
         * @internal
         * @var array()
         */
        private $_contents = array();

        /**
         * @param string [$name = '']
         * @param array [$contents = array()]
         */
        public function __construct($name = '', $contents = array())
        {
            $this->_name = $name;
            $this->_contents = $contents;
        }

        /**
         * @todo Implement this
         */
        public function execute()
        {
        }
        
    }

}
