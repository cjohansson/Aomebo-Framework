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
namespace Aomebo\Associatives
{

    /**
     * @method static \Aomebo\Associatives\Engine getInstance()
     */
    class Engine extends \Aomebo\Singleton
    {

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_dependencies;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_selectedDependencies;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_associatives;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_selectedAssociatives;

        /**
         * @var string
         */
        const TYPE_STYLE = 'style';

        /**
         * @var string
         */
        const TYPE_SCRIPT = 'script';

        /**
         * @var string
         */
        const SUFFIX_STYLE = '.css';

        /**
         * @var string
         */
        const SUFFIX_SCRIPT = '.js';

        /**
         * @var string
         */
        const SUFFIX_PHP = '.php';

        /**
         * @var string
         */
        const SUFFIX_JAVASCRIPT = '.js';

        /**
         * @var string
         */
        const SUFFIX_STYLESHEET = '.css';

        /**
         * @var string
         */
        const SUFFIX_MARKUP = '.html';

        /**
         * @var string
         */
        const SUFFIX_MARKUP_ALTERNATIVE = '.htm';

        /**
         * @var string
         */
        const MIME_MARKUP = 'text/html';

        /**
         * @var string
         */
        const MIME_JAVASCRIPT = 'text/javascript';

        /**
         * @var string
         */
        const MIME_STYLESHEET = 'text/css';

        /**
         * @var string
         */
        const MIME_STYLE = 'text/css';

        /**
         * @var string
         */
        const MIME_SCRIPT = 'text/javascript';

        /**
         * @var string
         */
        const MODE_CENTRALIZED = 'centralized';

        /**
         * @var string
         */
        const MODE_MODULARIZED = 'modularized';

        /**
         * @var string
         */
        const MODE_EXTERNAL = 'mode_external';

        /**
         * @var string
         */
        const MODE_INLINE = 'mode_inline';

        /**
         * @var string
         */
        const MODE_IGNORE = 'mode_ignore';

        /**
         * @var string
         */
        const REQUEST_TYPE_AJAX = 'ajax';

        /**
         * @var string
         */
        const REQUEST_TYPE_DEFAULT = 'default';

        /**
         * @var string
         */
        const REQUEST_METHOD_POST = 'POST';

        /**
         * @var string
         */
        const REQUEST_METHOD_GET = 'GET';

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_supressOutput = false;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_media = '';

        /**
         * @throws \Exception
         */
        public function __construct()
        {
            parent::__construct();
            if (!self::_isConstructed()) {

                self::_scanDependencies();
                self::_scanAssociatives();

                if (!\Aomebo\Configuration::getSetting(
                    'paths,resources dir is absolute')
                ) {

                    $resPath =
                        \Aomebo\Dispatcher\System::getResourcesDirInternalPath();
                    if (!is_dir($resPath)) {
                        \Aomebo\Filesystem::makeDirectory($resPath);
                    }
                }

            }
            self::_flagThisConstructed();
        }

        /**
         * @static
         * @param string $media
         */
        public static function setMedia($media)
        {
            self::$_media = $media;
        }

        /**
         * @static
         * @param bool $flag
         */
        public static function setSupressOutputFlag($flag)
        {
            self::$_supressOutput = (!empty($flag));
        }

        /**
         * @static
         * @return bool
         */
        public static function getSupressOutputFlag()
        {
            return self::$_supressOutput;
        }

        /**
         * @static
         * @param string $csName        case-sensitive module-name
         * @deprecated
         */
        public static function addAssociatives($csName)
        {
            self::addAssociate($csName);
        }

        /**
         * This method adds a module to the queue for
         * parsing of associatives later.
         *
         * @static
         * @param string $csName        case-sensitive module-name
         */
        public static function addAssociate($csName)
        {
            $cisName = strtolower($csName);
            if (isset(self::$_associatives[$cisName])
                && !isset(self::$_selectedAssociatives[$cisName])
            ) {
                self::$_selectedAssociatives[$cisName] =
                    & self::$_associatives[$cisName];
            }
        }


        /**
         * This method removes a module from the queue for
         * parsing of associatives later.
         *
         * @static
         * @param string $csName
         */
        public static function removeSelectedAssociate($csName)
        {
            $cisName = strtolower($csName);
            if (isset(self::$_associatives[$cisName])
                && isset(self::$_selectedAssociatives[$cisName])
            ) {
                unset(self::$_selectedAssociatives[$cisName]);
            }
        }

        /**
         * This method removes a dependency from the queue for
         * parsing of associatives later.
         *
         * @static
         * @param string $csName
         */
        public static function removeDependency($csName)
        {
            $cisName = strtolower($csName);
            if (isset(self::$_dependencies[$cisName])
                && isset(self::$_selectedDependencies[$cisName])
            ) {
                unset(self::$_selectedDependencies[$cisName]);
            }
        }

        /**
         * @static
         * @return array|bool
         */
        public static function getAssociateByName($name)
        {
            $cisName = strtolower($name);
            return (isset(self::$_associatives[$cisName]) ?
                self::$_associatives[$cisName] : false);
        }

        /**
         * @static
         * @return array|bool
         */
        public static function getDependencyByName($name)
        {
            $cisName = strtolower($name);
            return (isset(self::$_dependencies[$cisName]) ?
                self::$_dependencies[$cisName] : false);
        }

        /**
         * @static
         * @return array
         */
        public static function getAssociatives()
        {
            return self::$_associatives;
        }

        /**
         * @static
         * @return array
         */
        public static function getDependencies()
        {
            return self::$_dependencies;
        }

        /**
         * @static
         * @param array|\Aomebo\Associatives\Dependent $dependencies     Array or single \Aomebo\Associatives\Depedent
         * @throws \Exception
         */
        public static function addDependencies($dependencies)
        {
            if (is_array($dependencies)
                && sizeof($dependencies) > 0
            ) {

                foreach ($dependencies as $dependency)
                {
                    if (is_object($dependency)
                        && is_a($dependency, '\Aomebo\Associatives\Dependent')
                    ) {
                        /** @var \Aomebo\Associatives\Dependent $dependency */
                        self::addDependency($dependency);
                    }
                }

            } else if (is_object($dependencies)
                && is_a($dependencies, '\Aomebo\Associatives\Dependent')
            ) {
                /** @var \Aomebo\Associatives\Dependent $dependencies */
                self::addDependency($dependencies);
            }
        }

        /**
         * @static
         * @param \Aomebo\Associatives\Dependent $dependent
         * @throws \Exception
         * @return bool
         */
        public static function addDependency($dependent)
        {
            if ($dependent->isValid()) {
                $dependencyCisName = strtolower($dependent->name);
                if (!isset(self::$_selectedDependencies[$dependencyCisName])) {

                    if (isset(self::$_dependencies[$dependencyCisName]))
                    {

                        self::$_selectedDependencies[$dependencyCisName] =
                                    & self::$_dependencies[$dependencyCisName];

                        $dependency = & self::$_dependencies[$dependencyCisName];

                        if ($dependency['has_script_subdependencies']) {
                            self::addDependencies(
                                $dependency['script_subdependencies']);
                        }
                        if ($dependency['has_style_subdependencies']) {
                            self::addDependencies(
                                $dependency['style_subdependencies']
                            );
                        }

                        return true;

                    } else {
                        Throw new \Exception(
                            sprintf(
                                self::systemTranslate('Could not find dependency "%s" in "%s" from "%s"'),
                                $dependencyCisName,
                                print_r(self::$_dependencies, true),
                                $dependent->name
                            )
                        );
                    }
                }
            }

            return false;

        }

        /**
         * @static
         * @return null|string
         */
        public static function getAssociativeData()
        {

            $tmp = '';

            if (!self::$_supressOutput) {
                $tmp .= self::_getDependencyData(self::$_media);
                $tmp .= self::_getAssociativeData(self::$_media);
            }

            $tmp .= \Aomebo\Trigger\System::processTriggers(
                \Aomebo\Trigger\System::TRIGGER_KEY_GET_ASSOCIATIVES_DATA);

            return (!empty($tmp) ? $tmp : null);

        }

        /**
         * This function should store flags which associatives
         * have associative data and where it is located and which
         * type of data (variant-related or default).
         *
         * @internal
         * @static
         * @throws \Exception
         */
        private static function _scanAssociatives()
        {

            $useCache =
                \Aomebo\Configuration::getSetting('framework,use associatives cache');

            $session =
                \Aomebo\Session\Handler::getInstance();
            $sessionBlock = $session->getSessionBlock();
            $associativesDir =
                \Aomebo\Configuration::getSetting('paths,associatives dir');
            $dispatcher = \Aomebo\Dispatcher\System::getInstance();

            $requestType = ($dispatcher::isAjaxRequest() ?
                self::REQUEST_TYPE_AJAX : self::REQUEST_TYPE_DEFAULT);

            self::$_associatives = array();
            self::$_selectedAssociatives = array();

            $suffixToMimeArray = array(
                self::SUFFIX_JAVASCRIPT => self::MIME_JAVASCRIPT,
                self::SUFFIX_STYLESHEET => self::MIME_STYLESHEET,
                self::SUFFIX_MARKUP => self::MIME_MARKUP,
                self::SUFFIX_MARKUP_ALTERNATIVE => self::MIME_MARKUP,
            );
            $allowedMimes = array(
                self::MIME_JAVASCRIPT => true,
                self::MIME_STYLESHEET => true,
                self::MIME_MARKUP => true,
            );
            $allowedModes = array(
                self::MODE_EXTERNAL => true,
                self::MODE_IGNORE => true,
                self::MODE_INLINE => true,
            );

            $cacheParameters = 'Associatives/Engine/Associatives';
            $cacheKey = md5('last_mod=' . \Aomebo\Application::getRuntimesLastModificationTime()
                . '&engine=' . \Aomebo\Filesystem::getFileLastModificationTime(__FILE__, false)
            );

            $loadedCache = false;

            if ($useCache
                && \Aomebo\Cache\System::cacheExists(
                    $cacheParameters,
                    $cacheKey,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM)
            ) {

                if (self::$_associatives = \Aomebo\Cache\System::loadCache(
                    $cacheParameters,
                    $cacheKey,
                    \Aomebo\Cache\System::FORMAT_JSON_ENCODE,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                )) {
                    $loadedCache = true;
                }

            }

            if (!$loadedCache) {

                if ($useCache) {
                    \Aomebo\Cache\System::clearCache(
                        $cacheParameters,
                        null,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    );
                }

                if ($runtimes = \Aomebo\Application::getRuntimes()) {

                    foreach ($runtimes as $runtime)
                    {

                        /** @var \Aomebo\Runtime $runtime */

                        $csName = $runtime->getField('name');
                        $cisName = strtolower($csName);

                        $options = array();

                        if ($runtime->isAssociatable()) {

                            /** @var \Aomebo\Runtime\Associatable $runtime */
                            $options = $runtime->getAssociatives();

                        }

                        $dir = dirname($runtime->getAbsoluteFilename())
                            . DIRECTORY_SEPARATOR . $associativesDir;

                        // Does directory exist?
                        if (is_dir($dir))
                        {

                            // Get associatives files
                            if ($files = scandir($dir)) {

                                // Iterate through files
                                foreach ($files as $file)
                                {

                                    // File is not a sybolic directory
                                    if ($file != '.'
                                        && $file != '..'
                                    ) {

                                        $fileSuffix = substr($file, strrpos($file, '.'));

                                        if (isset($suffixToMimeArray[$fileSuffix])) {
                                            $fileMime = $suffixToMimeArray[$fileSuffix];
                                        } else {
                                            $fileMime = 'unknown';
                                        }

                                        $fileFullPath = $dir . DIRECTORY_SEPARATOR . $file;
                                        $fileSessionApproved = true;
                                        $fileMode = self::MODE_EXTERNAL;
                                        $fileRequestTypeApproved = true;
                                        $fileRequestMethodApproved = true;
                                        $fileemTime = filemtime($fileFullPath);
                                        $fileCacheHash = $fileemTime;

                                        // There exists options specified for file
                                        if (isset($options[$file]))
                                        {

                                            // Custom mode?
                                            if (isset($options[$file]['mode'])) {
                                                $fileMode = $options[$file]['mode'];
                                            }

                                            // Any requirements on session?
                                            if (isset($options[$file]['session_requirement'])) {
                                                if (is_array($options[$file]['session_requirement']))
                                                {

                                                    if (sizeof($options[$file]['session_requirement']) == 1) {

                                                        foreach ($options[$file]['session_requirement'] as
                                                            $sessionAuthFunctionName => $sessionAuthFunctionValue
                                                        ) {
                                                            if (method_exists($sessionBlock,
                                                                $sessionAuthFunctionName)
                                                            ) {

                                                                /** @method mixed $sessionBlock->$sessionAuthFunctionName() */

                                                                $sessionValue =
                                                                    $sessionBlock->$sessionAuthFunctionName($file);

                                                                if ($sessionValue == $sessionAuthFunctionValue) {
                                                                    $fileSessionApproved = true;
                                                                    $fileCacheHash .= '1';
                                                                } else {
                                                                    $fileSessionApproved = false;
                                                                    $fileCacheHash .= '0';
                                                                }
                                                            } else {

                                                                Throw new \Exception(
                                                                    sprintf(
                                                                        self::systemTranslate(
                                                                            'Invalid session requirement method specified! Could not find %s in %s'
                                                                        ),
                                                                        $sessionAuthFunctionName,
                                                                        $sessionBlock
                                                                    )
                                                                );
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $sessionAuthFunctionName =
                                                        $options[$file]['session_requirement'];
                                                    if (method_exists($sessionBlock, $sessionAuthFunctionName)) {
                                                        if ($sessionBlock->$sessionAuthFunctionName()) {
                                                            $fileSessionApproved = true;
                                                            $fileCacheHash .= '1';
                                                        } else {
                                                            $fileSessionApproved = false;
                                                            $fileCacheHash .= '0';
                                                        }
                                                    } else {
                                                        $fileSessionApproved = false;
                                                        $fileCacheHash .= '0';
                                                    }
                                                }
                                            }

                                            // User agent specified
                                            if (isset($options[$file]['http_agent'])) {
                                                if (is_array($options[$file]['http_agent'])
                                                ) {
                                                    if (in_array($_SERVER['HTTP_USER_AGENT'],
                                                        $options[$file]['http_agent'])
                                                    ) {
                                                        $fileCacheHash .= '1';
                                                    } else {
                                                        $fileCacheHash .= '0';
                                                    }
                                                } else {
                                                    if ($_SERVER['HTTP_USER_AGENT'] ==
                                                        $options[$file]['http_agent']
                                                    ) {
                                                        $fileCacheHash .= '1';
                                                    } else {
                                                        $fileCacheHash .= '0';
                                                    }
                                                }
                                            }

                                            // Custom mime?
                                            if (isset($options[$file]['mime'])) {
                                                $fileMime = $options[$file]['mime'];
                                            }

                                            // Request requirement?
                                            if (isset($options[$file]['request_type'])) {
                                                if ($options[$file]['request_type'] ==
                                                    $requestType
                                                ) {
                                                    $fileRequestTypeApproved = true;
                                                    $fileCacheHash .= '1';
                                                } else {
                                                    $fileRequestTypeApproved = false;
                                                    $fileCacheHash .= '0';
                                                }
                                            }

                                            // Request method requirement?
                                            if (isset($options[$file]['request_mode'])) {
                                                if ($options[$file]['request_mode'] ==
                                                    $_SERVER['REQUEST_METHOD']
                                                ) {
                                                    $fileRequestMethodApproved = true;
                                                    $fileCacheHash .= '1';
                                                } else {
                                                    $fileRequestMethodApproved = false;
                                                    $fileCacheHash .= '0';
                                                }
                                            }

                                        }

                                        // Resource is approved?
                                        if ($fileSessionApproved
                                            && isset($allowedMimes[$fileMime])
                                            && isset($allowedModes[$fileMode])
                                            && $fileRequestMethodApproved
                                            && $fileRequestTypeApproved
                                            && $fileMode != self::MODE_IGNORE
                                        ) {

                                            // First resource for module?
                                            if (!isset(self::$_associatives[$cisName]))
                                            {
                                                self::$_associatives[$cisName] =
                                                    array(
                                                        'cis_name' => $cisName,
                                                        'cs_name' => $csName,
                                                        'centralized_path' => \Aomebo\Dispatcher\System::getBaseUri()
                                                            . $associativesDir . DIRECTORY_SEPARATOR
                                                            . $csName,
                                                        'modularized_path' => \Aomebo\Dispatcher\System::getBaseUri()
                                                            . $csName . DIRECTORY_SEPARATOR
                                                            . $associativesDir,
                                                        'options' => $options,
                                                        'has_scripts' => false,
                                                        'has_styles' => false,
                                                        'has_markups' => false,
                                                        'has_external_scripts' => false,
                                                        'external_scripts' => array(),
                                                        'external_scripts_hash' => '',
                                                        'has_inline_scripts' => false,
                                                        'inline_scripts' => array(),
                                                        'has_external_styles' => false,
                                                        'external_styles' => array(),
                                                        'external_styles_hash' => '',
                                                        'has_inline_styles' => false,
                                                        'inline_styles' => array(),
                                                        'has_inline_markups' => false,
                                                        'inline_markups' => array(),
                                                        'fileemtime' => $fileemTime,
                                                    );
                                            }

                                            $associative = & self::$_associatives[$cisName];

                                            // Is resource-mime javascript? */
                                            if ($fileMime == self::MIME_JAVASCRIPT)
                                            {

                                                // Is file external?
                                                if ($fileMode == self::MODE_EXTERNAL)
                                                {
                                                    $associative['has_scripts'] = true;
                                                    $associative['has_external_scripts'] = true;
                                                    $associative['external_scripts'][] =
                                                        $fileFullPath;
                                                    if ($fileCacheHash !== '')
                                                    {
                                                        $associative['external_scripts_hash'] .=
                                                            $fileCacheHash;
                                                    }
                                                } else if ($fileMode == self::MODE_INLINE) {
                                                    $associative['has_scripts'] = true;
                                                    $associative['has_inline_scripts'] = true;
                                                    $associative['inline_scripts'][] =
                                                        \Aomebo\Filesystem::getFileContents(
                                                            $fileFullPath);
                                                }

                                            } else if ($fileMime === self::MIME_STYLESHEET)
                                            {

                                                if ($fileMode == self::MODE_EXTERNAL)
                                                {
                                                    $associative['has_styles'] = true;
                                                    $associative['has_external_styles'] = true;
                                                    $associative['external_styles'][] =
                                                        $fileFullPath;
                                                    if ($fileCacheHash !== '') {
                                                        $associative['external_styles_hash'] .=
                                                            $fileCacheHash;
                                                    }
                                                } else if ($fileMode == self::MODE_INLINE)
                                                {
                                                    $associative['has_styles'] = true;
                                                    $associative['has_inline_styles'] = true;
                                                    $associative['inline_styles'][] =
                                                        \Aomebo\Filesystem::getFileContents(
                                                            $fileFullPath);
                                                }
                                            } else if ($fileMime === self::MIME_MARKUP) {

                                                if ($fileMode == self::MODE_INLINE)
                                                {
                                                    $associative['has_markups'] = true;
                                                    $associative['has_inline_markups'] = true;
                                                    $associative['inline_markups'][] =
                                                        \Aomebo\Filesystem::getFileContents(
                                                            $fileFullPath);
                                                }

                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            if (\Aomebo\Configuration::getSetting(
                                'paths,create runtime directories')
                            ) {
                                \Aomebo\Filesystem::makeDirectories(
                                    $dir,
                                    true,
                                    true
                                );
                            }
                        }
                    }
                }

                if ($useCache) {
                    \Aomebo\Cache\System::saveCache(
                        $cacheParameters,
                        $cacheKey,
                        self::$_associatives,
                        \Aomebo\Cache\System::FORMAT_JSON_ENCODE,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    );
                }

            }
        }

        /**
         * The default behaviour of the dependencies
         * is to include all resources of suffix(js,css,html,htm)
         * into corresponding script and link tags.
         * If a dependency class is present then a dependency
         * can also have subdependencies, and also override
         * default behaviour of individual files in the dependency.
         *
         * @internal
         * @static
         * @throws \Exception
         */
        private static function _scanDependencies()
        {

            $useCache =
                \Aomebo\Configuration::getSetting('framework,use dependencies cache');

            $session = \Aomebo\Session\Handler::getInstance();
            $sessionBlock = $session->getSessionBlock();

            $roots = array(
                _SITE_ROOT_ . 'Dependencies',
                _PUBLIC_ROOT_ . 'Dependencies',
            );

            $lastModificationTime = 0;

            foreach ($roots as $root)
            {
                if ($dirModTime = \Aomebo\Filesystem::getDirectoryLastModificationTime(
                    $root, true, 2, false)
                ) {
                    if ($dirModTime > $lastModificationTime) {
                        $lastModificationTime = $dirModTime;
                    }
                }
            }

            self::$_dependencies = array();
            self::$_selectedDependencies = array();

            $cacheParameters = 'Associatives/Engine/Dependencies';
            $cacheKey = md5('last_mod=' . $lastModificationTime
                . '&engine=' . \Aomebo\Filesystem::getFileLastModificationTime(__FILE__, false)
            );

            $loadedCache = false;

            if ($useCache
                && \Aomebo\Cache\System::cacheExists(
                    $cacheParameters,
                    $cacheKey,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM)
            ) {

                if (self::$_dependencies = \Aomebo\Cache\System::loadCache(
                    $cacheParameters,
                    $cacheKey,
                    \Aomebo\Cache\System::FORMAT_JSON_ENCODE,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                )) {
                    $loadedCache = true;
                }

            }

            if (!$loadedCache) {

                if ($useCache) {

                    \Aomebo\Cache\System::clearCache(
                        $cacheParameters,
                        null,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    );

                }

                // Scan for files and externals
                foreach ($roots as $root)
                {

                    if (!is_dir($root)
                        && \Aomebo\Configuration::getSetting(
                            'paths,create associatives directories')
                    ) {
                        \Aomebo\Filesystem::makeDirectory($root);
                    }

                    if (is_dir($root)) {

                        $dirs = scandir($root);

                        foreach ($dirs as $dir)
                        {
                            if ($dir != '.'
                                && $dir != '..'
                                && is_dir($root . DIRECTORY_SEPARATOR . $dir)
                            ) {

                                $csName = $dir;
                                $cisName = strtolower($dir);

                                if (!isset(self::$_dependencies[$cisName])) {
                                    self::$_dependencies[$cisName] =
                                    array(
                                        'centralized_path' => \Aomebo\Dispatcher\System::getBaseUri()
                                            . 'Dependencies' . DIRECTORY_SEPARATOR
                                            . $csName,
                                        'modularized_path' => \Aomebo\Dispatcher\System::getBaseUri()
                                            . 'Dependencies' . DIRECTORY_SEPARATOR
                                            . $csName,
                                        'cis_name' => $cisName,
                                        'cs_name' => $csName,
                                        'options' => array(),
                                        'has_scripts' => false,
                                        'has_styles' => false,
                                        'has_markups' => false,
                                        'has_external_scripts' => false,
                                        'external_scripts' => array(),
                                        'external_scripts_hash' => '',
                                        'has_inline_scripts' => false,
                                        'inline_scripts' => array(),
                                        'has_external_styles' => false,
                                        'external_styles' => array(),
                                        'external_styles_hash' => '',
                                        'has_inline_styles' => false,
                                        'inline_styles' => array(),
                                        'has_inline_markups' => false,
                                        'inline_markups' => array(),
                                        'has_subdependencies' => false,
                                        'has_script_subdependencies' => false,
                                        'count_script_subdependencies' => 0,
                                        'script_subdependencies' => array(),
                                        'has_style_subdependencies' => false,
                                        'count_style_subdependencies' => 0,
                                        'style_subdependencies' => array(),
                                        'fileemtime' => 0,
                                    );

                                    if ($root == _SITE_ROOT_ . 'Dependencies') {
                                        $mirrorRoot = _PUBLIC_ROOT_;
                                    } else {
                                        $mirrorRoot = _SITE_ROOT_;
                                    }

                                    $mirrorDir = $mirrorRoot . 'Dependencies'
                                        . DIRECTORY_SEPARATOR . $dir;

                                    if (!is_dir($mirrorDir)
                                        && \Aomebo\Configuration::getSetting(
                                            'paths,create associatives directories')
                                    ) {
                                        \Aomebo\Filesystem::makeDirectories(
                                            $mirrorRoot . 'Dependencies',
                                            true,
                                            true
                                        );
                                    }

                                }

                                $dependency = & self::$_dependencies[$cisName];

                                if (file_exists($root . DIRECTORY_SEPARATOR
                                    . $csName . DIRECTORY_SEPARATOR
                                    . 'Dependency' . _PHP_EX_)
                                ) {
                                    $className = '\\Dependencies\\'
                                        . $csName . '\\Dependency';
                                    try
                                    {

                                        /** @var \Aomebo\Associatives\Dependency $dependencyClass*/
                                        $dependencyClass = new $className();
                                        if ($options =
                                            $dependencyClass->getOptions()
                                        ) {
                                            foreach ($options as $fileCs => $option) {
                                                $fileCis = strtolower($fileCs);
                                                $dependency['options'][$fileCis] = $option;
                                            }
                                        }

                                    } catch (\Exception $e) {
                                        Throw new \Exception(
                                            sprintf(
                                                self::systemTranslate(
                                                    'Could not parse dependency ("%s")'
                                                ),
                                                $e->getMessage()
                                            )
                                        );
                                    }
                                }

                                $subfiles = scandir($root . DIRECTORY_SEPARATOR . $csName);

                                foreach ($subfiles as $subfile)
                                {

                                    if ($subfile != '.'
                                        && $subfile != '..'
                                    ) {

                                        $suffix = substr($subfile,
                                            strrpos($subfile, '.'));
                                        $subfileCsName = $subfile;
                                        $subfileCisName = strtolower(
                                            $subfile);
                                        $subfileFullPath = $root
                                            . DIRECTORY_SEPARATOR . $csName
                                            . DIRECTORY_SEPARATOR . $subfileCsName;
                                        $fileemtime = filemtime($subfileFullPath);
                                        $fileCacheHash = $fileemtime;

                                        if ($fileemtime > $dependency['fileemtime']) {
                                            $dependency['fileemtime'] = $fileemtime;
                                        }

                                        if (!empty($dependency['options'][$subfileCisName]['mode'])) {
                                            $subfileMode =
                                                $dependency['options'][$subfileCisName]['mode'];
                                        } else {
                                            $subfileMode =
                                                \Aomebo\Associatives\Dependency::MODE_EXTERNAL;
                                        }

                                        if (!empty($dependency['options'][$subfileCisName]['session_requirement'])) {
                                            $sessionAuthFunctionName =
                                                $dependency['options'][$subfileCisName]['session_requirement'];
                                            if (method_exists($sessionBlock, $sessionAuthFunctionName)) {
                                                if ($sessionBlock->$sessionAuthFunctionName()) {
                                                    $subfileSessionAuthorized = true;
                                                    $fileCacheHash .= '1';
                                                } else {
                                                    $subfileSessionAuthorized = false;
                                                    $fileCacheHash .= '0';
                                                }
                                            } else {
                                                $subfileSessionAuthorized = false;
                                                    $fileCacheHash .= '0';
                                            }
                                        } else {
                                            $subfileSessionAuthorized = true;
                                        }

                                        if (!empty($dependency['options'][$subfileCisName]['http_agent'])) {
                                            if (is_array($dependency['options']
                                                [$subfileCisName]['http_agent'])
                                            ) {
                                                if (in_array($_SERVER['HTTP_USER_AGENT'],
                                                    $dependency['options']
                                                    [$subfileCisName]['http_agent'])
                                                ) {
                                                    $subfileHttpAgent = true;
                                                    $fileCacheHash .= '1';
                                                } else {
                                                    $subfileHttpAgent = false;
                                                    $fileCacheHash .= '0';
                                                }
                                            } else {
                                                if ($_SERVER['HTTP_USER_AGENT'] ==
                                                    $dependency['options']
                                                    [$subfileCisName]['http_agent']
                                                ) {
                                                    $subfileHttpAgent = true;
                                                    $fileCacheHash .= '1';
                                                } else {
                                                    $subfileHttpAgent = false;
                                                    $fileCacheHash .= '0';
                                                }
                                            }
                                        } else {
                                            $subfileHttpAgent = true;
                                        }

                                        if (!empty($dependency['options'][$subfileCisName]['mime'])) {
                                            $subfileMime =
                                                $dependency['options'][$subfileCisName]['mime'];
                                        } else {
                                            if ($suffix == self::SUFFIX_SCRIPT) {
                                                $subfileMime =
                                                    \Aomebo\Associatives\Dependency::MIME_JAVASCRIPT;
                                            } else if ($suffix == self::SUFFIX_STYLE) {
                                                $subfileMime =
                                                    \Aomebo\Associatives\Dependency::MIME_STYLESHEET;
                                            } else if ($suffix == self::SUFFIX_MARKUP
                                                || $suffix == self::SUFFIX_MARKUP_ALTERNATIVE
                                            ) {
                                                $subfileMime =
                                                    \Aomebo\Associatives\Dependency::MIME_MARKUP;
                                            } else {
                                                $subfileMime = false;
                                            }
                                        }
                                        if ($subfileMime != false
                                            && $subfileMode !=
                                            \Aomebo\Associatives\Dependency::MODE_IGNORE
                                            && $subfileSessionAuthorized
                                            && $subfileHttpAgent
                                        ) {
                                            if ($subfileMime ==
                                                \Aomebo\Associatives\Dependency::MIME_JAVASCRIPT
                                            ) {
                                                if ($subfileMode ==
                                                    \Aomebo\Associatives\Dependency::MODE_EXTERNAL
                                                ) {

                                                    $dependency['has_scripts'] = true;
                                                    $dependency['has_external_scripts'] = true;
                                                    $dependency['external_scripts'][] =
                                                        $subfileFullPath;
                                                    if ($fileCacheHash !== '') {
                                                        $dependency['external_scripts_hash'] .=
                                                            $fileCacheHash;
                                                    }

                                                } else if ($subfileMode ==
                                                    \Aomebo\Associatives\Dependency::MODE_INLINE
                                                ) {

                                                    $dependency['has_scripts'] = true;
                                                    $dependency['has_inline_scripts'] = true;
                                                    $dependency['inline_scripts'][] =
                                                        \Aomebo\Filesystem::getFileContents(
                                                            $subfileFullPath);

                                                }
                                            } else if ($subfileMime ===
                                                \Aomebo\Associatives\Dependency::MIME_STYLESHEET
                                            ) {
                                                if ($subfileMode ==
                                                    \Aomebo\Associatives\Dependency::MODE_EXTERNAL
                                                ) {

                                                    $dependency['has_styles'] = true;
                                                    $dependency['has_external_styles'] = true;
                                                    $dependency['external_styles'][] =
                                                        $subfileFullPath;
                                                    if ($fileCacheHash !== '') {
                                                        $dependency['external_styles_hash'] .=
                                                            $fileCacheHash;
                                                    }

                                                } else if ($subfileMode ==
                                                    \Aomebo\Associatives\Dependency::MODE_INLINE
                                                ) {

                                                    $dependency['has_styles'] = true;
                                                    $dependency['has_inline_styles'] = true;
                                                    $dependency['inline_styles'][] =
                                                        \Aomebo\Filesystem::getFileContents(
                                                            $subfileFullPath);

                                                }
                                            } else if ($subfileMime ===
                                                \Aomebo\Associatives\Dependency::MIME_MARKUP
                                            ) {

                                                if ($subfileMode ==
                                                    \Aomebo\Associatives\Dependency::MODE_INLINE
                                                ) {

                                                    $dependency['has_markups'] = true;
                                                    $dependency['has_inline_markups'] = true;
                                                    $dependency['inline_markups'][] =
                                                        \Aomebo\Filesystem::getFileContents(
                                                            $subfileFullPath);
                                                }

                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Rescan for subdependencies
                reset($roots);

                foreach ($roots as $root)
                {

                    if (is_dir($root)) {

                        $dirs = scandir($root);

                        foreach ($dirs as $dir)
                        {
                            if ($dir != '.'
                                && $dir != '..'
                                && is_dir($root . DIRECTORY_SEPARATOR . $dir)
                            ) {

                                $csName = $dir;
                                $cisName = strtolower($dir);
                                $dependency = & self::$_dependencies[$cisName];

                                if (file_exists($root . DIRECTORY_SEPARATOR
                                    . $csName . DIRECTORY_SEPARATOR
                                    . 'Dependency' . _PHP_EX_)
                                ) {
                                    $className = '\\Dependencies\\'
                                        . $csName . '\\Dependency';
                                    try {
                                        $dependencyClass = new $className();
                                        if ($subdependencies =
                                            $dependencyClass->getSubDependencies()
                                        ) {
                                            foreach ($subdependencies as $subdependency
                                            ) {

                                                $subdependencyCisName = strtolower($subdependency);

                                                if (self::$_dependencies[$subdependencyCisName]['has_scripts'])
                                                {

                                                    $dependency['has_subdependencies'] = true;
                                                    $dependency['has_script_subdependencies'] = true;
                                                    $dependency['count_script_subdependencies']++;
                                                    $dependency['script_subdependencies'][] =
                                                        $subdependencyCisName;

                                                }

                                                if (self::$_dependencies[$subdependencyCisName]['has_styles']) {
                                                    $dependency['has_subdependencies'] = true;
                                                    $dependency['has_style_subdependencies'] = true;
                                                    $dependency['count_style_subdependencies']++;
                                                    $dependency['style_subdependencies'][]
                                                        = $subdependencyCisName;
                                                }

                                                if (self::$_dependencies[$subdependencyCisName]['has_markups']) {

                                                    $dependency['has_subdependencies'] = true;

                                                    $dependency['has_style_subdependencies'] = true;
                                                    $dependency['count_style_subdependencies']++;
                                                    $dependency['style_subdependencies'][]
                                                        = $subdependencyCisName;

                                                    $dependency['has_script_subdependencies'] = true;
                                                    $dependency['count_script_subdependencies']++;
                                                    $dependency['script_subdependencies'][] =
                                                        $subdependencyCisName;


                                                }

                                            }
                                        }
                                    } catch (\Exception $e) {}
                                }
                            }
                        }
                    }
                }

                if ($useCache) {

                    \Aomebo\Cache\System::saveCache(
                        $cacheParameters,
                        $cacheKey,
                        self::$_dependencies,
                        \Aomebo\Cache\System::FORMAT_JSON_ENCODE,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    );

                }

            }
        }

        /**
         * This method performs compare between dependencies.
         *
         * @internal
         * @static
         * @param array $dependencyA
         * @param array $dependencyB
         * @return int
         */
        public static function dependencySortScripts($dependencyA, $dependencyB)
        {
            if ($dependencyA['count_script_subdependencies']
                > $dependencyB['count_script_subdependencies']
            ) {
                return 1;
            } else if ($dependencyA['count_script_subdependencies']
                == $dependencyB['count_script_subdependencies']
            ) {
                return strcmp($dependencyA['cis_name'], $dependencyB['cis_name']);
            } else {
                return -1;
            }
        }

        /**
         * This method sort dependency styles.
         *
         * @internal
         * @static
         * @param array $dependencyA
         * @param array $dependencyB
         * @return int
         */
        public static function dependencySortStyles($dependencyA, $dependencyB)
        {
            if ($dependencyA['count_style_subdependencies']
                > $dependencyB['count_style_subdependencies']
            ) {
                return 1;
            } else if ($dependencyA['count_style_subdependencies']
                == $dependencyB['count_style_subdependencies']
            ) {
                return strcmp($dependencyA['cis_name'], $dependencyB['cis_name']);
            } else {
                return -1;
            }
        }

        /**
         * This method returns dependency data.
         *
         * @internal
         * @static
         * @param string|null [$media = null]
         * @return string
         */
        private static function _getDependencyData($media = null)
        {

            $return = '';

            if (sizeof(self::$_selectedDependencies) > 0) {

                $dispatcher =
                    \Aomebo\Dispatcher\System::getInstance();
                $parser =
                    \Aomebo\Associatives\Parser::getInstance();

                // Sort script depdencies by script subdependencies ascending
                uasort(
                    self::$_selectedDependencies,
                    '\Aomebo\Associatives\Engine::dependencySortScripts'
                );

                $lastSubdependenciesCount = 0;
                $externalScripts = array();
                $externalScriptsFileemtime = 0;
                $externalStyles = array();
                $externalStylesFileemtime = 0;
                $inlineScripts = '';
                $inlineStyles = '';
                $inlineMarkups = '';

                foreach (self::$_selectedDependencies as $selectedDependency)
                {

                    $subdependenciesCount =
                        (int) $selectedDependency['count_script_subdependencies'];

                    if ($subdependenciesCount != $lastSubdependenciesCount) {

                        if (sizeof($externalScripts) > 0) {
                            $return .= '<script src="';
                            $return .= $dispatcher->buildAssocUri(array(
                                'at' => 'js',
                                'ds' => implode(',', $externalScripts),
                                'v' => $externalScriptsFileemtime,
                            ));
                            $return .= '" type="'
                                  . self::MIME_SCRIPT . '"></script>';
                        }

                        if (sizeof($externalStyles) > 0) {
                            $return .= '<link href="';
                            $return .= $dispatcher->buildAssocUri(array(
                                'at' => 'css',
                                'ds' => implode(',', $externalStyles),
                                'v' => $externalStylesFileemtime,
                            ));
                            $return .= '" rel="stylesheet" type="'
                                  . self::MIME_STYLE . '" media="'
                                  . (empty($media) ? 'screen' : $media)
                                  . '" />';
                        }

                        if (strlen($inlineMarkups) > 0) {
                            $return .= $inlineMarkups;
                        }

                        if (strlen($inlineScripts) > 0) {
                            $return  .= '<script type="' . self::MIME_SCRIPT . '">'
                                . '/* <![CDATA[ */'
                                . $inlineScripts
                                . '/* ]]> */'
                                . '</script>';
                        }

                        if (strlen($inlineStyles) > 0) {
                            $return .= '<style type="' . self::MIME_STYLE . '">'
                                . $inlineStyles
                                . '</style>';
                        }

                        $externalScripts = array();
                        $externalStyles = array();
                        $inlineScripts = '';
                        $inlineStyles = '';
                        $inlineMarkups = '';

                    }

                    $lastSubdependenciesCount = $subdependenciesCount;

                    if ($selectedDependency['has_markups']) {
                        if ($selectedDependency['has_inline_markups']) {
                            foreach ($selectedDependency['inline_markups']
                                as $inlineMarkup
                            ) {

                                $inlineParsedMarkup = $parser->parseDependency(
                                    $inlineMarkup,
                                    $selectedDependency['cis_name'],
                                    $selectedDependency['centralized_path'],
                                    $selectedDependency['modularized_path']);
                                $inlineMarkups .= $inlineParsedMarkup;

                            }
                        }
                    }

                    if ($selectedDependency['has_scripts']) {
                        if ($selectedDependency['has_inline_scripts']) {
                            foreach ($selectedDependency['inline_scripts']
                                as $inlineScript
                            ) {

                                $inlineParsedScript = $parser->parseDependency(
                                    $inlineScript,
                                    $selectedDependency['cis_name'],
                                    $selectedDependency['centralized_path'],
                                    $selectedDependency['modularized_path']);
                                $inlineScripts .= $inlineParsedScript;

                            }
                        }

                        if ($selectedDependency['has_external_scripts']) {
                            $externalScripts[] =
                                $selectedDependency['cis_name'];
                            if ($selectedDependency['fileemtime']
                                > $externalScriptsFileemtime
                            ) {
                                $externalScriptsFileemtime =
                                    $selectedDependency['fileemtime'];
                            }
                        }

                    }

                    if ($selectedDependency['has_styles']) {
                        if ($selectedDependency['has_inline_styles']) {
                            foreach ($selectedDependency['inline_styles']
                                as $inlineStyle
                            ) {

                                $inlineParsedStyle = $parser->parseDependency(
                                    $inlineStyle,
                                    $selectedDependency['cis_name'],
                                    $selectedDependency['centralized_path'],
                                    $selectedDependency['modularized_path']);
                                $inlineStyles .= '<style type="' . self::MIME_STYLE . '">'
                                    . $inlineParsedStyle
                                    . '</style>';

                            }
                        }

                        if ($selectedDependency['has_external_styles']) {
                            $externalStyles[] =
                                $selectedDependency['cis_name'];
                            if ($selectedDependency['fileemtime']
                                > $externalStylesFileemtime
                            ) {
                                $externalStylesFileemtime =
                                    $selectedDependency['fileemtime'];
                            }
                        }

                    }
                }

                if (sizeof($externalScripts) > 0) {

                    $return .= '<script src="';
                    $return .= $dispatcher->buildAssocUri(array(
                        'at' => 'js',
                        'ds' => implode(',', $externalScripts),
                        'v' => $externalScriptsFileemtime
                    ));
                    $return .= '" type="'
                          . self::MIME_SCRIPT . '"></script>';

                }

                if (sizeof($externalStyles) > 0) {

                    $return .= '<link href="';
                    $return .= $dispatcher->buildAssocUri(array(
                        'at' => 'css',
                        'ds' => implode(',', $externalStyles),
                        'v' => $externalStylesFileemtime
                    ));
                    $return .= '" rel="stylesheet" type="'
                          . self::MIME_STYLE . '" media="'
                          . (empty($media) ? 'screen' : $media)
                          . '" />';

                }

                if (strlen($inlineMarkups) > 0) {
                    $return .= $inlineMarkups;
                }

                if (strlen($inlineScripts) > 0) {
                    $return  .= '<script type="' . self::MIME_SCRIPT . '">'
                        . $inlineScripts
                        . '</script>';
                }

                if (strlen($inlineStyles) > 0) {
                    $return .= '<style type="' . self::MIME_STYLE . '">'
                        . $inlineStyles
                        . '</style>';
                }

            }

            return $return;
        }

        /**
         * This method returns associative data as string.
         *
         * @internal
         * @param string|null [$media = null]
         * @return string
         */
        private static function _getAssociativeData($media = null)
        {

            $return = '';

            if (sizeof(self::$_selectedAssociatives) > 0) {

                $dispatcher =
                    \Aomebo\Dispatcher\System::getInstance();
                $parser =
                    \Aomebo\Associatives\Parser::getInstance();
                $externalScripts = array();
                $externalScriptsFileemtime = 0;
                $externalStyles = array();
                $externalStylesFileemtime = 0;
                $inlineScripts = '';
                $inlineStyles = '';
                $inlineMarkups = '';

                foreach (self::$_selectedAssociatives as $selectedAssociative)
                {

                    if ($selectedAssociative['has_markups']) {
                        if ($selectedAssociative['has_inline_markups']) {
                            foreach ($selectedAssociative['inline_markups'] as $inlineMarkup)
                            {

                                $inlineParsedMarkup = $parser->parseDependency(
                                    $inlineMarkup,
                                    $selectedAssociative['cis_name'],
                                    $selectedAssociative['centralized_path'],
                                    $selectedAssociative['modularized_path']);
                                $inlineMarkups .= $inlineParsedMarkup;

                            }
                        }
                    }
                    if ($selectedAssociative['has_scripts']) {
                        if ($selectedAssociative['has_inline_scripts']) {
                            foreach ($selectedAssociative['inline_scripts'] as $inlineScript)
                            {

                                $inlineParsedScript = $parser->parseDependency(
                                    $inlineScript,
                                    $selectedAssociative['cis_name'],
                                    $selectedAssociative['centralized_path'],
                                    $selectedAssociative['modularized_path']);
                                $inlineScripts .= $inlineParsedScript;

                            }
                        }
                        if ($selectedAssociative['has_external_scripts']) {
                            $externalScripts[] = $selectedAssociative['cis_name'];
                            if ($selectedAssociative['fileemtime']
                                > $externalScriptsFileemtime
                            ) {
                                $externalScriptsFileemtime =
                                    $selectedAssociative['fileemtime'];
                            }
                        }
                    }
                    if ($selectedAssociative['has_styles']) {
                        if ($selectedAssociative['has_inline_styles']) {
                            foreach ($selectedAssociative['inline_styles'] as $inlineStyle)
                            {

                                $inlineParsedStyle = $parser->parseDependency(
                                    $inlineStyle,
                                    $selectedAssociative['cis_name'],
                                    $selectedAssociative['centralized_path'],
                                    $selectedAssociative['modularized_path']);
                                $inlineStyles .= '<style type="' . self::MIME_STYLE . '">'
                                    . $inlineParsedStyle . '</style>';

                            }
                        }
                        if ($selectedAssociative['has_external_styles']) {
                            $externalStyles[] =
                                $selectedAssociative['cis_name'];
                            if ($selectedAssociative['fileemtime']
                                > $externalStylesFileemtime
                            ) {
                                $externalStylesFileemtime =
                                    $selectedAssociative['fileemtime'];
                            }
                        }
                    }
                }

                if (sizeof($externalScripts) > 0) {
                    $return .= '<script src="';
                    $return .= $dispatcher->buildAssocUri(array(
                        'at' => 'js',
                        'fs' => implode(',', $externalScripts),
                        'v' => $externalScriptsFileemtime,
                    ));
                    $return .= '" type="'
                          . self::MIME_SCRIPT . '"></script>';
                }

                if (sizeof($externalStyles) > 0) {
                    $return .= '<link href="';
                    $return .= $dispatcher->buildAssocUri(array(
                        'at' => 'css',
                        'fs' => implode(',', $externalStyles),
                        'v' => $externalStylesFileemtime,
                    ));
                    $return .= '" rel="stylesheet" type="'
                          . self::MIME_STYLE . '" media="'
                          . (empty($media) ? 'screen' : $media)
                          . '" />';
                }

                if (strlen($inlineMarkups) > 0) {
                    $return .= $inlineMarkups;
                }

                if (strlen($inlineScripts) > 0) {
                    $return  .= '<script type="' . self::MIME_SCRIPT . '">'
                        . $inlineScripts
                        . '</script>';
                }

                if (strlen($inlineStyles) > 0) {
                    $return .= '<style type="' . self::MIME_STYLE . '">'
                        . $inlineStyles
                        . '</style>';
                }

            }

            return $return;

        }

    }
}
