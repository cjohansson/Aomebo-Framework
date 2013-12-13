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
namespace Aomebo\Template\Adapters
{

    /**
     * @method static \Aomebo\Template\Adapters\Base getInstance()
     */
    abstract class Base extends \Aomebo\Singleton
    {

        /**
         * @internal
         * @var \Aomebo\Runtime|null
         */
        private $_module;

        /**
         * @param string $name
         * @param \Closure|string|array $reference
         * @return bool
         */
        public function attachFunction($name, $reference)
        {
            return $this->_assignFunction($name, $reference);
        }

        /**
         * Attach a single variable.
         *
         * @param string $key
         * @param mixed $value
         * @throws \Exception
         * @return bool
         */
        public function attachVariable($key, $value)
        {
            if (!empty($key)) {
                return $this->_assign($key, $value);
            } else {
                Throw new \Exception(
                    'Invalid key in ' . __FUNCTION__
                        . ' in ' . __FILE__);
            }
        }

        /**
         * Attach multi-variables.
         *
         * @param array $keysToValuesArray
         * @throws \Exception
         * @return bool
         */
        public function attachVariables($keysToValuesArray)
        {
            if (isset($keysToValuesArray)
                && is_array($keysToValuesArray)
                && sizeof($keysToValuesArray) > 0
            ) {
                $accBool = true;
                foreach ($keysToValuesArray as $key => $value)
                {
                    $accBool = ($accBool && $this->attachVariable($key, $value));
                }
                return $accBool;
            } else {
                Throw new \Exception(
                    'Invalid parameters for ' . __FUNCTION__
                        . ' in ' . __FILE__);
            }
        }

        /**
         * Sets a view-script for a template adapter.
         *
         * @param string $filename
         * @throws \Exception
         * @return bool
         */
        public function setFile($filename)
        {

            unset($this->_module);

            $callers = debug_backtrace();

            if (isset($callers[1]['object'])) {
                if (is_a($callers[1]['object'], '\Aomebo\Runtime')) {

                    /** @var \Aomebo\Runtime $object  */
                    $object = $callers[1]['object'];

                    $directory = dirname($object->getAbsoluteFilename());

                    // Remove leading slash
                    if (!empty($filename)
                        && substr($filename, 0, 1) == DIRECTORY_SEPARATOR
                    ) {
                        $filename = substr($filename, 1);
                    }

                    if ($this->_getTemplate($directory, $filename)) {

                        $this->_module = $object;
                        $this->_attachDefaultVariables();
                        $this->_attachDefaultFunctions();
                        return true;

                    }
                } else {
                    Throw new \Exception(
                        'Invalid reference object for view template');
                }
            }

            return false;

        }

        /**
         * Parses view-script and then frees it.
         *
         * @return string
         */
        public function parseAndFree()
        {
            $output = $this->parse();
            $this->free();
            return $output;
        }

        /**
         * Parses view-script.
         *
         * @throws \Exception
         * @return string
         */
        abstract public function parse();

        /**
         * Frees the template object from memory.
         *
         * @internal
         * @return bool
         */
        abstract public function free();

        /**
         * Get Template.
         *
         * @internal
         * @param string $directory
         * @param string $filename
         * @throws \Exception
         * @return bool
         */
        abstract protected function _getTemplate($directory, $filename);

        /**
         * Assigns a variable.
         *
         * @internal
         * @param string $key
         * @param mixed $value
         * @return bool
         */
        abstract protected function _assign($key, $value);

        /**
         * @internal
         * @param string $name
         * @param \Closure|string|array $reference
         * @return bool
         */
        abstract protected function _assignFunction($name, $reference);

        /**
         * Attach the default functions.
         *
         * @internal
         */
        abstract protected function _attachDefaultFunctions();

        /**
         * @internal
         * @return string
         */
        protected function _getCacheLocation()
        {
            return \Aomebo\Template\Adapter::getCacheDir();
        }

        /**
         * Attach the default variables.
         *
         * @internal
         */
        private function _attachDefaultVariables()
        {

            $this->attachVariable('R',
                \Aomebo\Dispatcher\System::getResourcesDirExternalPath());
            $this->attachVariable('U',
                \Aomebo\Dispatcher\System::getUploadsDirExternalPath());

            if (isset($this->_module)) {
                $name = $this->_module->getField('name');
                $this->attachVariable('F', strtolower($name));
            }

            if ($variables = \Aomebo\Configuration::getSetting(
                'templates,variables')
            ) {
                foreach ($variables as $key => $value) {
                    $this->attachVariable($key, $value);
                }
            }

        }

    }
}
