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
    class PHP extends \Aomebo\Response\Type
    {

        /**
         * @var int
         */
        protected $_priority = 80;

        /**
         * @var string
         */
        protected $_name = 'PHP';

        /**
         * @return bool
         */
        public function isValidRequest()
        {
            return (\Aomebo\Configuration::getSetting('php_responses,'
                . \Aomebo\Dispatcher\System::getFullRequest(), false) ?
                true : false);
        }

        /**
         *
         */
        public function respond()
        {

            $filePath =
                \Aomebo\Configuration::getSetting('php_responses,'
                    . \Aomebo\Dispatcher\System::getFullRequest());

            if (!file_exists($filePath)) {
                if (file_exists(_PRIVATE_ROOT_ . $filePath)) {
                    $filePath = _PRIVATE_ROOT_ . $filePath;
                } else if (file_exists(_PUBLIC_ROOT_ . $filePath)) {
                    $filePath = _PUBLIC_ROOT_ . $filePath;
                } else if (file_exists(_SITE_ROOT_ . $filePath)) {
                    $filePath = _SITE_ROOT_ . $filePath;
                } else if (file_exists(_SYSTEM_ROOT_ . $filePath)) {
                    $filePath = _SYSTEM_ROOT_ . $filePath;
                }
            }

            if (file_exists($filePath)) {
                require_once($filePath);
            } else {
                Throw new \Exception(sprintf(
                    self::systemTranslate('Could not find file at "%s", "%s", "%s", "%s" or "%s".'),
                    $filePath,
                    _PRIVATE_ROOT_ . $filePath,
                    _PUBLIC_ROOT_ . $filePath,
                    _SITE_ROOT_ . $filePath,
                    _SYSTEM_ROOT_ . $filePath
                ));
            }

        }

    }

}
