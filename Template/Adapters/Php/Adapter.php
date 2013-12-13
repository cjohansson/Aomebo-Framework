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
namespace Aomebo\Template\Adapters\Php
{

    /**
     * @method static \Aomebo\Template\Adapters\Php\Adapter getInstance()
     */
    class Adapter extends \Aomebo\Template\Adapters\Base
    {

        /**
         * @internal
         * @var array
         */
        private $_variables;

        /**
         * @internal
         * @var string
         */
        private $_viewfile;

        /**
         * @internal
         * @var string
         */
        private $_parsed;

        /**
         * @internal
         * @var string
         */
        private $_directory;

        /**
         * @internal
         * @var string
         */
        private $_filename;

        /**
         * @static
         * @var array
         */
        private static $_methods;

        /**
         *
         */
        public function __construct()
        {
            if (!self::_isConstructed()) {

                self::_flagThisConstructed();

            }
        }

        /**
         * @return bool|void
         */
        public function free()
        {
        }

        /**
         * @param string $methodName
         * @param array|null [$arguments = null]
         * @return mixed
         */
        public function __call($methodName, $arguments = null)
        {
            if ($this->_methodExists($methodName)) {
                return self::_callMethod(
                    $methodName, $arguments);
            }
            return false;
        }

        /**
         * @throws \Exception
         * @return string
         */
        public function parse()
        {

            $interpreterEngine =
                \Aomebo\Interpreter\Engine::getInstance();
            $useOb =
                $interpreterEngine->getOutputBufferingFlag();

            if (!empty($this->_viewfile)) {

                if (isset($this->_variables)
                    && is_array($this->_variables)
                ) {
                    foreach ($this->_variables as $key => $value
                    ) {
                        if (substr($key, 0, 1) !== '_') {
                            $this->$key = $value;
                        }
                    }
                }

                try
                {

                    $viewscript = file_get_contents(
                        $this->_viewfile);

                    if ($useOb) {
                        ob_start();
                    }
                    @eval('?>' . $viewscript);
                    if (!empty($php_errormsg)) {
                        Throw new \Exception(
                            'Error: "' . $php_errormsg
                            . '", when parsing file "' . $this->_viewfile . '"');
                    }

                    if ($useOb) {
                        $this->_parsed = ob_get_clean();
                        return $this->_parsed;
                    } else {
                        return '';
                    }
                } catch (\Exception $e) {
                    Throw new \Exception('Could not open view-script at "'
                        . $this->_viewfile . '" error: "'
                        . $e->getMessage() . '"');
                }
            } else {
                Throw new \Exception('Found no viewfile.');
            }
        }

        /**
         * @internal
         * @param string $directory
         * @param string $filename
         * @throws \Exception
         * @return bool
         */
        protected function _getTemplate($directory, $filename)
        {
            if (file_exists($directory . DIRECTORY_SEPARATOR . $filename)) {
                $this->_directory = $directory;
                $this->_filename = $filename;
                $this->_viewfile = $directory . DIRECTORY_SEPARATOR . $filename;
                return true;
            }
            return false;
        }

        /**
         * @internal
         * @param string $key
         * @param mixed $value
         * @return bool
         */
        protected function _assign($key, $value)
        {
            if (substr($key, 0, 1) != '_') {

                $key = str_replace(' ', '_', $key);
                $this->_variables[$key] = $value;

                return true;

            }

            return false;
        }

        /**
         * @internal
         * @param string $name
         * @param \Closure|string|array $reference
         * @return bool
         */
        protected function _assignFunction($name, $reference)
        {
            return false;
        }

        /**
         * @internal
         */
        protected function _attachDefaultFunctions()
        {
            if (!isset(self::$_methods)) {
                self::$_methods = array();
                $this->_attachFunctionsInDirectory(
                    self::_getFunctionsAomeboDirectory());
                $this->_attachFunctionsInDirectory(
                    self::_getFunctionsSiteDirectory());
            }
        }

        /**
         * @internal
         * @param string $functionsDir
         */
        private function _attachFunctionsInDirectory($functionsDir)
        {
            if (is_dir($functionsDir)) {
                if ($items = scandir($functionsDir)) {
                    foreach ($items as $item)
                    {
                        if (!empty($item)
                            && $item != '.'
                            && $item != '..'
                        ) {
                            $pathItem = $functionsDir .
                                DIRECTORY_SEPARATOR . $item;
                            if (is_file($pathItem)) {
                                if (strtolower(substr($pathItem, -4))
                                    == '.php'
                                ) {

                                    require_once($pathItem);
                                    $methodName = substr(
                                        $item, 0,
                                        strrpos($item, '.'));

                                    if (function_exists($methodName)) {
                                        if (!self::_methodExists($methodName)) {
                                            self::$_methods[$methodName] =
                                                $methodName;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        /**
         * @internal
         * @static
         * @param string $methodName
         * @return bool
         */
        private static function _methodExists($methodName)
        {
            return (!empty($methodName)
                && isset(self::$_methods[$methodName]));
        }

        /**
         * @internal
         * @static
         * @param string $methodName
         * @param array|null [$args = null]
         * @return mixed
         */
        private static function _callMethod($methodName, $args = null)
        {
            if (self::_methodExists($methodName)) {
                if (isset($args)
                    && is_array($args)
                    && sizeof($args) > 0
                ) {
                    return call_user_func_array(
                        self::$_methods[$methodName],
                        $args);
                } else {
                    return call_user_func(
                        self::$_methods[$methodName]);
                }
            }
            return false;
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
         * @return string
         */
        private static function _getFunctionsSiteDirectory()
        {
            $path = _SYSTEM_SITE_ROOT_ . DIRECTORY_SEPARATOR . 'Template';
            if (!is_dir($path)) {
                \Aomebo\Filesystem::makeDirectory($path);
            }
            $path .= DIRECTORY_SEPARATOR . 'Adapters';
            if (!is_dir($path)) {
                \Aomebo\Filesystem::makeDirectory($path);
            }
            $path .= DIRECTORY_SEPARATOR . 'Php';
            if (!is_dir($path)) {
                \Aomebo\Filesystem::makeDirectory($path);
            }
            $path .= DIRECTORY_SEPARATOR . 'Functions';
            if (!is_dir($path)) {
                \Aomebo\Filesystem::makeDirectory($path);
            }
            return $path;
        }

    }
}
