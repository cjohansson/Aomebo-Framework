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
namespace Aomebo\Associatives
{

    /**
     *
     */
    abstract class Dependency extends \Aomebo\Base
    {

        /**
         * @var string
         */
        const TYPE_SCRIPT = 'script';

        /**
         * @var string
         */
        const TYPE_STYLE = 'style';

        /**
         * @var string
         */
        const MODE_EXTERNAL = 'mode_external';

        /**
         * @var string
         */
        const MODE_INLINE = 'mode_inline';

        /**
         * @var string
         */
        const MODE_IGNORE = 'mode_ignore';

        /**
         * @var string
         */
        const MIME_MARKUP = 'text/html';

        /**
         * @var string
         */
        const MIME_JAVASCRIPT = 'text/javascript';

        /**
         * @var string
         */
        const MIME_STYLESHEET = 'text/css';

        /**
         *
         */
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * @return array|bool
         */
        public function getSubDependencies()
        {
            if (isset($this->_subdependencies)
                && is_array($this->_subdependencies)
                && sizeof($this->_subdependencies) > 0
            ) {
                return $this->_subdependencies;
            } else {
                return false;
            }
        }

        /**
         * @return array|bool
         */
        public function getOptions()
        {
            if (isset($this->_options)
                && is_array($this->_options)
                && sizeof($this->_options) > 0
            ) {
                return $this->_options;
            } else {
                return false;
            }
        }


    }
}
