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
namespace Aomebo\Indexing
{

    /**
     * This class handles indexing of output.
     *
     * This class is optional meaning it can be disabled
     * and errors in this class will not any process.
     *
     * @method static \Aomebo\Indexing\Engine getInstance()
     */
    class Engine extends \Aomebo\Singleton
    {

        /**
         * This field contains table name for index.
         *
         * @var int
         */
        const TABLE = 'index';

        /**
         * This field contains name of content directory.
         *
         * @var string
         */
        const CONTENT_DIRECTORY = 'content';

        /**
         * This field contains how many data points
         * to calculate norm from.
         *
         * @var int
         */
        const DEFAULT_MODIFICATION_NORM_MAX = 5;

        /**
         * This variables specifies number of
         * days to expiration.
         *
         * @var int
         */
        const DEFAULT_EXPIRATION_PERIOD = 60;

        /**
         * This variable holds if this class is enabled.
         *
         * @internal
         * @var bool
         */
        private static $_enabled = false;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_ignoreOverride = false;

        /**
         * This field holds pointer to resultset.
         *
         * @internal
         * @static
         * @var \Aomebo\Database\Adapters\Resultset
         */
        private static $_resultset;

        /**
         *
         */
        public function __construct()
        {

            if (!$this->_isConstructed()) {

                parent::__construct();

                if (\Aomebo\Database\Adapter::useDatabase()) {

                    if (\Aomebo\Configuration::getSetting(
                        'output,indexing enabled')
                    ) {
                        if (!self::$_enabled =
                            $this->_isInstalled()
                        ) {
                            $this->_install();
                            self::$_enabled =
                                $this->_isInstalled();
                        }
                    }

                }

                $this->_flagThisConstructed();

            }
        }

        /**
         * Gets an uri.
         *
         * @param string $uri
         * @return array|bool
         */
        public function getUri($uri)
        {
            return $this->_getUri($uri);
        }

        /**
         * This method does the indexing of a page.
         */
        public function index()
        {

            $uri = \Aomebo\Dispatcher\System::getPageBaseUri()
                . \Aomebo\Dispatcher\System::getFullRequest();

            $indexingEnabled =
                \Aomebo\Configuration::getSetting('indexing,enabled');
            $session =
                \Aomebo\Session\Handler::getInstance();

            $isQueryStringUri = (strpos($uri, '?') !== false);

            /**
             * For automatic indexing, following criteria must be met:
             *
             * - Index engine is enabled
             * - Current request is flagged to be indexed
             * - Session user is not logged
             * - Current page is not the file-not-found page
             * - Current page is not about to redirect
             */
            if (self::$_enabled
                && !self::$_ignoreOverride
                && !$session->isLoggedIn()
                && $indexingEnabled
                && !\Aomebo\Dispatcher\System::isCurrentPageFileNotFoundPage()
                && !\Aomebo\Dispatcher\System::isRedirecting()
                && (!$isQueryStringUri
                    || (\Aomebo\Dispatcher\System::isRewriteEnabled()
                        && \Aomebo\Configuration::getSetting('indexing,index query string uris using mod_rewrite'))
                    || (!\Aomebo\Dispatcher\System::isRewriteEnabled()
                        && \Aomebo\Configuration::getSetting('indexing,index query string uris')))
            ) {

                \Aomebo\Dispatcher\System::setHttpHeaderField(
                    'Cache-Control',
                    'public, no-store, no-cache, must-revalidate');

                if ((\Aomebo\Dispatcher\System::isHttpGetRequest()
                    || \Aomebo\Dispatcher\System::isHttpHeadRequest())
                    && \Aomebo\Dispatcher\System::isNormalRequest()
                ) {
                    $this->_garbageCollect();
                    $this->_index();
                }

            } else {

                \Aomebo\Dispatcher\System::setHttpHeaderField(
                    'Last-Modified',
                    date('D, d M Y H:i:s e', time()));
                \Aomebo\Dispatcher\System::setHttpHeaderField(
                    'Cache-Control',
                    'private, no-store, no-cache, must-revalidate');
            }

            \Aomebo\Dispatcher\System::setHttpHeaderField(
                'Pragma',
                'no-store, no-cache');

        }

        /**
         * This method removes an uri from index.
         *
         * @param string $uri
         * @throws \Exception
         * @return bool
         */
        public function removeUri($uri)
        {
            if (!empty($uri)) {
                $dba =
                    \Aomebo\Database\Adapter::getInstance();
                if ($contentMd5 = $this->_getContentMd5($uri)) {
                    $this->_deleteContent($contentMd5);
                }
                if ($dba->query('DELETE FROM `{TABLE PREFIX}'
                        . '{SYSTEM TABLE PREFIX}' . self::TABLE . '` WHERE '
                        . '`uri` = {uri} LIMIT 1', array(
                    'uri' => array('value' => $uri, 'quoted' => true)))
                ) {
                    return true;
                }
            } else {
                Throw new \Exception(
                    'Invalid parameters for ' . __FUNCTION__);
            }
            return false;
        }

        /**
         * This method will disallow indexing of current page.
         *
         * @static
         * @return void
         */
        public static function disallowIndexing()
        {
            self::$_ignoreOverride = true;
        }

        /**
         * This method allows indexing of current page.
         *
         * @static
         * @return void
         */
        public static function allowIndexing()
        {
            self::$_ignoreOverride = false;
        }

        /**
         * This method returns the full index.
         *
         * @return array|bool
         */
        public function getIndex()
        {
            $dba =
                \Aomebo\Database\Adapter::getInstance();
            if ($result = $dba->query('SELECT * FROM '
                . '`{TABLE PREFIX}{SYSTEM TABLE PREFIX}'
                . self::TABLE . '` ORDER BY `added` DESC')
            ) {
                return $result->fetchAssocAllAndFree();
            }
            return false;
        }

        /**
         * This method opens the index.
         *
         * @return bool
         */
        public function openIndex()
        {
            $dba =
                \Aomebo\Database\Adapter::getInstance();
            if ($result = $dba->query('SELECT * FROM '
                . '`{TABLE PREFIX}{SYSTEM TABLE PREFIX}'
                . self::TABLE . '` ORDER BY `added` DESC',
                null, true)
            ) {
                self::$_resultset = $result;
                return true;
            }
            return false;
        }

        /**
         * Public alias method.
         *
         * @param string $uri
         * @param array $row
         * @return bool
         */
        public function addUri($uri, $row)
        {
            return $this->_addUri($uri, $row);
        }

        /**
         * @param string $uri
         * @param array $row
         * @return bool
         */
        public function updateUri($uri, $row)
        {
            return $this->_updateUri($uri, $row);
        }

        /**
         * This method gets next entry from index.
         *
         * @return array|bool
         */
        public function getNextEntryFromIndex()
        {
            if (isset(self::$_resultset))
            {
                if ($next = self::$_resultset->fetchAssoc()) {
                    return $next;
                } else {
                    self::$_resultset->free();
                }
            }
            return false;
        }

        /**
         * This method returns content md5 for uri.
         *
         * @internal
         * @param $uri
         * @throws \Exception
         * @return string|bool
         */
        private function _getContentMd5($uri)
        {
            if (isset($uri)) {
                $dba =
                    \Aomebo\Database\Adapter::getInstance();
                if ($result = $dba->query('SELECT * FROM '
                    . '`{TABLE PREFIX}{SYSTEM TABLE PREFIX}'
                    . self::TABLE . '` WHERE `uri` = {uri} LIMIT 1',
                    array('uri' => array(
                        'value' => $uri,
                        'quoted' => true)))
                ) {
                    $row = $result->fetchAssocAndFree();
                    return $row['content_md5'];
                }
            } else {
                Throw new \Exception(
                    'Invalid parameters for ' . __FUNCTION__);
            }
            return false;
        }

        /**
         * @internal
         */
        private function _index()
        {

            $interpreter =
                \Aomebo\Interpreter\Engine::getInstance();
            $output =
                $interpreter->getOutput();

            $uri = \Aomebo\Dispatcher\System::getPageBaseUri()
                . \Aomebo\Dispatcher\System::getFullRequest();
            $uriMd5 = md5($uri);
            $content =
                preg_replace(array('/(\s)+/', '/<[^>]*>/'), array(' ', ''), $output);
            $contentMd5 = md5($content);
            $eTag = md5($uriMd5 . $contentMd5);
            \Aomebo\Dispatcher\System::setHttpHeaderField(
                'ETag',
                $eTag);

            $now = time();

            // Is uri already indexed?
            if ($row = $this->_getUri($uri)) {

                // Does new MD5 differ from old indexed version?
                if ($contentMd5 != $row['content_md5']) {

                    // Delete old item from file-system
                    $this->_deleteContent($row['content_md5']);

                    $title = $interpreter->getMetaData('title');
                    $description = $interpreter->getMetaData('description');
                    $keywords = $interpreter->getMetaData('keywords');
                    $added = 'NOW()';
                    $contentLastModified = date('Y-m-d H:i:s', $now);
                    $contentModificationNumber =
                        (int) $row['content_modification_number'] + 1;
                    $then =
                        (int) strtotime($row['content_last_modified']);
                    $contentModificationDuration =
                        $now - $then;

                    if (!empty($row['content_modification_duration'])) {
                        $oldContentModificationDuration =
                            (int) $row['content_modification_duration'];
                        $contentModificationDurationNorm = round(
                            ($oldContentModificationDuration
                            + $contentModificationDuration) /
                            $contentModificationNumber, 2);
                    } else {
                        $contentModificationDurationNorm = 0;
                    }

                    if ($contentModificationNumber >
                        self::DEFAULT_MODIFICATION_NORM_MAX
                    ) {
                        $contentModificationDurationNorm = 0;
                    }

                    $this->_updateUri($uri, array(
                        'title' =>
                            $title,
                        'description' =>
                            $description,
                        'keywords' =>
                            $keywords,
                        'content' =>
                            $content,
                        'content_md5' =>
                            $contentMd5,
                        'content_last_modified' =>
                            $contentLastModified,
                        'content_modification_duration' =>
                            $contentModificationDuration,
                        'content_modification_duration_norm' =>
                            $contentModificationDurationNorm,
                        'content_modification_number' =>
                            $contentModificationNumber,
                    ));

                    \Aomebo\Dispatcher\System::setHttpHeaderField(
                        'Last-Modified',
                        date('D, d M Y H:i:s e', $now));

                // Otherwise - new MD5 does not differ from old one..
                } else {

                    $lastModifiedUnixTime =
                        (int) strtotime($row['content_last_modified']);
                    \Aomebo\Dispatcher\System::setHttpHeaderField(
                        'Last-Modified',
                        date('D, d M Y H:i:s e', $lastModifiedUnixTime));

                }


            // Otherwise - uri is new to the index..
            } else {

                $title = $interpreter->getMetaData('title');
                $description = $interpreter->getMetaData('description');
                $keywords = $interpreter->getMetaData('keywords');
                $contentLastModified = date('Y-m-d H:i:s', $now);
                $this->_addUri($uri, array(
                    'title' => $title,
                    'description' => $description,
                    'keywords' => $keywords,
                    'content' => $content,
                    'content_md5' => $contentMd5,
                    'content_last_modified' => $contentLastModified,
                ));
                $contentLastModifiedDuration = 0;

                \Aomebo\Dispatcher\System::setHttpHeaderField(
                    'Last-Modified',
                    date('D, d M Y H:i:s e', $now));

            }
        }

        /**
         * This method adds an uri to index.
         *
         * @internal
         * @param string $uri
         * @param array $row
         * @return bool
         */
        private function _addUri($uri, $row)
        {
            if (isset($uri, $row)
                && !empty($uri)
                && is_array($row)
                && sizeof($row) > 0
            ) {
                $values = array(
                    'uri' => array(
                        'value' => $uri,
                        'quoted' => true,
                    ),
                    'added' => 'NOW()',
                );
                if (!empty($row['title'])) {
                    $values['title'] = array(
                        'value' => $row['title'],
                        'quoted' => true,
                    );
                }
                if (!empty($row['description'])) {
                    $values['description'] = array(
                        'value' => $row['description'],
                        'quoted' => true,
                    );
                }
                if (!empty($row['keywords'])) {
                    $values['keywords'] = array(
                        'value' => $row['keywords'],
                        'quoted' => true,
                    );
                }
                if (!empty($row['content_md5'])) {
                    $values['content_md5'] = array(
                        'value' => $row['content_md5'],
                        'quoted' => true,
                    );
                    if (!empty($row['content'])) {
                        $this->_setContent(
                            $row['content_md5'], $row['content']);
                    }
                }
                if (!empty($row['content_last_modified'])) {
                    $values['content_last_modified'] = array(
                        'value' => $row['content_last_modified'],
                        'quoted' => true,
                    );
                }
                if (isset($row['content_modification_duration'])) {
                    $values['content_modification_duration'] = array(
                        'value' => $row['content_last_modified'],
                        'quoted' => true,
                    );
                }
                if (isset($row['content_modification_duration_norm'])) {
                    $values['content_modification_duration_norm'] = array(
                        'value' => $row['content_modification_duration_norm'],
                        'quoted' => true,
                    );
                }
                if (sizeof($values) > 0) {
                    $fieldString = '';
                    $i = 0;
                    reset($values);
                    foreach ($values as $key => $field) {
                        if ($i > 0) {
                            $fieldString .= ',';
                        }
                        $fieldString .= '`' . $key . '`';
                        $i++;
                    }
                    $valueString = '';
                    $i = 0;
                    reset($values);
                    foreach ($values as $key => $field) {
                        if ($i > 0) {
                            $valueString .= ',';
                        }
                        $valueString .= '{' . $key . '}';
                        $i++;
                    }
                    $dba =
                        \Aomebo\Database\Adapter::getInstance();
                    $dba->query('INSERT INTO `{TABLE PREFIX}'
                        . '{SYSTEM TABLE PREFIX}' . self::TABLE . '` ('
                        . $fieldString . ') VALUES(' . $valueString . ')',
                        $values, false, false);
                }
            } else {
                Throw new \Exception('Invalid parameters');
            }

            return true;

        }

        /**
         * This method updates an uri.
         *
         * @internal
         * @param string $uri
         * @param array $row
         * @throws \Exception
         * @return array|bool
         */
        private function _updateUri($uri, $row)
        {
            if (isset($uri, $row)
                && is_array($row)
                && sizeof($row) > 0
            ) {
                $values = array(
                    'uri' => array(
                        'value' => $uri,
                        'quoted' => true,
                    ),
                );
                if (!empty($row['title'])) {
                    $values['title'] = array(
                        'value' => $row['title'],
                        'quoted' => true,
                    );
                }
                if (!empty($row['description'])) {
                    $values['description'] = array(
                        'value' => $row['description'],
                        'quoted' => true,
                    );
                }
                if (!empty($row['keywords'])) {
                    $values['keywords'] = array(
                        'value' => $row['keywords'],
                        'quoted' => true,
                    );
                }
                if (!empty($row['content_md5'])) {
                    $values['content_md5'] = array(
                        'value' => $row['content_md5'],
                        'quoted' => true,
                    );
                    if (!empty($row['content'])) {
                        $this->_setContent(
                            $row['content_md5'], $row['content']);
                    }
                }
                if (!empty($row['content_last_modified'])) {
                    $values['content_last_modified'] = array(
                        'value' => $row['content_last_modified'],
                        'quoted' => true,
                    );
                }
                if (isset($row['content_modification_duration'])) {
                    $values['content_modification_duration'] = array(
                        'value' => $row['content_modification_duration'],
                        'quoted' => true,
                    );
                }
                if (isset($row['content_modification_duration_norm'])) {
                    $values['content_modification_duration_norm'] = array(
                        'value' => $row['content_modification_duration_norm'],
                        'quoted' => true,
                    );
                }
                if (isset($row['content_modification_number'])) {
                    $values['content_modification_number'] = array(
                        'value' => $row['content_modification_number'],
                        'quoted' => true,
                    );
                }
                if (sizeof($values) > 0) {
                    $updateString = '';
                    $i = 0;
                    reset($values);
                    foreach ($values as $key => $field) {
                        if ($key != 'uri') {
                            if ($i > 0) {
                                $updateString .= ',';
                            }
                            $updateString .= '`' . $key . '` = {' . $key . '}';
                            $i++;
                        }
                    }
                    $dba =
                        \Aomebo\Database\Adapter::getInstance();
                    if ($dba->query('UPDATE `{TABLE PREFIX}'
                        . '{SYSTEM TABLE PREFIX}' . self::TABLE . '` SET '
                        . $updateString . ' WHERE `uri` = {uri} LIMIT 1', $values)
                    ) {
                        return true;
                    }
                }
            } else {
                Throw new \Exception(
                    'Invalid parameters for ' . __FUNCTION__);
            }
            return false;
        }

        /**
         * This method deletes content from filesystem.
         *
         * @internal
         * @param string $contentMd5
         * @throws \Exception
         * @return bool
         */
        private function _deleteContent($contentMd5)
        {
            if (isset($contentMd5)) {
                $filename =
                    _SYSTEM_SITE_ROOT_ . DIRECTORY_SEPARATOR . 'Indexing'
                        . DIRECTORY_SEPARATOR . self::CONTENT_DIRECTORY
                        . DIRECTORY_SEPARATOR . $contentMd5;
                if (\Aomebo\Filesystem::deleteFile($filename)) {
                    return true;
                }
            } else {
                Throw new \Exception(
                    'Invalid parameters for _deleteContent'
                );
            }
            return false;
        }

        /**
         * This method stores content into filesystem.
         *
         * @internal
         * @param string $contentMd5
         * @param string $content
         * @throws \Exception
         * @return bool
         */
        private function _setContent($contentMd5, $content)
        {
            if (isset($contentMd5, $content)) {
                $filename =
                    _SYSTEM_SITE_ROOT_ . DIRECTORY_SEPARATOR . 'Indexing'
                    . DIRECTORY_SEPARATOR . self::CONTENT_DIRECTORY
                    . DIRECTORY_SEPARATOR . $contentMd5;
                \Aomebo\Filesystem::makeFile($filename, $content);
                return true;
            } else {
                Throw new \Exception(
                    'Invalid parameters for _setContent'
                );
            }
        }

        /**
         * This method cleans index from old entries.
         *
         * @internal
         * @return void
         */
        private function _garbageCollect()
        {
            if ($expireds = $this->_getExpiredItems()) {
                foreach ($expireds as $expired)
                {
                    $this->removeUri($expired['uri']);
                }
            }
        }

        /**
         * @internal
         * @return array|bool
         */
        private function _getExpiredItems()
        {
            $dba =
                \Aomebo\Database\Adapter::getInstance();
            $expiredDays =
                \Aomebo\Configuration::getSetting('indexing,expiration days');
            $expired = strtotime('-' . $expiredDays . ' days', time());
            if ($resultset = $dba->query('SELECT * FROM `{TABLE PREFIX}'
                . '{SYSTEM TABLE PREFIX}' . self::TABLE . '` '
                . 'WHERE `edited` <= {expired} '
                . 'AND `edited` != {none}',
                array(
                    'expired' => array(
                        'value' => $expired,
                        'quoted' => true,
                    ),
                    'none' => array(
                        'value' => '0000-00-00 00:00:00',
                        'quoted' => true,
                    ),
                ))
            ) {
                return $resultset->fetchAssocAllAndFree();
            }
            return false;
        }

        /**
         * This method returns a index row.
         *
         * @internal
         * @param string $uri
         * @throws \Exception
         * @return array|bool
         */
        private function _getUri($uri)
        {
            if (isset($uri)) {
                $dba =
                    \Aomebo\Database\Adapter::getInstance();
                if ($resultset =
                    $dba->query('SELECT * FROM `{TABLE PREFIX}{SYSTEM TABLE PREFIX}'
                        . self::TABLE . '` WHERE `uri` = {uri} LIMIT 1', array(
                        'uri' => array('value' => $uri, 'quoted' => true)))
                ) {
                    return $resultset->fetchAssocAndFree();
                }
            } else {
                Throw new \Exception(
                    'Invalid parameters for ' . __FUNCTION__);
            }
            return false;
        }

        /**
         * @internal
         * @return bool
         */
        private function _isInstalled()
        {
            $dba =
                \Aomebo\Database\Adapter::getInstance();
            return ($dba->tableExists(
                '{TABLE PREFIX}{SYSTEM TABLE PREFIX}' . self::TABLE)
                && is_dir(_SYSTEM_SITE_ROOT_ . DIRECTORY_SEPARATOR . 'Indexing')
                && is_dir(_SYSTEM_SITE_ROOT_ . DIRECTORY_SEPARATOR . 'Indexing'. DIRECTORY_SEPARATOR . self::CONTENT_DIRECTORY));
        }

        /**
         * @internal
         * @throws \Exception
         */
        private function _install()
        {
            $dba =
                \Aomebo\Database\Adapter::getInstance();
            $databaseAdapter =
                strtolower(\Aomebo\Configuration::getSetting(
                    'database,adapter'));

            // Aomebo Indexing Engine only supports mysql or mysqli
            if ($databaseAdapter == 'mysqli'
                || $databaseAdapter == 'mysql'
            ) {

                $storageEngine =
                    strtolower(\Aomebo\Configuration::getSetting('database,storage engine'));

                // Aomebo Indexing Engine only supports MyISAM or InnoDB storage engines
                if ($storageEngine == 'myisam'
                    || $storageEngine == 'innodb'
                    || $storageEngine == 'all'
                ) {

                    // Create table preferably with InnoDB otherwise MyISAM
                    $dba->query('CREATE TABLE IF NOT EXISTS `{TABLE PREFIX}{SYSTEM TABLE PREFIX}' . self::TABLE . '`('
                        . '`uri` BLOB NOT NULL DEFAULT "",'
                        . '`title` LONGBLOB NOT NULL DEFAULT "",'
                        . '`description` LONGBLOB NOT NULL DEFAULT "",'
                        . '`keywords` LONGBLOB NOT NULL DEFAULT "",'
                        . '`content_md5` VARCHAR(100) NOT NULL DEFAULT "",'
                        . '`content_last_modified` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",'
                        . '`content_modification_duration` INT(11) UNSIGNED NOT NULL DEFAULT 0,'
                        . '`content_modification_duration_norm` INT(11) UNSIGNED NOT NULL DEFAULT 0,'
                        . '`content_modification_number` INT(2) NOT NULL DEFAULT 0,'
                        . '`added` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",'
                        . '`edited` TIMESTAMP NOT NULL DEFAULT "0000-00-00 00:00:00" ON UPDATE CURRENT_TIMESTAMP, '
                        . 'PRIMARY KEY(uri(500))) '
                        . 'ENGINE={storage_engine} DEFAULT CHARSET={DATA CHARSET};', array(
                            'storage_engine' => ($storageEngine == 'myisam' ? 'MyISAM' : 'InnoDB')));

                    // Create Aomebo Index Indexing directory
                    if (!is_dir((_SYSTEM_SITE_ROOT_ . DIRECTORY_SEPARATOR
                        . 'Indexing'. DIRECTORY_SEPARATOR . self::CONTENT_DIRECTORY))
                    ) {
                        \Aomebo\Filesystem::makeDirectories(_SYSTEM_SITE_ROOT_
                            . DIRECTORY_SEPARATOR . 'Indexing'. DIRECTORY_SEPARATOR
                                . self::CONTENT_DIRECTORY);
                    }

                } else {
                    Throw new \Exception(
                        'Aomebo Indexing Engine supports only MyISAM or InnoDB as storage engines');
                }
            } else {
                Throw new \Exception(
                    'Aomebo Indexing Engine supports only MySQL or MySQLi as database adapter');
            }
        }

    }
}
