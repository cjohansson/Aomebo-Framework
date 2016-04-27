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
namespace Aomebo\Response\Responses
{

    /**
     *
     */
    class Favicon extends \Aomebo\Response\Type
    {

        /**
         * @var int
         */
        protected $_priority = 70;

        /**
         * @var string
         */
        protected $_name = 'Favicon';

        /**
         * @return bool
         */
        public function isValidRequest()
        {
            return \Aomebo\Dispatcher\System::isFaviconRequest();
        }

        /**
         *
         */
        public function respond()
        {

            $filePath =
                \Aomebo\Dispatcher\System::getResourcesDirInternalPath()
                . DIRECTORY_SEPARATOR
                . \Aomebo\Configuration::getSetting('site,shortcut icon');

            if (file_exists($filePath)) {

                \Aomebo\Dispatcher\System::setHttpHeaderField(
                    'Cache-Control',
                    'public, max-age=31536000' // 1 year
                );

                if ($filemtime = \Aomebo\Filesystem::getFileLastModificationTime(
                    $filePath)
                ) {
                    \Aomebo\Dispatcher\System::setHttpHeaderField(
                        'Last-Modified',
                        date('D, d M Y H:i:s e', $filemtime)
                    );
                }
                if ($type = self::_getMimeType($filePath)) {
                    \Aomebo\Dispatcher\System::setHttpHeaderField(
                        'Content-Type',
                        $type . '; charset=binary'
                    );
                }

                \Aomebo\Dispatcher\System::setFileNotFoundFlag(false);
                \Aomebo\Dispatcher\System::setHttpResponseStatus200Ok();
                \Aomebo\Dispatcher\System::outputHttpHeaders();
                readfile($filePath);

            } else {
                Throw new \Exception(sprintf(
                    self::systemTranslate('Could not find favicon at "%s".'),
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
                'svg' => 'image/svg+xml',
                'ico' => 'image/vnd.microsoft.icon',
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
