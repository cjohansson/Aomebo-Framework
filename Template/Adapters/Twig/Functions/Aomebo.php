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
namespace Aomebo\Template\Adapters\Twig
{

    /**
     * @package Aomebo\Template\Adapters\Twig
     */
    class Aomebo extends \Twig_Extension
    {

        /**
         * @return string
         */
        public function getName()
        {
            return 'aomebo';
        }

        /**
         * @link http://twig.sensiolabs.org/doc/advanced.html
         * @return array
         */
        public function getFunctions()
        {
            return array(
                new \Twig_SimpleFunction(
                    'url',
                    array( & $this, 'url')
                ),
            );
        }

        /**
         * @link http://twig.sensiolabs.org/doc/advanced.html
         * @return array
         */
        public function getFilters()
        {
            return array(
                new \Twig_SimpleFilter(
                    'asciiEncode',
                    array( & $this, 'asciiEncode'),
                    array('needs_environment' => true)
                ),
                new \Twig_SimpleFilter(
                    'uriComponent',
                    array( & $this, 'uriComponent'),
                    array('needs_environment' => true)
                ),
            );
        }

        /**
         * @param array|null [$params = null]
         * @return string
         */
        public function url($params = array())
        {
            $getArray = array();
            foreach ($params as $key => $value)
            {
                if (!empty($key)
                    && substr($key, 0, 1) != '_'
                ) {
                    $getArray[$key] = $value;
                }
            }

            $page = (!empty($params['_page']) ? $params['_page'] : '');
            $clear = (!empty($params['_clear']) ? true : false);
            $default = (!empty($params['_default']) ? true : false);
            $full = (!empty($params['_full']) ? true : false);

            if ($default) {
                if ($full) {
                    return \Aomebo\Dispatcher\System::buildDefaultFullUri();
                } else {
                    return \Aomebo\Dispatcher\System::buildDefaultUri();
                }
            } else {
                if ($full) {
                    return \Aomebo\Dispatcher\System::buildFullUri($getArray, $page, $clear);
                } else {
                    return \Aomebo\Dispatcher\System::buildUri($getArray, $page, $clear);
                }
            }
        }

        /**
         * @param \Twig_Environment $env
         * @see http://www.aomebo.org/ or https://github.com/cjohansson/Aomebo-Framework
         * @param string  $string
         * @param bool [$toLowerCase = true]
         * @param string [$replaceWith = '_']
         * @return string           modified string
         */
        public function asciiEncode(
            $env, $string, $toLowerCase = true, $replaceWith = '_')
        {
            return \Aomebo\Dispatcher\System::formatUriComponent(
                $string, 
                $toLowerCase, 
                $replaceWith, 
                $env->getCharset()
            );
        }

        /**
         * @see http://www.aomebo.org/ or https://github.com/cjohansson/Aomebo-Framework
         * @param \Twig_Environment $env
         * @param string $string
         * @param bool [$toLowerCase = true]
         * @param string [$replaceWith = '_']
         * @return string                           modified string
         */
        public function uriComponent(
            $env, $string, $toLowerCase = true, $replaceWith = '-')
        {
            return \Aomebo\Dispatcher\System::formatUriComponent(
                $string, 
                $toLowerCase, 
                $replaceWith, 
                $env->getCharset()
            );
        }
        
    }
    
}
