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
     * @method static \Aomebo\Template\Adapters\Twig\Adapter getInstance()
     */
    class Adapter extends \Aomebo\Template\Adapters\Base
    {

        /**
         * @internal
         * @var \Twig_Environment|null
         */
        private $_twig = null;

        /**
         * @internal
         * @var string
         */
        private $_directory = '';

        /**
         * @internal
         * @var string
         */
        private $_filename = '';

        /**
         * @internal
         * @var array
         */
        private $_context = array();

        /**
         * @var string
         */
        const TWIG_DIR = 'Twig-1.18.0';

        /**
         * @link http://twig.sensiolabs.org/doc/api.html
         */
        public function __construct()
        {

            parent::__construct();

            if (!$this->_isConstructed()) {

                require_once(self::_getLibsPath()
                    . DIRECTORY_SEPARATOR . 'Autoloader.php');
                \Twig_Autoloader::register(true);

                $this->_flagThisConstructed();

                self::_getFunctionsSiteDirectory(true);

            }

        }

        /**
         * @return string
         */
        public function parse()
        {
            $template = $this->_twig->loadTemplate($this->_filename);
            return $template->render($this->_context);
        }

        /**
         * Removes all references to variables to free memory.
         *
         * @return bool
         */
        public function free()
        {
            unset($this->_directory, $this->_filename, 
                $this->_template, $this->_context);
        }

        /**
         * @internal
         * @param string $directory
         * @param string $filename
         * @return bool
         * @link http://twig.sensiolabs.org/doc/api.html
         */
        protected function _getTemplate($directory, $filename)
        {

            $loader = new \Twig_Loader_Filesystem($directory . '/');
            
            $parameters = array();
            
            if (\Aomebo\Application::isWritingnabled()) {
                $parameters['cache'] = $this->_getCacheLocation();
                $parameters['auto_reload'] = true;
            } else {
                $parameters['cache'] = false;
            }

            $this->_twig = new \Twig_Environment($loader, $parameters);
            $this->_directory = $directory;
            $this->_filename = $filename;
            $this->_context = array();

            return true;

        }

        /**
         * @internal
         * @param string $key
         * @param mixed $value
         * @return bool
         */
        protected function _assign($key, $value)
        {
            $this->_context[$key] = $value;
            return true;
        }

        /**
         * @internal
         * @param string $name
         * @param \Closure|string|array $reference
         * @link http://twig.sensiolabs.org/doc/advanced.html
         * @return bool
         */
        protected function _assignFunction($name, $reference)
        {
            $this->_twig->addFunction($name, $reference);
        }

        /**
         * @internal
         */
        protected function _attachDefaultFunctions()
        {
            
            $this->_twig->addFunction(
                '__', 
                new \Twig_SimpleFunction('__', '__')
            );
            
            // Add extension from Aomebo
            $dir = $this->_getFunctionsAomeboDirectory();
            if ($items = scandir($dir)) {
                foreach ($items as $item)
                {
                    if (stripos($item, '.php') !== false) {
                        
                        require_once($dir . '/' . $item);
                        
                        $className = '\\Aomebo\\Template\\Adapters\\Twig\\' 
                            . basename($item);
                        
                        if (class_exists($className, false)) {
                            $this->_twig->addExtension(new $className());
                        }
                        
                    }
                }
            }
            
            // TODO: Add extensions from site here
            
        }


        /**
         * @internal
         * @static
         */
        private static function _getFunctionsAomeboDirectory()
        {
            $path =  __DIR__ . DIRECTORY_SEPARATOR . 'Functions';
            if (!is_dir($path)) {
                \Aomebo\Filesystem::makeDirectory($path);
            }
            return $path;
        }

        /**
         * @internal
         * @static
         * @param bool [$createFolders = false]
         * @return string
         */
        private static function _getFunctionsSiteDirectory(
            $createFolders = false)
        {
            $path = _SYSTEM_SITE_ROOT_ . DIRECTORY_SEPARATOR . 'Template';
            if ($createFolders
                && !is_dir($path)
            ) {
                \Aomebo\Filesystem::makeDirectory($path);
            }
            $path .= DIRECTORY_SEPARATOR . 'Adapters';
            if ($createFolders
                && !is_dir($path)
            ) {
                \Aomebo\Filesystem::makeDirectory($path);
            }
            $path .= DIRECTORY_SEPARATOR . 'Twig';
            if ($createFolders
                && !is_dir($path)
            ) {
                \Aomebo\Filesystem::makeDirectory($path);
            }
            $path .= DIRECTORY_SEPARATOR . 'Functions';
            if ($createFolders
                && !is_dir($path)
            ) {
                \Aomebo\Filesystem::makeDirectory($path);
            }
            return $path;
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getLibsPath()
        {
            return __DIR__ . DIRECTORY_SEPARATOR
                . self::TWIG_DIR . DIRECTORY_SEPARATOR . 'lib/Twig';
        }

    }
}
