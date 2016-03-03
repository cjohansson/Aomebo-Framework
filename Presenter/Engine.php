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

            $tagPresentationData =
                self::getTagPresentationData();

            $startTags = array();
            $tagEnds = array();

            foreach ($tagPresentationData as
                 $rawTag => $rawIndentations
            ) {
                $tag = strtolower($rawTag);
                $indentations = (int) $rawIndentations;
                $negTag = '/' . $tag;
                $negIndentations = (-1) * $indentations;
                $startTags[$tag] = $indentations;
                $tagEnds[$negTag] = $negIndentations;
            }

            $buffer = "";

            $chunks = array(0 => "");
            $chunksCount = 0;
            $chunkSize = 0;
            $chunkSizeLimit =
                \Aomebo\Configuration::getSetting('output,chunk size');

            // Encoding-specific characters
            $lineBreak =
                \Aomebo\Configuration::getSetting("output,linebreak character");
            $lineSeed =
                \Aomebo\Configuration::getSetting("output,tab character");

            $status = 0;
            $level = 0;
            $tag = "";
            $block = "";

            // Load print buffer from interpreter-engine
            $printBuffer =
                \Aomebo\Interpreter\Engine::getOutput();

            $lastIsTag = false;

            self::applyAdditionalMarkup($printBuffer);

            // Get length of print buffer
            $printBufferLength = strlen($printBuffer);

            \Aomebo\Dispatcher\System::setHttpHeaderField(
                'Content-type',
                self::getContentTypeMime() . '; charset='
                . self::getContentTypeCharset());

            \Aomebo\Dispatcher\System::outputHttpHeaders();

            if (!\Aomebo\Dispatcher\System::isHttpHeadRequest()) {

                if (\Aomebo\Configuration::getSetting(
                    'output,format')
                ) {

                    // Start parsing good source presentation
                    for ($i = 0; $i < $printBufferLength; $i++)
                    {

                        // Get char at this index
                        $c = $printBuffer[$i];

                        // no start tag has been found
                        if ($status == 0) {

                            if ($c == "<") {
                                
                                $status = 1;
                                $block = $c;
                                $buffer = "";
                                $tag = "";
                                
                            } else {
                                
                                if ($lastIsTag
                                    && $c != ' '
                                    && $c != "\n"
                                    && $c != "\t"
                                    && $c != "\r"
                                ) {
                                    
                                    $chunks[$chunksCount] .= $lineBreak;
                                    $chunkSize++;

                                    if ($level > 0) {
                                        for ($j=0; $j < $level; $j++)
                                        {
                                            $chunks[$chunksCount] .= $lineSeed;
                                            $chunkSize++;
                                        }
                                    }

                                    $lastIsTag = false;
                                    
                                }
                                
                                $chunks[$chunksCount] .= $c;
                                $chunkSize++;
                                
                            }

                        // A start tag has been found, get the tag
                        } else if ($status == 1) {

                            if ($c == ' '
                                || $c == "\n"
                                || $c == "\r"
                                || $c == "\t"
                            ) {
                                $c = ' ';
                            }
                            
                            $block .= $c;
                            
                            if ($c == ' '
                                && $tag == ""
                            ) {
                                if (isset($startTags[$buffer])
                                    || isset($tagEnds[$buffer])
                                ) {
                                    $tag = $buffer;
                                } else {
                                    if ($lastIsTag 
                                        && $level > 0
                                    ) {
                                        
                                        // $chunks[$chunksCount] .= $lineBreak;
                                        // $chunkSize++;

                                        for ($j=0; $j < $level; $j++)
                                        {
                                            $chunks[$chunksCount] .= $lineSeed;
                                            $chunkSize++;
                                        }
                                        
                                    }
                                    
                                    $status = 0;
                                    $chunks[$chunksCount] .= $block;
                                    $chunkSize += strlen($block);
                                    $buffer = "";
                                    $lastIsTag = false;
                                    
                                }
                            } else if ($c == ">") {
                                
                                if ($tag == "") {
                                    $tag = $buffer;
                                }

                                // The tag is marked as a start-tag to handle
                                if (isset($startTags[$tag])) {

                                    // reset status
                                    $status = 0;

                                    // add linebreak before tag start
                                    $chunks[$chunksCount] .= $lineBreak;
                                    $chunkSize++;

                                    // make code for current shift level
                                    if ($level > 0) {
                                        for ($j=0; $j < $level; $j++)
                                        {
                                            $chunks[$chunksCount] .= $lineSeed;
                                            $chunkSize++;
                                        }
                                    }

                                    // concat strings
                                    $chunks[$chunksCount] .= $block;
                                    $chunkSize+= strlen($block);

                                    // get current shift level
                                    $level += $startTags[$tag];

                                    $lastIsTag = true;

                                // the tag is marked as a end-tag to handle
                                } else if (isset($tagEnds[$tag])) {

                                    // reset status
                                    $status = 0;

                                    // add linebreak before tag start
                                    $chunks[$chunksCount] .= $lineBreak;
                                    $chunkSize++;

                                    // get current shift level
                                    $level += $tagEnds[$tag];

                                    // make code for current shift level
                                    if ($level > 0) {
                                        for ($j=0; $j < $level; $j++)
                                        {
                                            $chunks[$chunksCount] .= $lineSeed;
                                            $chunkSize++;
                                        }
                                    }

                                    // concat strings
                                    $chunks[$chunksCount] .= $block;
                                    $chunkSize += strlen($block);
                                    $lastIsTag = true;

                                } else {

                                    // make code for current shift level
                                    if ($lastIsTag 
                                        && $level > 0
                                    ) {
                                        
                                        $chunks[$chunksCount] .= $lineBreak;
                                        $chunkSize++;
                                        
                                        for ($j=0; $j < $level; $j++)
                                        {
                                            $chunks[$chunksCount] .= $lineSeed;
                                            $chunkSize++;
                                        }
                                        
                                    }

                                    $status = 0;
                                    $chunks[$chunksCount] .= $block;
                                    $chunkSize += strlen($block);
                                    $buffer = "";
                                    $lastIsTag = false;
                                    
                                }
                            } else {
                                $buffer .= $c;
                            }
                        }

                        // Print chunk
                        if ($chunkSize > $chunkSizeLimit) {
                            
                            echo $chunks[$chunksCount];
                            flush();
                            $chunkSize = 0;
                            $chunks[$chunksCount] = "";
                            
                        }
                    }

                    $chunks[$chunksCount] .= $lineBreak;
                    $chunksCount++;
                    $chunks[$chunksCount] = "";

                } else {

                    // Just chunk it out according to chunk size
                    for ($i = 0; $i < $printBufferLength; $i++)
                    {

                        // Get char at this index
                        $c = $printBuffer[$i];

                        // Add to chunks
                        $chunks[$chunksCount] .= $c;
                        $chunkSize++;

                        // Print chunk
                        if ($chunkSize > $chunkSizeLimit) {
                            
                            echo $chunks[$chunksCount];
                            flush();
                            $chunkSize = 0;
                            $chunks[$chunksCount] = "";
                            
                        }
                    }
                }

                // If we are loading a page
                if (\Aomebo\Dispatcher\System::isPageRequest()) {
                    
                    // Show credits?
                    if (\Aomebo\Configuration::getSetting(  
                        'framework,show credits')
                    ) {
                        
                        $chunks[$chunksCount] .=
                            $lineBreak . "<!-- CREDITS" . $lineBreak
                            . $lineSeed . \Aomebo\Configuration::getSetting('framework,version') . $lineBreak
                            . $lineSeed . \Aomebo\Configuration::getSetting('framework,website') . $lineBreak
                            . "-->" . $lineBreak;
                    }


                    // Show credits?
                    if (\Aomebo\Configuration::getSetting(
                        'framework,show statistics')
                    ) {

                        $chunks[$chunksCount] .=  $lineBreak
                            . "<!-- STATISTICS" . $lineBreak;

                        // Print elapsed time
                        $now = microtime(true);
                        $elapsedTotal = round($now - _SYSTEM_START_TIME_, 4);
                        $formattedElapsed = round($elapsedTotal, 2);
                        $chunks[$chunksCount] .= $lineSeed
                            . "Total elapsed time: '" . $formattedElapsed
                            . "' seconds." . $lineBreak;

                        $chunks[$chunksCount] .= "-->" . $lineBreak;
                    }
                }

                // Print all chunks
                for ($i = 0; $i <= $chunksCount; $i++)
                {
                    echo $chunks[$i];
                    flush();
                }

                \Aomebo\Trigger\System::processTriggers(
                    \Aomebo\Trigger\System::TRIGGER_KEY_AFTER_PRESENTATION);

            }
        }

        /**
         * @static
         * @param string $printBuffer
         */
        public static function applyAdditionalMarkup(& $printBuffer)
        {

            // Head style
            if ($associativesData =
                \Aomebo\Associatives\Engine::getAssociativeData()
            ) {
                $associativesDataStrlen =
                    strlen($associativesData);
                if ($insertPoint =
                    \Aomebo\Interpreter\Engine::getInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_HEAD_STYLE)
                ) {
                    $printBuffer =
                        substr($printBuffer, 0, $insertPoint)
                        . $associativesData
                        . substr($printBuffer, $insertPoint);
                    \Aomebo\Interpreter\Engine::moveInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_HEAD_STYLE,
                        $associativesDataStrlen);
                }
            }

            // Head meta
            if ($metaData =
                \Aomebo\Interpreter\Engine::getHeadMetaData()
            ) {
                $metaDataStrlen =
                    strlen($metaData);
                if ($insertPoint =
                    \Aomebo\Interpreter\Engine::getInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_HEAD_META)
                ) {
                    $printBuffer =
                        substr($printBuffer, 0, $insertPoint)
                        . $metaData
                        . substr($printBuffer, $insertPoint);
                    \Aomebo\Interpreter\Engine::moveInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_HEAD_META,
                        $metaDataStrlen);
                }
            }

            // Head style
            if ($styleData =
                \Aomebo\Interpreter\Engine::getHeadStyleData()
            ) {
                $styleDataStrlen =
                    strlen($styleData);
                if ($insertPoint =
                    \Aomebo\Interpreter\Engine::getInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_HEAD_STYLE)
                ) {
                    $printBuffer =
                        substr($printBuffer, 0, $insertPoint)
                        . $styleData
                        . substr($printBuffer, $insertPoint);
                    \Aomebo\Interpreter\Engine::moveInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_HEAD_STYLE,
                        $styleDataStrlen);
                }
            }

            // Head script
            if ($scriptData =
                \Aomebo\Interpreter\Engine::getHeadScriptData()
            ) {
                $scriptDataStrlen =
                    strlen($scriptData);
                if ($insertPoint =
                    \Aomebo\Interpreter\Engine::getInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_HEAD_SCRIPT)
                ) {
                    $printBuffer =
                        substr($printBuffer, 0, $insertPoint)
                        . $scriptData
                        . substr($printBuffer, $insertPoint);
                    \Aomebo\Interpreter\Engine::moveInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_HEAD_SCRIPT,
                        $scriptDataStrlen);
                }
            }

            // Head markup
            if ($scriptData =
                \Aomebo\Interpreter\Engine::getHeadMarkupData()
            ) {
                $scriptDataStrlen = strlen($scriptData);
                if ($insertPoint =
                    \Aomebo\Interpreter\Engine::getInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_HEAD_MARKUP)
                ) {
                    $printBuffer =
                        substr($printBuffer, 0, $insertPoint)
                        . $scriptData
                        . substr($printBuffer, $insertPoint);
                    \Aomebo\Interpreter\Engine::moveInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_HEAD_MARKUP,
                        $scriptDataStrlen);
                }
            }

            // Body script
            if ($scriptData =
                \Aomebo\Interpreter\Engine::getBodyScriptData()
            ) {
                $scriptDataStrlen =
                    strlen($scriptData);
                if ($insertPoint =
                    \Aomebo\Interpreter\Engine::getInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_SCRIPT)
                ) {
                    $printBuffer =
                        substr($printBuffer, 0, $insertPoint)
                        . $scriptData
                        . substr($printBuffer, $insertPoint);
                    \Aomebo\Interpreter\Engine::moveInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_SCRIPT,
                        $scriptDataStrlen);
                }
            }

            // Body style
            if ($scriptData =
                \Aomebo\Interpreter\Engine::getBodyStyleData()
            ) {
                $scriptDataStrlen =
                    strlen($scriptData);
                if ($insertPoint =
                    \Aomebo\Interpreter\Engine::getInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_STYLE)
                ) {
                    $printBuffer =
                        substr($printBuffer, 0, $insertPoint)
                        . $scriptData
                        . substr($printBuffer, $insertPoint);
                    \Aomebo\Interpreter\Engine::moveInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_STYLE,
                        $scriptDataStrlen);
                }
            }

            // Body markup
            if ($scriptData =
                \Aomebo\Interpreter\Engine::getBodyMarkupData()
            ) {
                $scriptDataStrlen =
                    strlen($scriptData);
                if ($insertPoint =
                    \Aomebo\Interpreter\Engine::getInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_MARKUP)
                ) {
                    $printBuffer =
                        substr($printBuffer, 0, $insertPoint)
                        . $scriptData
                        . substr($printBuffer, $insertPoint);
                    \Aomebo\Interpreter\Engine::moveInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_MARKUP,
                        $scriptDataStrlen);
                }
            }

            // Body append script
            if ($scriptData =
                \Aomebo\Interpreter\Engine::getBodyAppendScriptData()
            ) {
                $scriptDataStrlen =
                    strlen($scriptData);
                if ($insertPoint =
                    \Aomebo\Interpreter\Engine::getInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_APPEND_SCRIPT)
                ) {
                    $printBuffer =
                        substr($printBuffer, 0, $insertPoint)
                        . $scriptData
                        . substr($printBuffer, $insertPoint);
                    \Aomebo\Interpreter\Engine::moveInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_APPEND_SCRIPT,
                        $scriptDataStrlen);
                }
            }

            // Body append style
            if ($scriptData =
                \Aomebo\Interpreter\Engine::getBodyAppendStyleData()
            ) {
                $scriptDataStrlen =
                    strlen($scriptData);
                if ($insertPoint =
                    \Aomebo\Interpreter\Engine::getInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_APPEND_STYLE)
                ) {
                    $printBuffer =
                        substr($printBuffer, 0, $insertPoint)
                        . $scriptData
                        . substr($printBuffer, $insertPoint);
                    \Aomebo\Interpreter\Engine::moveInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_APPEND_STYLE,
                        $scriptDataStrlen);
                }
            }

            // Body append markup
            if ($scriptData =
                \Aomebo\Interpreter\Engine::getBodyAppendMarkupData()
            ) {
                $scriptDataStrlen =
                    strlen($scriptData);
                if ($insertPoint =
                    \Aomebo\Interpreter\Engine::getInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_APPEND_MARKUP)
                ) {
                    $printBuffer =
                        substr($printBuffer, 0, $insertPoint)
                        . $scriptData
                        . substr($printBuffer, $insertPoint);
                    \Aomebo\Interpreter\Engine::moveInsertPoint(
                        \Aomebo\Interpreter\Engine::INSERTION_POINT_BODY_APPEND_MARKUP,
                        $scriptDataStrlen);
                }
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
        public static function getTagPresentationData()
        {
            return array(
                'html' => 0,
                'meta' => 0,
                'head' => 1,
                'title' => 1,
                'body' => 0,
                'div' => 1,
                'section' => 1,
                'article' => 1,
                'table' => 1,
                'tr' => 1,
                'td' => 1,
                'br' => 0,
                'p' => 1,
                'form' => 1,
                'fieldset' => 1,
                'label' => 1,
                'h1' => 1,
                'h2' => 1,
                'h3' => 1,
                'h4' => 1,
                'h5' => 1,
                'h6' => 1,
                'header' => 1,
                'script' => 0,
                'link' => 0,
                'select' => 1,
                'option' => 0,
            );
        }

    }
}
