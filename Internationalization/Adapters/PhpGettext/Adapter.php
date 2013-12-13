<?php
/**
 * Aomebo - a module-based MVC framework for PHP 5.3+
 *
 * Copyright (C) 2010+ Christian Johansson <christian@cvj.se>
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
 * @see http://www.aomebo.org
 */

/**
 *
 */
namespace Aomebo\Internationalization\Adapters\PhpGettext
{

    /**
     * @method static \Aomebo\Internationalization\Adapters\PhpGettext\Adapter getInstance()
     * @see https://launchpad.net/php-gettext/
     */
    class Adapter extends \Aomebo\Internationalization\Adapters\Base
    {

        /**
         * @var string
         */
        const LIBRARY_DIR = 'php-gettext-1.0.11';

        /**
         * @return bool
         */
        public function init()
        {

            require_once(__DIR__ . DIRECTORY_SEPARATOR . self::LIBRARY_DIR . DIRECTORY_SEPARATOR . 'gettext.inc');

            $internationalizationSystem =
                \Aomebo\Internationalization\System::getInstance();

            $locale = $internationalizationSystem::getLocale();

            // Set language to locale
            T_setlocale(LC_ALL, $locale);

            $textDomains = $internationalizationSystem::getTextDomains();

            foreach ($textDomains as $textDomainName => $textDomainLocation)
            {
                bindtextdomain($textDomainName, $textDomainLocation);
            }

        }

        /**
         * @param string $domain
         * @return bool
         */
        public function setDomain($domain)
        {
            if (!empty($domain)) {
                textdomain($domain);
            }
            return false;
        }

    }

}
