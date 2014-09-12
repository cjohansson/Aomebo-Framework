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
namespace Aomebo\Template\Adapters\Smarty
{

    /**
     * @method static \Aomebo\Template\Adapters\Smarty\Adapter getInstance()
     */
    class Adapter extends \Aomebo\Template\Adapters\Base
    {

        /**
         * @internal
         * @var \Smarty
         */
        private $_smarty;

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
         * @var string
         */
        const SMARTY_DIR = 'Smarty-3.1.13';

        /**
         *
         */
        public function __construct()
        {

            parent::__construct();

            if (!$this->_isConstructed()) {

                spl_autoload_register(__NAMESPACE__
                    . '\\Adapter::autoLoad', true, true);

                require_once(self::_getLibsPath()
                    . DIRECTORY_SEPARATOR . 'Smarty.class.php');

                $this->_flagThisConstructed();

                self::_getFunctionsSiteDirectory(true);

            }

            $this->_smarty = new \Smarty();

        }

        /**
         * This method is our autoLoad method which will
         * search for files at different places.
         *
         * @internal
         * @static
         * @param string $name
         * @throws \Exception
         * @return bool
         */
        public static function autoLoad($name)
        {

            $syspluginPath = self::_getSysPluginsPath()
                . DIRECTORY_SEPARATOR . strtolower($name) . _PHP_EX_;

            if (file_exists($syspluginPath)) {
                try {
                    require_once($syspluginPath);
                    return true;
                } catch (\Exception $e) {
                    Throw new \Exception('Something went wrong when including '
                        . 'file "' . $syspluginPath . '", error: '
                        . $e->getMessage());
                }
            } else if ($pluginsPath = self::_getPluginsPath()) {
                if (is_array($pluginsPath)) {
                    foreach ($pluginsPath as $pluginPath)
                    {
                        $path = $pluginPath . DIRECTORY_SEPARATOR
                            . strtolower($name) . _PHP_EX_;
                        if (file_exists($path)) {
                            try {
                                require_once($path);
                                return true;
                            } catch (\Exception $e) {
                                Throw new \Exception('Something went wrong when including '
                                . 'file "' . $path . '", error: '
                                . $e->getMessage());
                            }
                        }
                    }
                } else if (!empty($pluginsPath)) {
                    $path = $pluginsPath . DIRECTORY_SEPARATOR
                        . strtolower($name) . _PHP_EX_;
                    if (file_exists($path)) {
                        try {
                            require_once($path);
                            return true;
                        } catch (\Exception $e) {
                            Throw new \Exception('Something went wrong when including '
                                . 'file "' . $path . '", error: '
                                . $e->getMessage());
                        }
                    }
                }
            } else {
            }

            return false;

        }

        /**
         * @return string
         */
        public function parse()
        {

            $interpreterEngine =
                \Aomebo\Interpreter\Engine::getInstance();
            $useOb =
                $interpreterEngine->getOutputBufferingFlag();

            if ($useOb) {
                ob_start();
            }

            $this->_smarty->display($this->_filename);

            if ($useOb) {
                $output = ob_get_clean();
            } else {
                $output = '';
            }

            unset($this->_directory, $this->_filename);

            return $output;

        }

        /**
         * Removes all references to variables to free memory.
         *
         * @return bool
         */
        public function free()
        {
            unset($this->_smarty,
                $this->_directory,
                $this->_filename);
        }

        /**
         * @param string $name
         * @param \Closure|string|array $reference
         * @return bool
         * @see http://www.smarty.net/docs/en/api.register.plugin.tpl
         */
        public function assignBlockFunction($name, $reference)
        {
            return $this->_registerPlugin('block', $name, $reference);
        }

        /**
         * @param string $name
         * @param \Closure|string|array $reference
         * @return bool
         * @see http://www.smarty.net/docs/en/api.register.plugin.tpl
         */
        public function assignCompilerFunction($name, $reference)
        {
            return $this->_registerPlugin('compiler', $name, $reference);
        }

        /**
         * @param string $name
         * @param \Closure|string|array $reference
         * @return bool
         * @see http://www.smarty.net/docs/en/api.register.plugin.tpl
         */
        public function assignModifierFunction($name, $reference)
        {
            return $this->_registerPlugin('modifier', $name, $reference);
        }

        /**
         * @internal
         * @param string $directory
         * @param string $filename
         * @return bool
         */
        protected function _getTemplate($directory, $filename)
        {

            $this->_smarty->setCompileDir($this->_getCacheLocation());
            $this->_smarty->setCacheDir($this->_getCacheLocation());
            $this->_smarty->setTemplateDir($directory);
            $this->_smarty->setConfigDir($directory);

            $this->_directory = $directory;
            $this->_filename = $filename;

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
            $this->_smarty->assign($key, $value);
            return true;
        }

        /**
         * @internal
         * @param string $name
         * @param \Closure|string|array $reference
         * @see http://www.smarty.net/docs/en/api.register.plugin.tpl
         * @return bool
         */
        protected function _assignFunction($name, $reference)
        {
            return $this->_registerPlugin('function', $name, $reference);
        }

        /**
         * @internal
         */
        protected function _attachDefaultFunctions()
        {
            $this->_smarty->setPluginsDir(self::_getPluginsPath());
        }

        /**
         * @internal
         * @param string $type
         * @param string $name
         * @param \Closure|string|array $reference
         * @see http://www.smarty.net/docs/en/api.register.plugin.tpl
         * @return bool
         */
        private function _registerPlugin($type, $name, $reference)
        {
            if ($this->_smarty->registerPlugin(
                $type,
                $name,
                $reference)
            ) {
                return true;
            }
            return false;
        }

        /**
         * @internal
         * @static
         * @return array
         */
        private static function _getPluginsPath()
        {
            return array(
                self::_getLibsPath() . DIRECTORY_SEPARATOR . 'plugins',
                self::_getFunctionsAomeboDirectory(),
                self::_getFunctionsSiteDirectory(),
            );
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
            $path .= DIRECTORY_SEPARATOR . 'Smarty';
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
        private static function _getSysPluginsPath()
        {
            return self::_getLibsPath()
                . DIRECTORY_SEPARATOR . 'sysplugins';
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getLibsPath()
        {
            return __DIR__ . DIRECTORY_SEPARATOR
                . self::SMARTY_DIR . DIRECTORY_SEPARATOR . 'libs';
        }

    }
}
