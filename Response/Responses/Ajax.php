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
    class Ajax extends \Aomebo\Response\Type
    {

        /**
         * @var int
         */
        protected $_priority = 70;

        /**
         * @var string
         */
        protected $_name = 'Ajax';

        /**
         * @return bool
         */
        public function isValidRequest()
        {
            $allowAjaxPostRequests =
                \Aomebo\Configuration::getSetting('dispatch,allow ajax post requests');
            $allowAjaxGetRequests =
                \Aomebo\Configuration::getSetting('dispatch,allow ajax get requests');
            $ajaxMode =
                \Aomebo\Configuration::getSetting('settings,ajax mode');
            $requestMethod = (!empty($_SERVER['REQUEST_METHOD']) ?
                           strtoupper($_SERVER['REQUEST_METHOD']) : 'GET');
            if (isset($_GET['mode'])
                && $_GET['mode'] == $ajaxMode
                && (($allowAjaxPostRequests
                    && $requestMethod == 'POST'
                    && (!empty($_POST['page']))
                    || !empty($_POST['_page']))
                || ($allowAjaxGetRequests
                    && $requestMethod == 'GET'
                    && (!empty($_GET['page'])
                    || !empty($_GET['_page']))))
            ) {
                return true;
            } else {
                return false;
            }
        }

        /**
         *
         */
        public function respond()
        {
            \Aomebo\Internationalization\System::getInstance();
            \Aomebo\Database\Adapter::getInstance();
            \Aomebo\Associatives\Engine::getInstance();
            \Aomebo\Interpreter\Engine::getInstance();
            \Aomebo\Cache\System::getInstance();
            \Aomebo\Indexing\Engine::getInstance();
            \Aomebo\Presenter\Engine::getInstance();

            new \Aomebo();
            \Aomebo\Interpreter\Engine::interpret();
            \Aomebo\Indexing\Engine::index();
            \Aomebo\Presenter\Engine::output();
        }

    }
}
