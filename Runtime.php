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

    /**
     * This class will be used by both models, controllers and modules
     * and gives access to features such as Cacheable, Routable,
     * Dependent, Executable, Installable, Associatable.
     *
     * @method static \Aomebo\Runtime getInstance()
     */
    class Runtime extends \Aomebo\Singleton implements \Serializable
    {

        /**
         * @internal
         * @var bool
         */
        private $_enabled = false;

        /**
         * @internal
         * @var bool
         */
        private $_executing = false;

        /**
         * @internal
         * @var array
         */
        private $_fields = array();

        /**
         * @internal
         * @var array
         */
        private $_parameterToIndex = array();

        /**
         * @internal
         * @var array
         */
        private $_indexToParameter = array();

        /**
         * @internal
         * @var array
         */
        private $_collectedSets = array();

        /**
         * @internal
         * @var \Aomebo\Dispatcher\Route|null
         */
        private $_executeRoute;

        /**
         * @var array
         * @deprecated
         */
        protected $_parameters = array();

        /**
         * @var array
         * @deprecated
         */
        protected $_routes = array();

        /**
         * @var array
         * @deprecated
         */
        protected $_dependencies = array();

        /**
         * @static
         * @var \Aomebo|null
         */
        protected static $_aomebo = null;

        /**
         * Lookup a message in the current domain.
         *
         * @static
         * @param string $message
         * @return string
         * @see gettext()
         */
        public static function gettext($message)
        {
            return self::$_aomebo->Internationalization()->System()->gettext(
                $message);
        }

        /**
         * Override the current domain.
         *
         * The dgettext() function allows you to override the current
         * domain for a single message lookup.
         *
         * @static
         * @param string $domain
         * @param string $message
         * @param null|string [$context = null]
         * @return string
         * @see dgettext()
         */
        public static function dgettext($domain, $message, $context = null)
        {
            return self::$_aomebo->Internationalization()->System()->dcgettext(
                $domain, $message, $context);
        }

        /**
         * @static
         * @param string $message
         * @param null|string [$domain = null]
         * @param null|string [$context = null]
         * @return string
         */
        public static function translate($message, $domain = null, $context = null)
        {
            return \Aomebo\Internationalization\System::siteTranslate(
                $message, $domain, $context);
        }

        /**
         * @static
         * @param string $message
         * @param null|string [$domain = null]
         * @param null|string [$context = null]
         * @return string
         */
        public static function t($message, $domain = null, $context = null)
        {
            return \Aomebo\Internationalization\System::siteTranslate(
                $message, $domain, $context);
        }

        /**
         * @static
         * @param string $message
         * @param null|string [$domain = null]
         * @param null|string [$context = null]
         * @return string
         */
        public static function __($message, $domain = null, $context = null)
        {
            return \Aomebo\Internationalization\System::siteTranslate(
                $message, $domain, $context);
        }

        /**
         * @static
         * @param string $singular
         * @param string $plural
         * @param null|string [$domain = null]
         * @param null|string [$context = null]
         * @return string
         */
        public static function _n($singular, $plural, $domain = null, $context = null)
        {
            return \Aomebo\Internationalization\System::sitePluralTranslate(
                $singular, $plural, $domain, $context);
        }


        /**
         * Plural version of gettext.
         *
         * The plural version of gettext(). Some languages have more than
         * one form for plural messages dependent on the count.
         *
         * @static
         * @param string $msgid1
         * @param string $msgid2
         * @param int $n
         * @return string
         * @see ngettext()
         */
        public static function ngettext($msgid1, $msgid2, $n)
        {
            return self::$_aomebo->Internationalization()->System()->ngettext(
                $msgid1, $msgid2, $n
            );
        }

        /**
         * Overrides the domain for a single lookup.
         *
         * This function allows you to override the current
         * domain for a single message lookup.
         *
         * @static
         * @param string $domain
         * @param string $message
         * @param int $category
         * @return string
         * @see dcgettext()
         */
        public static function dcgettext($domain, $message, $category)
        {
            return self::$_aomebo->Internationalization()->System()->ngettext(
                $domain, $message, $category
            );
        }

        /**
         * Plural version of dgettext.
         *
         * The dngettext() function allows you to override
         * the current domain for a single plural message lookup.
         *
         * @static
         * @param string $domain
         * @param string $msgid1
         * @param string $msgid2
         * @param int $n
         * @return string
         * @see dngettext()
         */
        public static function dngettext($domain, $msgid1, $msgid2, $n)
        {
            return self::$_aomebo->Internationalization()->System()->dngettext(
                $domain, $msgid1, $msgid2, $n
            );
        }

        /**
         * Plural version of dcgettext.
         *
         * This function allows you to override the current
         * domain for a single plural message lookup.
         *
         * @static
         * @param string $domain
         * @param string $msgid1
         * @param string $msgid2
         * @param int $n
         * @param int $category
         * @return string
         * @see dcngettext()
         */
        public static function dcngettext($domain, $msgid1, $msgid2, $n, $category)
        {
            return self::$_aomebo->Internationalization()->System()->dcngettext(
                $domain, $msgid1, $msgid2, $n, $category
            );
        }

        /**
         * @return bool
         */
        public function isInternationalized()
        {
            return (is_a($this, '\Aomebo\Runtime\Internationalized'));
        }

        /**
         * @return bool
         */
        public function isExecutable()
        {
            return (is_a($this, '\Aomebo\Runtime\Executable'));
        }

        /**
         * @return bool
         */
        public function isRoutable()
        {
            return (is_a($this, '\Aomebo\Runtime\Routable'));
        }

        /**
         * @return bool
         */
        public function isCacheable()
        {
            return (is_a($this, '\Aomebo\Runtime\Cacheable'));
        }

        /**
         * @return bool
         */
        public function isExecutionParameters()
        {
            return (is_a($this, '\Aomebo\Runtime\ExecutionParameters'));
        }

        /**
         * @return bool
         */
        public function isInstallable()
        {
            return (is_a($this, '\Aomebo\Runtime\Installable'));
        }

        /**
         * @return bool
         */
        public function isPageable()
        {
            return (is_a($this, '\Aomebo\Runtime\Pageable'));
        }

        /**
         * @return bool
         */
        public function isUninstallable()
        {
            return (is_a($this, '\Aomebo\Runtime\Uninstallable'));
        }

        /**
         * @return bool
         */
        public function isUpdatable()
        {
            return (is_a($this, '\Aomebo\Runtime\Updatable'));
        }

        /**
         * @return bool
         */
        public function isInitializable()
        {
            return (is_a($this, '\Aomebo\Runtime\Initializable'));
        }

        /**
         * @return bool
         */
        public function isDependent()
        {
            return (is_a($this, '\Aomebo\Runtime\Dependent'));
        }

        /**
         * @return bool
         */
        public function isAssociatable()
        {
            return (is_a($this, '\Aomebo\Runtime\Associatable'));
        }

        /**
         * @param bool $executing
         */
        public function setExecuting($executing)
        {
            $this->_executing = (!empty($executing));
        }

        /**
         * @return bool
         */
        public function isExecuting()
        {
            return (!empty($this->_executing));
        }

        /**
         * @param string|null [$output = null]
         * @param array|null [$parameters = null]
         * @param Base|null [$lastEvaluatedRuntime = null]
         * @return \Aomebo\Runtime
         * @deprecated
         */
        public function executeFunction(
            $output = null,
            $parameters = null,
            & $lastEvaluatedRuntime = null)
        {
            return $this->executeRuntime(
                $output,
                $parameters,
                $lastEvaluatedRuntime);
        }

        /**
         * @param string|null [$output = null]
         * @param array|null [$parameters = null]
         * @param Base|null [$lastEvaluatedRuntime = null]
         * @return \Aomebo\Runtime
         */
        public function executeRuntime(
            $output = null,
            $parameters = null,
            & $lastEvaluatedRuntime = null)
        {

            if ($this->isExecutable()) {
                if ($this->isEnabled()) {

                    // Get interpreter-engine
                    $interpreterEngine =
                        \Aomebo\Interpreter\Engine::getInstance();
                    $functionCallIndex =
                        $interpreterEngine->getRuntimeCallIndex();

                    $this->resetFields();

                    $dispatcher =
                        \Aomebo\Dispatcher\System::getInstance();

                    if ($this->isRoutable()) {
                        $this->_executeRoute =
                            $dispatcher->buildGetValues($this);
                    }

                    $associatives =
                        \Aomebo\Associatives\Engine::getInstance();
                    $functionName = $this->getField('name');
                    $this->setField('function_call_index',
                        $functionCallIndex);

                    if (!$dispatcher::isAjaxRequest()) {

                        $associatives->addAssociatives($functionName);

                        // Convert old way of specifying dependencies to new way
                        if (isset($this->_dependencies)
                            && is_array($this->_dependencies)
                            && sizeof($this->_dependencies) > 0
                        ) {
                            foreach ($this->_dependencies as $dependency)
                            {
                                $dependencyObject = new \Aomebo\Associatives\Dependent(
                                    $dependency
                                );
                                $associatives->addDependencies(
                                    $dependencyObject);
                            }
                        }

                        if ($this->isDependent()) {

                            /** @var \Aomebo\Runtime\Dependent $ref */
                            $ref = & $this;

                            if ($ref->getDependencies()) {
                                $associatives->addDependencies(
                                    $ref->getDependencies());
                            }

                        }

                    }

                    if (isset($lastEvaluatedRuntime)) {
                        $this->setField('last_evaluated_runtime',
                            $lastEvaluatedRuntime);
                    }

                    $firstParameter =
                        $this->getParameterFromIndex(0);

                    if (is_array($parameters)
                        && sizeof($parameters) > 0
                    ) {
                        foreach ($parameters as $key => $value)
                        {
                            if (isset($key, $value)) {
                                $this->setField($key, $value);
                            }
                        }
                    } else if (!is_array($parameters)
                        && !empty($parameters)
                    ) {
                        $this->setField($firstParameter,
                            $parameters);
                    } else if (!empty($output)) {
                        $this->setField($firstParameter,
                            $output);
                    }

                    // If we are using module-cache
                    if (\Aomebo\Configuration::getSetting(
                        'output,module cache')
                    ) {
                        if ($this->useCache()) {

                            /** @var \Aomebo\Runtime\Cacheable $ref */
                            $ref = & $this;
                            $cacheParameters = $ref->getCacheParameters();
                            $cacheKey = $ref->getCacheKey();

                            if (\Aomebo\Cache\System::cacheExists(
                                $cacheParameters,
                                $cacheKey)
                            ) {

                                $cacheData = \Aomebo\Cache\System::loadCache(
                                    $cacheParameters,
                                    $cacheKey,
                                    \Aomebo\Cache\System::FORMAT_SERIALIZE);

                                /** @var array $cacheData */

                                foreach ($cacheData as $fieldKey => $fieldValue)
                                {
                                    $this->setField($fieldKey, $fieldValue);
                                }

                            } else {

                                $this->_doExecute();

                                $cacheData = array();
                                $ignoreFields = array(
                                    'name' => true,
                                    'last_evaluated_runtime' => true,
                                    'relative_path' => true,
                                    'full_path' => true,
                                );

                                foreach ($this->_fields as $fieldKey => $fieldValue)
                                {
                                    if (!isset($ignoreFields[$fieldKey])) {
                                        $cacheData[$fieldKey] = $fieldValue;
                                    }
                                }

                                \Aomebo\Cache\System::saveCache(
                                    $cacheParameters,
                                    $cacheKey,
                                    $cacheData,
                                    \Aomebo\Cache\System::FORMAT_SERIALIZE);

                            }

                        } else {
                            $this->_doExecute();
                        }
                    } else {
                        $this->_doExecute();
                    }

                    // Increment call index
                    $interpreterEngine->incrementRuntimeCallIndex();

                }

                return $this;

            }

            return null;

        }

        /**
         *
         */
        public function clearCollectedSets()
        {
            $this->_collectedSets = array();
        }

        /**
         * This metho deals with setting of field values some
         * values should be prohibited to be set by other than
         * this class internal methods.
         *
         * @param string $field
         * @param string $value
         */
        public function setField($field, $value)
        {
            if (is_array($field)
                && sizeof($field)
            ) {
                $sizeof = sizeof($field);
                $fieldPointer = & $this->_fields;
                for ($i = 0; $i < $sizeof; $i++) {
                    $field = $field[$i];
                    if (!isset($fieldPointer[$field])) {
                        $fieldPointer[$field] = array();
                    }
                    $fieldPointer = & $fieldPointer[$field];
                }
                $fieldPointer = $value;
            } else {
                $this->_fields[$field] = $value;
            }
            if ($this->isExecuting()) {
                $this->_collectedSets[$field] = $value;
            }
        }

        /**
         * This method returns all public fields of data.
         *
         * @param string $field
         * @param string [$default = null]
         * @return string|null
         */
        public function getField($field, $default = null)
        {
            if (is_array($field)) {
                $sizeof = sizeof($field);
                $value = $this->_fields;
                for ($i = 0; $i < $sizeof; $i++) {
                    $sub = $field[$i];
                    if (empty($value[$sub])) {
                        if (isset($default)) {
                            return $default;
                        } else {
                            return null;
                        }
                    } else {
                        $value = $value[$sub];
                    }
                }
                return $value;
            } else {
                if (isset($this->_fields[$field])) {
                    return $this->_fields[$field];
                } else {
                    if (isset($default)) {
                        return $default;
                    } else {
                        return null;
                    }
                }
            }
        }

        /**
         * This method is used in caching
         * purposes only (save).
         *
         * @return string
         */
        public function serialize()
        {
            return serialize(array(
                '_enabled' => $this->_enabled,
                '_fields' => $this->_fields,
                '_parameterToIndex' => $this->_parameterToIndex,
                '_indexToParameter' => $this->_indexToParameter,
            ));
        }

        /**
         * This method is used in cachinging
         * purposes only (restore).
         *
         * @param string $data
         */
        public function unserialize($data)
        {
            if (!empty($data)) {
                if ($unserialized = @unserialize($data)) {
                    if (isset($unserialized['_enabled'])) {
                        $this->_enabled = $unserialized['_enabled'];
                    }
                    if (isset($unserialized['_fields'])) {
                        $this->_fields = $unserialized['_fields'];
                    }
                    if (isset($unserialized['_parameterToIndex'])) {
                        $this->_parameterToIndex = $unserialized['_parameterToIndex'];
                    }
                    if (isset($unserialized['_indexToParameter'])) {
                        $this->_indexToParameter = $unserialized['_indexToParameter'];
                    }
                    if (!self::_isConstructed()) {

                        parent::__construct();
                        self::$_aomebo = \Aomebo::getInstance();
                        self::_flagThisConstructed();
                        $this->load();

                    }
                }
            }
        }

        /**
         * @param array $routes
         */
        public function loadRoutes($routes)
        {

            if (isset($routes)) {
                if (is_array($routes)
                    && sizeof($routes) > 0
                ) {
                    foreach ($routes as $route)
                    {
                        if (isset($route)
                            && is_a($route, '\Aomebo\Dispatcher\Route')
                        ) {

                            /** @var \Aomebo\Dispatcher\Route $route */

                            if ($route->isValid()) {
                                \Aomebo\Dispatcher\System::addRoute($route);
                            }

                        }

                    }
                } else if (is_a($routes, '\Aomebo\Dispatcher\Route')) {

                    /** @var \Aomebo\Dispatcher\Route $routes */

                    if ($routes->isValid()) {
                        \Aomebo\Dispatcher\System::addRoute($routes);
                    }

                }
            }

        }

        /**
         * This method is a public variant returning
         * whether or not to use cache.
         *
         * @return bool
         */
        public function useCache()
        {
            if ($this->isCacheable()) {

                /** @var \Aomebo\Runtime\Cacheable $ref */
                $ref = & $this;
                return $ref->useCache();

            }

            return false;

        }

        /**
         * @return bool
         */
        public function isEnabled()
        {
            return (!empty($this->_enabled));
        }

        /**
         * @param bool $enabled
         */
        public function setEnabled($enabled)
        {
            $this->_enabled = (!empty($enabled));
        }

        /**
         * @return array|bool
         */
        public function getFieldsToIndex()
        {
            return $this->_parameterToIndex;
        }

        /**
         * This method returns a parameter by index.
         *
         * @param int $index
         * @return int|bool
         */
        public function getParameterFromIndex($index)
        {
            if (isset($this->_indexToParameter[$index])) {
                return $this->_indexToParameter[$index];
            } else {
                return false;
            }
        }

        /**
         *
         */
        public function resetFields()
        {
            $this->_fields = array();
            $file = $this->getAbsoluteFilename();
            $this->setField('name', basename(dirname($file)));
            $this->setField('relative_path', dirname($file));
            $this->setField('full_path', dirname($file));
        }

        /**
         *
         */
        public function __construct()
        {

            if (!self::_isConstructed()) {

                parent::__construct();
                self::$_aomebo =
                    \Aomebo::getInstance();
                self::_flagThisConstructed();

            }

            $isPageOrShellRequest =
                \Aomebo\Dispatcher\System::isPageRequest()
                || \Aomebo\Dispatcher\System::isShellRequest();

            $this->setExecuting(false);
            $this->setEnabled(true);
            $this->resetFields();

            if ($this->isEnabled()) {

                if ($isPageOrShellRequest) {

                    /**
                     * Routable interface.
                     *
                     * @see \Aomebo\Runtime\Routable
                     */
                    if ($this->isRoutable()) {

                        /** @var \Aomebo\Runtime\Routable $ref */
                        $ref = & $this;

                        if ($routes = $ref->getRoutes()) {
                            $this->loadRoutes($routes);
                        }

                    // TODO: This below is deprecated and will be removed in next major version.
                    } else if (isset($this->_routes)
                        && is_array($this->_routes)
                        && sizeof($this->_routes) > 0
                    ) {
                        foreach ($this->_routes as $route)
                        {
                            if (isset($route)
                                && is_array($route)
                                && isset($route['regexp'],
                                $route['sprintf'],
                                $route['keys'])
                            ) {
                                $routeObject = new \Aomebo\Dispatcher\Route(
                                    (!empty($route['name']) ? $route['name'] : null),
                                    $route['regexp'],
                                    $route['sprintf'],
                                    $route['keys'],
                                    (!empty($route['method']) ? $route['method'] : null));
                                if ($routeObject->isValid()) {
                                    \Aomebo\Dispatcher\System::addRoute($routeObject);
                                }
                            }
                        }
                    }
                                       
                }

                if ($this->isExecutionParameters()) {

                    /** @var \Aomebo\Runtime\ExecutionParameters $ref */
                    $ref = & $this;

                    if ($parameters = $ref->getParameters()) {
                        if (is_array($parameters)
                            && sizeof($parameters) > 0
                        ) {
                            foreach ($parameters as $parameterIndex => $parameterKey)
                            {
                                $this->_parameterToIndex[$parameterKey] =
                                    $parameterIndex;
                                $this->_indexToParameter[$parameterIndex] =
                                    $parameterKey;
                            }
                        }
                    }

                // TODO: This below is deprecated and will be removed in next major version.
                } else if (isset($this->_parameters)
                    && is_array($this->_parameters)
                    && sizeof($this->_parameters) > 0
                ) {
                    $sizeof =
                        sizeof($this->_parameters);
                    for ($i = 0; $i < $sizeof; $i++)
                    {
                        $parameter = $this->_parameters[$i];
                        $this->_parameterToIndex[$parameter] = $i;
                        $this->_indexToParameter[$i] = $parameter;
                    }
                }

            }
            
            $this->load();

        }

        /**
         * This method loads the runtime, it is separated from __construct() because
         * this parts runs on construct and also after a unserialize of the runtime.
         *
         * @throws \Exception
         */
        public function load()
        {
            
            $isPageOrShellRequest =
                \Aomebo\Dispatcher\System::isPageRequest()
                || \Aomebo\Dispatcher\System::isShellRequest();

            $autoInstallSpecificRuntimes = \Aomebo\Configuration::getSetting(
                'application,auto-install specific runtimes');

            /**
             * Installable interface.
             *
             * Only on page or shell requests, if auto-install for all is enabled
             * or auto-install for this module specifically.
             *
             * @see \Aomebo\Runtime\Installable
             */

            \Aomebo\Trigger\System::addTrigger(
                \Aomebo\Trigger\System::TRIGGER_KEY_SYSTEM_AUTOINSTALL,
                array($this, 'autoInstall')
            );

            \Aomebo\Trigger\System::addTrigger(
                \Aomebo\Trigger\System::TRIGGER_KEY_SYSTEM_AUTOUNINSTALL,
                array($this, 'autoUninstall')
            );

            \Aomebo\Trigger\System::addTrigger(
                \Aomebo\Trigger\System::TRIGGER_KEY_SYSTEM_AUTOUPDATE,
                array($this, 'autoUpdate')
            );

            if ($isPageOrShellRequest
                && $this->isInstallable()
                && (\Aomebo\Configuration::getSetting(
                        'application,auto-install all runtimes')
                    || !empty($autoInstallSpecificRuntimes[$this->getField('name')])
                )
            ) {
                $this->autoInstall();
            }

            if ($this->isEnabled()) {

                if ($isPageOrShellRequest) {

                    /**
                     * Internationalized interface
                     *
                     * @see \Aomebo\Runtime\Internationalized
                     */
                    if ($this->isInternationalized()) {

                        /** @var \Aomebo\Runtime\Internationalized $ref */
                        $ref = & $this;

                        if ($textDomains = $ref->getTextDomains()) {
                            \Aomebo\Internationalization\System::
                                addTextDomains($textDomains);
                        }

                    }

                    /**
                     * Pageable interface
                     *
                     * @see \Aomebo\Runtime\Pageable
                     */
                    if ($this->isPageable()) {

                        /** @var \Aomebo\Runtime\Pageable $ref */
                        $ref = & $this;

                        if ($pages = $ref->getPages()) {

                            // Add interpreter page dynamically
                            \Aomebo\Interpreter\Engine::addPages($pages);

                        }

                        if ($pagesToUris = $ref->getPagesToUri()) {

                            // Add uri for page dynamically
                            \Aomebo\Dispatcher\System::addPagesToUris(
                                $pagesToUris
                            );

                        }

                        if ($urisToPages = $ref->getUriToPages()) {

                            // Add page for uri dynamically
                            \Aomebo\Dispatcher\System::addUrisToPages(
                                $urisToPages
                            );

                        }

                    }
                    
                }
                
            }

            if ($this->isInitializable()) {

                /** @var \Aomebo\Runtime\Initializable $ref */
                $ref = & $this;
                $ref->initialize();

            }
            
        }

        /**
         * 
         */
        public function autoInstall()
        {

            if ($this->isInstallable()) {
            
                /** @var \Aomebo\Runtime\Installable $ref */
                $ref = & $this;
    
                if (!$ref->isInstalled()) {
                    $ref->install();
                    if (!$ref->isInstalled()) {
                        $this->setEnabled(false);
                    }
                }
                
            }

        }

        /**
         *
         */
        public function autoUninstall()
        {

            if ($this->isUninstallable()) {

                /** @var \Aomebo\Runtime\Uninstallable $ref */
                $ref = & $this;

                if (!$ref->isUninstalled()) {
                    $ref->uninstall();
                }

            }

        }

        /**
         *
         */
        public function autoUpdate()
        {

            if ($this->isUpdatable()) {

                /** @var \Aomebo\Runtime\Updatable $ref */
                $ref = & $this;

                if (!$ref->isUpdated()) {
                    $ref->update();
                }

            }

        }


        /**
         * @internal
         * @return string
         */
        public function __toString()
        {
            if (isset($this->_fields)) {
                if (isset($this->_fields['output'])) {
                    return $this->_fields['output'];
                }
            }
            return '';
        }

        /**
         * @static
         * @param string|int|bool $string
         * @param bool [$toLowerCase = true]
         * @param string [$replaceWith = '-']
         * @return string
         */
        protected static function _formatUriComponent(
            $string,
            $toLowerCase = true,
            $replaceWith = '-')
        {
            return \Aomebo\Dispatcher\System::formatUriComponent(
                $string, $toLowerCase, $replaceWith);
        }

        /**
         * @static
         * @param array|null [$getArray = null]
         * @param string|null [$page = null]
         * @param bool [$clear = false]
         * @return string
         */
        protected static function _buildUri($getArray = null,
            $page = null, $clear = true)
        {
            return \Aomebo\Dispatcher\System::buildUri(
                $getArray, $page, $clear);
        }

        /**
         * @static
         * @param array|null [$getArray = null]
         * @param string|null [$page = null]
         * @param bool [$clear = true]
         * @return string
         */
        protected static function _buildFullUri($getArray = null,
            $page = null, $clear = true)
        {
            return \Aomebo\Dispatcher\System::buildFullUri(
                $getArray, $page, $clear);
        }

        /**
         * @static
         * @return string
         */
        protected static function _buildDefaultUri()
        {
            return \Aomebo\Dispatcher\System::buildDefaultUri();
        }

        /**
         * @static
         * @return string
         */
        protected static function _buildDefaultFullUri()
        {
            return \Aomebo\Dispatcher\System::buildDefaultFullUri();
        }

        /**
         * @static
         * @param bool [$newInstance = false]
         * @return Template\Adapters\Smarty\Adapter
         */
        protected static function _getSmartyView($newInstance = false)
        {
            return (!empty($newInstance) ?
                new \Aomebo\Template\Adapters\Smarty\Adapter()
                : \Aomebo\Template\Adapters\Smarty\Adapter::getInstance()
            );
        }

        /**
         * @static
         * @param bool [$newInstance = false]
         * @return Template\Adapters\Twig\Adapter
         */
        protected static function _getTwigView($newInstance = false)
        {
            return (!empty($newInstance) ?
                new \Aomebo\Template\Adapters\Twig\Adapter()
                : \Aomebo\Template\Adapters\Twig\Adapter::getInstance()
            );
        }

        /**
         * @static
         * @param bool [$newInstance = false]
         * @return Template\Adapters\Php\Adapter
         */
        protected static function _getPhpView($newInstance = false)
        {
            return (!empty($newInstance) ?
                new \Aomebo\Template\Adapters\Php\Adapter()
                : \Aomebo\Template\Adapters\Php\Adapter::getInstance()
            );
        }

        /**
         * @static
         * @param string $key
         * @param mixed|null [$default = null]
         * @return mixed
         */
        protected static function _getPostData($key, $default = null)
        {
            return self::_getArrayData($_POST, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed [$default = null]
         * @return mixed
         */
        protected static function _getPostLiterals($key, $default = null)
        {
            return self::_getArrayLiterals($_POST, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed [$default = null]
         * @return int|null
         */
        protected function _getPostInteger($key, $default = null)
        {
            return self::_getArrayInteger($_POST, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed|null [$default = null]
         * @return bool
         */
        protected static function _getPostBoolean($key, $default = null)
        {
            return self::_getArrayBoolean($_POST, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed|null [$default = null]
         * @return mixed
         */
        protected static function _getGetData($key, $default = null)
        {
            return self::_getArrayData($_GET, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed [$default = null]
         * @return string
         */
        protected static function _getGetLiterals($key, $default = null)
        {
            return self::_getArrayLiterals($_GET, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed [$default = null]
         * @return int|null
         */
        protected static function _getGetInteger($key, $default = null)
        {
            return self::_getArrayInteger($_GET, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed|null [$default = null]
         * @return bool
         */
        protected static function _getGetBoolean($key, $default = null)
        {
            return self::_getArrayBoolean($_GET, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed|null [$default = null]
         * @return mixed
         */
        protected static function _getServerData($key, $default = null)
        {
            return self::_getArrayData($_SERVER, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed [$default = null]
         * @return string
         */
        protected static function _getServerLiterals($key, $default = null)
        {
            return self::_getArrayLiterals($_SERVER, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed [$default = null]
         * @return int|null
         */
        protected static function _getServerInteger($key, $default = null)
        {
            return self::_getArrayInteger($_SERVER, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed|null [$default = null]
         * @return bool
         */
        protected static function _getServerBoolean($key, $default = null)
        {
            return self::_getArrayBoolean($_SERVER, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed|null [$default = null]
         * @return mixed
         */
        protected static function _getCookieData($key, $default = null)
        {
            return self::_getArrayData($_COOKIE, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed [$default = null]
         * @return string
         */
        protected static function _getCookieLiterals($key, $default = null)
        {
            return self::_getArrayLiterals($_COOKIE, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed [$default = null]
         * @return int|null
         */
        protected static function _getCookieInteger($key, $default = null)
        {
            return self::_getArrayInteger($_COOKIE, $key, $default);
        }

        /**
         * @static
         * @param string $key
         * @param mixed|null [$default = null]
         * @return bool
         */
        protected static function _getCookieBoolean($key, $default = null)
        {
            return self::_getArrayBoolean($_COOKIE, $key, $default);
        }

        /**
         * @static
         * @param array $array
         * @param string $key
         * @param mixed|null [$default = null]
         * @return mixed|null
         */
        protected static function _getArrayData(& $array, $key, $default = null)
        {
            if (isset($array)
                && !empty($key)
                && is_array($array)
                && sizeof($array) > 0
                && isset($array[$key])
            ) {
                return $array[$key];
            }
            return $default;
        }

        /**
         * @static
         * @param array $array
         * @param string $key
         * @param mixed [$default = null]
         * @return mixed|null
         */
        protected static function _getArrayLiterals(& $array, $key, $default = null)
        {
            if (isset($array)
                && !empty($key)
                && is_array($array)
                && sizeof($array) > 0
                && isset($array[$key])
                && $array[$key] != ''
            ) {
                return (string) $array[$key];
            }
            return $default;
        }

        /**
         * @static
         * @param array $array
         * @param string $key
         * @param mixed [$default = null]
         * @return int|null
         */
        protected function _getArrayInteger(& $array, $key, $default = null)
        {
            if (isset($array)
                && !empty($key)
                && is_array($array)
                && sizeof($array) > 0
                && isset($array[$key])
                && preg_match(
                    '/^[\d]+$/',
                    $array[$key],
                    $matches) === 1
            ) {
                return (int) $array[$key];
            }
            return $default;
        }

        /**
         * @static
         * @param array $array
         * @param string $key
         * @param mixed|null [$default = null]
         * @return bool
         */
        protected static function _getArrayBoolean(& $array, $key, $default = null)
        {
            if (isset($array)
                && !empty($key)
                && is_array($array)
                && sizeof($array) > 0
                && isset($array[$key])
            ) {
                return ($array[$key] == true);
            }
            return $default;
        }

        /**
         * This method executes module and saves optional
         * output into the "output" field if not specified.
         *
         * @internal
         * @throws \Exception
         */
        private function _doExecute()
        {
            if ($this->isExecutable()
                && !$this->isExecuting()
            ) {

                $this->setExecuting(true);
                $this->clearCollectedSets();

                /** @var \Aomebo\Runtime\Executable $ref */
                $ref = & $this;

                try
                {

                    // Do we have a matching route?
                    if (isset($this->_executeRoute)) {

                        /** @var \Aomebo\Dispatcher\Route $route */
                        $route = & $this->_executeRoute;
                        $output = $route->execute();

                    } else {
                        $output = $ref->execute();
                    }

                } catch (\Exception $e) {

                    $this->setField('error', $e->getMessage());

                    $displayRuntimeExceptions =
                        \Aomebo\Configuration::getSetting(
                            'feedback,display runtime exceptions');
                    $logRuntimeExceptions =
                        \Aomebo\Configuration::getSetting(
                            'feedback,log runtime exceptions');

                    self::$_aomebo->Feedback()->Debug()->output(
                        'Aomebo Runtime: { '
                        . 'name: "' . $this->getField('name') . '", '
                        . 'file: "' . $this->getAbsoluteFilename() . '", '
                        . 'error-file: "'. $e->getFile() . '",'
                        . 'error-line: "' . $e->getLine() . '",'
                        . 'error-message: "' . $e->getMessage() . '"',
                        $displayRuntimeExceptions,
                        $logRuntimeExceptions);

                    if (\Aomebo\Configuration::getSetting(
                        'feedback,halt on runtime exceptions')
                    ) {
                        Throw new \Exception($e->getMessage());
                    }

                }

                if (!empty($output)) {
                    $this->setField('output', $output);
                }

                $this->setExecuting(false);

            }
        }

    }
}
