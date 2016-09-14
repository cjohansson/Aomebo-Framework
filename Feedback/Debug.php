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
            if (!$this->_isConstructed()
                && \Aomebo\Configuration::isLoaded()
            ) {

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
                        '// ' . sprintf(
                            self::systemTranslate('Log file created %s'),
                            date('Y-m-d H:m:i')
                        ) . PHP_EOL
                    );
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
        public static function isDebugMode()
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

            $lineBreakCharacter =
                (\Aomebo\Configuration::isLoaded() ?
                 \Aomebo\Configuration::getSetting('output,linebreak character')
                 : "\n");

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
                if (!\Aomebo\Configuration::isLoaded()
                     || \Aomebo\Configuration::getSetting(
                    'feedback,include backtrace')
                ) {

                    $backtraceLimit =
                        (int) (\Aomebo\Configuration::isLoaded() ?
                               \Aomebo\Configuration::getSetting('feedback,backtrace limit')
                               : 20);

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

                    $message .= sprintf(
                        self::systemTranslate(
                        'Error-backtrace (limit: %d): '),
                        $backtraceLimit
                    ) . $lineBreakCharacter . print_r($debugBacktrance, true) . "\n";

                    $message .= self::getMemoryDump() . "\n";

                }

                self::output($message);

                // Show error page
                try
                {

                    $errorPage = _SITE_ROOT_
                        . (\Aomebo\Configuration::isLoaded() ?
                           \Aomebo\Configuration::getSetting('dispatch,error page')
                           : 'error.html');

                    if (file_exists($errorPage)) {
                        echo \Aomebo\Filesystem::getFileContents($errorPage);
                    } else {
                        Throw new \Exception(sprintf(
                            self::systemTranslate('Errorpage not found at "%s".'),
                            $errorPage));
                    }

                } catch (\Exception $e) {
                    self::output(sprintf(
                        self::systemTranslate('Error: "%s"'),
                        $e->getMessage()));
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
         * @param mixed $message
         */
        public static function display($message)
        {
            self::output($message, true, false);
        }

        /**
         * @static
         * @param mixed $message
         */
        public static function log($message)
        {
            self::output($message, false, true);
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

                if (is_array($message)
                    || is_object($message)
                ) {
                    $message = '<pre>' . print_r($message, true) . '</pre>';
                }

                $lineBreakCharacter = (\Aomebo\Configuration::isLoaded() ?
                    \Aomebo\Configuration::getSetting('output,linebreak character')
                                       : "\n");
                $message .= $lineBreakCharacter;

                if (\Aomebo\Configuration::isLoaded()
                     && \Aomebo\Configuration::getSetting(
                    'feedback,dump environment variables')
                ) {
                    $message .= self::getEnvironmentVariablesDump()
                        . $lineBreakCharacter;
                }

                $message .= self::getMemoryDump() . $lineBreakCharacter;

                if (!isset($log)) {
                    $log = (\Aomebo\Configuration::isLoaded() ?
                            \Aomebo\Configuration::getSetting('feedback,log errors')
                            : true);
                }

                if ($log) {
                    error_log($message);
                }

                if (!isset($display)) {
                    $display = (\Aomebo\Configuration::isLoaded() ?
                                \Aomebo\Configuration::getSetting('feedback,display errors')
                                : true);
                }

                if ($display) {
                    if (\Aomebo\Dispatcher\System::isShellRequest()) {
                        echo $message . $lineBreakCharacter;
                    } else {
                        echo '<pre>' . $message . $lineBreakCharacter . '</pre>';
                    }
                }

            }
        }

        /**
         * @static
         * @return string
         */
        public static function getMemoryDump()
        {
            return sprintf(
                self::systemTranslate('Free memory at init: "%s", free memory at error: "%s", free peak memory: "%s".'),
                \Aomebo\Application::getFreeMemoryAtInit(),
                \Aomebo\System\Memory::getSystemFreeMemory(),
                \Aomebo\System\Memory::getSystemFreeMemoryPeak()
            );
        }

        /**
         * @static
         * @return string
         */
        public static function getEnvironmentVariablesDump()
        {
            $sessionData = '';
            if ($sesionBlock = \Aomebo\Session\Handler::getSessionBlock()) {
                if ($sessionBlockData = $sesionBlock->getBlockData()) {
                    $sessionData = print_r($sessionBlockData, true);
                }
            }

            return sprintf(
                self::systemTranslate('$_POST: "%s", $_GET: "%s", $_SERVER: "%s", $_SESSION: "%s", $_COOKIE: "%s", $_ENV: "%s", $_FILES: "%s", SESSION-BLOCK-DATA: "%s"'),
                (isset($_POST) ? print_r($_POST, true) : 'null'),
                (isset($_GET) ? print_r($_GET, true) : 'null'),
                (isset($_SERVER) ? print_r($_SERVER, true) : 'null'),
                (isset($_SESSION) ? print_r($_SESSION, true) : 'null'),
                (isset($_COOKIE) ? print_r($_COOKIE, true) : 'null'),
                (isset($_ENV) ? print_r($_ENV, true) : 'null'),
                (isset($_FILES) ? print_r($_FILES, true) : 'null'),
                $sessionData
            );
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

            $lineBreakCharacter = (\Aomebo\Configuration::isLoaded()
                                   ? \Aomebo\Configuration::getSetting('output,linebreak character')
                                   : "\n");

            // Backtrace
            if (!\Aomebo\Configuration::isLoaded()
                || \Aomebo\Configuration::getSetting(
                'feedback,include backtrace')
            ) {

                $backtraceLimit =
                    (int) (\Aomebo\Configuration::isLoaded() ?
                           \Aomebo\Configuration::getSetting(
                               'feedback,backtrace limit')
                           : 20);

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
                    sprintf(
                        self::systemTranslate('Exception-backtrace (limit: %d): "%s"'),
                        $backtraceLimit,
                        print_r($debugBacktrance, true)
                    );

            }

            if (!\Aomebo\Configuration::isLoaded()
                 || \Aomebo\Configuration::getSetting(
                'feedback,dump environment variables')
            ) {
                $message .= self::getEnvironmentVariablesDump();
            }

            $message .= self::getMemoryDump() . $lineBreakCharacter;

            // Exception message
            if (isset($exception)) {

                /** @var \Exception $exception */
                $message .= sprintf(
                    self::systemTranslate('Exception-message: "%s"'),
                    $exception->getMessage()
                );

            } else {
                $message .= self::systemTranslate('Unspecified exception');
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
