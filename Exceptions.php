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
namespace Aomebo\Exceptions
{

    /**
     *
     */
    class TranslatedException extends \Exception
    {

        /**
         * @param string [$message = null]
         * @param array [$stringVariables = array()]
         * @param \Exception|null [$previous = null]
         */
        public function __construct($message = null,
            $stringVariables = array())
        {
            if (!empty($message)) {
                $message = \Aomebo\Internationalization\System::
                    systemTranslate($message);
            }
            if (sizeof($stringVariables) > 0) {
                $message = vsprintf($message, $stringVariables);
            }
            parent::__construct($message, 0, null);
        }

    }

    /**
     * 
     */
    class InvalidParametersException extends TranslatedException
    {

        /**
         * @param string [$message = null]
         * @param array [$stringVariables = array()]
         * @param \Exception|null [$previous = null]
         */
        public function __construct($message = null, 
            $stringVariables = array())
        {
            if (empty($message)) {
                $message = 'Invalid parameters';
            }
            parent::__construct($message, $stringVariables);
        }
        
    }
    
}
