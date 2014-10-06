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
namespace Aomebo
{

    // This line is required in order to be able to extend Aomebo Base class.
    require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Base.php');

    // This line is required in order to be able to extend Aomebo Singleton class.
    require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Singleton.php');

    // This line is required in order to be able to extend Aomebo Singleton class.
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
        const PARAMETER_SHOW_CONFIGURATION =
            'showConfiguration';

        /**
         * @var string
         */
        const PARAMETER_TESTING_MODE =
            'testing';

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
        private static $_autoloadFailureTriggersException = false;

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
         * This starts up the framework.
         *
         * @param array|null [$parameters = null]       Contains all site-specific parameters.
         * @throws \Exception
         */
        public function __construct($parameters = null)
        {

            // Only allow one instance per request.
            if (!self::_isConstructed()) {

                /** @define _SYSTEM_START_TIME_     Startup time for system */
                define('_SYSTEM_START_TIME_', microtime(true));

                self::$_pid = posix_getpid();

                // Set public internal path
                $backtrace = self::getDebugBacktrace(2);

                if (isset($backtrace[1]['file'])) {
                    self::setParameter(
                        self::PARAMETER_PUBLIC_INTERNAL_PATH,
                        dirname($backtrace[1]['file']));
                }

                // Set public external path
                if (isset($_SERVER['PHP_SELF'])
                    && substr($_SERVER['PHP_SELF'], 0, 1) == '/'
                ) {
                    self::setParameter(
                        self::PARAMETER_PUBLIC_EXTERNAL_PATH,
                        $_SERVER['PHP_SELF']);
                }

                // Don't show setup by default
                if (!self::hasParameter(self::PARAMETER_SHOW_SETUP)) {
                    self::setParameter(
                        self::PARAMETER_SHOW_SETUP,
                        false);
                }

                // Don't show configuration by default
                if (!self::hasParameter(self::PARAMETER_SHOW_CONFIGURATION)) {
                    self::setParameter(
                        self::PARAMETER_SHOW_CONFIGURATION, false);
                }

                // Any parameters specified?
                if (isset($parameters)
                    && is_array($parameters)
                ) {
                    self::setParameters($parameters);
                }

                self::_flagThisConstructed();

                // Is configuration set and right keys in it?
                if (self::hasParameter(self::PARAMETER_PUBLIC_INTERNAL_PATH)
                    && self::hasParameter(self::PARAMETER_PUBLIC_EXTERNAL_PATH)
                ) {

                    // Is no site-path specified or should setup be displayed?
                    if (!self::hasParameter(self::PARAMETER_SITE_PATH)
                        || self::getParameter(self::PARAMETER_SHOW_SETUP)
                    ) {

                        self::setParameter(
                            self::PARAMETER_SITE_PATH,
                            self::_getSetupSitePath());

                    // Otherwise - should configuration be presented?
                    } else if (self::getParameter(self::PARAMETER_SHOW_CONFIGURATION)) {

                        self::setParameter(
                            self::PARAMETER_SITE_PATH,
                            self::_getConfigurationSitePath());

                    }

                    $parameters = & self::$_parameters;

                    // Correct inputs
                    if (substr($parameters[self::PARAMETER_SITE_PATH], -1) != DIRECTORY_SEPARATOR) {
                        $parameters[self::PARAMETER_SITE_PATH] .= DIRECTORY_SEPARATOR;
                    }
                    if (!isset($_SERVER['PHP_SELF'])) {
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

                    // Define system constants
                    $this->_defineConstants($parameters);

                    // Apply framework default auto-loader
                    spl_autoload_register(__NAMESPACE__
                        . '\\Application::autoLoad', true, false);

                    // Get the configuration
                    $configuration = \Aomebo\Configuration::getInstance();

                    // Is no configuration adapter set?
                    if (!self::hasParameter(self::PARAMETER_CONFIGURATION_ADAPTER)) {
                        self::setParameter(
                            self::PARAMETER_CONFIGURATION_ADAPTER,
                            $configuration::DEFAULT_ADAPTER);
                    }

                    // Load application-data
                    self::_loadApplicationData();

                    // Increment number of concurrent requests by one
                    if ($requests = self::getApplicationData('requests')) {

                        if (!is_array($requests)) {
                            $requests = array();
                        }

                        $requests[self::$_pid] = _SYSTEM_START_TIME_;
                        $requestTimeout = _SYSTEM_START_TIME_ - 10;

                        foreach ($requests as $pid => $time)
                        {
                            if ($time < $requestTimeout) {
                                unset($requests[$pid]);
                            }
                        }

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

                    // Try to load configuration
                    if ($configuration::load(
                        self::getParameter(self::PARAMETER_CONFIGURATION_INTERNAL_FILENAME),
                        self::getParameter(self::PARAMETER_CONFIGURATION_EXTERNAL_FILENAME),
                        self::getParameter(self::PARAMETER_STRUCTURE_INTERNAL_FILENAME),
                        self::getParameter(self::PARAMETER_STRUCTURE_EXTERNAL_FILENAME),
                        self::getParameter(self::PARAMETER_CONFIGURATION_ADAPTER))
                    ) {

                        self::$_freeMemoryAtInit =
                            \Aomebo\System\Memory::getSystemFreeMemory();

                        $maximumConcurrentRequests =
                            \Aomebo\Configuration::getSetting('application,maximum concurrent requests');

                        // Does server has enough free memory for handling request?
                        if (\Aomebo\System\Memory::systemHasEnoughMemory()
                            && (!$maximumConcurrentRequests
                            || sizeof($requests) < $maximumConcurrentRequests)
                        ) {

                            // Store setting if autoload should trigger exception
                            $this->setAutoloadFailureTriggersException(
                                \Aomebo\Configuration::getSetting('output,autoload failure triggers exception'));

                            // Load runtimes
                            self::_loadRuntimes();

                            // Load site class (if any)
                            self::_loadSiteClass();

                            // Load feedback engine
                            new \Aomebo\Feedback\Debug();

                            // Load the response handler
                            $responseHandler = \Aomebo\Response\Handler::getInstance();

                            // Load dispatcher for analyzing of request
                            $dispatcher = \Aomebo\Dispatcher\System::getInstance();

                            // Is a testing request?
                            if (!empty(self::$_parameters[self::PARAMETER_TESTING_MODE])) {

                                // Load the internationalization system
                                \Aomebo\Internationalization\System::getInstance();

                                // Load our database
                                \Aomebo\Database\Adapter::getInstance();

                                // Load interpreter for parsing of pages
                                \Aomebo\Interpreter\Engine::getInstance();

                                // Load cache system
                                \Aomebo\Cache\System::getInstance();

                                // Load our session handler
                                \Aomebo\Session\Handler::getInstance();

                                new \Aomebo();

                                // Present our output
                                $presenter =
                                    \Aomebo\Presenter\Engine::getInstance();
                                $presenter->output();

                            } else if ($dispatcher::isAssociativesRequest()) {

                                if ((!\Aomebo\Configuration::getSetting('dispatch,allow only associatives request with matching referer')
                                    || $dispatcher::requestRefererMatchesSiteUrl())
                                    && ($dispatcher::isHttpGetRequest()
                                        || $dispatcher::isHttpHeadRequest())
                                ) {

                                    // Load our database
                                    \Aomebo\Database\Adapter::getInstance();

                                    // Load the associatives engine
                                    \Aomebo\Associatives\Engine::getInstance();

                                    new \Aomebo();

                                    // Parse the requests associatives
                                    \Aomebo\Associatives\Parser::parseRequest();

                                } else {
                                    $dispatcher::setHttpResponseStatus403Forbidden();
                                }

                            } else if ($dispatcher::isShellRequest()) {

                                // Is shell requests allowed?
                                if (\Aomebo\Configuration::getSetting(
                                    'dispatch,allow shell requests')
                                ) {

                                    // Load the internationalization system
                                    \Aomebo\Internationalization\System::getInstance();

                                    // Load our database
                                    \Aomebo\Database\Adapter::getInstance();

                                    // Load interpreter for parsing of pages
                                    \Aomebo\Interpreter\Engine::getInstance();

                                    // Load cache system
                                    \Aomebo\Cache\System::getInstance();

                                    // Load our session handler
                                    \Aomebo\Session\Handler::getInstance();

                                    new \Aomebo();

                                    // Interpret page
                                    \Aomebo\Interpreter\Engine::interpret();

                                    // Present our output
                                    $presenter = \Aomebo\Presenter\Engine::getInstance();
                                    $presenter->output();

                                    // Save application-data
                                    self::_flushApplicationData();

                                } else {
                                    $dispatcher::setHttpResponseStatus403Forbidden();
                                }

                            } else if ($dispatcher::isFaviconRequest()) {

                                // Do nothing

                            } else if ($dispatcher::isPageRequest()) {

                                // Load the internationalization system
                                \Aomebo\Internationalization\System::getInstance();

                                // Load our database
                                \Aomebo\Database\Adapter::getInstance();

                                // Load the associatives engine
                                \Aomebo\Associatives\Engine::getInstance();

                                // Load interpreter for parsing of pages
                                \Aomebo\Interpreter\Engine::getInstance();

                                // Load cache system
                                \Aomebo\Cache\System::getInstance();

                                // Load indexing engine
                                $indexing =
                                    \Aomebo\Indexing\Engine::getInstance();

                                new \Aomebo();

                                // Interpret page
                                \Aomebo\Interpreter\Engine::interpret();

                                // Index our output
                                $indexing->index();

                                // Present our output
                                $presenter = \Aomebo\Presenter\Engine::getInstance();
                                $presenter->output();

                                // Save application-data
                                self::_flushApplicationData();

                            } else {
                                \Aomebo\Dispatcher\System::
                                    setHttpResponseStatus403Forbidden();
                            }

                        } else {
                            \Aomebo\Dispatcher\System::
                                setHttpResponseStatus503ServiceUnavailable();
                        }

                    } else {
                        Throw new \Exception('Failed to load configuration');
                    }
                } else {
                    Throw new \Exception(
                        'Invalid parameters for Aomebo Application in '
                        . __FILE__ . ', parameters: "' . print_r(self::$_parameters, true)
                        . '"');
                }
            }

        }

        /**
         *
         */
        public function __destruct()
        {

            // Decrement number of concurrent requests by one
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
            return (sizeof(self::$_runtimes) > 0 ?
                self::$_runtimes : false);
        }

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
         * @throws \Exception
         */
        public static function setParameter($key, $value)
        {
            if (isset($key, $value)) {
                self::$_parameters[$key] = $value;
            } else {
                Throw new \Exception(
                    'Invalid parameters for ' . __METHOD__);
            }
        }

        /**
         * @static
         * @param array $array                  associative array
         * @throws \Exception
         */
        public static function setParameters($array)
        {
            if (isset($array)
                && is_array($array)
            ) {
                if (sizeof($array) > 0) {
                    foreach ($array as $key => $value)
                    {
                        self::setParameter($key, $value);
                    }
                }
            } else {
                Throw new \Exception(
                    'Invalid parameters for ' . __METHOD__);
            }
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

                /**
                 * @see http://www.php.net/function.debug-backtrace
                 */
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
         * @return bool
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
            return (isset($key) 
                && isset(self::$_parameters[$key]));
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
                    self::_loadApplicationData();
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
                    self::_flushApplicationData();
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
            $pathSystem = _SYSTEM_ROOT_;
            $pathSystemLevels = 0;
            $pathPrivate = _PRIVATE_ROOT_;
            $pathPrivateAlternate = _PRIVATE_ROOT_;
            $pathPublic = _PUBLIC_ROOT_;
            $pathPublicAlternate = _PUBLIC_ROOT_;
            $pathSite = _SITE_ROOT_;
            $pathSiteAlternate = _SITE_ROOT_;
            $namespaces = explode('\\', $name);
            $sizeof = sizeof($namespaces);
            for ($i = 0; $i < $sizeof; $i++) {
                if ($namespaces[$i] != 'Aomebo') {
                    if ($pathSystemLevels > 0) {
                        $pathSystem .= DIRECTORY_SEPARATOR;
                    }
                    $pathSystem .= $namespaces[$i];
                    $pathSystemLevels++;
                }
                if ($i > 0) {
                    $pathPrivate .= DIRECTORY_SEPARATOR;
                    $pathPublic .= DIRECTORY_SEPARATOR;
                    $pathSite .= DIRECTORY_SEPARATOR;
                    $pathPrivateAlternate .= DIRECTORY_SEPARATOR;
                    $pathPublicAlternate .= DIRECTORY_SEPARATOR;
                    $pathSiteAlternate .= DIRECTORY_SEPARATOR;
                }
                $pathPrivate .= $namespaces[$i];
                $pathPublic .= $namespaces[$i];
                $pathSite .= $namespaces[$i];
                if ($i < ($sizeof - 1)
                    || !isset($namespaces[$i - 1])
                ) {
                    $pathPrivateAlternate .= $namespaces[$i];
                    $pathPublicAlternate .= $namespaces[$i];
                    $pathSiteAlternate .= $namespaces[$i];
                } else {
                    $pathPrivateAlternate .= $namespaces[$i - 1];
                    $pathPublicAlternate .= $namespaces[$i - 1];
                    $pathSiteAlternate .= $namespaces[$i - 1];
                }

            }
            $pathPrivate .= _PHP_EX_;
            $pathPublic .= _PHP_EX_;
            $pathSystem .= _PHP_EX_;
            $pathSite .= _PHP_EX_;
            $pathPrivateAlternate .= _PHP_EX_;
            $pathPublicAlternate .= _PHP_EX_;
            $pathSiteAlternate .= _PHP_EX_;
            if (file_exists($pathSystem)) {
                try {
                    require_once($pathSystem);
                } catch (\Exception $e) {
                    Throw new \Exception('Something went wrong when including '
                        . 'file "' . $pathSystem . '", error: '
                        . $e->getMessage());
                }
            } else if (file_exists($pathPrivate)) {
                try {
                    require_once($pathPrivate);
                } catch (\Exception $e) {
                    Throw new \Exception('Something went wrong when including '
                        . 'file "' . $pathPrivate . '", error: '
                        . $e->getMessage());
                }
            } else if (file_exists($pathPublic)) {
                try {
                    require_once($pathPublic);
                } catch (\Exception $e) {
                    Throw new \Exception('Something went wrong when including '
                        . 'file "' . $pathPublic . '", error: '
                        . $e->getMessage());
                }
            } else if (file_exists($pathSite)) {
                try {
                    require_once($pathSite);
                } catch (\Exception $e) {
                    Throw new \Exception('Something went wrong when including '
                        . 'file "' . $pathSite . '", error: '
                        . $e->getMessage());
                }
            } else if (file_exists($pathPrivateAlternate)) {
                try {
                    require_once($pathPrivateAlternate);
                } catch (\Exception $e) {
                    Throw new \Exception('Something went wrong when including '
                        . 'file "' . $pathPrivateAlternate . '", error: '
                        . $e->getMessage());
                }
            } else if (file_exists($pathPublicAlternate)) {
                try {
                    require_once($pathPublicAlternate);
                } catch (\Exception $e) {
                    Throw new \Exception('Something went wrong when including '
                        . 'file "' . $pathPublicAlternate . '", error: '
                        . $e->getMessage());
                }
            } else if (file_exists($pathSiteAlternate)) {
                try {
                    require_once($pathSiteAlternate);
                } catch (\Exception $e) {
                    Throw new \Exception('Something went wrong when including '
                        . 'file "' . $pathSiteAlternate . '", error: '
                        . $e->getMessage());
                }
            } else {
                if (self::$_autoloadFailureTriggersException) {
                    Throw new \Exception('Couldn\'t find file "' . $name
                        . '" at "' . $pathSystem . '", "' . $pathPrivate . '" or at "'
                        . $pathPublic . '" or at "' . $pathSite . '"');
                }
            }
        }

        /**
         * @internal
         * @static
         */
        private static function _loadSiteClass()
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
                require_once($classPath);
            }
        }

        /**
         * This method starts the scanning of filesystem
         * for Runtimes.
         *
         * @internal
         * @static
         * @throws \Exception
         */
        private static function _loadRuntimes()
        {

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
                        $root)
                    ) {
                        if ($diremtime > $runtimesLastModificationTime) {
                            $runtimesLastModificationTime = $diremtime;
                        }
                    }
                }

                $cacheParameters = 'Application/Runtimes';
                $cacheKey = md5('last_mod=' . $runtimesLastModificationTime
                    . '&framework=' . \Aomebo\Filesystem::getDirectoryLastModificationTime(
                        __DIR__)
                );

            }

            $loadedCache = false;
            self::$_runtimesLastModificationTime =
                $runtimesLastModificationTime;

            if ($useRuntimeCache
                && \Aomebo\Cache\System::cacheExists(
                $cacheParameters,
                $cacheKey,
                \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM)
            ) {

                if ($data = \Aomebo\Cache\System::loadCache(
                        $cacheParameters,
                        $cacheKey,
                        \Aomebo\Cache\System::FORMAT_SERIALIZE,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    )
                ) {

                    $loadedCache = true;

                    try
                    {

                        if (!empty($data['runtimes'])) {
                            if ($runtimes = @unserialize($data['runtimes'])) {
                                self::$_runtimes = $runtimes;
                            } else {
                                $loadedCache = false;
                            }
                        }

                        if (!empty($data['routes'])) {
                            if ($routes = @unserialize($data['routes'])) {
                                \Aomebo\Dispatcher\System::setRoutes(
                                    $routes
                                );
                            } else {
                                $loadedCache = false;
                            }
                        }

                    } catch (\Exception $e) {}

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

                                $absPath =
                                    $root . DIRECTORY_SEPARATOR . $dir;

                                // Is it a valid directory?
                                if (is_dir($absPath)) {
                                    self::_loadRuntimesFromDirectory(
                                        $absPath);
                                }

                            }

                        }
                    }
                }

                if ($useRuntimeCache) {

                    $data = array(
                        'runtimes' => serialize(self::$_runtimes),
                        'routes' => serialize(\Aomebo\Dispatcher\System::getRoutes()),
                    );

                    \Aomebo\Cache\System::saveCache(
                        $cacheParameters,
                        $cacheKey,
                        $data,
                        \Aomebo\Cache\System::FORMAT_SERIALIZE,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    );

                }

            }

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
            if (!empty($absPath)
                && is_dir($absPath)
            ) {

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

                // Can we find a runtime file?
                if ($foundFile) {

                    /** @var string $foundFileName */

                    $foundClass = false;

                    try
                    {

                        require_once($foundFileName);

                        // Build class names
                        $className = '\\' . $namespaceName . '\\'
                            . $dir . '\\' . $namespaceClassName;

                        if (class_exists($className, false)) {
                            $foundClassName = $className;
                            $foundClass = true;
                        }

                    } catch (\Exception $e) {}

                    if ($foundClass) {

                        /** @var string $foundClassName */

                        try
                        {

                            /** @var \Aomebo\Runtime $runtime */
                            $runtime = new $foundClassName();

                            if (is_a($runtime, '\\Aomebo\\Runtime'))
                            {
                                self::$_runtimes[] = $runtime;
                            }

                        } catch (\Exception $e) {

                            if (\Aomebo\Configuration::getSetting(
                                'feedback,halt on runtime construct exceptions')
                            ) {
                                Throw new \Exception(
                                    'Failed to construct runtime "'
                                    . $foundClassName . '"');
                            }

                        }

                    }
                }

            }
        }

        /**
         * @internal
         * @param array $parameters
         * @throws \Exception
         */
        private function _defineConstants($parameters)
        {
            if (isset($parameters)
                && is_array($parameters)
            ) {

                /** @define string _PHP_EX_                 Php extension */
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

                Throw new \Exception(
                    'Invalid parameters for ' . __FILE__);

            }
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getSetupSitePath()
        {
            return __DIR__ . DIRECTORY_SEPARATOR . 'GUI' . DIRECTORY_SEPARATOR
            . 'Setup' . DIRECTORY_SEPARATOR . 'private';
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getConfigurationSitePath()
        {
            return __DIR__ . DIRECTORY_SEPARATOR . 'Configuration' . DIRECTORY_SEPARATOR
            . 'Setup' . DIRECTORY_SEPARATOR . 'private';
        }

        /**
         * @internal
         * @static
         */
        private static function _loadApplicationData()
        {
            if (file_exists(self::_getApplicationDataPath())) {
                if ($fileData = file_get_contents(
                    self::_getApplicationDataPath())
                ) {
                    try {
                        if ($jsonData = json_decode($fileData, true)) {
                            self::$_applicationData = $jsonData;
                        }
                    } catch (\Exception $e) {}
                }
            }
        }

        /**
         * @internal
         * @static
         */
        private static function _flushApplicationData()
        {
            if (!self::$_flushedApplicationData) {
                try {
                    if ($jsonData = json_encode(self::$_applicationData)) {
                        file_put_contents(self::_getApplicationDataPath(), $jsonData);
                    }
                    self::$_flushedApplicationData = true;
                } catch (\Exception $e) {}
            }
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getApplicationDataPath()
        {
            return _SITE_ROOT_ . '.application-data';
        }

    }

}

