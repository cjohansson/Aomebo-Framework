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
     * This class first loads structure internally and externally, first from
     * PHP sources, then from YML sources. After that it loads configuration internally
     * and externally, first from PHP sources and then from YML sources. It also validates
     * the configuration with the structure and uses filesystem cache to speed up this loading.
     *
     * @method static \Aomebo\Configuration getInstance()
     */
    class Configuration extends Singleton
    {

        /**
         * @var string
         */
        const CONFIG_FILENAME = 'configuration';

        /**
         * @var string
         */
        const STRUCTURE_FILENAME = 'structure';

        /**
         * @var string
         */
        const STRUCTURE_KEY_TYPE = 'type';

        /**
         * @var string
         */
        const STRUCTURE_KEY_REQUIRED = 'required';

        /**
         * @var string
         */
        const STRUCTURE_KEY_DEFAULT = 'default';

        /**
         * @var string
         */
        const STRUCTURE_TYPE_BOOLEAN = 'boolean';

        /**
         * @var string
         */
        const STRUCTURE_TYPE_STRING = 'string';

        /**
         * @var string
         */
        const STRUCTURE_TYPE_INTEGER = 'integer';

        /**
         * @var string
         */
        const STRUCTURE_TYPE_INTEGER_UNSIGNED = 'unsigned integer';

        /**
         * @var string
         */
        const STRUCTURE_TYPE_ARRAY = 'array';

        /**
         * @var string
         */
        const STRUCTURE_TYPE_ARRAY_ASSOCIATIVE = 'associative array';

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_structure;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_structureKeys;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_configuration;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_settings;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_internalConfigurationFilename;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_externalConfigurationFilename;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_internalStructureFilename;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_externalStructureFilename;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_isLoaded = false;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_spycLoaded = false;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_hasSiteConfiguration = false;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_keyToValueCache = array();

        /**
         *
         */
        public function __construct()
        {
            parent::__construct();
            if (!$this->_isConstructed()) {
                $this->_flagThisConstructed();
            }
        }

        /**
         * @static
         * @return bool
         */
        public static function hasSiteConfiguration()
        {
            return self::$_hasSiteConfiguration;
        }

        /**
         * @static
         * @return bool
         */
        public static function isLoaded()
        {
            return self::$_isLoaded;
        }

        /**
         * This method merges array on a multidimension level.
         *
         * @internal
         * @static
         * @param array $original
         * @param array $new
         */
        public static function multiDimensionalArrayMerge(& $original, $new)
        {
            if (is_array($original)
                && is_array($new)
            ) {
                foreach ($new as $key => & $value)
                {
                    if (is_array($value)) {
                        if (isset($original[$key])) {
                            if (is_array($original[$key])) {
                                self::multiDimensionalArrayMerge(
                                    $original[$key],
                                    $new[$key]);
                            } else {
                                $original[$key] =
                                    $new[$key];
                            }
                        } else {
                            $original[$key] =
                                $new[$key];
                        }
                    } else {
                        $original[$key] = $new;
                    }
                }
            }
        }

        /**
         * This method tries to load the configuration.
         *
         * @static
         * @param string [$internalConfigurationFilename = '']
         * @param string [$externalConfigurationFilename = '']
         * @param string [$internalStructureFilename = '']
         * @param string [$externalStructureFilename = '']
         * @param string|null [$configurationAdapter = null]
         * @throws \Exception
         * @return bool
         */
        public static function load(
            $internalConfigurationFilename = '',
            $externalConfigurationFilename = '',
            $internalStructureFilename = '',
            $externalStructureFilename = '')
        {

            $cacheString = '';

            // Use alternate internal config-file if requested
            if (!empty($internalConfigurationFilename)) {
                self::$_internalConfigurationFilename = $internalConfigurationFilename;
            } else {
                self::$_internalConfigurationFilename = self::CONFIG_FILENAME;
            }

            // Build absolute path
            $internalConfigurationFilename =
                _SYSTEM_ROOT_ . self::$_internalConfigurationFilename;

            if (file_exists($internalConfigurationFilename . '.php')
                && strtolower($internalConfigurationFilename . '.php') !==
                strtolower(__FILE__)
            ) {
                $cacheString .=
                    '&ICTime=' . filemtime($internalConfigurationFilename . '.php')
                    . '&ICSize=' . filesize($internalConfigurationFilename . '.php');

            } else if (file_exists($internalConfigurationFilename . '.yml')) {
                $cacheString .=
                    '&ICTime=' . filemtime($internalConfigurationFilename . '.yml')
                    . '&ICSize=' . filesize($internalConfigurationFilename . '.yml');

            }

            // Use alternate external config-file if requested
            if (!empty($externalConfigurationFilename)) {
                self::$_externalConfigurationFilename = $externalConfigurationFilename;
            } else {
                self::$_externalConfigurationFilename = self::CONFIG_FILENAME;
            }

            // Build absolute path
            $externalConfigurationFilename =
                _SITE_ROOT_ . self::$_externalConfigurationFilename;

            if (file_exists($externalConfigurationFilename . '.php')
                && strtolower($externalConfigurationFilename . '.php') !==
                strtolower(__FILE__)
            ) {
                self::$_hasSiteConfiguration = true;
                $cacheString .=
                    '&ECTime=' . filemtime($externalConfigurationFilename . '.php')
                    . '&ECSize=' . filesize($externalConfigurationFilename . '.php');

            } else if (file_exists($externalConfigurationFilename . '.yml')) {
                self::$_hasSiteConfiguration = true;
                $cacheString .=
                    '&ECTime=' . filemtime($externalConfigurationFilename . '.yml')
                    . '&ECSize=' . filesize($externalConfigurationFilename . '.yml');

            }

            // Use alternate internal structure-file if requested
            if (!empty($internalStructureFilename)) {
                self::$_internalStructureFilename = $internalStructureFilename;
            } else {
                self::$_internalStructureFilename = self::STRUCTURE_FILENAME;
            }

            // Build absolute path
            $internalStructureFilename =
                _SYSTEM_ROOT_ . self::$_internalStructureFilename;

            if (file_exists($internalStructureFilename . '.php')) {
                $cacheString .=
                    '&ISTime=' . filemtime($internalStructureFilename . '.php')
                    . '&ISSize=' . filesize($internalStructureFilename . '.php');

            } else if (file_exists($internalStructureFilename . '.yml')) {
                $cacheString .=
                    '&ISTime=' . filemtime($internalStructureFilename . '.yml')
                    . '&ISSize=' . filesize($internalStructureFilename . '.yml');

            }

            // Use alternate external structure-file if requested
            if (!empty($externalStructureFilename)) {
                self::$_externalStructureFilename = $externalStructureFilename;
            } else {
                self::$_externalStructureFilename = self::STRUCTURE_FILENAME;
            }

            // Build absolute path
            $externalStructureFilename =
                _SITE_ROOT_ . self::$_externalStructureFilename;

            if (file_exists($externalStructureFilename . '.php')) {
                $cacheString .=
                    '&ESTime=' . filemtime($externalStructureFilename . '.php')
                    . '&ESSize=' . filesize($externalStructureFilename . '.php');

            } else if (file_exists($externalStructureFilename . '.yml')) {
                $cacheString .=
                    '&ESTime=' . filemtime($externalStructureFilename . '.yml')
                    . '&ESSize=' . filesize($externalStructureFilename . '.yml');

            }

            /**
             * Cache parameters, static.
             */
            $cacheParameters = 'Configuration';

            /**
             * Cache key, unique per:
             * - Filemtime of configuration files
             */
            $cacheKey = md5($cacheString);

            if (\Aomebo\Cache\System::cacheExists(
                $cacheParameters,
                $cacheKey,
                \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM)
            ) {
                if ($cacheData = \Aomebo\Cache\System::loadCache(
                    $cacheParameters,
                    $cacheKey,
                    \Aomebo\Cache\System::FORMAT_JSON_ENCODE)
                ) {

                    if (isset($cacheData['configuration'],
                            $cacheData['structure'],
                            $cacheData['settings'])
                        && is_array($cacheData['configuration'])
                        && is_array($cacheData['structure'])
                        && is_array($cacheData['settings'])
                        && count($cacheData['structure']) > 0
                        && count($cacheData['settings']) > 0
                    ) {
                        self::$_configuration = $cacheData['configuration'];
                        self::$_structure = $cacheData['structure'];
                        self::$_settings = $cacheData['settings'];
                        self::$_isLoaded = true;
                        return true;

                    } else {
                        \Aomebo\Cache\System::clearCache(
                            $cacheParameters,
                            null,
                            \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                        );
                        Throw new \Exception(
                            self::systemTranslate(
                                'Invalid configuration cache, cleared caches.')
                        );
                    }

                } else {
                    \Aomebo\Cache\System::clearCache(
                        $cacheParameters,
                        null,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    );
                    Throw new \Exception(
                        self::systemTranslate(
                            'Invalid configuration cache, cleared caches.')
                    );
                }

            } else {
                \Aomebo\Cache\System::clearCache(
                    $cacheParameters,
                    null,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                );
                \Aomebo\Associatives\Parser::cleanAssociativesCache();
                \Aomebo\Associatives\Parser::cleanDependenciesCache();

                self::$_structure = array();
                self::$_configuration = array();
                $internalStructure = array();
                $convertQueue = array();

                // Does internal structure-file exists?
                if (file_exists($internalStructureFilename . '.php')) {
                    $internalStructure = self::loadPhpConfiguration(
                        $internalStructureFilename . '.php'
                    );

                } else if (file_exists($internalStructureFilename . '.yml')) {
                    $internalStructure = self::loadYmlConfiguration(
                        $internalStructureFilename . '.yml'
                    );
                    // Convert YML to PHP
                    $convertQueue[] = array(
                        $internalStructureFilename . '.php',
                        $internalStructure
                    );
                }

                if (count($internalStructure) > 0) {
                    self::multiDimensionalArrayMerge(
                        self::$_structure,
                        $internalStructure
                    );
                }

                $internalConfiguration = array();

                // Does internal configuration-file exists?
                if (file_exists($internalConfigurationFilename . '.php')
                    && strtolower($internalConfigurationFilename . '.php') !==
                    strtolower(__FILE__)
                ) {
                    $internalConfiguration = self::loadPhpConfiguration(
                        $internalConfigurationFilename . '.php'
                    );

                } else if (file_exists($internalConfigurationFilename . '.yml')) {
                    $internalConfiguration = self::loadYmlConfiguration(
                        $internalConfigurationFilename . '.yml'
                    );
                    // Convert YML to PHP
                    $convertQueue[] = array(
                        $internalConfigurationFilename . '.php',
                        $internalConfiguration
                    );
                }

                if (count($internalConfiguration) > 0) {
                    self::multiDimensionalArrayMerge(
                        self::$_configuration,
                        $internalConfiguration
                    );
                }

                $externalStructure = array();

                // Does external structure-file exists?
                if (file_exists($externalStructureFilename . '.php')) {
                    $externalStructure = self::loadPhpConfiguration(
                        $externalStructureFilename . '.php'
                    );

                } else if (file_exists($externalStructureFilename . '.yml')) {
                    $externalStructure = self::loadYmlConfiguration(
                        $externalStructureFilename . '.yml'
                    );
                    // Convert YML to PHP
                    $convertQueue[] = array(
                        $externalStructureFilename . '.php',
                        $externalStructure
                    );
                }

                if (count($externalStructure) > 0) {
                    self::multiDimensionalArrayMerge(
                        self::$_structure,
                        $externalStructure
                    );
                }

                $externalConfiguration = array();

                // Does external config-file exists and is not this file?
                if (file_exists($externalConfigurationFilename . '.php')
                    && strtolower($externalConfigurationFilename . '.php') !==
                    strtolower(__FILE__)
                ) {
                    $externalConfiguration = self::loadPhpConfiguration(
                        $externalConfigurationFilename . '.php'
                    );

                } else if (file_exists($externalConfigurationFilename . '.yml')) {
                    $externalConfiguration = self::loadYmlConfiguration(
                        $externalConfigurationFilename . '.yml'
                    );
                    // Convert YML to PHP
                    $convertQueue[] = array(
                        $externalConfigurationFilename . '.php',
                        $externalConfiguration
                    );
                }

                if (count($externalConfiguration) > 0) {
                    self::multiDimensionalArrayMerge(
                        self::$_configuration,
                        $externalConfiguration
                    );
                }

                // Does configuration validate?
                if (self::_validate()) {

                    $cacheData = array(
                        'configuration' => & self::$_configuration,
                        'structure' => & self::$_structure,
                        'settings' => & self::$_settings,
                    );

                    self::$_isLoaded = true;

                    \Aomebo\Cache\System::saveCache(
                        $cacheParameters,
                        $cacheKey,
                        $cacheData,
                        \Aomebo\Cache\System::FORMAT_JSON_ENCODE,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    );

                    // Are any data in convert-queue?
                    if (\Aomebo\Application::isWritingnabled()
                        && count($convertQueue) > 0
                    ) {
                        foreach ($convertQueue as $convertQueueItem)
                        {
                            if (isset($convertQueueItem[0],
                                $convertQueueItem[1])
                            ) {
                                self::savePhpConfigurationFile(
                                    $convertQueueItem[0],
                                    $convertQueueItem[1]
                                );
                            }
                        }
                    }
                    return true;
                }
            }

            return false;
        }

        /**
         * This method returns whether a setting exists of not.
         *
         * @static
         * @param string $key
         * @throws \Exception
         * @return bool
         */
        public static function hasSetting($key)
        {
            if (isset($key)) {
                $exp = explode(',', $key);
                $d = & self::$_settings;
                if (is_array($exp)
                    && count($exp) > 0
                ) {
                    foreach ($exp as $e)
                    {
                        if (!isset($d[$e])) {
                            return false;
                        } else {
                            $d = & $d[$e];
                        }
                    }
                    return true;
                } else {
                    if (isset($d[$key])) {
                        return true;
                    }
                }
            } else {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate('Invalid parameters "%s"'),
                        print_r(func_get_args(), true)
                    )
                );
            }
            return false;
        }

        /**
         * This method retreives setting from configuration.
         *
         * @static
         * @param string $key
         * @param bool [$throwException = true]
         * @throws \Exception
         * @return mixed
         */
        public static function getSetting($key, $throwException = true)
        {
            if (isset($key)) {
                if (!isset(self::$_keyToValueCache[$key])) {

                    self::$_keyToValueCache[$key] = false;

                    $exp = explode(',', $key);
                    $d = & self::$_settings;

                    if (is_array($exp)
                        && count($exp) > 0
                    ) {

                        $found = true;

                        foreach ($exp as $e)
                        {
                            if (!isset($d[$e])) {
                                if ($throwException) {
                                    Throw new \Exception(
                                        sprintf(
                                            self::systemTranslate(
                                                'Setting-value for key: "%s" not found.'),
                                            $key
                                        )
                                    );
                                } else {
                                    $found = false;
                                    break;
                                }
                            } else {
                                $d = & $d[$e];
                            }
                        }

                        if ($found) {
                            self::$_keyToValueCache[$key] = $d;
                        }

                    } else {
                        if (isset($d[$key])) {
                            self::$_keyToValueCache[$key] = $d[$key];
                        }
                    }

                }

                return self::$_keyToValueCache[$key];

            } else {
                if ($throwException) {
                    Throw new \Exception(
                        sprintf(
                            self::systemTranslate('Invalid parameters "%s"'),
                            print_r(func_get_args(), true)
                        )
                    );
                }
            }
            return null;
        }

        /**
         * This method saves settings to configuration.
         *
         * @static
         * @param array $settings
         * @throws \Exception
         * @return boolean
         */
        public static function saveSettings($settings)
        {
            if (isset($settings)
                && is_array($settings)
                && count($settings) > 0
            ) {

                $failedKeys = array();

                // Iterate through settings to set
                $allOk = true;

                foreach ($settings as $key => $value)
                {

                    if (!self::_saveSetting($key, $value)) {
                        $failedKeys[] = $key;
                        $allOk = false;
                    }

                }

                // Did all go well?
                if ($allOk) {
                    if (self::_flushSettings()) {
                        return true;
                    }
                }

            } else {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate('Invalid parameters "%s"'),
                        print_r(func_get_args(), true)
                    )
                );
            }

            if (count($failedKeys) > 0) {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate(
                            'Failed to save settings. Failed with saving keys: "%s"'),
                            implode(',', $failedKeys) . '"'
                    )
                );
            } else {
                Throw new \Exception(self::systemTranslate('Failed to save settings.'));
            }

        }

        /**
         * This method save a single settings key and value.
         *
         * @static
         * @param $key
         * @param $value
         * @return bool
         */
        public static function saveSetting($key, $value)
        {
            if (self::_saveSetting($key, $value)) {
                if (self::_flushSettings()) {
                    return true;
                }
            }
            return false;
        }

        /**
         * This method tries to set a value.
         *
         * @internal
         * @static
         * @param $key
         * @param $value
         * @throws \Exception
         * @return bool
         */
        private static function _saveSetting($key, $value)
        {
            if (isset($key, $value)) {

                $exp = explode(',', $key);
                $d = & self::$_settings;

                if (is_array($exp)
                    && count($exp) > 0
                ) {
                    foreach ($exp as $e)
                    {
                        if (!isset($d[$e])) {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate(
                                        'Setting-value for key: "%s" not found.'),
                                        $key
                                )
                            );
                        } else {
                            $d = & $d[$e];
                        }
                    }

                    self::$_keyToValueCache[$key] = $value;
                    $d = $value;
                    return true;

                } else {
                    if (isset($d[$key])) {
                        $d[$key] = $value;
                        self::$_keyToValueCache[$key] = $value;
                        return true;
                    } else {
                        Throw new \Exception(
                            sprintf(
                                self::systemTranslate(
                                    'Setting-value for key: "%s" not found.'),
                                $key
                            )
                        );
                    }
                }
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
            }
        }

        /**
         * Flush settings to filesystem.
         *
         * @internal
         * @static
         * @throws \Exception
         * @return bool
         */
        private static function _flushSettings()
        {
            return self::_savePhpConfiguration();
        }

        /**
         * This method will validate configuration based on structure.
         *
         * @internal
         * @static
         * @throws \Exception
         * @return bool
         */
        private static function _validate()
        {
            if (self::_validateStructure()) {
                if (self::_validateConfiguration()) {
                    date_default_timezone_set(
                        self::getSetting('site,default time-zone'));
                    ini_set('default_socket_timeout',
                            self::getSetting('application,socket timeout'));
                    return true;
                }
            }
            return false;
        }

        /**
         * This method validates structure syntax.
         *
         * @internal
         * @static
         * @return bool
         */
        private static function _validateStructure()
        {
            if (isset(self::$_structure)
                && is_array(self::$_structure)
                && count(self::$_structure) > 0
            ) {
                self::$_structureKeys = array(
                    self::STRUCTURE_TYPE_ARRAY => true,
                    self::STRUCTURE_TYPE_ARRAY_ASSOCIATIVE => true,
                    self::STRUCTURE_TYPE_BOOLEAN => true,
                    self::STRUCTURE_TYPE_INTEGER => true,
                    self::STRUCTURE_TYPE_INTEGER_UNSIGNED => true,
                    self::STRUCTURE_TYPE_STRING => true,
                );
                return self::_validateStructureRec(self::$_structure);
            }
            return false;
        }

        /**
         * This method validates structure of node recursive.
         *
         * @internal
         * @static
         * @param array $node
         * @throws \Exception
         * @return bool
         */
        private static function _validateStructureRec(& $node)
        {
            if (isset($node)
                && is_array($node)
                && count($node) > 0
            ) {
                $status = true;
                foreach ($node as $key => $value) {
                    if (is_array($value)) {
                        if (!isset($value[self::STRUCTURE_KEY_TYPE])
                            && !isset($value[self::STRUCTURE_KEY_REQUIRED])
                            && !isset($value[self::STRUCTURE_KEY_DEFAULT])
                        ) {
                            $status = $status
                                && self::_validateStructureRec($value);
                        } else {
                            $defStatus = true;
                            if (!isset($value[self::STRUCTURE_KEY_TYPE])
                                || is_array($value[self::STRUCTURE_KEY_TYPE])
                                || !isset(self::$_structureKeys[$value[self::STRUCTURE_KEY_TYPE]])
                            ) {
                                Throw new \Exception(
                                    sprintf(
                                        self::systemTranslate('Configuration value for "%s.%s" has invalid value "%s". Valid values are "%s" in structure node "%s".'),
                                        $key,
                                        self::STRUCTURE_KEY_TYPE,
                                        (isset($value[self::STRUCTURE_KEY_TYPE]) ? $value[self::STRUCTURE_KEY_TYPE] : 'null'),
                                        implode(',', array_keys(self::$_structureKeys)),
                                        print_r($node, true)
                                    ));
                            }
                            if (!isset($value[self::STRUCTURE_KEY_REQUIRED])
                                || ($value[self::STRUCTURE_KEY_REQUIRED] !== true
                                && $value[self::STRUCTURE_KEY_REQUIRED] !== false)
                            ) {
                                Throw new \Exception(
                                    sprintf(
                                        self::systemTranslate(
                                            'Configuration value for "%s" '
                                            . 'has invalid value "%s" '
                                            . 'in structure node: "%s"'),
                                        $key . '.' . self::STRUCTURE_KEY_REQUIRED,
                                        $value[self::STRUCTURE_KEY_REQUIRED],
                                        print_r($node, true) . '"'
                                    )
                                );      
                            }
                            $status = ($status && $defStatus);
                        }
                    }
                }
                return $status;
            }
            return false;
        }

        /**
         * This method validates configuration according to structure.
         *
         * @internal
         * @static
         * @return bool
         */
        private static function _validateConfiguration()
        {
            self::$_settings = array();
            if (isset(self::$_configuration)
                && is_array(self::$_configuration)
                && isset(self::$_structure)
                && is_array(self::$_structure)
                && count(self::$_structure) > 0
            ) {
                return self::_validateConfigurationRec(
                    self::$_structure,
                    self::$_configuration,
                    self::$_settings);
            }
            return false;
        }

        /**
         * This method performs validation of configuration node
         * according to structure recursively.
         *
         * @internal
         * @static
         * @param array $structureNode
         * @param array|null [$configurationNode = null]
         * @param array $settingsNode
         * @throws \Exception
         * @return bool
         */
        private static function _validateConfigurationRec($structureNode,
            $configurationNode = null, & $settingsNode)
        {
            if (isset($structureNode)
                && is_array($structureNode)
                && count($structureNode) > 0
            ) {
                $status = true;
                foreach ($structureNode as $structureKey => $structureValue)
                {
                    if (is_array($structureValue)) {
                        if (!isset($structureValue[self::STRUCTURE_KEY_TYPE])
                            && !isset($structureValue[self::STRUCTURE_KEY_REQUIRED])
                            && !isset($structureValue[self::STRUCTURE_KEY_DEFAULT])
                        ) {
                            $settingsNode[$structureKey] = array();
                            if (isset($configurationNode[$structureKey])) {
                                $status = $status
                                    && self::_validateConfigurationRec(
                                        $structureValue,
                                        $configurationNode[$structureKey],
                                        $settingsNode[$structureKey]);
                            } else {
                                $status = $status
                                    && self::_validateConfigurationRec(
                                        $structureValue,
                                        null,
                                        $settingsNode[$structureKey]);
                            }
                        } else {
                            if (isset($configurationNode[$structureKey])
                                && self::_isValidValueAccordingToType(
                                    $configurationNode[$structureKey],
                                    $structureValue[self::STRUCTURE_KEY_TYPE])
                            ) {
                                $settingsNode[$structureKey] =
                                    $configurationNode[$structureKey];
                            } else {
                                if (isset($structureValue[self::STRUCTURE_KEY_DEFAULT])
                                    && self::_isValidValueAccordingToType(
                                        $structureValue[self::STRUCTURE_KEY_DEFAULT],
                                        $structureValue[self::STRUCTURE_KEY_TYPE])
                                ) {
                                    $settingsNode[$structureKey] =
                                        $structureValue[self::STRUCTURE_KEY_DEFAULT];
                                } else if (!empty($structureValue[self::STRUCTURE_KEY_REQUIRED])) {
                                    Throw new \Exception(
                                        sprintf(
                                            self::systemTranslate(
                                                'No value defined in external or internal configuration for '
                                                . 'required value "%s" in structure node: "%s" '
                                                . 'and configuration node: "%s"'
                                            ),
                                            $structureKey,
                                            print_r($structureNode, true),
                                            (is_array($configurationNode) ? print_r($configurationNode, true)
                                                : (isset($configurationNode) ? $configurationNode : 'null'))
                                        )
                                    );
                                }
                            }
                        }
                    }
                }
                return $status;
            }
            return false;
        }

        /**
         * Determins if value is valid type.
         *
         * @internal
         * @static
         * @param mixed|null $value
         * @param string $type
         * @throws \Exception
         * @return bool
         */
        private static function _isValidValueAccordingToType($value, $type)
        {
            if (isset($value)
                && isset(self::$_structureKeys[$type])
            ) {
                if ($type == self::STRUCTURE_TYPE_ARRAY) {
                    return self::_isArray($value);
                } else if ($type == self::STRUCTURE_TYPE_ARRAY_ASSOCIATIVE) {
                    return self::_isAssociativeArray($value);
                } else if ($type == self::STRUCTURE_TYPE_BOOLEAN) {
                    return self::_isBoolean($value);
                } else if ($type == self::STRUCTURE_TYPE_INTEGER) {
                    return self::_isInteger($value);
                } else if ($type == self::STRUCTURE_TYPE_INTEGER_UNSIGNED) {
                    return self::_isUnsignedInteger($value);
                } else if ($type == self::STRUCTURE_TYPE_STRING) {
                    return self::_isString($value);
                }
            } else {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate(
                            'Invalid parameters: "%s"'
                        ),
                        print_r(func_get_args(), true)
                    )
                );
            }
            return false;
        }

        /**
         * Determins if value is a integer.
         *
         * @internal
         * @static
         * @param mixed|null $value
         * @return bool
         */
        private static function _isInteger($value)
        {
            return is_numeric($value);
        }

        /**
         * Determin if value is a unsigned integer.
         *
         * @internal
         * @static
         * @param mixed|null $value
         * @return bool
         */
        private static function _isUnsignedInteger($value)
        {
            return (self::_isInteger($value)
                && (int) $value > 0);
        }

        /**
         * Determins if value is array.
         *
         * @internal
         * @static
         * @param mixed|null $value
         * @return bool
         */
        private static function _isArray($value)
        {
            return is_array($value);
        }

        /**
         * Determins if value is a associative array.
         *
         * @internal
         * @static
         * @param mixed|null $value
         * @return bool
         */
        private static function _isAssociativeArray($value)
        {
            return is_array($value);
        }

        /**
         * Determins if value is a boolean.
         *
         * @internal
         * @static
         * @param mixed|null $value
         * @return bool
         */
        private static function _isBoolean($value)
        {
            return is_bool($value);
        }

        /**
         * Determins if value is a string.
         *
         * @internal
         * @param mixed|null $value
         * @return bool
         */
        private static function _isString($value)
        {
            return is_string($value);
        }

        /**
         * @static
         * @param string $path
         * @return mixed
         */
        public static function loadPhpData($path)
        {
            if (file_exists($path)) {
                try {
                    $return = require($path);

                    if (isset($return)
                        && $return !== false
                    ) {
                        return $return;
                    }

                } catch (\Exception $e) {
                    \Aomebo\Feedback\Debug::output(sprintf(
                        'Loading php-data at "%s" caused error "%s"',
                        $path,
                        $e->getMessage()
                    ));
                }
            }

            return array();
        }

        /**
         * @static
         * @param string $path
         * @param string [$variableName = 'configuration']
         * @return array
         */
        public static function loadPhpConfiguration($path,
            $variableName = 'configuration')
        {
            if (file_exists($path)) {
                try {
                    global $$variableName;
                    $$variableName = array();
                    $return = require_once($path);
                    global $$variableName;

                    if (isset($$variableName)
                        && is_array($$variableName)
                        && count($$variableName)
                    ) {
                        // Move configuration to local scope
                        $localVariableName = $variableName . '_local';
                        $$localVariableName = $$variableName;
                        unset($$variableName);
                        return $$localVariableName;

                    } else if (isset($return)
                               && is_array($return)
                               && count($return)
                    ) {
                        return $return;
                    }

                } catch (\Exception $e) {
                    \Aomebo\Feedback\Debug::output(sprintf(
                        'Loading configuration "%s" caused error "%s"',
                        $path,
                        $e->getMessage()
                    ));
                }
            }

            return array();
        }

        /**
         * @param string $path
         * @return array
         * @throws \Exception
         */
        public static function loadYmlConfiguration($path)
        {
            if (!self::$_spycLoaded) {
                $spyc = new \Aomebo\Library\Books\spyc\Book();
                if ($spyc->load()) {
                    self::$_spycLoaded = false;
                } else {
                    Throw new \Exception(
                        self::systemTranslate('Failed to load Spyc Library'
                    ));
                }
            }
            if (file_exists($path)) {
                $configuration = \Spyc::YAMLLoad($path);
                if (isset($configuration)
                    && is_array($configuration)
                ) {
                    return $configuration;
                }
            }
            return array();
        }

        /**
         * @internal
         * @static
         * @return bool
         */
        private static function _savePhpConfiguration()
        {
            return self::savePhpConfigurationFile(
                _SITE_ROOT_ . self::$_externalConfigurationFilename . '.php',
                self::$_settings
            );
        }

        /**
         * @static
         * @param array $data
         * @return bool|string
         */
        public static function generatePhpData($data)
        {
            if (isset($data)
                && is_array($data)
                && count($data) > 0
            ) {
                return "<?php \n"
                    . "\n"
                    . 'return ' . var_export($data, true)
                    . ';' . "\n"
                    . "\n";
            }
            return false;
        }

        /**
         * @static
         * @param string $file
         * @param array $data
         * @return bool
         */
        public static function savePhpDataFile($file, $data)
        {
            if ($phpData = self::generatePhpData($data)) {
                if (\Aomebo\Filesystem::makeFile(
                    $file,
                    $phpData)
                ) {
                    return true;
                }
            }
            return false;
        }

        /**
         * @param array $data
         * @param string [$variableName = 'configuration']
         * @return bool|string
         */
        public static function generatePhpConfigurationData($data,
                $variableName = 'configuration')
        {
            if (isset($data)
                && is_array($data)
                && count($data) > 0
            ) {
                return "<?php \n"
                    . "\n"
                    . 'global $' . $variableName . '; ' . "\n"
                    . "\n"
                    . '$' . $variableName . ' = ' . var_export($data, true)
                    . ';' . "\n"
                    . "\n";
            }
            return false;
        }

        /**
         * @static
         * @param string $file
         * @param array $data
         * @param string [$variableName = 'configuration']
         * @return bool
         */
        public static function savePhpConfigurationFile($file, $data,
            $variableName = 'configuration')
        {
            if ($phpData = self::generatePhpConfigurationData(
                $data, $variableName)
            ) {
                if (\Aomebo\Filesystem::makeFile(
                    $file,
                    $phpData)
                ) {
                    return true;
                }
            }
            return false;
        }

        /**
         * @static
         * @param string $file
         * @param array $data
         * @throws \Exception
         * @return bool
         */
        public static function saveYmlConfigurationFile($file, $data)
        {

            if (!self::$_spycLoaded) {
                $spyc = new \Aomebo\Library\Books\spyc\Book();
                if ($spyc->load()) {
                    self::$_spycLoaded = false;
                } else {
                    Throw new \Exception(
                        self::systemTranslate('Failed to load Spyc Library'));
                }
            }

            if (isset($file, $data)
                && is_array($data)
            ) {

                $yamlData = \spyc::YAMLDump($data, 4, 0);

                if (\Aomebo\Filesystem::makeFile(
                    $file,
                    $yamlData)
                ) {
                    return true;
                }

            }

            return false;

        }

    }
}
