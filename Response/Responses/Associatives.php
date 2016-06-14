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
    class Associatives extends \Aomebo\Response\Type
    {

        /**
         * @var int
         */
        protected $_priority = 90;

        /**
         * @var string
         */
        protected $_name = 'Associatives';

        /**
         * @return bool
         */
        public function isValidRequest()
        {
            return \Aomebo\Dispatcher\System::isAssociativesRequest();
        }

        /**
         *
         */
        public function respond()
        {
            if ((!\Aomebo\Configuration::getSetting('dispatch,allow only associatives request with matching referer')
                    || \Aomebo\Dispatcher\System::requestRefererMatchesSiteUrl())
                && (\Aomebo\Dispatcher\System::isHttpGetRequest()
                    || \Aomebo\Dispatcher\System::isHttpHeadRequest())
            ) {

                // Load our database - TODO: Remove this
                \Aomebo\Database\Adapter::getInstance();

                // Load the associatives engine
                \Aomebo\Associatives\Engine::getInstance();

                new \Aomebo();

                // Parse the requests associatives
                \Aomebo\Associatives\Parser::parseRequest();

            } else {
                \Aomebo\Dispatcher\System::setHttpResponseStatus403Forbidden();
            }
        }

    }

}
