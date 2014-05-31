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
namespace Aomebo\Associatives
{

    /**
     * @method static \Aomebo\Associatives\Parser getInstance()
     */
    class Parser extends \Aomebo\Singleton
    {

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
        const DATA_FUNCTIONS = 'functions';

        /**
         * @var string
         */
        const DATA_DEPENDENCIES = 'dependencies';

        /**
         * @var string
         */
        const TYPE_STYLE = 'css';

        /**
         * @var string
         */
        const TYPE_SCRIPT = 'js';

        /**
         * @var string
         */
        const MIME_STYLE = 'text/css';

        /**
         * @var string
         */
        const MIME_SCRIPT = 'text/javascript';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_chunkBuffer;

        /**
         * @internal
         * @static
         * @var int
         */
        private static $_chunkSize;

        /**
         * @internal
         * @static
         * @var int
         */
        private static $_chunkLimit;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_useCache = false;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_cacheContents = '';

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_minify = false;

        /**
         *
         */
        public function __construct()
        {
            if (!self::_isConstructed()) {
                parent::__construct();
                self::_flagThisConstructed();
            }
        }

        /**
         * @static
         * @param string $stylesheet
         * @return string
         */
        public static function minifyStylesheet($stylesheet)
        {
            return preg_replace(
                array(
                    '/(\s)+/',
                    '/\/\*(.(?!\*\/))+.{1}\*\//s',
                    '/\/\/[^\n]+/',
                ),
                array(
                    ' ',
                    '',
                    '',
                ),
                $stylesheet
            );
        }

        /**
         * @static
         * @param string $javascript
         * @return string
         */
        public static function minifyJavascript($javascript)
        {
            return preg_replace(
                array(
                    '/(?:(^|^[^\\\"\']+))(\/\*((.(?!\*\/))+).{1}\*\/)/sm',
                    '/(?:(^|^[^\\\"\']+))\/\/[^\n]*/m',
                    '/(\s)+/',
                ),
                array(
                    '',
                    '',
                    ' ',
                ),
                $javascript
            );
        }

        /**
         * @static
         * @return bool
         */
        public static function cleanAssociativesCache()
        {
            return \Aomebo\Cache\System::clearCache(
                'Associatives'
                . '/'
                . 'Runtimes'
            );
        }

        /**
         * @static
         * @return bool
         */
        public static function cleanDependenciesCache()
        {
            return \Aomebo\Cache\System::clearCache(
                'Associatives'
                . '/'
                . 'Dependencies');
        }

        /**
         * This method parses a dependency
         *
         * @param string $data
         * @param string $module
         * @param string $centralizedPath
         * @param string $modularizedPath
         * @return string
         */
        public static function parseDependency($data, $module,
            $centralizedPath, $modularizedPath)
        {

            $mode = \Aomebo\Configuration::getSetting(
                'settings,associatives mode');

            $dynamicPath = & $centralizedPath;
            if ($mode ==
                \Aomebo\Associatives\Parser::MODE_CENTRALIZED
            ) {
                $dynamicPath = & $centralizedPath;
            } else if ($mode ===
                \Aomebo\Associatives\Parser::MODE_MODULARIZED
            ) {
                $dynamicPath = & $modularizedPath;
            }

            $replace = array(
                'D' => $dynamicPath,
                'C' => $centralizedPath,
                'M' => $modularizedPath,
                'R' => \Aomebo\Dispatcher\System::getResourcesDirExternalPath(),
                'U' => \Aomebo\Dispatcher\System::getUploadsDirExternalPath(),
                'F' => $module,
            );

            if ($variables = \Aomebo\Configuration::getSetting(
                'templates,variables')
            ) {
                foreach ($variables as $key => $value) {
                    $replace[$key] = $value;
                }
            }

            $data = str_replace(
                self::_getReplaceKeys($replace),
                $replace,
                $data);
            $data = str_replace(
                self::_getOldReplaceKeys($replace),
                $replace,
                $data);

            return $data;

        }

        /**
         * @throws \Exception
         */
        public static function parseRequest()
        {

            $engine =
                \Aomebo\Associatives\Engine::getInstance();
            $associatives = $engine->getAssociatives();
            $dependencies = $engine->getDependencies();

            self::$_chunkBuffer = '';
            self::$_chunkSize = (int) 0;
            self::$_chunkLimit =
                \Aomebo\Configuration::getSetting('output,chunk size');
            self::$_useCache =
                \Aomebo\Configuration::getSetting('output,associatives cache');

            $patterns = array(
                '/<D>/',
                '/<C>/',
                '/<M>/',
                '/<R>/',
                '/<F>/',
                '/box-shadow: ([\d]+px) ([\d]+px) ([\d]+px) (\#[\w]+;)/',
                '/border-radius: ([\d]+px;)/',
                '/border-radius: ([\d]+px) ([\d]+px) ([\d]+px) ([\d]+px;)/',
                '/border-(top|bottom)-(left|right)-radius: ([\d]+px;)/',
                '/opacity: ([\d\.]+);/e',
            );
            ksort($patterns);
            $dynamicPath = '';
            $centralizedPath = '';
            $modularizedPath = '';

            $resPath =
                \Aomebo\Dispatcher\System::getResourcesDirInternalPath();
            $module = '';
            $linebreak = \Aomebo\Configuration::getSetting('output,linebreak character');

            $tab = \Aomebo\Configuration::getSetting('output,tab character');
            $replacements = array(
                & $dynamicPath,
                & $centralizedPath,
                & $modularizedPath,
                & $resPath,
                & $module,
                "box-shadow: $1 $2 $3 $4" . $linebreak . $tab . "-webkit-box-shadow: $1 $2 $3 $4" . $linebreak . $tab . "-moz-box-shadow: $1 $2 $3 $4",
                "border-radius: $1" . $linebreak . $tab . "-moz-border-radius: $1",
                "border-radius: $1 $2 $3 $4" . $linebreak . $tab . "-moz-border-radius: $1 $2 $3 $4",
                "border-$1-$2-radius: $3" . $linebreak . $tab . "-moz-border-radius-$1$2: $3",
                "'filter: alpha(opacity=' . ($1*100) . ');" . $linebreak . $tab . "-moz-opacity: $1;" . $linebreak . $tab . "opacity: $1;'",
            );
            ksort($replacements);

            $date = date(\Aomebo\Configuration::getSetting(
                'output,default dateformat'));

            // Add cache stamp optionally
            if (\Aomebo\Configuration::getSetting(
                'output,add resources cache tag')
            ) {
                $cacheStamp = '/* Cache created ' . $date
                    . ' */ ' . $linebreak;
            } else {
                $cacheStamp = '';
            }

            $microtime = explode(' ', microtime());
            $start = $microtime[0] + $microtime[1];
            /*
                Explanation of GET-tags:
                    at = associative type (css or js)
                    ds = dependencies
                    fs = functions / modules
            */

            $associativeType = self::TYPE_STYLE;
            $mimeType = self::MIME_STYLE;

            // Is associative type specified?
            if (!empty($_GET['at'])) {
                $associativeType = $_GET['at'];
                if ($associativeType ==
                    self::TYPE_STYLE
                ) {
                    $mimeType = self::MIME_STYLE;
                    self::_addChunk('@charset "'
                        . \Aomebo\Configuration::getSetting('output,character set')
                        . '"; ' . $linebreak);
                } else if ($associativeType ==
                    self::TYPE_SCRIPT
                ) {
                    $mimeType = self::MIME_SCRIPT;
                }
            }

            if ($associativeType === self::TYPE_STYLE
                || $associativeType === self::TYPE_SCRIPT
            ) {

                \Aomebo\Dispatcher\System::setHttpHeaderField(
                    'Content-type',
                    $mimeType . '; charset='
                    . \Aomebo\Configuration::getSetting('output,character set'));

                if ($associativeType == self::TYPE_STYLE) {
                    if (\Aomebo\Configuration::getSetting('output,minify stylesheets')) {
                        self::$_minify = true;
                    }
                } else if ($associativeType == self::TYPE_SCRIPT) {
                    if (\Aomebo\Configuration::getSetting('output,minify javascripts')) {
                        self::$_minify = true;
                    }
                }

            }

            if (!empty($associativeType)
                && (!empty($_GET['fs'])
                || !empty($_GET['ds']))
            ) {

                $resourceMode = self::DATA_FUNCTIONS;
                if (!empty($_GET['fs'])) {
                    $resourceString = $_GET['fs'];
                    $resourceMode = self::DATA_FUNCTIONS;
                } else if (!empty($_GET['ds'])) {
                    $resourceString = $_GET['ds'];
                    $resourceMode = self::DATA_DEPENDENCIES;
                } else {
                    $resourceString = '';
                }

                $resources = explode(',', $resourceString);

                if (is_array($resources)
                    && sizeof($resources) > 0
                ) {

                    $toParse = array();
                    $totalCacheHash = '';

                    foreach ($resources as $resource)
                    {

                        if ($resourceMode === self::DATA_FUNCTIONS) {
                            if (isset($associatives[$resource])) {
                                $associative = & $associatives
                                    [$resource];
                                if ($associativeType == self::TYPE_SCRIPT
                                    && $associative['has_external_scripts']
                                    || $associativeType == self::TYPE_STYLE
                                    && $associative['has_external_styles']
                                ) {

                                    $associativeResources = array();

                                    if ($associativeType == self::TYPE_SCRIPT) {

                                        $associativeResources =
                                            & $associative['external_scripts'];
                                        $totalCacheHash .=
                                            $associative['external_scripts_hash'];

                                    } else if ($associativeType == self::TYPE_STYLE) {

                                        $associativeResources =
                                            & $associative['external_styles'];
                                        $totalCacheHash .=
                                            $associative['external_styles_hash'];

                                    }

                                    foreach ($associativeResources
                                        as $associativeResource
                                    ) {
                                        $toParse[] = array(
                                            'name' => $resource,
                                            'type' => $associativeType,
                                            'internal_path' => $associativeResource,
                                            'external_path' => $associativeResource,
                                            'centralized_path' => $associative['centralized_path'],
                                            'modularized_path' => $associative['modularized_path'],
                                            'time' => filemtime($associativeResource),
                                        );
                                    }

                                }
                            }
                        } else if ($resourceMode === self::DATA_DEPENDENCIES) {

                            if (isset($dependencies[$resource])) {

                                $dependency = & $dependencies
                                    [$resource];

                                if ($associativeType == self::TYPE_SCRIPT
                                    && $dependency['has_external_scripts']
                                    || $associativeType == self::TYPE_STYLE
                                    && $dependency['has_external_styles']
                                ) {

                                    $dependencyResources = array();
                                    if ($associativeType == self::TYPE_SCRIPT) {
                                        $dependencyResources =
                                            & $dependency['external_scripts'];
                                        $totalCacheHash .=
                                            $dependency['external_scripts_hash'];
                                    } else if ($associativeType == self::TYPE_STYLE) {
                                        $dependencyResources =
                                            & $dependency['external_styles'];
                                        $totalCacheHash .=
                                            $dependency['external_styles_hash'];
                                    }

                                    foreach ($dependencyResources
                                        as $dependencyResource
                                    ) {
                                        $toParse[] = array(
                                            'name' => $resource,
                                            'type' => $associativeType,
                                            'internal_path' => $dependencyResource,
                                            'external_path' => $dependencyResource,
                                            'centralized_path' => $dependency['centralized_path'],
                                            'modularized_path' => $dependency['modularized_path'],
                                            'time' => filemtime($dependencyResource),
                                        );
                                    }

                                }
                            }
                        }
                    }

                    /**
                     * Cache parameters, unique per:
                     * - Associatives request type
                     * - Associatives type
                     * - Request string
                     */
                    $cacheParameters = 'Associatives'
                        . '/' . ($resourceMode == self::DATA_FUNCTIONS ?
                        'Runtimes' : 'Dependencies')
                        . '/' . md5($associativeType)
                        . '/' . md5($resourceString);

                    /**
                     * Cache key, unique per:
                     * - Filemtime for included resources
                     */
                    $cacheKey = md5($totalCacheHash);

                    $cacheEtag = md5($cacheParameters . '/'
                        . $cacheKey);

                    // Do we have a already cached resource for this request?
                    if (self::$_useCache
                        && \Aomebo\Cache\System::cacheExists(
                            $cacheParameters,
                            $cacheKey
                        )
                    ) {

                        \Aomebo\Dispatcher\System::setHttpHeaderField(
                            'ETag',
                            $cacheEtag);

                        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])
                            && $_SERVER['HTTP_IF_NONE_MATCH'] == $cacheEtag
                        ) {

                            \Aomebo\Dispatcher\System::setHttpResponseStatus304NotModified();
                            \Aomebo\Dispatcher\System::outputHttpHeaders();

                        } else {

                            \Aomebo\Dispatcher\System::outputHttpHeaders();

                            if (!\Aomebo\Dispatcher\System::isHttpHeadRequest()) {
                                echo \Aomebo\Cache\System::loadCache(
                                    $cacheParameters,
                                    $cacheKey,
                                    \Aomebo\Cache\System::FORMAT_RAW
                                );
                            }

                        }

                    // Otherwise - generate a resource
                    } else {

                        if (self::$_useCache) {

                            \Aomebo\Cache\System::clearCache(
                                $cacheParameters);

                            self::$_cacheContents = $cacheStamp;

                        }

                        \Aomebo\Dispatcher\System::outputHttpHeaders();

                        if (!\Aomebo\Dispatcher\System::isHttpHeadRequest()) {

                            $parsedBlocks = (int) 0;
                            $printSectionNames =
                                \Aomebo\Configuration::getSetting('output,add resources sections');

                            if ($chunkAdd = \Aomebo\Trigger\System::processTriggers(
                                \Aomebo\Trigger\System::TRIGGER_KEY_ASSOCIATIVES_PARSER)
                            ) {
                                self::_addChunk($chunkAdd);
                            }

                            foreach ($toParse as $parseFile)
                            {

                                $chunkAdd = '';

                                if ($parsedBlocks > 0) {
                                    $chunkAdd .= $linebreak;
                                }

                                if ($printSectionNames) {
                                    $chunkAdd .= $linebreak . '/* '
                                        . $parseFile['name'] . ' */' . $linebreak;
                                }

                                $chunkAdd .= file_get_contents(
                                    $parseFile['internal_path'])
                                    . $linebreak;

                                if ($resourceMode ===
                                    self::DATA_FUNCTIONS
                                ) {

                                    $chunkAdd = self::parseDependency(
                                        $chunkAdd,
                                        $parseFile['name'],
                                        $parseFile['centralized_path'],
                                        $parseFile['modularized_path']
                                    );

                                } else if ($resourceMode ===
                                    self::DATA_DEPENDENCIES
                                ) {

                                    $chunkAdd = self::parseDependency(
                                        $chunkAdd,
                                        $parseFile['name'],
                                        $parseFile['centralized_path'],
                                        $parseFile['modularized_path']
                                    );

                                }

                                if (self::$_minify) {
                                    if ($associativeType == self::TYPE_SCRIPT) {
                                        $chunkAdd =
                                            self::minifyJavascript($chunkAdd);
                                    } else if ($associativeType == self::TYPE_STYLE) {
                                        $chunkAdd =
                                            self::minifyStylesheet($chunkAdd);
                                    }
                                }

                                self::_addChunk($chunkAdd);
                                $parsedBlocks++;

                            }

                            $microtime = explode(' ', microtime());
                            $end = $microtime[0] + $microtime[1];
                            $elapsed = round($end - $start, 4);

                            if (\Aomebo\Configuration::getSetting(
                                'output,add resources statistics tag')
                            ) {
                                self::_addChunk($linebreak . '/* Generated in \''
                                    . $elapsed . '\' seconds. */' . $linebreak);
                            }

                            self::_flushBuffer();

                            if (self::$_useCache) {
                                \Aomebo\Cache\System::saveCache(
                                    $cacheParameters,
                                    $cacheKey,
                                    self::$_cacheContents,
                                    \Aomebo\Cache\System::FORMAT_RAW
                                );
                            }

                        }
                    }

                } else {
                    Throw new \Exception('Malformed data.');
                }
            }

        }

        /**
         * Add chunk to total chunks.
         *
         * @internal
         * @static
         * @param string $chunk
         * @param bool [$flush = false]
         */
        private static function _addChunk($chunk, $flush = false)
        {
            if (!empty($chunk)) {
                self::$_chunkBuffer .= $chunk;
                self::$_chunkSize += (int) strlen($chunk);
                if ($flush
                    || (self::$_chunkSize > self::$_chunkLimit)
                ) {
                    self::_flushBuffer();
                }
            }
        }

        /**
         * @internal
         * @static
         */
        private static function _flushBuffer()
        {
            if (self::$_chunkSize > 0) {
                echo self::$_chunkBuffer;
                if (self::$_useCache) {
                    self::$_cacheContents .=
                        self::$_chunkBuffer;
                }
                self::$_chunkBuffer = '';
                self::$_chunkSize = (int) 0;
            }
        }

        /**
         * @internal
         * @param array $array      Associative array
         * @return array
         */
        private function _getReplaceKeys($array)
        {
            $keys = array();
            if (isset($array)
                && is_array($array)
                && sizeof($array) > 0
            ) {
                foreach ($array as $key => $value)
                {
                    if ($keyName = self::_formatKey($key)) {
                        $keys[] = $keyName;
                    }
                }
            }
            return $keys;
        }

        /**
         * @internal
         * @static
         * @param array $array      Associative array
         * @return array
         */
        private static function _getOldReplaceKeys($array)
        {
            $keys = array();
            if (isset($array)
                && is_array($array)
                && sizeof($array) > 0
            ) {
                foreach ($array as $key => $value)
                {
                    if ($keyName = self::_oldFormatKey($key)) {
                        $keys[] = $keyName;
                    }
                }
            }
            return $keys;
        }

        /**
         * @internal
         * @static
         * @param string $key
         * @return string|bool
         */
        private static function _formatKey($key)
        {
            if (!empty($key)
                && strpos($key, '/') == false
                && strpos($key, '*') == false
            ) {
                return '/* ' . $key . ' */';
            } else {
                return false;
            }
        }

        /**
         * @internal
         * @static
         * @param string $key
         * @return string|bool
         */
        private static function _oldFormatKey($key)
        {
            if (!empty($key)
                && strpos($key, '<') == false
                && strpos($key, '>') == false
            ) {
                return '<' . $key . '>';
            } else {
                return false;
            }
        }

    }
}
