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
namespace Aomebo\Response\Responses
{

    /**
     *
     */
    class Favicon extends \Aomebo\Response\Type
    {

        /**
         * @var int
         */
        protected $_priority = 70;

        /**
         * @return bool
         */
        public function isValidRequest()
        {
            return \Aomebo\Dispatcher\System::isFaviconRequest();
        }

        /**
         *
         */
        public function respond()
        {

            $favIconPath =
                \Aomebo\Dispatcher\System::getResourcesDirInternalPath()
                . DIRECTORY_SEPARATOR
                . \Aomebo\Configuration::getSetting('site,shortcut icon');

            if (file_exists($favIconPath)) {

                \Aomebo\Dispatcher\System::setHttpHeaderField(
                    'Content-Type',
                    'image/vnd.microsoft.icon; charset=binary'
                );
                \Aomebo\Dispatcher\System::setHttpHeaderField(
                    'Content-Disposition',
                    'inline; filename="favicon.ico"'
                );
                readfile($favIconPath);

            } else {
                Throw new \Exception('Could not find favicon at "' . $favIconPath . '"');
            }

        }

    }

}
