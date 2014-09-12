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
namespace Aomebo\Presenter
{

    /**
     * @method static \Aomebo\Presenter\Engine getInstance()
     */
    class Engine extends \Aomebo\Singleton
    {

        /**
         * @internal
         * @static
         * @var string|null
         */
        private static $_contentTypeMime;

        /**
         * @internal
         * @static
         * @var string|null
         */
        private static $_contentTypeCharset;

        /**
         * @static
         * @param string $mime
         */
        public static function setContentTypeMime($mime)
        {
            if (!empty($mime)) {
                self::$_contentTypeMime = $mime;
            }
        }

        /**
         * @static
         * @return bool|string
         */
        public static function getContentTypeMime()
        {
            if (isset(self::$_contentTypeMime)) {
                return self::$_contentTypeMime;
            }
            return false;
        }

        /**
         * @static
         * @param string $charset
         */
        public static function setContentTypeCharset($charset)
        {
            if (!empty($charset)) {
                self::$_contentTypeCharset = $charset;
            }
        }

        /**
         * @static
         * @return string|bool
         */
        public static function getContentTypeCharset()
        {
            if (isset(self::$_contentTypeCharset)) {
                return self::$_contentTypeCharset;
            }
            return false;
        }

        /**
         *
         */
        public function __construct()
        {

            if (!self::_isConstructed()) {

                parent::__construct();

                if (!self::getContentTypeCharset()) {
                    self::setContentTypeCharset(
                        \Aomebo\Configuration::getSetting('output,character set'));
                }

                if (!self::getContentTypeMime()) {
                    self::setContentTypeMime(
                        \Aomebo\Configuration::getSetting('output,mime'));
                }

                self::_flagThisConstructed();

            }

        }

        /**
         *
         */
        public function output()
        {

            \Aomebo\Trigger\System::processTriggers(
                \Aomebo\Trigger\System::TRIGGER_KEY_BEFORE_PRESENTATION);

            $interpreter =
                \Aomebo\Interpreter\Engine::getInstance();
            $associatives =
                \Aomebo\Associatives\Engine::getInstance();

            $tagPresentationData =
                self::_getTagPresentationData();

            $PE_tag_starts = array();
            $PE_tag_ends = array();

            foreach ($tagPresentationData as
                     $rawTag => $rawIndentations
            ) {
                $tag = strtolower($rawTag);
                $indentations = (int) $rawIndentations;
                $negTag = '/' . $tag;
                $negIndentations = (-1) * $indentations;
                $PE_tag_starts[$tag] = $indentations;
                $PE_tag_ends[$negTag] = $negIndentations;
            }

            $PE["buffer"] = "";

            $PE["chunks"] = array(0 => "");
            $PE["chunks count"] = 0;
            $PE["chunk size"] = 0;
            $PE["chunk size limit"] =
                \Aomebo\Configuration::getSetting('output,chunk size');

            // Encoding-specific characters
            $PE["lB"] =
                \Aomebo\Configuration::getSetting("output,linebreak character");
            $PE["lS"] =
                \Aomebo\Configuration::getSetting("output,tab character");

            $PE["status"] = 0;
            $PE["level"] = 0;
            $PE["tag"] = "";
            $PE["block"] = "";

            // Load print buffer from interpreter-engine
            $PE["print buffer"] = $interpreter->getOutput();

            $PE["lastIsTag"] = false;

            // Head style
            if ($associativesData =
                $associatives->getAssociativeData()
            ) {
                $associativesDataStrlen =
                    strlen($associativesData);
                if ($insertPoint =
                    $interpreter->getInsertPoint(
                        $interpreter::INSERTION_POINT_HEAD_STYLE)
                ) {
                    $PE['print buffer'] =
                        substr($PE['print buffer'], 0,
                            $insertPoint)
                        . $associativesData
                        . substr($PE['print buffer'],
                            $insertPoint);
                    $interpreter->moveInsertPoint(
                        $interpreter::INSERTION_POINT_HEAD_STYLE,
                        $associativesDataStrlen);
                }
            }

            // Head meta
            if ($metaData = $interpreter->getHeadMetaData()) {
                $metaDataStrlen =
                    strlen($metaData);
                $PE['has meta'] = true;
                if ($insertPoint =
                    $interpreter->getInsertPoint(
                        $interpreter::INSERTION_POINT_HEAD_META)
                ) {
                    $PE['print buffer'] =
                        substr($PE['print buffer'], 0,
                            $insertPoint)
                        . $metaData
                        . substr($PE['print buffer'],
                            $insertPoint);
                    $interpreter->moveInsertPoint(
                        $interpreter::INSERTION_POINT_HEAD_META,
                        $metaDataStrlen);
                }
            } else {
                $PE['has meta'] = false;
            }

            // Head style
            if ($styleData = $interpreter->getHeadStyleData()) {
                $styleDataStrlen =
                    strlen($styleData);
                $PE['has style'] = true;
                if ($insertPoint =
                    $interpreter->getInsertPoint(
                        $interpreter::INSERTION_POINT_HEAD_STYLE)
                ) {
                    $PE['print buffer'] =
                        substr($PE['print buffer'], 0,
                            $insertPoint)
                        . $styleData
                        . substr($PE['print buffer'],
                            $insertPoint);
                    $interpreter->moveInsertPoint(
                        $interpreter::INSERTION_POINT_HEAD_STYLE,
                        $styleDataStrlen);
                }
            } else {
                $PE['has style'] = false;
            }

            // Head script
            if ($scriptData = $interpreter->getHeadScriptData()) {
                $scriptDataStrlen =
                    strlen($scriptData);
                $PE['has script'] = true;
                if ($insertPoint =
                    $interpreter->getInsertPoint(
                        $interpreter::INSERTION_POINT_HEAD_SCRIPT)
                ) {
                    $PE['print buffer'] =
                        substr($PE['print buffer'], 0,
                            $insertPoint)
                        . $scriptData
                        . substr($PE['print buffer'],
                            $insertPoint);
                    $interpreter->moveInsertPoint(
                        $interpreter::INSERTION_POINT_HEAD_SCRIPT,
                        $scriptDataStrlen);
                }
            } else {
                $PE['has script'] = false;
            }

            // Head markup
            if ($scriptData = $interpreter->getHeadMarkupData()) {
                $scriptDataStrlen = strlen($scriptData);
                $PE['has script'] = true;
                if ($insertPoint =
                    $interpreter->getInsertPoint(
                        $interpreter::INSERTION_POINT_HEAD_MARKUP)
                ) {
                    $PE['print buffer'] =
                        substr($PE['print buffer'], 0,
                            $insertPoint)
                        . $scriptData
                        . substr($PE['print buffer'],
                            $insertPoint);
                    $interpreter->moveInsertPoint(
                        $interpreter::INSERTION_POINT_HEAD_MARKUP,
                        $scriptDataStrlen);
                }
            }

            // Body script
            if ($scriptData = $interpreter->getBodyScriptData()) {
                $scriptDataStrlen =
                    strlen($scriptData);
                if ($insertPoint =
                    $interpreter->getInsertPoint(
                        $interpreter::INSERTION_POINT_BODY_SCRIPT)
                ) {
                    $PE['print buffer'] =
                        substr($PE['print buffer'], 0,
                            $insertPoint)
                        . $scriptData
                        . substr($PE['print buffer'],
                            $insertPoint);
                    $interpreter->moveInsertPoint(
                        $interpreter::INSERTION_POINT_BODY_SCRIPT,
                        $scriptDataStrlen);
                }
            }

            // Body style
            if ($scriptData = $interpreter->getBodyStyleData()) {
                $scriptDataStrlen =
                    strlen($scriptData);
                if ($insertPoint =
                    $interpreter->getInsertPoint(
                        $interpreter::INSERTION_POINT_BODY_STYLE)
                ) {
                    $PE['print buffer'] =
                        substr($PE['print buffer'], 0,
                            $insertPoint)
                        . $scriptData
                        . substr($PE['print buffer'],
                            $insertPoint);
                    $interpreter->moveInsertPoint(
                        $interpreter::INSERTION_POINT_BODY_STYLE,
                        $scriptDataStrlen);
                }
            }

            // Body markup
            if ($scriptData = $interpreter->getBodyMarkupData()) {
                $scriptDataStrlen =
                    strlen($scriptData);
                if ($insertPoint =
                    $interpreter->getInsertPoint(
                        $interpreter::INSERTION_POINT_BODY_MARKUP)
                ) {
                    $PE['print buffer'] =
                        substr($PE['print buffer'], 0,
                            $insertPoint)
                        . $scriptData
                        . substr($PE['print buffer'],
                            $insertPoint);
                    $interpreter->moveInsertPoint(
                        $interpreter::INSERTION_POINT_BODY_MARKUP,
                        $scriptDataStrlen);
                }
            }

            // Get length of print buffer
            $PE["length of print buffer"] = strlen($PE["print buffer"]);

            \Aomebo\Dispatcher\System::setHttpHeaderField(
                'Content-type',
                self::getContentTypeMime() . '; charset='
                . self::getContentTypeCharset());

            \Aomebo\Dispatcher\System::outputHttpHeaders();

            if (!\Aomebo\Dispatcher\System::isHttpHeadRequest()) {

                if (\Aomebo\Configuration::getSetting('output,format')) {

                    // Start parsing good source presentation
                    for ($PE["i"] = 0; $PE["i"] < $PE["length of print buffer"]; $PE["i"]++) {

                        // Get char at this index
                        $PE["c"] = $PE["print buffer"][$PE["i"]];

                        // no start tag has been found
                        if ($PE["status"] == 0) {

                            if ($PE["c"] == "<") {
                                $PE["status"] = 1;
                                $PE["block"] = $PE["c"];
                                $PE["buffer"] = "";
                                $PE["tag"] = "";
                            } else {
                                if($PE["lastIsTag"] && $PE["level"] > 0) {
                                    $PE["chunks"][$PE["chunks count"]] .= $PE["lB"];
                                    $PE["chunk size"]++;
                                    for ($PE["j"]=0; $PE["j"] < $PE["level"]; $PE["j"]++) {
                                        $PE["chunks"][$PE["chunks count"]] .= $PE["lS"];
                                        $PE["chunk size"]++;
                                    }
                                }
                                $PE["chunks"][$PE["chunks count"]] .= $PE["c"];
                                $PE["chunk size"]++;
                                $PE["lastIsTag"] = false;
                            }

                            // A start tag has been found, get the tag
                        } else if($PE["status"] == 1) {
                            $PE["block"] .= $PE["c"];
                            if ($PE["c"] == " " && $PE["tag"] == "") {
                                if (isset($PE_tag_starts[$PE["buffer"]])
                                    || isset($PE_tag_ends[$PE["buffer"]])
                                ) {
                                    $PE["tag"] = $PE["buffer"];
                                } else {
                                    if ($PE["lastIsTag"] && $PE["level"] > 0) {
                                        $PE["chunks"][$PE["chunks count"]] .= $PE["lB"];
                                        $PE["chunk size"]++;
                                        for ($PE["j"]=0; $PE["j"] < $PE["level"]; $PE["j"]++) {
                                            $PE["chunks"][$PE["chunks count"]] .= $PE["lS"];
                                            $PE["chunk size"]++;
                                        }
                                    }
                                    $PE["status"] = 0;
                                    $PE["chunks"][$PE["chunks count"]] .= $PE["block"];
                                    $PE["chunk size"] += strlen($PE["block"]);
                                    $PE["buffer"] = "";
                                    $PE["lastIsTag"] = false;
                                }
                            } else if ($PE["c"] == ">") {
                                if ($PE["tag"] == "") {
                                    $PE["tag"] = $PE["buffer"];
                                }

                                // The tag is marked as a start-tag to handle
                                if (isset($PE_tag_starts[$PE["tag"]])) {

                                    // reset status
                                    $PE["status"] = 0;

                                    // add linebreak before tag start
                                    $PE["chunks"][$PE["chunks count"]] .= $PE["lB"];
                                    $PE["chunk size"]++;

                                    // make code for current shift level
                                    if ($PE["level"] > 0) {
                                        for ($PE["j"]=0; $PE["j"] < $PE["level"]; $PE["j"]++) {
                                            $PE["chunks"][$PE["chunks count"]] .= $PE["lS"];
                                            $PE["chunk size"]++;
                                        }
                                    }

                                    // concat strings
                                    $PE["chunks"][$PE["chunks count"]] .= $PE["block"];
                                    $PE["chunk size"]+= strlen($PE["block"]);

                                    // get current shift level
                                    $PE["level"] += $PE_tag_starts[$PE["tag"]];

                                    $PE["lastIsTag"] = true;

                                    // the tag is marked as a end-tag to handle
                                } else if (isset($PE_tag_ends[$PE["tag"]])) {

                                    // reset status
                                    $PE["status"] = 0;

                                    // add linebreak before tag start
                                    $PE["chunks"][$PE["chunks count"]] .= $PE["lB"];
                                    $PE["chunk size"]++;

                                    // get current shift level
                                    $PE["level"] += $PE_tag_ends[$PE["tag"]];

                                    // make code for current shift level
                                    if ($PE["level"] > 0) {
                                        for ($PE["j"]=0; $PE["j"] < $PE["level"]; $PE["j"]++) {
                                            $PE["chunks"][$PE["chunks count"]] .= $PE["lS"];
                                            $PE["chunk size"]++;
                                        }
                                    }

                                    // concat strings
                                    $PE["chunks"][$PE["chunks count"]] .= $PE["block"];
                                    $PE["chunk size"] += strlen($PE["block"]);
                                    $PE["lastIsTag"] = true;

                                } else {

                                    // make code for current shift level
                                    if ($PE["lastIsTag"] && $PE["level"] > 0) {
                                        $PE["chunks"][$PE["chunks count"]] .= $PE["lB"];
                                        $PE["chunk size"]++;
                                        for ($PE["j"]=0; $PE["j"] < $PE["level"]; $PE["j"]++) {
                                            $PE["chunks"][$PE["chunks count"]] .= $PE["lS"];
                                            $PE["chunk size"]++;
                                        }
                                    }

                                    $PE["status"] = 0;
                                    $PE["chunks"][$PE["chunks count"]] .= $PE["block"];
                                    $PE["chunk size"] += strlen($PE["block"]);
                                    $PE["buffer"] = "";
                                    $PE["lastIsTag"] = false;
                                }
                            } else {
                                $PE["buffer"] .= $PE["c"];
                            }
                        }

                        // Print chunk
                        if ($PE["chunk size"] > $PE["chunk size limit"]) {
                            echo $PE["chunks"][$PE["chunks count"]];
                            $PE["chunk size"] = 0;
                            $PE["chunks"][$PE["chunks count"]] = "";
                        }
                    }

                    $PE["chunks"][$PE["chunks count"]] .= $PE["lB"];
                    $PE["chunk size"]++;
                    $PE["chunks count"]++;
                    $PE["chunks"][$PE["chunks count"]] = "";

                } else {

                    // Just chunk it out according to chunk size
                    for ($PE["i"] = 0; $PE["i"] < $PE["length of print buffer"]; $PE["i"]++) {

                        // Get char at this index
                        $PE["c"] = $PE["print buffer"][$PE["i"]];

                        // Add to chunks
                        $PE["chunks"][$PE["chunks count"]] .= $PE["c"];
                        $PE["chunk size"]++;

                        // Print chunk
                        if ($PE["chunk size"] > $PE["chunk size limit"]) {
                            echo $PE["chunks"][$PE["chunks count"]];
                            $PE["chunk size"] = 0;
                            $PE["chunks"][$PE["chunks count"]] = "";
                        }
                    }
                }

                // If we are loading a page
                if (true == true) {
                    // Show credits?
                    if (\Aomebo\Configuration::getSetting('framework,show credits')) {
                        $PE["chunks"][$PE["chunks count"]] .=
                            $PE["lB"] . "<!-- CREDITS" . $PE["lB"]
                            . $PE["lS"] . \Aomebo\Configuration::getSetting('framework,version') . $PE["lB"]
                            . $PE["lS"] . \Aomebo\Configuration::getSetting('framework,website') . $PE["lB"]
                            . "-->".$PE["lB"];
                    }


                    // Show credits?
                    if (\Aomebo\Configuration::getSetting('framework,show statistics')) {

                        $PE["chunks"][$PE["chunks count"]] .=  $PE["lB"]
                            . "<!-- STATISTICS" . $PE["lB"];

                        // Print elapsed time
                        $PE["now"] = microtime(true);
                        $PE["elapsed total"] = round($PE["now"] - _SYSTEM_START_TIME_, 4);
                        $PE["present elapsed"] = round($PE["elapsed total"], 2);
                        $PE["chunks"][$PE["chunks count"]] .= $PE["lS"]
                            . "Total elapsed time: '" . $PE["present elapsed"]
                            . "' seconds." . $PE["lB"];

                        $PE["chunks"][$PE["chunks count"]] .= "-->".$PE["lB"];
                    }
                }

                // Print all chunks
                for ($PE["i"] = 0; $PE["i"] <= $PE["chunks count"]; $PE["i"]++) {
                    echo $PE["chunks"][$PE["i"]];
                }

                // Clean up locally used variables
                unset($PE, $tagPresentationData, $PE_tag_ends, $PE_tag_starts, $PE_time);

                \Aomebo\Trigger\System::processTriggers(
                    \Aomebo\Trigger\System::TRIGGER_KEY_AFTER_PRESENTATION);

            }
        }

        /**
         * This method returns an array with tags (lowercase) that
         * should have linebreaks after them and also how many
         * intendations that should follow in a formatted presentation.
         *
         * @static
         * @return array
         */
        private static function _getTagPresentationData()
        {
            return array(
                'html' => 0,
                'meta' => 0,
                'head' => 1,
                'title' => 1,
                'body' => 0,
                'div' => 1,
                'table' => 1,
                'tr' => 1,
                'td' => 1,
                'br' => 0,
                'p' => 1,
                'form' => 1,
                'label' => 0,
                'h1' => 0,
                'script' => 0,
                'link' => 0,
            );
        }

    }
}
