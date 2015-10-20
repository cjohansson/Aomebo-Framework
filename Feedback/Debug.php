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
namespace Aomebo\Feedback
{

    /**
     * @method static \Aomebo\Feedback\Debug getInstance()
     */
    class Debug extends \Aomebo\Singleton
    {

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_debugMode = false;

        /**
         * @throws \Exception
         */
        public function __construct()
        {
            if (!$this->_isConstructed()) {

                parent::__construct();

                ini_set('error_reporting',
                    \Aomebo\Configuration::getSetting('feedback,error reporting'));
                ini_set('display_errors',
                    \Aomebo\Configuration::getSetting('feedback,display errors'));
                ini_set('display_startup_errors',
                    \Aomebo\Configuration::getSetting('feedback,display startup errors'));
                ini_set('log_errors',
                    \Aomebo\Configuration::getSetting('feedback,log errors'));
                
                if (\Aomebo\Application::isWritingnabled()) {
                    $logLocation = _SITE_ROOT_
                        . \Aomebo\Configuration::getSetting('feedback,error log');
                } else {
                    $logLocation = ini_get('error_log');
                }

                $dateComponentReplacements = array(
                    '%h' => date('h'),
                    '%H' => date('H'),
                    '%d' => date('d'),
                    '%j' => date('j'),
                    '%m' => date('m'),
                    '%n' => date('n'),
                    '%y' => date('y'),
                    '%Y' => date('Y'),
                    '%W' => date('W'),
                    '%i' => date('i'),
                );

                $logLocation = str_replace(
                    array_keys($dateComponentReplacements),
                    array_values($dateComponentReplacements),
                    $logLocation
                );

                if (file_exists($logLocation)) {

                    if (\Aomebo\Configuration::getSetting(
                        'feedback,truncate error log')
                    ) {
                        \Aomebo\Filesystem::truncateFile(
                            $logLocation,
                            \Aomebo\Configuration::getSetting(
                                'feedback,truncate error log size'));
                    }

                } else {
                    \Aomebo\Filesystem::makeFile(
                        $logLocation,
                        '// Log file created ' . date('Y-m-d H:m:i') . PHP_EOL);
                }

                ini_set('error_log', $logLocation);
                ini_set('log_errors_max_len', 1024);
                ini_set('ignore_repeated_errors', false);
                ini_set('ignore_repeated_source', false);
                ini_set('report_memleaks', true);
                ini_set('track_errors', true);
                ini_set('html_errors', false);
    /*
                ini_set('xmlrpc_errors', false);
                ini_set('xmlrpc_error_number', 0);
                ini_set('docref_root', '');
                ini_set('docref_ext', '');
    */

                ini_set('error_prepend_string',
                    \Aomebo\Configuration::getSetting('framework,name') . ': ');
    /*
                ini_set('error_append_string', '');
    */

                self::setDebugMode(
                    \Aomebo\Configuration::getSetting('feedback,debug mode'));

                // Set error handler
                set_error_handler(
                    array($this, 'errorHandler'),
                    \Aomebo\Configuration::getSetting('feedback,error reporting'));

                // Set exception handler
                set_exception_handler(
                    array($this, 'exceptionHandler'));

                $this->_flagThisConstructed();

            }
        }

        /**
         * @static
         * @param bool $mode
         */
        public static function setDebugMode($mode)
        {
            self::$_debugMode = (!empty($mode));
        }

        /**
         * @static
         * @return bool
         */
        public static function getDebugMode()
        {
            return (!empty(self::$_debugMode));
        }

        /**
         * @param int $errno
         * @param string $errstr
         * @param string|null [$errfile = null]
         * @param int|null [$errline = null]
         * @param array|null [$errcontext = null]
         * @throws \Exception
         * @return bool|null
         */
        public function errorHandler($errno, $errstr, $errfile = null,
            $errline = null, $errcontext = null)
        {

            $message = '';

            if (error_reporting() === 0) {
                return false;
            }

            // Exit script and show error-page if it was a critical error
            if ($errno == E_ERROR
                || $errno == E_PARSE
                || $errno == E_CORE_ERROR
                || $errno == E_COMPILE_ERROR
                || $errno == E_USER_ERROR
            ) {

                // Backtrace
                if (\Aomebo\Configuration::getSetting(
                    'feedback,include backtrace')
                ) {

                    $backtraceLimit =
                        (int) \Aomebo\Configuration::getSetting(
                        'feedback,backtrace limit');

                    if (!empty($backtraceLimit)
                        && $backtraceLimit > 0
                    ) {

                        $debugBacktrance =
                            \Aomebo\Application::getDebugBacktrace(
                                $backtraceLimit);

                    } else {

                        $debugBacktrance =
                            \Aomebo\Application::getDebugBacktrace();

                    }

                    $message .= 'Error-backtrace (limit: ' . $backtraceLimit . '): ' . "\n"
                        . print_r($debugBacktrance, true) . "\n";

                    $message .= 'Free memory at init: "' . \Aomebo\Application::getFreeMemoryAtInit() . '", '
                        . 'free memory at error: "' . \Aomebo\System\Memory::getSystemFreeMemory() . '"' . "\n";

                }

                self::output($message);

                // Show error page
                try
                {

                    $errorPage = _SITE_ROOT_
                        . \Aomebo\Configuration::getSetting(
                            'dispatch,error page');

                    if (file_exists($errorPage)) {
                        echo \Aomebo\Filesystem::getFileContents(
                            $errorPage);
                    } else {
                        Throw new \Exception(sprintf(
                            self::systemTranslate('Errorpage not found at "%s".'),
                            $errorPage));
                    }

                } catch (\Exception $e) {
                    self::output('Error: "' . $e->getMessage() . '"');
                }

                $dispatcher =
                    \Aomebo\Dispatcher\System::getInstance();
                $dispatcher->
                    setHttpResponseStatus500InternalServerError();

            }

            return true;

        }

        /**
         * @static
         * @param string $message
         * @param bool|null [$display = null]
         * @param bool|null [$log = null]
         */
        public static function output($message, $display = null, $log = null)
        {
            if (!empty($message)) {

                if (\Aomebo\Configuration::getSetting(
                    'feedback,dump environment variables')
                ) {
                    $message .=
                        \Aomebo\Configuration::getSetting(
                            'output,linebreak character')
                        . '$_POST: "' . (isset($_POST) ?
                            print_r($_POST, true) : 'null') . '",'
                        . '$_GET: "' . (isset($_GET) ?
                            print_r($_GET, true) : 'null') . '",'
                        . '$_SERVER: "' . (isset($_SERVER) ?
                            print_r($_SERVER, true) : 'null') . '"';
                }

                $message .= 'Free memory at init: "' . \Aomebo\Application::getFreeMemoryAtInit() . '", '
                    . 'free memory at error: "' . \Aomebo\System\Memory::getSystemFreeMemory() . '",'
                    . 'free peak memory: "' . \Aomebo\System\Memory::getSystemFreeMemoryPeak() . '"'
                    . "\n";

                if (!isset($log)) {
                    $log = \Aomebo\Configuration::getSetting(
                        'feedback,log errors');
                }

                if ($log) {
                    error_log($message);
                }

                if (!isset($display)) {
                    $display = \Aomebo\Configuration::getSetting(
                        'feedback,display errors');
                }

                if ($display) {

                    if (\Aomebo\Dispatcher\System::isShellRequest()) {
                        echo $message .
                            \Aomebo\Configuration::getSetting(
                                'output,linebreak character');
                    } else {
                        echo '<pre>' . $message .
                            \Aomebo\Configuration::getSetting(
                            'output,linebreak character')
                            . '</pre>';
                    }

                }

            }
        }

        /**
         * This method deals with handling exceptions.
         *
         * @param \Exception|null [$exception = null]
         * @throws \Exception
         */
        public function exceptionHandler($exception = null)
        {

            $message = '';

            // Backtrace
            if (\Aomebo\Configuration::getSetting(
                'feedback,include backtrace')
            ) {

                $backtraceLimit =
                    (int) \Aomebo\Configuration::getSetting(
                        'feedback,backtrace limit');

                if (!empty($backtraceLimit)
                    && $backtraceLimit > 0
                ) {

                    $debugBacktrance =
                        \Aomebo\Application::getDebugBacktrace(
                            $backtraceLimit);

                } else {

                    $debugBacktrance =
                        \Aomebo\Application::getDebugBacktrace();

                }

                $message .=
                    'Exception-backtrace (limit:' . $backtraceLimit . '): "'
                    . print_r($debugBacktrance, true) . '"';

            }

            if (\Aomebo\Configuration::getSetting(
                'feedback,dump environment variables')
            ) {

                $message .= '$_POST: "' . (isset($_POST) ?
                    print_r($_POST, true) : 'null') . '",'
                    . '$_GET: "' . (isset($_GET) ?
                    print_r($_GET, true) : 'null') . '",'
                    . '$_SERVER: "' . (isset($_SERVER) ?
                    print_r($_SERVER, true) : 'null') . '"';

            }

            $message .= 'Free memory at init: "' . \Aomebo\Application::getFreeMemoryAtInit() . '", '
                . 'free memory at error: "' . \Aomebo\System\Memory::getSystemFreeMemory() . '",'
                . 'free peak memory: "' . \Aomebo\System\Memory::getSystemFreeMemoryPeak() . '"'
                . "\n";

            // Exception message
            if (isset($exception)) {

                /** @var \Exception $exception */
                $message .= 'Exception-message: "' . $exception->getMessage() . '"';

            } else {

                $message .= 'Unspecified exception';

            }

            self::output($message);

            // Show error-page
            try
            {

                $errorPage = _SITE_ROOT_
                    . \Aomebo\Configuration::getSetting('dispatch,error page');

                if (file_exists($errorPage)) {
                    echo \Aomebo\Filesystem::getFileContents(
                        $errorPage);
                } else {
                    Throw new \Exception(sprintf(
                        self::systemTranslate('Errorpage not found at "%s".'),
                        $errorPage));
                }
            } catch (\Exception $e) {
                self::output(sprintf(
                    self::systemTranslate('Exception: "%s".'),
                    $e->getMessage()));
            }

            $dispatcher =
                \Aomebo\Dispatcher\System::getInstance();
            $dispatcher->
                setHttpResponseStatus500InternalServerError();

        }

    }
}
