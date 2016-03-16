<?php
/**
 * Aomebo - a module-based MVC framework for PHP 5.3 and higher
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
 * @license LGPL version 3
 * @see http://www.aomebo.org/ or https://github.com/cjohansson/Aomebo-Framework
 */

/**
 *
 */
namespace Aomebo\Response\Responses {

    /**
     *
     */
    class File extends \Aomebo\Response\Type
    {

        /**
         * @var int
         */
        protected $_priority = 80;

        /**
         * @var string
         */
        protected $_name = 'File';

        /**
         * @return bool
         */
        public function isValidRequest()
        {
            return (\Aomebo\Configuration::getSetting('file_responses,'
                . \Aomebo\Dispatcher\System::getFullRequest(), false) ?
                true : false);
        }

        /**
         *
         */
        public function respond()
        {

            $filePath =
                \Aomebo\Configuration::getSetting('file_responses,'
                    . \Aomebo\Dispatcher\System::getFullRequest());

            if (file_exists($filePath)) {

                if ($type = self::_getMimeType($filePath)) {
                    header('Content-Type:' . $type);
                }
                if ($size = filesize($filePath)) {
                    header('Content-Length: ' . filesize($size));
                }
                readfile($filePath);

            } else {
                Throw new \Exception(sprintf(
                    self::systemTranslate('Could not find file at "%s".'),
                    $filePath));
            }

        }

        /**
         * @internal
         * @param string $file
         * @return string|bool
         */
        private function _getMimeType($file)
        {

            $mime_types = array(
                'pdf' => 'application/pdf', 
                'exe' => 'application/octet-stream', 
                'zip' => 'application/zip', 
                'docx' => 'application/msword', 
                'doc' => 'application/msword', 
                'xls' => 'application/vnd.ms-excel', 
                'ppt' => 'application/vnd.ms-powerpoint', 
                'gif' => 'image/gif', 
                'png' => 'image/png', 
                'jpeg' => 'image/jpg', 
                'jpg' => 'image/jpg', 
                'mp3' => 'audio/mpeg', 
                'wav' => 'audio/x-wav', 
                'mpeg' => 'video/mpeg', 
                'mpg' => 'video/mpeg', 
                'mpe' => 'video/mpeg', 
                'mov' => 'video/quicktime', 
                'avi' => 'video/x-msvideo', 
                '3gp' => 'video/3gpp', 
                'css' => 'text/css', 
                'jsc' => 'application/javascript', 
                'js' => 'application/javascript', 
                'php' => 'text/html', 
                'htm' => 'text/html', 
                'html' => 'text/html'
            );

            $extension = strtolower(end(explode('.', $file)));

            return (isset($mime_types[$extension]) ?
                $mime_types[$extension] : false);

        }

    }

}
