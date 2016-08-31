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
namespace Aomebo\Response\Responses
{
    /**
     *
     */
    class Page extends \Aomebo\Response\Type
    {

        /**
         * @var int
         */
        protected $_priority = 60;

        /**
         * @var string
         */
        protected $_name = 'Page';

        /**
         * @return bool
         */
        public function isValidRequest()
        {
            return \Aomebo\Dispatcher\System::isPageRequest();
        }

        /**
         *
         */
        public function respond()
        {

            // Load the internationalization system
            \Aomebo\Internationalization\System::getInstance();

            // Load our database
            \Aomebo\Database\Adapter::getInstance();

            // Load the associatives engine
            \Aomebo\Associatives\Engine::getInstance();

            // Load interpreter for parsing of pages
            \Aomebo\Interpreter\Engine::getInstance();

            // Load cache system
            \Aomebo\Cache\System::getInstance();

            // Load indexing engine
            \Aomebo\Indexing\Engine::getInstance();

            new \Aomebo();

            // Interpret page
            \Aomebo\Interpreter\Engine::interpret();

            // Index our output
            \Aomebo\Indexing\Engine::index();

            // Present our output
            \Aomebo\Presenter\Engine::getInstance();
            \Aomebo\Presenter\Engine::output();

        }
    }
}
