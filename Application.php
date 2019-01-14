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
namespace Aomebo
{

    // This line is required in order to be able to use \Aomebo\Exceptions
    require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Exceptions.php');

    // This line is required in order to be able to extend the \Aomebo\Base class.
    require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Base.php');

    // This line is required in order to be able to extend the \Aomebo\Singleton class.
    require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Singleton.php');

    // This line is required in order to be able to keep references to frameworks objects
    require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Aomebo.php');

    /**
     * @method static \Aomebo\Application getInstance()
     */
    final class Application extends \Aomebo\Singleton
    {

        /**
         * @var string
         */
        const PARAMETER_SITE_PATH =
            'sitePath';

        /**
         * @var string
         */
        const PARAMETER_PUBLIC_INTERNAL_PATH =
            'publicPath';

        /**
         * @var string
         */
        const PARAMETER_PUBLIC_EXTERNAL_PATH =
            'publicExternalPath';

        /**
         * @var string
         */
        const PARAMETER_CONFIGURATION_INTERNAL_FILENAME =
            'internalConfigurationFilename';

        /**
         * @var string
         */
        const PARAMETER_CONFIGURATION_EXTERNAL_FILENAME =
            'externalConfigurationFilename';

        /**
         * @var string
         */
        const PARAMETER_STRUCTURE_EXTERNAL_FILENAME =
            'externalStructureFilename';

        /**
         * @var string
         */
        const PARAMETER_STRUCTURE_INTERNAL_FILENAME =
            'internalStructureFilename';

        /**
         * @var string
         */
        const PARAMETER_CONFIGURATION_ADAPTER =
            'configurationAdapter';

        /**
         * @var string
         */
        const PARAMETER_SHOW_SETUP =
            'showSetup';

        /**
         * @var string
         */
        const PARAMETER_TESTING_MODE =
            'testing';

        /**
         * @var string
         */
        const PARAMETER_BOOTSTRAP_MODE =
            'bootstrap';

        /**
         * @var string
         */
        const PARAMETER_PASS_EXECUTION_GUARDS =
            'pass_execution_guards';

        /**
         * @var string
         */
        const PARAMETER_RESPOND =
            'respond';

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_parameters = array();

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_autoloadFailureTriggersException = true;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_runtimes = array();

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_applicationData = array();

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_flushedApplicationData = true;

        /**
         * @internal
         * @static
         * @var float
         */
        private static $_freeMemoryAtInit = 0.0;

        /**
         * @internal
         * @static
         * @var int
         */
        private static $_runtimesLastModificationTime = 0;

        /**
         * @internal
         * @static
         * @var null|int
         */
        private static $_pid = null;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_writingEnabled = true;

        /**
         * @internal
         * @var array
         */
        private static $_inhibitConstruction = array();

        /**
         * @internal
         * @var array
         */
        private static $_autoLoadPaths = array();

        /**
         * This starts up the framework.
         *
         * @param array|null [$parameters = null] Contains all site-specific parameters.
         * @throws \Exception
         */
        public function __construct($parameters = null)
        {

            // Only allow one instance per request.
            if (!self::_isConstructed()) {


                /** @define _SYSTEM_START_TIME_     Startup time for system */
                define('_SYSTEM_START_TIME_', microtime(true));

                /**
                 * Default error settings
                 *
                 * These settings will later be overridden by site settings.
                 */
                ini_set('display_errors', false);
                ini_set('log_errors', true);
                ini_set('error_reporting', E_ALL);

                /**
                 * Set default time-zone
                 *
                 * This is to prevent PHP for generating any warnings.
                 * This time-zone is soon overridden.
                 */
                date_default_timezone_set('UTC');

                self::$_pid = posix_getpid();

                // Set public internal path based on back-trace
                $backtrace = self::getDebugBacktrace(2);

                if (!self::hasParameter(self::PARAMETER_PUBLIC_INTERNAL_PATH)
                    && isset($backtrace[1]['file'])
                ) {
                    self::setParameter(
                        self::PARAMETER_PUBLIC_INTERNAL_PATH,
                        dirname($backtrace[1]['file'])
                    );
                }

                /**
                 * Set public external path
                 *
                 * Only possible for requests from web-server (not PHP-client)
                 */
                if (!self::hasParameter(self::PARAMETER_PUBLIC_EXTERNAL_PATH)
                    && isset($_SERVER['SCRIPT_NAME'])
                    && substr($_SERVER['SCRIPT_NAME'], 0, 1) == '/'
                ) {
                    self::setParameter(
                        self::PARAMETER_PUBLIC_EXTERNAL_PATH,
                        $_SERVER['SCRIPT_NAME']
                    );
                }

                // Don't show setup by default
                if (!self::hasParameter(self::PARAMETER_SHOW_SETUP)) {
                    self::setParameter(
                        self::PARAMETER_SHOW_SETUP,
                        false
                    );
                }

                // Pass execution guards by default
                if (!self::hasParameter(self::PARAMETER_PASS_EXECUTION_GUARDS)) {
                    self::setParameter(
                        self::PARAMETER_PASS_EXECUTION_GUARDS,
                        true
                    );
                }

                // Respond by default
                if (!self::hasParameter(self::PARAMETER_RESPOND)) {
                    self::setParameter(
                        self::PARAMETER_RESPOND,
                        true
                    );
                }

                // Any parameters specified?
                if (isset($parameters)
                    && is_array($parameters)
                ) {
                    self::setParameters($parameters);
                }

                parent::__construct();
                self::_flagThisConstructed();

                // Do we have a internal and external path specification?
                if (self::hasParameter(self::PARAMETER_PUBLIC_INTERNAL_PATH)
                    && self::hasParameter(self::PARAMETER_PUBLIC_EXTERNAL_PATH)
                ) {

                    // Is no site-path specified or should setup be displayed?
                    if (!self::hasParameter(self::PARAMETER_SITE_PATH)
                        || self::getParameter(self::PARAMETER_SHOW_SETUP)
                    ) {
                        self::setParameter(
                            self::PARAMETER_SITE_PATH,
                            self::_getSetupSitePrivatePath()
                        );
                        self::setParameter(
                            self::PARAMETER_PUBLIC_INTERNAL_PATH,
                            self::_getSetupSitePublicPath()
                        );
                        self::$_writingEnabled = false;
                    }

                    $parameters = & self::$_parameters;

                    // Correct inputs
                    if (substr($parameters[self::PARAMETER_SITE_PATH], -1) != DIRECTORY_SEPARATOR) {
                        $parameters[self::PARAMETER_SITE_PATH] .= DIRECTORY_SEPARATOR;
                    }
                    if (!isset($_SERVER['SCRIPT_NAME'])) {
                        $parameters[self::PARAMETER_PUBLIC_EXTERNAL_PATH] = DIRECTORY_SEPARATOR;
                    }
                    if (empty($parameters[self::PARAMETER_CONFIGURATION_INTERNAL_FILENAME])) {
                        $parameters[self::PARAMETER_CONFIGURATION_INTERNAL_FILENAME] = '';
                    }
                    if (empty($parameters[self::PARAMETER_CONFIGURATION_EXTERNAL_FILENAME])) {
                        $parameters[self::PARAMETER_CONFIGURATION_EXTERNAL_FILENAME] = '';
                    }
                    if (empty($parameters[self::PARAMETER_STRUCTURE_INTERNAL_FILENAME])) {
                        $parameters[self::PARAMETER_STRUCTURE_INTERNAL_FILENAME] = '';
                    }
                    if (empty($parameters[self::PARAMETER_STRUCTURE_EXTERNAL_FILENAME])) {
                        $parameters[self::PARAMETER_STRUCTURE_EXTERNAL_FILENAME] = '';
                    }

                    self::defineConstantsFromParameters($parameters);
                    self::applyAutoLoader();
                    $configuration = \Aomebo\Configuration::getInstance();
                    self::loadApplicationData();

                    // Can we load configuration?
                    if ($configuration::load(
                        self::getParameter(self::PARAMETER_CONFIGURATION_INTERNAL_FILENAME),
                        self::getParameter(self::PARAMETER_CONFIGURATION_EXTERNAL_FILENAME),
                        self::getParameter(self::PARAMETER_STRUCTURE_INTERNAL_FILENAME),
                        self::getParameter(self::PARAMETER_STRUCTURE_EXTERNAL_FILENAME))
                    ) {

                        self::setAutoloadFailureTriggersException(
                            \Aomebo\Configuration::getSetting('output,autoload failure triggers exception'));
                        if (self::getParameter(self::PARAMETER_PASS_EXECUTION_GUARDS)) {
                            self::passExecutionGuards();
                        }
                        if (self::getParameter(self::PARAMETER_RESPOND)) {
                            self::respond();
                        }

                    } else {
                        Throw new \Exception(
                            self::systemTranslate('Failed to load configuration.')
                        );
                    }
                } else {
                    Throw new \Exception(
                        sprintf(
                            self::systemTranslate(
                                'Invalid parameters for Aomebo Application. Parameters: "%s".'
                            ),
                            print_r(self::$_parameters, true)
                        )
                    );
                }

            }
        }

        /**
         * @static
         * @param string $path
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function addAutoLoadPath($path)
        {
            if (!empty($path)) {
                self::$_autoLoadPaths[] = $path;
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * @static
         * @param array $paths
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function addAutoLoadPaths($paths)
        {
            if (is_array($paths) && count($paths) > 0) {
                foreach ($paths as $path) {
                    self::addAutoLoadPath($path);
                }
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * Guards the number of allow concurrent requests
         * and also awaits enough free memory.
         *
         * @static
         */
        public static function passExecutionGuards()
        {
            self::$_freeMemoryAtInit = \Aomebo\System\Memory::getSystemFreeMemory();

            // Do we have a list of concurrent requests?
            if ($requests = self::getApplicationData('requests')) {

                $maximumConcurrentRequests = \Aomebo\Configuration::getSetting(
                    'application,maximum concurrent requests');
                $maximumConcurrentRequestsPeriod = \Aomebo\Configuration::getSetting(
                    'application,maximum concurrent requests period');

                if (!is_array($requests)) {
                    $requests = array();
                }

                // Add current request to list
                $requests[self::$_pid] = _SYSTEM_START_TIME_;
                $requestTimeout = _SYSTEM_START_TIME_ - $maximumConcurrentRequestsPeriod;

                do
                {
                    // Remove processes which have timed out
                    foreach ($requests as $pid => $time)
                    {
                        if ($time < $requestTimeout) {
                            unset($requests[$pid]);
                        }
                    }
                    if ($maximumConcurrentRequests > 0
                        && count($requests) > $maximumConcurrentRequests
                    ) {
                        self::setApplicationData(
                            'requests',
                            $requests,
                            true
                        );
                        sleep(1);
                        $requests = self::getApplicationData('requests', true);
                    }
                } while($maximumConcurrentRequests > 0
                    && count($requests) > $maximumConcurrentRequests
                );

                self::setApplicationData(
                    'requests',
                    $requests,
                    true
                );

            } else {
                self::setApplicationData(
                    'requests',
                    array(self::$_pid => _SYSTEM_START_TIME_),
                    true
                );
            }

            // Wait until server has enough memory
            if (!\Aomebo\System\Memory::systemHasEnoughMemory()) {
                while (!\Aomebo\System\Memory::systemHasEnoughMemory())
                {
                    sleep(1);
                }
            }
        }

        /**
         * @static
         */
        public static function respond()
        {
            new \Aomebo\Filesystem();
            new \Aomebo\Feedback\Debug();
            self::loadRuntimes();
            self::loadSiteClass();
            new \Aomebo\Interpreter\Engine();
            new \Aomebo\Request();
            new \Aomebo\Response\Handler();
            new \Aomebo\Dispatcher\System();
            if (\Aomebo\Response\Handler::hasResponse()) {
                \Aomebo\Response\Handler::respond();
            } else {
                \Aomebo\Dispatcher\System::setHttpResponseStatus400BadRequest();
            }
        }

        /**
         * @static
         */
        public static function applyAutoLoader()
        {
            spl_autoload_register('Aomebo\\Application::autoLoad', true, false);
            self::addAutoLoadPaths(array(
                _SYSTEM_ROOT_,
                _PRIVATE_ROOT_,
                _PUBLIC_ROOT_,
                _SITE_ROOT_
            ));
        }

        /**
         * This is a function which returns whether or not a class construction is allowed or not.
         * This is to monitor and prevent execution errors.
         *
         * @static
         * @param string $name
         * @return bool
         */
        public static function isInhibitedToConstruct($name)
        {
            return (!empty($name) && !empty(self::$_inhibitConstruction[$name]));
        }

        /**
         * @static
         * @return bool
         */
        public static function isWritingnabled()
        {
            return (self::$_writingEnabled == true);
        }

        /**
         * Decrease number of concurrent requests by one.
         */
        public function __destruct()
        {
            if ($requests = self::getApplicationData('requests', true)) {
                if (isset($requests[self::$_pid])) {
                    unset($requests[self::$_pid]);
                    self::setApplicationData(
                        'requests',
                        $requests,
                        true
                    );
                }
            } else {
                self::setApplicationData(
                    'requests',
                    array(),
                    true
                );
            }
        }

        /**
         * @static
         * @return int
         */
        public static function getRuntimesLastModificationTime()
        {
            return self::$_runtimesLastModificationTime;
        }

        /**
         * @static
         * @return array|bool
         */
        public static function getRuntimes()
        {
            return (count(self::$_runtimes) > 0 ?
                self::$_runtimes : false);
        }

        /**
         * @static
         * @return float|int
         */
        public static function getFreeMemoryAtInit()
        {
            return self::$_freeMemoryAtInit;
        }

        /**
         * @static
         * @return bool
         */
        public static function clearCache()
        {
            return \Aomebo\Filesystem::deleteFilesInDirectory(
                self::getCacheDir());
        }

        /**
         * @static
         * @return string
         */
        public static function getCacheDir()
        {
            $cacheDir = _SITE_ROOT_ . 'Cache';
            if (!is_dir($cacheDir)) {
                \Aomebo\Filesystem::makeDirectory($cacheDir);
            }
            return $cacheDir;
        }

        /**
         * @static
         * @param string $key
         * @param mixed $value
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function setParameter($key, $value)
        {
            if (isset($key, $value)) {
                self::$_parameters[$key] = $value;
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * @static
         * @param array $array associative array
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function setParameters($array)
        {
            if (isset($array)
                && is_array($array)
            ) {
                if (count($array) > 0) {
                    foreach ($array as $key => $value)
                    {
                        self::setParameter($key, $value);
                    }
                }
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * @static
         * @return bool|int|null|string
         */
        public static function autoInstall()
        {
            return \Aomebo\Trigger\System::processTriggers(
                \Aomebo\Trigger\System::TRIGGER_KEY_SYSTEM_AUTOINSTALL
            );
        }

        /**
         * @static
         * @return bool|int|null|string
         */
        public static function autoUninstall()
        {
            return \Aomebo\Trigger\System::processTriggers(
                \Aomebo\Trigger\System::TRIGGER_KEY_SYSTEM_AUTOUNINSTALL
            );
        }

        /**
         * @static
         * @return bool|int|null|string
         */
        public static function autoUpdate()
        {
            return \Aomebo\Trigger\System::processTriggers(
                \Aomebo\Trigger\System::TRIGGER_KEY_SYSTEM_AUTOUPDATE
            );
        }

        /**
         * @static
         * @return bool
         */
        public static function shouldAutoInstall()
        {
            return \Aomebo\Configuration::getSetting(
                'framework,auto-install');
        }

        /**
         * @static
         * @param int [$limit = 0]
         * @return array
         */
        public static function getDebugBacktrace($limit = 0)
        {
            if (!empty($limit)
                && $limit > 0
            ) {
                /**
                 * @see http://www.php.net/function.debug-backtrace
                 */
                if (phpversion() >= '5.3.6') {
                    $debugBacktrance = debug_backtrace(
                        DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit);
                } else {
                    $debugBacktrance = debug_backtrace(true);
                }
            } else {
                /** @see http://www.php.net/function.debug-backtrace */
                if (phpversion() >= '5.3.6') {
                    $debugBacktrance = debug_backtrace(
                        DEBUG_BACKTRACE_PROVIDE_OBJECT);
                } else {
                    $debugBacktrance = debug_backtrace(true);
                }
            }
            return $debugBacktrance;
        }

        /**
         * @static
         * @param string $key
         * @return mixed
         */
        public static function getParameter($key)
        {
            return (self::hasParameter($key) ?
                self::$_parameters[$key] : false);
        }

        /**
         * @static
         * @param string $key
         * @return bool
         */
        public static function hasParameter($key)
        {
            return isset($key, self::$_parameters, self::$_parameters[$key]);
        }

        /**
         * @static
         * @return double
         */
        public static function getElapsedMicroTime()
        {
            /** @var double $now */
            $now = microtime(true);
            return $now - _SYSTEM_START_TIME_;
        }

        /**
         * @static
         * @param bool $value
         */
        public static function setAutoloadFailureTriggersException($value)
        {
            self::$_autoloadFailureTriggersException =
                (!empty($value));
        }

        /**
         * @static
         * @param string $key
         * @param bool [$reloadFromFilesystem = false]
         * @return mixed
         */
        public static function getApplicationData($key,
            $reloadFromFilesystem = false)
        {
            if (!empty($key)) {
                if (!empty($reloadFromFilesystem)) {
                    self::loadApplicationData();
                }
                if (isset(self::$_applicationData[$key])) {
                    return self::$_applicationData[$key];
                }
            }
            return null;
        }

        /**
         * @static
         * @param string $key
         * @param mixed $value
         * @param bool [$flush = true]
         */
        public static function setApplicationData($key, $value, $flush = true)
        {
            if (!empty($key)) {
                self::$_applicationData[$key] = $value;
                self::$_flushedApplicationData = false;
                if (!empty($flush)) {
                    self::flushApplicationData();
                }
            }
        }

        /**
         * @static
         * @param string $name
         * @throws \Exception
         */
        public static function autoLoad($name)
        {
            $trySubPaths = array();

            if ($explodes = explode('\\', $name)) {

                $path = implode(DIRECTORY_SEPARATOR, $explodes) . _PHP_EX_;

                $trySubPaths[] = $path;

                // Support framework files from this directory
                if (strlen($path) >= 7) {
                    if (substr($path, 0, 7) ==
                        'Aomebo'  . DIRECTORY_SEPARATOR
                    ) {
                        $trySubPaths[] = substr($path, 7);
                    }
                }

                $count = count($explodes);

                // Support files like "\Modules\Cron\Cron" when calling "\Modules\Cron\Module"
                if ($count > 2) {
                    $explodes[$count - 1] = $explodes[$count - 2];
                    $trySubPaths[] = implode(DIRECTORY_SEPARATOR, $explodes) . _PHP_EX_;
                }

            } else {
                $trySubPaths[] = $name . _PHP_EX_;
            }

            $triedPaths = array();
            $foundFile = false;

            foreach (self::$_autoLoadPaths as $autoLoadPath)
            {
                foreach ($trySubPaths as $trySubPath)
                {
                    $tryPath = $autoLoadPath . $trySubPath;
                    $triedPaths[] = $tryPath;
                    if (file_exists($tryPath)) {
                        try {
                            require_once($tryPath);
                            $foundFile = true;
                        } catch (\Exception $e) {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate(
                                        'Something went wrong when including file "%s", error: "%s".'
                                    ),
                                    $tryPath,
                                    $e->getMessage()
                                )
                            );
                        }
                    }
                    if ($foundFile) {
                        break;
                    }
                }
                if ($foundFile) {
                    break;
                }
            }

            if (!$foundFile && self::$_autoloadFailureTriggersException) {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate(
                            "Couldn't find file '%s' at '%s'."
                        ),
                        $name,
                        implode("','", $triedPaths)
                    )
                );
            }

        }

        /**
         * @static
         * @throws \Exception
         */
        public static function loadSiteClass()
        {
            $classPath =
                \Aomebo\Configuration::getSetting('site,class path');
            if (strlen($classPath) > 0
                && substr($classPath, 0, 1) == DIRECTORY_SEPARATOR
            ) {
                $classPath = _SITE_ROOT_ . substr($classPath, 1);
            } else {
                $classPath = _SITE_ROOT_ . $classPath;
            }
            if (file_exists($classPath)) {
                try {
                    require_once($classPath);
                } catch (\Exception $e) {
                    Throw new \Exception(sprintf(self::systemTranslate(
                        'Loading site-class "%s" spawned error "%s"',
                        $e->getMessage()
                    )));
                }
            }
        }

        /**
         * This method starts the scanning of filesystem
         * for Run-times.
         *
         * @static
         * @throws \Exception
         */
        public static function loadRuntimes()
        {

            // Inhibit premature construction of these classes
            self::$_inhibitConstruction['Aomebo\Interpreter\Engine'] = true;
            self::$_inhibitConstruction['Aomebo\Feedback\Debug'] = true;
            self::$_inhibitConstruction['Aomebo\Dispatcher\System'] = true;
            self::$_inhibitConstruction['Aomebo\Response\Handler'] = true;

            $roots = array();
            $runtimesLastModificationTime = 0;
            $useRuntimeCache = \Aomebo\Configuration::getSetting(
                'framework,use runtime cache');

            if ($siteDirectories = \Aomebo\Configuration::getSetting(
                'paths,runtime site directories')
            ) {
                foreach ($siteDirectories as $siteDirectory)
                {
                    $roots[] = _SITE_ROOT_ . $siteDirectory;
                }
            }

            if ($publicDirectories = \Aomebo\Configuration::getSetting(
                'paths,runtime public directories')
            ) {
                foreach ($publicDirectories as $publicDirectory)
                {
                    $roots[] = _PUBLIC_ROOT_ . $publicDirectory;
                }
            }

            $cacheParameters = '';
            $cacheKey = '';

            if ($useRuntimeCache) {
                foreach ($roots as $root)
                {
                    if ($diremtime = \Aomebo\Filesystem::getDirectoryLastModificationTime(
                        $root, true, 2, false)
                    ) {
                        if ($diremtime > $runtimesLastModificationTime) {
                            $runtimesLastModificationTime = $diremtime;
                        }
                    }
                }

                $cacheParameters = 'Application/Runtimes';
                $cacheKey = md5('last_mod=' . $runtimesLastModificationTime
                    . '&framework=' . \Aomebo\Filesystem::getDirectoryLastModificationTime(
                    __DIR__, false, 0, false)
                );
            }

            $loadedCache = false;
            self::$_runtimesLastModificationTime =
                $runtimesLastModificationTime;

            if ($useRuntimeCache
                && self::isWritingnabled()
                && \Aomebo\Cache\System::cacheExists(
                    $cacheParameters,
                    $cacheKey,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM)
            ) {
                if ($data = \Aomebo\Cache\System::loadCache(
                    $cacheParameters,
                    $cacheKey,
                    \Aomebo\Cache\System::FORMAT_SERIALIZE,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM)
                ) {
                    $loadedCache = true;
                    try
                    {
                        if (!empty($data['runtimes'])) {
                            if ($runtimes = unserialize($data['runtimes'])) {
                                if (is_array($runtimes)
                                    && count($runtimes) > 0
                                ) {

                                    // Check that it really is valid run-times
                                    $allIsRuntime = true;
                                    foreach ($runtimes as $runtime)
                                    {
                                        if (!is_object($runtime)
                                            || !is_a($runtime, '\Aomebo\Runtime')
                                        ) {
                                            $allIsRuntime = false;
                                            break;
                                        }
                                    }
                                    if ($allIsRuntime) {
                                        self::$_runtimes = $runtimes;
                                    } else {
                                        \Aomebo\Feedback\Debug::log(self::systemTranslate(
                                            'At least one run-time in cache was of wrong type. Cleaning.'
                                        ));
                                        $loadedCache = false;
                                    }

                                } else {
                                    \Aomebo\Feedback\Debug::output(self::systemTranslate(
                                        'Run-times cache returned zero run-times. Cleaning.'
                                    ));
                                    $loadedCache = false;
                                }
                            } else {
                                $loadedCache = false;
                            }
                        }
                        if (!empty($data['routes'])) {
                            if ($routes = unserialize($data['routes'])) {
                                if (is_array($routes)
                                    && count($routes) > 0
                                ) {

                                    // Check that it really is routes
                                    $allIsRoutes = true;
                                    foreach ($routes as $route)
                                    {
                                        if (!is_object($route)
                                            || !is_a($route, '\Aomebo\Dispatcher\Route')
                                        ) {
                                            $allIsRoutes = false;
                                            break;
                                        }
                                    }
                                    if ($allIsRoutes) {
                                        \Aomebo\Dispatcher\System::setRoutes($routes);
                                    } else {
                                        \Aomebo\Feedback\Debug::log(self::systemTranslate(
                                            'At least one route in cache was of wrong type. Cleaning.'
                                        ));
                                        $loadedCache = false;
                                    }

                                } else {
                                    \Aomebo\Feedback\Debug::output(self::systemTranslate(
                                        'Routes cache returned zero routes. Cleaning.'
                                    ));
                                    $loadedCache = false;
                                }
                            } else {
                                $loadedCache = false;
                            }
                        }
                    } catch (\Exception $e) {
                        \Aomebo\Feedback\Debug::output(sprintf(
                            self::systemTranslate('Loading run-times and routes cache returned error "%s"'),
                            $e->getMessage()
                        ));
                        $loadedCache = false;
                    }
                }
            }

            if (!$loadedCache) {
                if ($useRuntimeCache) {
                    \Aomebo\Cache\System::clearCache(
                        $cacheParameters,
                        null,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    );
                }

                // Iterate through all roots
                foreach ($roots as $root)
                {

                    if (!is_dir($root)
                        && \Aomebo\Configuration::getSetting('paths,create runtime directories')
                    ) {
                        \Aomebo\Filesystem::makeDirectory($root);
                    }

                    if (is_dir($root))
                    {
                        $dirs = scandir($root);

                        // Iterate through all directories
                        foreach ($dirs as $dir)
                        {

                            // Is directory not current dir or parent dir pointer?
                            if (!empty($dir)
                                && $dir != '.'
                                && $dir != '..'
                            ) {
                                $absPath = $root . DIRECTORY_SEPARATOR . $dir;

                                // Is it a valid directory?
                                if (is_dir($absPath)) {
                                    self::_loadRuntimesFromDirectory($absPath);
                                }

                            }
                        }
                    }

                }

                if ($useRuntimeCache) {
                    \Aomebo\Cache\System::saveCache(
                        $cacheParameters,
                        $cacheKey,
                        array(
                            'runtimes' => serialize(self::$_runtimes),
                            'routes' => serialize(\Aomebo\Dispatcher\System::getRoutes()),
                        ),
                        \Aomebo\Cache\System::FORMAT_SERIALIZE,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    );
                }
            }

            // Remove the inhibition of the  construction of these classes
            self::$_inhibitConstruction['Aomebo\Interpreter\Engine'] = false;
            self::$_inhibitConstruction['Aomebo\Feedback\Debug'] = false;
            self::$_inhibitConstruction['Aomebo\Dispatcher\System'] = false;
            self::$_inhibitConstruction['Aomebo\Response\Handler'] = false;
        }

        /**
         * This method scans directory for runtimes and
         * loads them into memory.
         *
         * @internal
         * @static
         * @param string $absPath
         * @throws \Exception
         */
        private static function _loadRuntimesFromDirectory($absPath)
        {
            if (!empty($absPath)) {

                $namespaceName = basename(dirname($absPath));
                $namespaceClassName = substr($namespaceName, 0, -1);
                $dir = basename($absPath);
                $foundFile = false;

                $file = $absPath . DIRECTORY_SEPARATOR
                    . $namespaceClassName . _PHP_EX_;
                $alternateFile = $absPath . DIRECTORY_SEPARATOR
                    . $dir . _PHP_EX_;

                if (file_exists($file)) {
                    $foundFileName = $file;
                    $foundFile = true;
                } else if (file_exists($alternateFile)) {
                    $foundFileName = $alternateFile;
                    $foundFile = true;
                }

                // Can we find a run-time file?
                if ($foundFile) {

                    /** @var string $foundFileName */
                    $foundClass = false;
                    try
                    {
                        require_once($foundFileName);
                        $className = '\\' . $namespaceName . '\\'
                            . $dir . '\\' . $namespaceClassName;
                        if (class_exists($className, false)) {
                            $foundClassName = $className;
                            $foundClass = true;
                        }

                    } catch (\Exception $e) {
                        \Aomebo\Feedback\Debug::log(self::systemTranslate(sprintf(
                            "Including '%s' caused error '%s'",
                            $foundFileName,
                            $e->getMessage()
                        )));
                    }

                    if ($foundClass) {
                        /** @var string $foundClassName */

                        try
                        {
                            $runtime = new $foundClassName();
                            if (is_object($runtime)
                                && is_a($runtime, '\Aomebo\Runtime')
                            ) {

                                /** @var \Aomebo\Runtime $runtime */
                                self::$_runtimes[] = $runtime;
                            }
                        } catch (\Exception $e) {
                            if (\Aomebo\Configuration::getSetting(
                                'feedback,halt on runtime construct exceptions')
                            ) {
                                Throw new \Exception(
                                    sprintf(
                                        self::systemTranslate('Failed to construct runtime "%s".'),
                                        $foundClassName
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }

        /**
         * @static
         */
        public static function loadApplicationData()
        {
            if (file_exists(self::getApplicationDataPath())) {
                if ($fileData = file_get_contents(
                    self::getApplicationDataPath())
                ) {
                    try {
                        if ($jsonData = json_decode($fileData, true)) {
                            self::$_applicationData = $jsonData;
                        }
                    } catch (\Exception $e) {
                        \Aomebo\Feedback\Debug::log(self::systemTranslate(sprintf(
                            "Failed to load application-data from '%s' caused error '%s'",
                            self::getApplicationDataPath(),
                            $e->getMessage()
                        )));
                    }
                }
            }
        }

        /**
         * @static
         * @param array $parameters
         * @throws \Aomebo\Exceptions\InvalidParametersException
         */
        public static function defineConstantsFromParameters($parameters)
        {
            if (isset($parameters)
                && is_array($parameters)
            ) {

                /** @define string _PHP_EX_                 PHP file suffix */
                define('_PHP_EX_', '.php');

                /** @define string _PRIVATE_ROOT_           Absolute root to private */
                define('_PRIVATE_ROOT_',
                    dirname($parameters[self::PARAMETER_SITE_PATH]) . DIRECTORY_SEPARATOR);

                /** @define string _SITE_ROOT_              Absolute root to site, always ends with directory separator */
                define('_SITE_ROOT_', $parameters[self::PARAMETER_SITE_PATH]);

                /** @define string _SYSTEM_ROOT_            Absolute root to system */
                define('_SYSTEM_ROOT_',
                    __DIR__ . DIRECTORY_SEPARATOR);

                /** @define string _PUBLIC_ROOT_            Absolute root to public */
                define('_PUBLIC_ROOT_',
                    $parameters[self::PARAMETER_PUBLIC_INTERNAL_PATH] . DIRECTORY_SEPARATOR);

                /** @define string _SYSTEM_SITE_ROOT_       Absolute root to system inside site */
                define('_SYSTEM_SITE_ROOT_', _SITE_ROOT_ . 'Aomebo');

                if (substr($parameters[self::PARAMETER_PUBLIC_EXTERNAL_PATH], -1, 1)
                    == DIRECTORY_SEPARATOR
                ) {

                    /** @define string _PUBLIC_EXTERNAL_ROOT_        Browser root */
                    define('_PUBLIC_EXTERNAL_ROOT_',
                        $parameters[self::PARAMETER_PUBLIC_EXTERNAL_PATH]);

                } else {
                    if (dirname($parameters[self::PARAMETER_PUBLIC_EXTERNAL_PATH]) ==
                        DIRECTORY_SEPARATOR
                    ) {

                        /** @define string _PUBLIC_EXTERNAL_ROOT_       Browser root */
                        define('_PUBLIC_EXTERNAL_ROOT_',
                            dirname($parameters[self::PARAMETER_PUBLIC_EXTERNAL_PATH]));

                    } else {

                        /** @define string _PUBLIC_EXTERNAL_ROOT_       Browser root */
                        define('_PUBLIC_EXTERNAL_ROOT_',
                            dirname($parameters[self::PARAMETER_PUBLIC_EXTERNAL_PATH])
                                . DIRECTORY_SEPARATOR);

                    }
                }
            } else {
                Throw new \Aomebo\Exceptions\InvalidParametersException();
            }
        }

        /**
         * Update framework. Requires connection to GIT repo.
         *
         * @return string
         */
        public function update()
        {
            $command = escapeshellarg(_SYSTEM_ROOT_ . 'update.sh');
            $result = str_replace(
                "\n",
                "\n<br />",
                shell_exec($command)
            );
            return sprintf(
                "<strong>Update (%s):</strong>\n<p>%s</p>\n",
                $command,
                $result
            );
        }

        /**
         * Requires `composer install` in root directory.
         *
         * @static
         * @return string
         */
        public static function test()
        {
            $command = escapeshellarg(_SYSTEM_ROOT_ . 'test.sh');
            $result = str_replace(
                "\n",
                "\n<br />",
                shell_exec($command)
            );
            return sprintf(
                "<strong>Unit Tests (%s):</strong>\n<p>%s</p>\n",
                $command,
                $result
            );
        }

        /**
         * @static
         * @return string
         */
        public static function getVersion()
        {
            if (file_exists(__DIR__ . '/.git/refs/heads/master')) {
                return file_get_contents(__DIR__ . '/.git/refs/heads/master');
            }
            return '-';
        }

        /**
         * @static
         */
        public static function flushApplicationData()
        {
            if (!self::$_flushedApplicationData
                && self::isWritingnabled()
            ) {
                try {
                    if ($jsonData = json_encode(self::$_applicationData)) {
                        file_put_contents(self::getApplicationDataPath(), $jsonData);
                    }
                    self::$_flushedApplicationData = true;
                } catch (\Exception $e) {
                    \Aomebo\Feedback\Debug::log(self::systemTranslate(sprintf(
                        "Failed to write application-data to '%s', caused error '%s'",
                        self::getApplicationDataPath(),
                        $e->getMessage()
                    )));
                }
            }
        }

        /**
         * @static
         * @return string
         */
        public static function getApplicationDataPath()
        {
            return _SITE_ROOT_ . '.application-data';
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getSetupSitePrivatePath()
        {
            return __DIR__ . DIRECTORY_SEPARATOR . 'GUI' . DIRECTORY_SEPARATOR
                . 'Setup' . DIRECTORY_SEPARATOR . 'private';
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getSetupSitePublicPath()
        {
            return __DIR__ . DIRECTORY_SEPARATOR . 'GUI' . DIRECTORY_SEPARATOR
                . 'Setup' . DIRECTORY_SEPARATOR . 'public';
        }

    }
}
