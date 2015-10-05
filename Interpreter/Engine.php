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
namespace Aomebo\Interpreter
{

    /**
     * @method static \Aomebo\Interpreter\Engine getInstance()
     */
    class Engine extends \Aomebo\Singleton
    {

        /**
         * @var string
         */
        const INSERTION_POINT_HEAD_STYLE =
            'insertionPointHeadStyle';

        /**
         * @var string
         */
        const INSERTION_POINT_HEAD_SCRIPT =
            'insertionPointHeadScript';

        /**
         * @var string
         */
        const INSERTION_POINT_HEAD_META =
            'insertionPointHeadMeta';

        /**
         * @var string
         */
        const INSERTION_POINT_BODY_SCRIPT =
            'insertionPointBodyScript';

        /**
         * @var string
         */
        const INSERTION_POINT_BODY_STYLE =
            'insertionPointBodyStyle';

        /**
         * @var string
         */
        const INSERTION_POINT_HEAD_MARKUP =
            'insertionPointHeadMarkup';

        /**
         * @var string
         */
        const INSERTION_POINT_BODY_MARKUP =
            'insertionPointBodyMarkup';

        /**
         * @var int
         */
        const INTERPRETATION_STATUS_OK = 0;

        /**
         * @var int
         */
        const INTERPRETATION_STATUS_RESTART = 1;

        /**
         * @var int
         */
        const INTERPRETATION_STATUS_ABORT = 2;

        /**
         * @internal
         * @static
         * @var int
         */
        private static $_runtimeCallIndex = 0;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_mode = '';

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_runtimeNameToData = array();

        /**
         * @static
         * @internal
         * @var array
         */
        private static $_runtimeNameToObject = array();

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_lastEvaluatedRuntime = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_output = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_headMetaData = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_headStyleData = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_headScriptData = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_bodyStyleData = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_bodyScriptData = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_headMarkupData = '';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_bodyMarkupData = '';

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_insertPoints = array();

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_metaData = array();

        /**
         * @internal
         * @static
         * @var int
         */
        private static $_interpretationStatus = 0;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_useOutputBuffering = true;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_pagesToRuntimes = array();

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_runtimesToPages = array();

        /**
         * @static
         * @internal
         * @var array
         */
        private static $_pagesToData = array();

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_adapters = array();

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_pageSuffixToAdapter = array();

        /**
         * @throws \Exception
         */
        public function __construct()
        {
            if (!self::_isConstructed()) {

                parent::__construct();
                self::_checkMode();
                self::_loadRuntimes();
                self::_loadAdapters();
                self::_loadPages();
                self::_flagThisConstructed();

            }
        }

        /**
         * @static
         * @param string $runtime
         * @return bool|array
         */
        public static function getPagesByRuntime($runtime)
        {
            self::_instanciate();
            if (!empty($runtime)
                && isset(self::$_runtimesToPages[$runtime])
            ) {
                return self::$_runtimesToPages[$runtime];
            }
            return false;
        }

        /**
         * @static
         * @param string $page
         * @return bool|array
         */
        public static function getRuntimesByPage($page)
        {
            self::_instanciate();
            if (!empty($page)
                && isset(self::$_pagesToRuntimes[$page])
            ) {
                return self::$_pagesToRuntimes[$page];
            }
            return false;
        }

        /**
         * @static
         * @param bool $useOutoutBuffering
         * @throws \Exception
         */
        public static function setOutputBuffering($useOutoutBuffering)
        {
            if (isset($useOutoutBuffering)) {
                self::$_useOutputBuffering = (!empty($useOutoutBuffering));
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameter')
                );
            }
        }

        /**
         * This method returns true if parameter is a parameter to parent.
         *
         * @static
         * @param string $runtimeName
         * @param string $parameterName
         * @return bool
         */
        public static function isRuntimeParameter(
            $runtimeName, $parameterName)
        {
            if (isset(self::$_runtimeNameToObject[$runtimeName])
                && isset(self::$_runtimeNameToData
                [$runtimeName]['parameters'][$parameterName])
            ) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * This method returns whether to use
         * output buffering or not.
         *
         * @static
         * @return bool
         */
        public static function getOutputBufferingFlag()
        {
            return self::$_useOutputBuffering;
        }

        /**
         * This method returns brige configuration.
         *
         * @static
         * @param string $name
         * @return array|bool
         */
        public static function getBridgeConfig($name)
        {
            if (!empty(self::$_runtimeNameToData[$name]['bridge_config'])) {
                return self::$_runtimeNameToData[$name]['bridge_config'];
            } else {
                return false;
            }
        }

        /**
         * This method returns mode.
         *
         * @static
         * @return string
         */
        public static function getMode()
        {
            return self::$_mode;
        }

        /**
         * This method returns interpretated output.
         *
         * @static
         * @return string
         */
        public static function getOutput()
        {
            return self::$_output;
        }

        /**
         * This method sets insertion points.
         *
         * @static
         * @param string $name
         * @param int $insertPoint
         */
        public static function setInsertPoint($name, $insertPoint)
        {
            self::$_insertPoints[$name] = $insertPoint;
        }

        /**
         * This method returns insertion point.
         *
         * @static
         * @param string $name
         * @return int|bool
         */
        public static function getInsertPoint($name)
        {
            if (isset(self::$_insertPoints[$name])) {
                return self::$_insertPoints[$name];
            } else {
                return false;
            }
        }

        /**
         * This function moves all insert points after given.
         *
         * @static
         * @param string $name
         * @param int $strlen
         * @return bool
         */
        public static function moveInsertPoint($name, $strlen)
        {
            if (isset(self::$_insertPoints[$name])) {

                $after = self::$_insertPoints[$name];

                foreach (self::$_insertPoints as
                    $insertPointName => $insertPoint
                ) {
                    if ($insertPoint >= $after) {
                       self::$_insertPoints[$insertPointName] += $strlen;
                    }
                }
                return true;
            } else {
                return false;
            }
        }

        /**
         * This method adds header meta data.
         *
         * @static
         * @param string $meta
         */
        public static function addHeadMetaData($meta)
        {
            self::$_headMetaData .= $meta;
        }

        /**
         * This method adds a meta refresh.
         *
         * @static
         * @param string $url
         * @param int $time
         */
        public static function addMetaRefresh($url, $time)
        {
            self::$_headMetaData .=
                '<meta http-equiv="refresh" content="' . $time
                . ';url=' . $url . '" />';
        }

        /**
         * @static
         * @param array $pages
         * @param bool [$replaceExisting = false]
         * @return bool
         * @throws \Exception
         */
        public static function addPages($pages, $replaceExisting = false)
        {
            if (isset($pages)
                && is_array($pages)
                && sizeof($pages) > 0
            ) {
                $accBool = true;
                foreach ($pages as $name => $data)
                {
                    $accBool = ($accBool
                        && self::addPage($name, $data, $replaceExisting)
                    );
                }
                return $accBool;
            } else {
                Throw new \Exception(self::systemTranslate(
                    'Invalid parameters'));
            }
        }

        /**
         * @static
         * @param string $name
         * @param array $data
         * @param bool [$replaceExisting = false]
         * @return bool
         * @throws \Exception
         */
        public static function addPage($name, $data, $replaceExisting = false)
        {
            if (!empty($name)
                && isset($data)
                && is_array($data)
                && sizeof($data) > 0
            ) {
                if (!isset(self::$_pagesToData[$name])
                    || !empty($replaceExisting)
                ) {
                    
                    self::$_pagesToData[$name] = $data;                        
                    return true;
                    
                }
            } else {
                Throw new \Exception(self::systemTranslate(
                    'Invalid parameters'));
            }
            return false;
        }

        /**
         * This method returns header meta data.
         *
         * @static
         * @return string
         */
        public static function getHeadMetaData()
        {
            return self::$_headMetaData;
        }

        /**
         * @static
         * @return string
         */
        public static function getHeadMarkupData()
        {
            return self::$_headMarkupData;
        }

        /**
         * @static
         * @return string
         */
        public static function getBodyMarkupData()
        {
            return self::$_bodyMarkupData;
        }

        /**
         * Alias to addHeadInlineStyleData
         *
         * @static
         * @param string $style
         * @see \Aomebo\Interpreter\Engine::addHeadInlineStyleData()
         */
        public static function addHeadStyleData($style)
        {
            self::addHeadInlineStyleData($style);
        }

        /**
         * This method adds stylesheet data to head.
         *
         * @static
         * @param string $style
         */
        public static function addHeadInlineStyleData($style)
        {
            self::$_headStyleData .=
                self::_buildInlineStyleMarkup($style);
        }

        /**
         * Alias.
         *
         * @static
         * @param string $style
         * @see \Aomebo\Interpreter\Engine::addBodyInlineStyleData()
         */
        public static function addBodyStyleData($style)
        {
            self::addBodyInlineStyleData($style);
        }

        /**
         * @static
         * @param string $style
         */
        public static function addBodyInlineStyleData($style)
        {
            self::$_bodyStyleData .=
                self::_buildInlineStyleMarkup($style);
        }

        /**
         * @static
         * @param string $href
         */
        public static function addBodyExternalStyleData($href)
        {
            self::$_bodyStyleData .=
                self::_buildExternalStyleMarkup($href);
        }

        /**
         * This method adds external stylesheet to head.
         *
         * @static
         * @param string $href
         */
        public static function addHeadExternalStyleData($href)
        {
            self::$_headStyleData .=
                self::_buildExternalStyleMarkup($href);
        }

        /**
         * This method returns head stylesheet data.
         *
         * @static
         * @return string
         */
        public static function getHeadStyleData()
        {
            return self::$_headStyleData;
        }

        /**
         * Alias to addHeadInlineScriptData()
         *
         * @static
         * @param string $script
         * @see \Aomebo\Interpreter\Engine::addHeadInlineScriptData()
         */
        public static function addHeadScriptData($script)
        {
            self::addHeadInlineScriptData($script);
        }

        /**
         * This method returns Runtime if it exists.
         *
         * @static
         * @param string $name
         * @return \Aomebo\Runtime|bool
         */
        public static function getRuntimeByName($name)
        {
            if (isset(self::$_runtimeNameToObject[$name])) {
                return self::$_runtimeNameToObject[$name];
            } else {
                return false;
            }
        }

        /**
         * This method returns number of parameters for Runtime.
         *
         * @static
         * @param string $name
         * @return array|bool
         */
        public static function getRuntimeParameterCountByName($name)
        {
            if (isset(self::$_runtimeNameToObject[$name])
                && isset(self::$_runtimeNameToData[$name]['parameter_count'])
            ) {
                return self::$_runtimeNameToData[$name]['parameter_count'];
            } else {
                return false;
            }
        }

        /**
         * This method adds javascript data to head.
         *
         * @static
         * @param string $script
         */
        public static function addHeadInlineScriptData($script)
        {
            self::$_headScriptData .=
                self::_buildInlineScriptMarkup($script);
        }

        /**
         * Alias.
         *
         * @static
         * @param string $script
         * @see \Aomebo\Interpreter\Engine::addBodyInlineScriptData()
         */
        public static function addBodyScriptData($script)
        {
            self::addBodyInlineScriptData($script);
        }

        /**
         * This method adds javascript data to head.
         *
         * @static
         * @param string $source
         */
        public static function addHeadExternalScriptData($source)
        {
            self::$_headScriptData .=
                self::_buildExternalScriptMarkup($source);
        }

        /**
         * @static
         * @param string $markup
         */
        public static function addHeadMarkupData($markup)
        {
            self::$_headMarkupData .=
                $markup;
        }

        /**
         * @static
         * @param string $markup
         */
        public static function addBodyMarkupData($markup)
        {
            self::$_bodyMarkupData .=
                $markup;
        }

        /**
         * This method adds javascript data to body.
         *
         * @static
         * @param string $source
         */
        public static function addBodyExternalScriptData($source)
        {
            self::$_bodyScriptData .=
                self::_buildExternalScriptMarkup($source);
        }

        /**
         * This method adds javascript data to body.
         *
         * @static
         * @param string $source
         */
        public static function addBodyInlineScriptData($source)
        {
            self::$_bodyScriptData .=
                self::_buildInlineScriptMarkup($source);
        }

        /**
         * This method returns javascript data from head.
         *
         * @static
         * @return string
         */
        public static function getHeadScriptData()
        {
            return self::$_headScriptData;
        }

        /**
         * @static
         * @return string
         */
        public static function getBodyScriptData()
        {
            return self::$_bodyScriptData;
        }

        /**
         * @static
         * @return string
         */
        public static function getBodyStyleData()
        {
            return self::$_bodyStyleData;
        }

        /**
         * This method is just for internal processing.
         *
         * @internal
         * @static
         * @param string $key
         * @param mixed $value
         * @return void
         */
        public static function setMetaData($key, $value)
        {
            self::$_metaData[$key] = $value;
        }

        /**
         * This method returns meta data if any.
         *
         * @static
         * @param string $key
         * @return bool
         */
        public static function getMetaData($key)
        {
            return (isset(self::$_metaData[$key]) ? self::$_metaData[$key] : '');
        }

        /**
         * @static
         */
        public static function incrementRuntimeCallIndex()
        {
            self::$_runtimeCallIndex++;
        }

        /**
         * @static
         * @return int
         */
        public static function getRuntimeCallIndex()
        {
            return self::$_runtimeCallIndex;
        }

        /**
         * This method interprets the whole tree of page-contents.
         *
         * @static
         * @throws \Exception
         * @return bool
         */
        public static function interpret()
        {

            \Aomebo\Trigger\System::processTriggers(
                \Aomebo\Trigger\System::TRIGGER_KEY_BEFORE_INTERPRETATION);

            if (self::$_interpretationStatus !=
                self::INTERPRETATION_STATUS_ABORT
            ) {
                if ($processed = self::_process()) {

                    self::setInterpretationStatus(self::INTERPRETATION_STATUS_OK);
                    self::$_headMetaData = '';
                    self::$_headStyleData = '';
                    self::$_headScriptData = '';
                    self::$_headMarkupData = '';
                    self::$_bodyStyleData = '';
                    self::$_bodyScriptData = '';
                    self::$_bodyMarkupData = '';
                    self::$_runtimeCallIndex = 0;
                    self::$_lastEvaluatedRuntime = null;
                    self::$_insertPoints = array();
                    self::$_output = self::_interpretNode($processed);

                    if (self::_hasOKStatus()) {

                        \Aomebo\Trigger\System::processTriggers(
                            \Aomebo\Trigger\System::TRIGGER_KEY_AFTER_INTERPRETATION);

                        $session = \Aomebo\Session\Handler::getInstance();
                        $session->processEvaluation();

                        self::_setInsertionPoints();

                        return true;

                    } else {

                        if (self::$_interpretationStatus ==
                            self::INTERPRETATION_STATUS_RESTART
                        ) {
                            return self::interpret();
                        } else if (self::$_interpretationStatus ==
                            self::INTERPRETATION_STATUS_ABORT
                        ) {
                        } else {
                            Throw new \Exception(
                                self::systemTranslate('Invalid interpretation status')
                            );
                        }

                    }

                } else {
                    Throw new \Exception(
                        self::systemTranslate(
                            "Couldn't process page."
                        )
                    );
                }
            }

            return false;

        }

        /**
         * @internal
         * @static
         */
        private static function _setInsertionPoints()
        {

            // head meta
            if (!isset(self::$_insertPoints[
                self::INSERTION_POINT_HEAD_META])
            ) {
                if ($pos = stripos(self::$_output, '<title')) {
                    self::setInsertPoint(
                        self::INSERTION_POINT_HEAD_META,
                        $pos - 1);
                } else  {
                    unset(self::$_insertPoints[
                        self::INSERTION_POINT_HEAD_META]);
                }
            }

            // head script
            if (!isset(self::$_insertPoints[
                self::INSERTION_POINT_HEAD_SCRIPT])
            ) {
                if ($pos = stripos(self::$_output, '</title')) {
                    self::setInsertPoint(
                        self::INSERTION_POINT_HEAD_SCRIPT,
                        $pos + 9);
                } else  {
                    unset(self::$_insertPoints[
                        self::INSERTION_POINT_HEAD_SCRIPT]);
                }
            }

            // head style
            if (!isset(self::$_insertPoints[
                self::INSERTION_POINT_HEAD_STYLE])
            ) {
                if ($pos = stripos(self::$_output, '</title')) {
                    self::setInsertPoint(
                        self::INSERTION_POINT_HEAD_STYLE,
                        $pos + 9);
                } else {
                    unset(self::$_insertPoints[
                        self::INSERTION_POINT_HEAD_STYLE]);
                }
            }

            // head markup
            if (!isset(self::$_insertPoints[
            self::INSERTION_POINT_HEAD_MARKUP])
            ) {
                if ($pos = stripos(self::$_output, '</title')) {
                    self::setInsertPoint(
                        self::INSERTION_POINT_HEAD_MARKUP,
                        $pos + 9);
                } else {
                    unset(self::$_insertPoints[
                    self::INSERTION_POINT_HEAD_MARKUP]);
                }
            }

            // body script
            if (!isset(self::$_insertPoints[self::INSERTION_POINT_BODY_SCRIPT])) {
                if ($pos = stripos(self::$_output, '<body')) {
                    if ($pos = strpos(self::$_output, '>', $pos)) {
                        self::setInsertPoint(
                            self::INSERTION_POINT_BODY_SCRIPT,
                            $pos + 1);
                    }
                } else  {
                    unset(self::$_insertPoints[
                        self::INSERTION_POINT_BODY_SCRIPT]);
                }
            }

            // body style
            if (!isset(self::$_insertPoints[self::INSERTION_POINT_BODY_STYLE])) {
                if ($pos = stripos(self::$_output, '<body')) {
                    if ($pos = strpos(self::$_output, '>', $pos)) {
                        self::setInsertPoint(
                            self::INSERTION_POINT_BODY_STYLE,
                            $pos + 1);
                    }
                } else  {
                    unset(self::$_insertPoints[
                        self::INSERTION_POINT_BODY_STYLE]);
                }
            }

            // body markup
            if (!isset(self::$_insertPoints[self::INSERTION_POINT_BODY_MARKUP])) {
                if ($pos = stripos(self::$_output, '<body')) {
                    if ($pos = strpos(self::$_output, '>', $pos)) {
                        self::setInsertPoint(
                            self::INSERTION_POINT_BODY_MARKUP,
                            $pos + 1);
                    }
                } else  {
                    unset(self::$_insertPoints[
                    self::INSERTION_POINT_BODY_MARKUP]);
                }
            }

        }

        /**
         * This method sets flag to restart interpretation.
         *
         * @static
         */
        public static function restartInterpretation()
        {
            self::setInterpretationStatus(
                self::INTERPRETATION_STATUS_RESTART);
        }

        /**
         * This method sets flag to abort interpretation.
         *
         * @static
         */
        public static function abortInterpretation()
        {
            self::setInterpretationStatus(
                self::INTERPRETATION_STATUS_ABORT);
        }

        /**
         * @static
         * @param int $status
         */
        public static function setInterpretationStatus($status)
        {
            self::$_interpretationStatus = $status;
        }

        /**
         * This method returns whether or not status is ok.
         *
         * @internal
         * @static
         * @return bool
         */
        private static function _hasOKStatus()
        {
            return (self::$_interpretationStatus ==
                self::INTERPRETATION_STATUS_OK);
        }

        /**
         * This method interprets collects runtimes in a single node in tree,
         * recursively.
         *
         * @internal
         * @static
         * @param array|string & $node
         * @param array|null [& $parent = null]
         * @param array [& $runtimes = array()]
         * @throws \Exception
         * @return string|bool
         */
        private static function _collectNodeRuntimes(
            & $node,
            & $parent = null,
            & $runtimes = array())
        {
            if (is_array($node)) {
                foreach ($node as $child)
                {
                    if (is_array($child)) {
                        if (isset($child[\Aomebo\Interpreter\Adapters\Base::FIELD_KEY],
                            $child[\Aomebo\Interpreter\Adapters\Base::FIELD_VALUE])
                        ) {
                            $key =
                                strtolower($child[\Aomebo\Interpreter\Adapters\Base::FIELD_KEY]);
                            $value =
                                $child[\Aomebo\Interpreter\Adapters\Base::FIELD_VALUE];
                            if (self::_isRuntimeName($key)) {
                                if (!isset($runtimes[$key])) {
                                    $runtimes[$key] = $key;
                                }
                                if (is_array($value)) {
                                    self::_collectNodeRuntimes(
                                        $value,
                                        $key,
                                        $runtimes
                                    );
                                } else {
                                    $lowValue = strtolower($value);
                                    if (!empty($value)) {
                                        if (self::_isRuntimeName($lowValue)) {
                                            if (!isset($runtimes[$lowValue])) {
                                                $runtimes[$lowValue] = $lowValue;
                                            }
                                        }
                                    }
                                }
                            } else if (isset($parent)
                                && self::isRuntimeParameter(
                                    $parent, $key)
                            ) {
                                self::_collectNodeRuntimes(
                                    $value,
                                    $parent,
                                    $runtimes
                                );
                            } else {
                                Throw new \Exception(
                                    sprintf(
                                        self::systemTranslate(
                                            '"%s" is neither a Runtime or a Runtime '
                                            . 'parameter to "%s". '
                                            . 'Loaded Runtimes: "%s"'
                                        ),
                                        $key,
                                        $parent,
                                        print_r(self::$_runtimeNameToObject, true)
                                    )
                                );
                            }
                        } else {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate(
                                        'Invalid array: "%s"'
                                    ),
                                    print_r($child, true)
                                )
                            );
                        }
                    } else {
                        Throw new \Exception(
                            sprintf(
                                self::systemTranslate('"%s" is not an array.'),
                                $child
                            )
                        );
                    }
                }
                if (isset($parent)
                    && self::_isRuntimeName($parent)
                ) {
                    if (!isset($runtimes[$parent])) {
                        $runtimes[$parent] = $parent;
                    }
                }
            } else {
                if (self::_isRuntimeName($node)) {
                    if (!isset($runtimes[$node])) {
                        $runtimes[$node] = $node;
                    }
                }
            }
        }

        /**
         * This method interprets a single node in tree, recursively.
         *
         * @internal
         * @static
         * @param array|string $node
         * @param array|null [$parent = null]
         * @throws \Exception
         * @return string|bool
         */
        private static function _interpretNode(& $node, & $parent = null)
        {
            if (self::_hasOKStatus()) {

                $output = '';
                $parameters = array();
                self::$_lastEvaluatedRuntime = null;

                if (is_array($node)) {
                    foreach ($node as $child)
                    {
                        if (is_array($child)) {
                            if (isset($child[\Aomebo\Interpreter\Adapters\Base::FIELD_KEY],
                                $child[\Aomebo\Interpreter\Adapters\Base::FIELD_VALUE])
                            ) {
                                $key =
                                    strtolower($child[\Aomebo\Interpreter\Adapters\Base::FIELD_KEY]);
                                $value =
                                    $child[\Aomebo\Interpreter\Adapters\Base::FIELD_VALUE];
                                if (self::_isRuntimeName($key)) {
                                    if (is_array($value)) {
                                        $output .= self::
                                            _interpretNode($value, $key);
                                    } else {
                                        $lowValue = strtolower($value);
                                        if (!empty($value)) {
                                            if (self::_isRuntimeName($lowValue)) {
                                                $output .=
                                                    self::_evaluateRuntime($key,
                                                        self::_evaluateRuntime($lowValue));
                                            } else {
                                                $output .= self::
                                                    _evaluateRuntime($key, $value);
                                            }
                                        } else {
                                            $output .= self::
                                                _evaluateRuntime($key);
                                        }
                                    }
                                } else if (isset($parent)
                                    && self::isRuntimeParameter(
                                    $parent, $key)
                                ) {
                                    $parameters[$key] =
                                        self::_interpretNode($value);
                                } else {
                                    Throw new \Exception(
                                        sprintf(
                                            self::systemTranslate(
                                                '"%s" is neither a Runtime or a Runtime '
                                                . 'parameter to "%s". '
                                                . 'Loaded Runtimes: "%s"'
                                            ),
                                            $key,
                                            $parent,
                                            print_r(self::$_runtimeNameToObject, true)
                                        )
                                    );
                                }
                            } else {
                                Throw new \Exception(
                                    sprintf(
                                        self::systemTranslate(
                                            'Invalid array: "%s"'
                                        ),
                                        print_r($child, true)
                                    )
                                );
                            }
                        } else {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate('"%s" is not an array.'),
                                    $child
                                )
                            );
                        }
                    }
                    if (isset($parent)
                        && self::_isRuntimeName($parent)
                    ) {
                        return self::_evaluateRuntime(
                            $parent, $parameters, $output);
                    } else {
                        return $output;
                    }
                } else {
                    if (self::_isRuntimeName($node)) {
                        return self::_evaluateRuntime($node);
                    } else {
                        return $node;
                    }
                }
            } else {
                return false;
            }
        }

        /**
         * This method gathers all runtimes in a node, recursively.
         *
         * @internal
         * @static
         * @param mixed $node
         * @param mixed [$parent = null]
         * @param array [$runtimes = array()]
         * @throws \Exception
         * @return string|bool
         */
        private static function _getNodeRuntimes(& $node,
            & $parent = null, & $runtimes = array())
        {
            if (self::_hasOKStatus()) {
                if (is_array($node)) {
                    foreach ($node as $child)
                    {
                        if (is_array($child)) {
                            if (isset($child[Adapters\Base::FIELD_KEY],
                                $child[Adapters\Base::FIELD_VALUE])
                            ) {
                                $key = strtolower($child[Adapters\Base::FIELD_KEY]);
                                $value = $child[Adapters\Base::FIELD_VALUE];
                                if (self::_isRuntimeName($key)) {
                                    if (!isset($runtimes[$key])) {
                                        $runtimes[$key] = $key;
                                    }
                                    if (is_array($value)) {
                                        self::_getNodeRuntimes($value, $key, $runtimes);
                                    } else {
                                        $lowValue = strtolower($value);
                                        if (!empty($value)) {
                                            if (self::_isRuntimeName($lowValue)) {
                                                if (!isset($runtimes[$lowValue])) {
                                                    $runtimes[$lowValue] = $lowValue;
                                                }
                                            }
                                        }
                                    }
                                } else if (isset($parent)
                                    && self::isRuntimeParameter(
                                        $parent, $key)
                                ) {
                                    self::_getNodeRuntimes($value);
                                } else {
                                    Throw new \Exception(
                                        sprintf(
                                            self::systemTranslate(
                                                '"%s" is neither a Runtime or a Runtime '
                                                . 'parameter to "%s". '
                                                . 'Loaded Runtimes: "%s"'
                                            ),
                                            $key,
                                            $parent,
                                            print_r(self::$_runtimeNameToObject, true)
                                        )
                                    );
                                }
                            } else {
                                Throw new \Exception(
                                    sprintf(
                                        self::systemTranslate(
                                            'Invalid array: "%s"'
                                        ),
                                        print_r($child, true)
                                    )
                                );
                            }
                        } else {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate('"%s" is not an array.'),
                                    $child
                                )
                            );
                        }
                    }
                    if (isset($parent)
                        && self::_isRuntimeName($parent)
                    ) {
                        if (!isset($runtimes[$parent])) {
                            $runtimes[$parent] = $parent;
                        }
                    } else {
                        return $runtimes;
                    }
                } else {
                    if (self::_isRuntimeName($node)) {
                        if (!isset($runtimes[$node])) {
                            $runtimes[$node] = $node;
                        }
                    }
                }
            } else {
                return false;
            }
            return false;
        }

        /**
         * This method evaluates a Runtime with parameters.
         *
         * @internal
         * @static
         * @param string $name
         * @param mixed [$parameters = null]
         * @param string [$output = null]
         * @throws \Exception
         * @return string|bool
         */
        private static function _evaluateRuntime($name,
            $parameters = null, $output = null)
        {
            if (self::_hasOKStatus()) {
                if ($runtime =
                    self::getRuntimeByName($name)
                ) {

                    $runtime->executeRuntime($output,
                        $parameters, self::$_lastEvaluatedRuntime);

                    self::$_lastEvaluatedRuntime = $runtime;

                    return $runtime->getField('output');

                } else {
                    Throw new \Exception(
                        sprintf(
                            self::systemTranslate('"%s" is not a valid '
                                . 'Runtime for evaluation.'
                            ),
                            $name
                        )
                    );
                }
            } else {
                return false;
            }
        }

        /**
         * This method starts interpreting page.
         *
         * @internal
         * @static
         * @throws \Exception
         * @return string|bool
         */
        private static function _process()
        {

            $dispatcher =
                \Aomebo\Dispatcher\System::getInstance();

            try
            {

                if ($dispatcher::isAjaxRequest()) {

                    $pageData = $dispatcher->getCurrentAjaxPageData();

                    /**
                     * Cache parameters, unique per:
                     * - Ajax request
                     * - Page data
                     */
                    $cacheParameters = 'InterpreterEngine/AjaxRequest';

                    /**
                     * Cache key, unique per:
                     * - Page data
                     */
                    $cacheKey = md5($pageData);

                    if (\Aomebo\Cache\System::cacheExists(
                        $cacheParameters,
                        $cacheKey)
                    ) {
                        return
                            \Aomebo\Cache\System::loadCache(
                                $cacheParameters,
                                $cacheKey,
                                \Aomebo\Cache\System::FORMAT_JSON_ENCODE);
                    } else {

                        \Aomebo\Cache\System::clearCache(
                            $cacheParameters,
                            $cacheKey,
                            \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                        );
                        
                        /**
                         * Only allow Xml page-data to be sent using AJAX because otherwise we would be possible 
                         * to send php via ajax which would be evaluated.
                         */
                        
                        if ($processed = self::_processPageData(
                            $pageData, 
                            self::$_adapters['Xml'])
                        ) {

                            \Aomebo\Cache\System::saveCache(
                                $cacheParameters,
                                $cacheKey,
                                $processed,
                                \Aomebo\Cache\System::FORMAT_JSON_ENCODE);

                            return $processed;

                        } else {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate(
                                        'Could not process page from ajax request %s'
                                    ),
                                    $pageData
                                )
                            );
                        }

                    }

                } else {

                    // Is ordinary page request
                    if ($page = $dispatcher::getPage()) {

                        return self::_processPage(
                            $page,
                            self::_getPagesDirectory() . $page
                        );

                    } else {
                        Throw new \Exception(
                            sprintf(
                                self::systemTranslate(
                                    'Found no page to interpret for request '
                                    . 'GET: %s, POST: %s, SERVER: %s'
                                ),
                                $_GET,
                                $_POST,
                                $_SERVER
                            )
                        );
                    }
                }
            } catch (\Exception $e) {
                Throw new \Exception(
                    sprintf(
                        self::systemTranslate(
                            'Something went wrong when starting interpretation, error: "%s"'
                        ),
                        $e->getMessage()
                    )
                );
            }
        }

        /**
         * @static
         * @param string $page
         * @param string $path
         * @param bool [$returnContents = true]
         * @return array
         * @throws \Exception
         */
        private static function _processPage($page, $path, 
            $returnContents = true)
        {
            if (!empty($page)
                && !empty($path)
            ) {
                if (!isset(self::$_pagesToData[$page])) {

                    /* Cache parameters, unique per:
                     * - Normal request
                     * - Page
                     */
                    $cacheParameters =
                        'InterpreterEngine/NormalRequest/' . md5($page);

                    /**
                     * Cache key, unique per:
                     * - Page last modified
                     * - This file modified
                     */
                    $cacheKey = md5('page_mod='
                        . \Aomebo\Filesystem::getFileLastModificationTime(
                        $path, false) . '&sys_mod=' . filemtime(__FILE__)
                    );

                    if (\Aomebo\Cache\System::cacheExists(
                        $cacheParameters,
                        $cacheKey)
                    ) {

                        self::$_pagesToData[$page] = \Aomebo\Cache\System::loadCache(
                            $cacheParameters,
                            $cacheKey,
                            \Aomebo\Cache\System::FORMAT_JSON_ENCODE
                        );

                        return (!empty($returnContents) ?
                            self::$_pagesToData[$page] : true);

                    } else {

                        \Aomebo\Cache\System::clearCache(
                            $cacheParameters,
                            null,
                            \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                        );
                        
                        $pageData = '';
                        $adapter = null;
                        foreach (self::$_adapters as $adapter)
                        {
                            
                            /** @var \Aomebo\Interpreter\Adapters\Base $adapter */
                            
                            if (file_exists($path . $adapter->getFileSuffix())) {
                                $pageData = \Aomebo\Filesystem::getFileContents(
                                    $path  . $adapter->getFileSuffix());
                                break;
                            }
                        }
                        
                        if (isset($adapter)
                            && !empty($pageData)
                        ) {
                            if ($processed = self::_processPageData(
                                $pageData, $adapter)
                            ) {

                                \Aomebo\Cache\System::saveCache(
                                    $cacheParameters,
                                    $cacheKey,
                                    $processed,
                                    \Aomebo\Cache\System::FORMAT_JSON_ENCODE
                                );

                                self::$_pagesToData[$page] = $processed;

                                return (!empty($returnContents) ?
                                    self::$_pagesToData[$page] : true);

                            } else {
                                Throw new \Exception(
                                    sprintf(
                                        self::systemTranslate('Could not process page at: "%s", pageData: "%s"'),
                                        $path,
                                        $pageData
                                    )
                                );
                            }
                        } else {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate('Failed to find page "%s" with any adapter'),
                                    $page
                                )
                            );
                        }
                    }
                } else {
                    if (!empty($returnContents)) {
                        return self::$_pagesToData[$page];
                    } else {
                        return true;
                    }
                }
            } else {
                Throw new \Exception(
                    self::systemTranslate('Invalid parameters')
                );
            }
        }

        /**
         * @static
         * @param string $page
         * @param string $path
         * @param array|null [$contents = null]
         * @throws \Exception
         */
        private static function _processPageRuntimes($page, $path, 
            $contents = null)
        {
            if (!isset(self::$_pagesToRuntimes[$page])) {

                /* Cache parameters, unique per:
                 * - Normal request
                 * - Page
                 */
                $cacheParameters =
                    'InterpreterEngine/PageToRuntimes/' . md5($page);

                /**
                 * Cache key, unique per:
                 * - Page last modified
                 * - This file modified
                 */
                $cacheKey = md5('page_mod=' .
                    \Aomebo\Filesystem::getFileLastModificationTime($path, false)
                    . '&sys_mod=' . filemtime(__FILE__)
                );

                if (\Aomebo\Cache\System::cacheExists(
                    $cacheParameters,
                    $cacheKey)
                ) {
                    self::$_pagesToRuntimes[$page] =
                        \Aomebo\Cache\System::loadCache(
                            $cacheParameters,
                            $cacheKey,
                            \Aomebo\Cache\System::FORMAT_JSON_ENCODE
                        );
                } else {

                    \Aomebo\Cache\System::clearCache(
                        $cacheParameters,
                        null,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    );

                    $runtimes = array();
                    $parent = null;
                    
                    if (!isset($contents)) {
                        $contents = self::_processPage(
                            $page,
                            $path,
                            true
                        );
                    }

                    self::_collectNodeRuntimes(
                        $contents,
                        $parent,
                        $runtimes
                    );

                    \Aomebo\Cache\System::saveCache(
                        $cacheParameters,
                        $cacheKey,
                        $runtimes,
                        \Aomebo\Cache\System::FORMAT_JSON_ENCODE,
                        \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_FILESYSTEM
                    );

                    self::$_pagesToRuntimes[$page] = $runtimes;

                }
            }
        }

        /**
         * @internal
         * @static
         */
        private static function _processRuntimePages()
        {
            foreach (self::$_pagesToRuntimes as $page => $runtimes)
            {
                foreach ($runtimes as $runtime => $ignore)
                {
                    if (!isset(self::$_runtimesToPages[$runtime])) {
                        self::$_runtimesToPages[$runtime] = array();
                    }
                    if (!isset(self::$_runtimesToPages[$runtime][$page])) {
                        self::$_runtimesToPages[$runtime][$page] = $page;
                    }
                }
            }
        }

        /**
         * @internal
         * @static
         * @param string $pageData
         * @param \Aomebo\Interpreter\Adapters\Base $adapter
         * @return string|null
         * @throws \Exception
         */
        private static function _processPageData($pageData, $adapter)
        {
            
            if (isset($adapter)
                && !empty($pageData)
            ) {
                try {
                    
                    if (method_exists(
                        $adapter, 
                        'applyDefaultEncapsulation')
                    ) {
                        $pageData = $adapter->applyDefaultEncapsulation($pageData);
                    }
    
                    if ($processed = 
                        $adapter->process($pageData)
                    ) {
                        return $processed;    
                    }
    
                } catch (\Exception $e) {}
            } else {
                Throw new \Exception(self::systemTranslate('Invalid parameters'));
            }

            return null;

        }

        /**
         * This method checks what mode we are in.
         *
         * @internal
         * @static
         */
        private static function _checkMode()
        {
        }

        /**
         * This method loads the default adapter.
         *
         * @internal
         * @static
         * @throws \Exception
         */
        private static function _loadAdapters()
        {
            
            self::$_adapters = array();
            
            if ($items = scandir(__DIR__ . '/Adapters/')) {
                foreach ($items as $item)
                {
                    if ($item != '.'
                        && $item != '..'
                        && is_dir(__DIR__ . '/Adapters/' . $item)
                    ) {
                        $adapter = $item;
                        try {

                            $className = '\\Aomebo\\Interpreter\\Adapters\\'
                                . $adapter . '\\Adapter';

                            /** @var \Aomebo\Interpreter\Adapters\Base $adapterClass  */
                            $adapterClass = new $className();

                            self::$_adapters[$adapter] = $adapterClass;
                            
                            if ($suffix = $adapterClass->getFileSuffix()) {
                                self::$_pageSuffixToAdapter[$suffix] = 
                                    & self::$_adapters[$adapter]; 
                            }

                        } catch (\Exception $e) {
                            Throw new \Exception(
                                sprintf(
                                    self::systemTranslate('Failed to load default adapter: Error: %s'),
                                    $e->getMessage()
                                )
                            );
                        }
                    }
                }
            }
        }

        /**
         * This method should load all pages and store in
         * which pages each runtime exists and also
         * create caches for each page.
         *
         * @internal
         * @static
         */
        private static function _loadPages()
        {
            
            // Is there any pages which hasn't been processed yet and added dynamically?
            if (sizeof(self::$_pagesToData) > 0) {
                foreach (self::$_pagesToData as $page => $data)
                {
                    
                    $parent = null;
                    $runtimes = array();
                    
                    self::_collectNodeRuntimes(
                        $data,
                        $parent,
                        $runtimes
                    );

                    self::$_pagesToRuntimes[$page] = $runtimes;

                }

            }
            
            $pagesDir = self::_getPagesDirectory();
            
            if ($files = scandir($pagesDir))
            {
                foreach ($files as $file)
                {
                    if (!empty($file)
                        && $file != '.'
                        && $file != '..'
                    ) {
                        
                        $pageSuffix = 
                            strtolower(substr($file, strpos($file, '.')));
                        
                        if (isset(self::$_pageSuffixToAdapter[$pageSuffix])) {

                            $page = substr($file, 0, -strlen($pageSuffix));

                            $contents = self::_processPage(
                                $page,
                                $pagesDir . $page,
                                true
                            );

                            self::_processPageRuntimes(
                                $page,
                                $pagesDir . $page,
                                $contents
                            );
                            
                            if ($pageSuffix == '.xml'
                                && \Aomebo\Configuration::getSetting(
                                    'interpreter,convert_xml_pages_to_php')
                            ) {
                                if (self::_convertToPhpPage(
                                    $page, 
                                    $pagesDir,
                                    self::$_pagesToData[$page])
                                ) {
                                    
                                    // Delete XML file here because php pages are faster
                                    \Aomebo\Filesystem::deleteFile(
                                        $pagesDir . $file
                                    );
                                    
                                }
                            }

                        }
                    }
                }
            }

            self::_processRuntimePages();

        }

        /**
         * @internal
         * @static
         * @param string $name
         * @param string $dir
         * @param array $data
         * @return bool
         * @throws \Exception
         */
        private static function _convertToPhpPage($name, $dir, $data)
        {
            if (!empty($name)
                && !empty($dir)
                && isset($data)
            ) {
                if (\Aomebo\Configuration::savePhpConfigurationFile(
                    $dir . $name . '.php',
                    $data,
                    'page')
                ) {
                    return true;
                }
            }
            return false;
        }

        /**
         * This method builts a list of runtimes loaded.
         *
         * @internal
         * @static
         */
        private static function _loadRuntimes()
        {
            if ($runtimes = \Aomebo\Application::getRuntimes()) {
                foreach ($runtimes as & $runtime)
                {

                    /** @var \Aomebo\Runtime $runtime */

                    if ($runtime->isExecutable()) {

                        $name = strtolower($runtime->getField('name'));

                        self::$_runtimeNameToData[$name]['parameters'] =
                            $runtime->getFieldsToIndex();
                        self::$_runtimeNameToObject[$name] =
                            & $runtime;
                        self::$_runtimeNameToData[$name]['parameter_count'] =
                            sizeof(self::$_runtimeNameToData[$name]['parameters']);

                    }

                }
            }
        }

        /**
         * This method returns true if Runtime name exists.
         *
         * @internal
         * @static
         * @param string $name
         * @return bool
         */
        private static function _isRuntimeName($name)
        {
            if (isset(self::$_runtimeNameToObject[$name])) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * @internal
         * @static
         * @param string $src
         * @return string
         */
        private static function _buildExternalScriptMarkup($src)
        {
            return '<script type="text/javascript" src="'
            . $src . '"></script>';
        }

        /**
         * @internal
         * @static
         * @param string $code
         * @return string
         */
        private static function _buildInlineScriptMarkup($code)
        {
            return '<script type="text/javascript">'
            . $code . '</script>';
        }

        /**
         * @internal
         * @static
         * @param string $style
         * @return string
         */
        private static function _buildInlineStyleMarkup($style)
        {
            return '<style type="text/css">' . $style . '</style>';
        }

        /**
         * @internal
         * @static
         * @param string $href
         * @return string
         */
        private static function _buildExternalStyleMarkup($href)
        {
            return '<link rel="stylesheet" href="' . $href . '" />';
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getPagesDirectory()
        {
            return _SITE_ROOT_
                . \Aomebo\Configuration::getSetting('paths,pages dir')
                . DIRECTORY_SEPARATOR;
        }

        /**
         * @internal
         * @static
         */
        private static function _instanciate()
        {
            if (!self::_isConstructed()) {
                self::getInstance(__CLASS__);
            }
        }

    }
}
