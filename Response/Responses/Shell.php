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
    class Shell extends \Aomebo\Response\Type
    {

        /**
         * @var int
         */
        protected $_priority = 80;

        /**
         * @var string
         */
        protected $_name = 'Shell';

        /**
         * @return bool
         */
        public function isValidRequest()
        {
            return !empty($_SERVER['SHELL'])
	            && \Aomebo\Configuration::getSetting(
		            'dispatch,allow shell requests');
        }

        /**
         *
         */
        public function respond()
        {
	        \Aomebo\Internationalization\System::getInstance();
	        \Aomebo\Database\Adapter::getInstance();
	        \Aomebo\Interpreter\Engine::getInstance();
	        \Aomebo\Cache\System::getInstance();
	        \Aomebo\Session\Handler::getInstance();
	        \Aomebo\Presenter\Engine::getInstance();

	        new \Aomebo();
	        \Aomebo\Dispatcher\System::parsePage();
	        \Aomebo\Interpreter\Engine::interpret();
	        \Aomebo\Presenter\Engine::output();
        }

    }
}
