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
namespace Aomebo\Session
{

    /**
     * This class is handling our root session and
     * pushes events out to session-blocks.
     *
     * @method static \Aomebo\Session\Handler getInstance()
     */
    class Handler extends \Aomebo\Singleton
    {

        /**
         * @var string
         */
        const COOKIE_KEY_SESSION_ID = 'sid';

        /**
         * @var int
         */
        const SESSION_EXPIRES = 30;

        /**
         * @var string
         */
        const TABLE_SESSIONS = 'sessions';

        /**
         * @var string
         */
        const TABLE_SESSIONS_BLOCKS_DATA = 'sessions_blocks_data';

        /**
         * @var int
         */
        const OVERRIDE_UPDATE_SESSION_NONE = 0;

        /**
         * @var int
         */
        const OVERRIDE_UPDATE_SESSION_UPDATE = 1;

        /**
         * @var int
         */
        const OVERRIDE_UPDATE_SESSION_DONT_UPDATE = 2;

        /**
         * @var int
         */
        const OVERRIDE_SEND_COOKIE_NONE = 0;

        /**
         * @var int
         */
        const OVERRIDE_SEND_COOKIE_SEND = 1;

        /**
         * @var int
         */
        const OVERRIDE_SEND_COOKIE_DONT_SEND = 2;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_blockNameToBlockObject;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_blockNameToBlockData;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_blockNameToSaveBlockData;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_sessionData;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_mainBlockName;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_sendCookie;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_changeOfSessionState;

        /**
         * @internal
         * @static
         * @var int
         */
        private static $_overrideUpdateSession;

        /**
         * @internal
         * @static
         * @var int
         */
        private static $_overrideSendCookie;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_clientSentValidCookie;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_clientSentCookie;

        /**
         * @throws \Exception
         */
        public function __construct()
        {

            if (!self::_isConstructed()) {

                parent::__construct();

                \Aomebo\Trigger\System::addTrigger(
                    \Aomebo\Trigger\System::TRIGGER_KEY_SYSTEM_AUTOINSTALL,
                    array($this, 'autoInstall')
                );

                if (\Aomebo\Database\Adapter::useDatabase()) {
                    
                    if (\Aomebo\Application::shouldAutoInstall()) {
                        self::autoInstall();
                    }

                    self::_loadRootSession();
                    self::_loadSessionBlocks();

                    if (\Aomebo\Dispatcher\System::isPageRequest()) {
                        self::_sessionGarbageCollect();
                    }

                    self::_initSessionEvent();

                }

                self::_flagThisConstructed();

            }

        }

        /**
         * @static
         * @throws \Exception
         */
        public static function autoInstall()
        {
            if (\Aomebo\Database\Adapter::useDatabase()) {
                if (!self::isInstalled())
                {
                    self::install();
                    if (!self::isInstalled()) {
                        Throw new \Exception(
                            self::systemTranslate(
                                'Could not install Session Handler'
                            )
                        );
                    }
                }
            }
            return true;
        }

        /**
         * This functions is triggered from blocks
         * when they try to save block data.
         *
         * @static
         * @param Blocks\Base $block
         * @param array $blockData
         */
        public static function saveSessionBlockData(& $block, $blockData)
        {
            $blockName = $block->getName();
            if (isset(self::$_blockNameToBlockObject[$blockName])) {
                self::$_blockNameToBlockData[$blockName] = $blockData;
                self::$_blockNameToSaveBlockData[$blockName] = true;
            }
        }

        /**
         * This functions is triggered from blocks when they
         * try to save block data instantly.
         *
         * @static
         * @param Blocks\Base $block
         * @param array $blockData
         */
        public static function saveInstantSessionBlockData(& $block, $blockData)
        {
            self::saveSessionBlockData($block, $blockData);
            $block->initSessionEvent($blockData);
            self::processEvaluation();
        }

        /**
         * This function saves what needs to be saved after a response.
         *
         * @static
         */
        public static function processEvaluation()
        {

            if (!\Aomebo\Dispatcher\System::isShellRequest()) {
                if (\Aomebo\Database\Adapter::useDatabase()) {

                    // Save root session
                    self::_saveSessionInStorage();

                    // Save all availible session-blocks
                    foreach (self::$_blockNameToSaveBlockData as
                        $blockName => $saveBlockData
                    ) {
                        if ($saveBlockData) {
                            $blockData = self::$_blockNameToBlockData[$blockName];
                            self::_saveSessionBlockData($blockName, $blockData);
                        }
                    }

                    // Should we always send cookie header or is it the right time to send cookie?
                    if ((self::$_sendCookie && self::alwaysUserSession())
                        || (self::$_sendCookie && self::isChangeOfSessionState())
                        || (self::$_sendCookie && self::isOverrideSendCookie())
                    ) {
                        self::_sendSessionCookie(self::$_sessionData['session_id']);
                    }

                }

            }

        }

        /**
         * @static
         * @return bool
         */
        public static function alwaysUserSession()
        {
            return
                \Aomebo\Configuration::getSetting('session,always use session');
        }

        /**
         * @static
         * @param bool [$flag = true]
         */
        public static function flagOverrideSendCookie($flag)
        {
            self::$_overrideSendCookie = $flag;
        }

        /**
         * @static
         * @return bool
         */
        public static function isOverrideSendCookie()
        {
            return (self::$_overrideSendCookie == self::OVERRIDE_SEND_COOKIE_SEND);
        }

        /**
         * @static
         */
        public static function flagSessionDontUpdate()
        {
            self::$_overrideUpdateSession = self::OVERRIDE_UPDATE_SESSION_DONT_UPDATE;
        }

        /**
         * @static
         */
        public static function flagSessionUpdate()
        {
            self::$_overrideUpdateSession = self::OVERRIDE_UPDATE_SESSION_UPDATE;
        }

        /**
         * @static
         */
        public static function flagCookieDontSend()
        {
            self::$_overrideSendCookie = self::OVERRIDE_SEND_COOKIE_DONT_SEND;
        }

        /**
         * @static
         */
        public static function flagCookieSend()
        {
            self::$_overrideSendCookie = self::OVERRIDE_SEND_COOKIE_SEND;
        }

        /**
         * @static
         * @param string $sessionId
         * @return bool
         */
        public static function setSessionId($sessionId)
        {
            if (isset(self::$_sessionData['session_id'])) {
                self::$_sessionData['session_id'] =  $sessionId;
                self::$_sendCookie = true;
                self::flagChangeOfSessionState();
                return true;
            }
            return false;
        }

        /**
         * @static
         * @param bool [$flag = true]
         */
        public static function flagChangeOfSessionState($flag = true)
        {
            self::$_changeOfSessionState = !empty($flag);
        }

        /**
         * @static
         * @return bool
         */
        public static function isChangeOfSessionState()
        {
            return (!empty(self::$_changeOfSessionState));
        }

        /**
         * @static
         * @return string|bool
         */
        public static function getSessionId()
        {
            if (isset(self::$_sessionData['session_id'])) {
                return self::$_sessionData['session_id'];
            }
            return false;
        }

        /**
         * This function returns the current session block object.
         *
         * @static
         * @param string [$blockName = null]
         * @return Blocks\Base|bool
         */
        public static function getSessionBlock($blockName = null)
        {
            if (empty($blockName)) {
                $blockName = self::$_mainBlockName;
            }
            if (isset(self::$_blockNameToBlockObject[$blockName])) {
                return self::$_blockNameToBlockObject[$blockName];
            } else {
                return false;
            }
        }

        /**
         * Returns whether a person is logged in or not.
         *
         * @static
         * @param string [$blockName = null]
         * @return Blocks\Base|bool
         */
        public static function isLoggedIn($blockName = null)
        {
            if (empty($blockName)) {
                $blockName = self::$_mainBlockName;
            }
            if (isset(self::$_blockNameToBlockObject[$blockName])) {

                /** @var \Aomebo\Session\Blocks\Base $blockObject */
                $blockObject = self::$_blockNameToBlockObject[$blockName];

                if ($blockObject->isLoggedIn()) {
                    return true;
                }
            }
            return false;
        }

        /**
         * This function handles logging into account.
         *
         * @static
         * @param string $username
         * @param string $password
         * @param array [$options = null]
         * @param string [$blockName = null]
         * @return bool
         */
        public static function login($username,
            $password, $options = null,
            $blockName = null)
        {
            if (empty($blockName)) {
                $blockName = self::$_mainBlockName;
            }
            if (isset(self::$_blockNameToBlockObject[$blockName])) {

                /** @var \Aomebo\Session\Blocks\Base $blockObject */
                $blockObject = self::$_blockNameToBlockObject[$blockName];

                if ($blockObject->loginEvent(
                    $username, $password, $options)
                ) {
                    self::flagChangeOfSessionState();
                    return true;
                }

            }
            return false;
        }

        /**
         * This function handles logging out from account.
         *
         * @static
         * @param string [$blockName = null]
         * @return bool
         */
        public static function logout($blockName = null)
        {
            if (empty($blockName)) {
                $blockName = self::$_mainBlockName;
            }
            if (isset(self::$_blockNameToBlockObject[$blockName])) {

                /** @var \Aomebo\Session\Blocks\Base $blockObject  */
                $blockObject = self::$_blockNameToBlockObject[$blockName];

                if ($blockObject->logoutEvent()) {
                    self::flagChangeOfSessionState();
                    return true;
                }

            }
            return false;
        }

        /**
         * This function is generating a new session id for a existing session.
         *
         * @static
         */
        public static function changeSessionId()
        {

            $oldSessionId = self::$_sessionData['session_id'];

            self::$_sessionData['session_id'] = self::_getNewSessionId();
            self::$_sendCookie = true;

            \Aomebo\Database\Adapter::query(
                'UPDATE `' . self::getTableSessions() . '` '
                . 'SET `session_id` = {session_id} WHERE '
                . '`session_id` = {old_session_id} LIMIT 1',
                array(
                    'session_id' => array(
                        'value' => self::$_sessionData['session_id'],
                        'quoted' => true,
                    ),
                    'old_session_id' => array(
                        'value' => $oldSessionId,
                        'quoted' => true,
                    )
            ));

            \Aomebo\Database\Adapter::query(
                'UPDATE `' . self::getTableSessionsBlocksData() . '` '
                . 'SET `session_id` = {session_id} WHERE '
                . '`session_id` = {old_session_id} LIMIT 1',
                array(
                    'session_id' => array(
                        'value' => self::$_sessionData['session_id'],
                        'quoted' => true,
                    ),
                    'old_session_id' => array(
                        'value' => $oldSessionId,
                        'quoted' => true,
            )));

        }

        /**
         * @static
         * @param string $sessionId
         * @throws \Exception
         * @return bool
         */
        public static function killSession($sessionId)
        {
            if (isset($sessionId)) {

                $ackBool = true;

                // Delete from sessions blocks table
                if (\Aomebo\Database\Adapter::query(
                    'DELETE FROM `' . self::getTableSessionsBlocksData() . '` '
                    . 'WHERE `session_id` = {session_id} LIMIT 1',
                    array(
                        'session_id' => array(
                            'value' => $sessionId,
                            'quoted' => true,
                        ),
                    ), false, false)
                ) {
                    $ackBool = ($ackBool && true);
                } else {
                    $ackBool = ($ackBool && false);
                }

                // Delete from sessions table
                if (\Aomebo\Database\Adapter::query(
                    'DELETE FROM `' . self::getTableSessions() . '` '
                    . 'WHERE `session_id` = {session_id} LIMIT 1',
                    array(
                        'session_id' => array(
                            'value' => $sessionId,
                            'quoted' => true,
                        ),
                    ), false, false)
                ) {
                    $ackBool = ($ackBool && true);
                } else {
                    $ackBool = ($ackBool && false);
                }

                return $ackBool;

            } else {
                Throw new \Exception('Invalid parameters for ' . __FUNCTION__);
            }
        }

        /**
         * @internal
         * @static
         * @return bool
         */
        public static function clientSentValidCookie()
        {
            return self::$_clientSentValidCookie;
        }

        /**
         * @internal
         * @static
         * @return bool
         */
        public static function clientSentCookie()
        {
            return self::$_clientSentCookie;
        }

        /**
         * @static
         * @return string
         */
        public static function getTableSessions()
        {
            return '{TABLE PREFIX}{SYSTEM TABLE PREFIX}' . self::TABLE_SESSIONS;
        }

        /**
         * @static
         * @return string
         */
        public static function getTableSessionsBlocksData()
        {
            return '{TABLE PREFIX}{SYSTEM TABLE PREFIX}' . self::TABLE_SESSIONS_BLOCKS_DATA;
        }

        /**
         * @internal
         * @static
         * @throws \Exception
         */
        private static function _saveSessionInStorage()
        {

            // Does session id already exist?
            if (self::_sessionExists(self::getSessionId())) {

                $renewNormal =
                    \Aomebo\Configuration::getSetting('session,renew existing session,normal');
                $renewAjax =
                    \Aomebo\Configuration::getSetting('session,renew existing session,ajax');

                // Check if we should update session
                if (self::$_overrideUpdateSession == self::OVERRIDE_UPDATE_SESSION_UPDATE
                    || (((\Aomebo\Dispatcher\System::isNormalRequest()
                    && $renewNormal)
                    || (\Aomebo\Dispatcher\System::isAjaxRequest()
                    && $renewAjax))
                    && self::$_overrideUpdateSession != self::OVERRIDE_UPDATE_SESSION_DONT_UPDATE)
                ) {

                    // Do we fail with updating of session information?
                    if (!\Aomebo\Database\Adapter::query(
                        'UPDATE `' . self::getTableSessions() . '` SET '
                        . '`session_time_last` = {session_time_last},'
                        . '`session_remote_ip` = {session_remote_ip},'
                        . '`session_remote_port` = {session_remote_port},'
                        . '`session_http_agent` = {session_http_agent},'
                        . '`session_http_accept_encoding` = {session_http_accept_encoding},'
                        . '`session_http_connection` = {session_http_connection},'
                        . '`session_request_uri` = {session_request_uri},'
                        . '`session_request_query_string` = {session_request_query_string},'
                        . '`session_request_path_info` = {session_request_path_info} '
                        . 'WHERE `session_id` = {session_id}', array(
                            'session_id' => array(
                                    'value' => self::$_sessionData['session_id'],
                                    'quoted' => true,
                            ),
                            'session_time_last' => 'NOW()',
                            'session_remote_ip' => array(
                                'value' => self::$_sessionData['session_remote_ip'],
                                'quoted' => true,
                            ),
                            'session_remote_port' => array(
                                'value' => self::$_sessionData['session_remote_port'],
                                'quoted' => true,
                            ),
                            'session_http_agent' => array(
                                'value' => self::$_sessionData['session_http_agent'],
                                'quoted' => true,
                            ),
                            'session_http_accept' => array(
                                'value' => self::$_sessionData['session_http_accept'],
                                'quoted' => true,
                            ),
                            'session_http_accept_language' => array(
                                'value' => self::$_sessionData['session_http_accept_language'],
                                'quoted' => true,
                            ),
                            'session_http_accept_encoding' => array(
                                'value' => self::$_sessionData['session_http_accept_encoding'],
                                'quoted' => true,
                            ),
                            'session_http_connection' => array(
                                'value' => self::$_sessionData['session_http_connection'],
                                'quoted' => true,
                            ),
                            'session_request_uri' => array(
                                'value' => self::$_sessionData['session_request_uri'],
                                'quoted' => true,
                            ),
                            'session_request_query_string' => array(
                                'value' => self::$_sessionData['session_request_query_string'],
                                'quoted' => true,
                            ),
                            'session_request_path_info' => array(
                                'value' => self::$_sessionData['session_request_path_info'],
                                'quoted' => true,
                            )))
                    ) {
                        Throw new \Exception('Could not update old session. (query: "'
                            . \Aomebo\Database\Adapter::getLastSql()
                            . '", error:"'
                            . \Aomebo\Database\Adapter::getLastError() . '")');
                    }

                }

            // Otherwise - session is new
            } else {

                // Should sessions always be used or is it a change of session state?
                if (\Aomebo\Configuration::getSetting('session,always use session')
                    || self::isChangeOfSessionState()
                ) {

                    if (!\Aomebo\Database\Adapter::query(
                        'INSERT INTO `'  . self::getTableSessions() . '`'
                        . '(`session_id`,`session_time_start`,'
                        . '`session_time_last`,`session_remote_ip`,`session_remote_port`,'
                        . '`session_http_agent`,`session_http_connection`,'
                        . '`session_http_accept`,`session_http_accept_language`,'
                        . '`session_http_accept_encoding`,'
                        . '`session_request_uri`,`session_request_query_string`,'
                        . '`session_request_path_info`) VALUES'
                        . '({session_id},{session_time_start},'
                        . '{session_time_last},{session_remote_ip},{session_remote_port},'
                        . '{session_http_agent},{session_http_connection},'
                        . '{session_http_accept},{session_http_accept_language},'
                        . '{session_http_accept_encoding},'
                        . '{session_request_uri},{session_request_query_string},'
                        . '{session_request_path_info})',
                        array(
                            'session_id' => array(
                                'value' => self::$_sessionData['session_id'],
                                'quoted' => true,
                            ),
                            'session_time_start' => 'NOW()',
                            'session_time_last' => 'NOW()',
                            'session_remote_ip' => array(
                                'value' => self::$_sessionData['session_remote_ip'],
                                'quoted' => true,
                            ),
                            'session_remote_port' => array(
                                'value' => self::$_sessionData['session_remote_port'],
                                'quoted' => true,
                            ),
                            'session_http_agent' => array(
                                'value' => self::$_sessionData['session_http_agent'],
                                'quoted' => true,
                            ),
                            'session_http_accept' => array(
                                'value' => self::$_sessionData['session_http_accept'],
                                'quoted' => true,
                            ),
                            'session_http_accept_language' => array(
                                'value' => self::$_sessionData['session_http_accept_language'],
                                'quoted' => true,
                            ),
                            'session_http_accept_encoding' => array(
                                'value' => self::$_sessionData['session_http_accept_encoding'],
                                'quoted' => true,
                            ),
                            'session_http_connection' => array(
                                'value' => self::$_sessionData['session_http_connection'],
                                'quoted' => true,
                            ),
                            'session_request_uri' => array(
                                'value' => self::$_sessionData['session_request_uri'],
                                'quoted' => true,
                            ),
                            'session_request_query_string' => array(
                                'value' => self::$_sessionData['session_request_query_string'],
                                'quoted' => true,
                            ),
                            'session_request_path_info' => array(
                                'value' => self::$_sessionData['session_request_path_info'],
                                'quoted' => true,
                            )), false, false)
                    ) {

                        // This occurs probably when a request from same client already has stored the session

                    }

                }

            }
        }

        /**
         * This function does the actual saving of block data when needed.
         *
         * @internal
         * @static
         * @param string $blockName
         * @param array $blockData
         * @return bool
         */
        private static function _saveSessionBlockData($blockName, $blockData)
        {

            $accBool = true;

            \Aomebo\Database\Adapter::query(
                'DELETE FROM `' . self::getTableSessionsBlocksData() . '` '
                . 'WHERE `session_id` = {session_id} '
                . 'AND `session_block_name` = {session_block_name}',
                array(
                    'session_id' => array(
                        'value' => self::$_sessionData['session_id'],
                        'quoted' => true,
                    ),
                    'session_block_name' => array(
                        'value' => $blockName,
                        'quoted' => true,
                    ),
                ));

            foreach ($blockData as $key => $value)
            {

                // Insert value for key
                if (\Aomebo\Database\Adapter::query(
                    'INSERT INTO `' . self::getTableSessionsBlocksData() . '`'
                    . '(`session_id`,`session_block_name`,`session_block_data_key`,'
                    . '`session_block_data_value`,`session_block_data_added`) '
                    . 'VALUES({id},{block_name},{key},{value},{added})',
                    array(
                        'id' => array(
                            'value' => self::$_sessionData['session_id'],
                            'quoted' => true,
                        ),
                        'block_name' => array(
                            'value' => $blockName,
                            'quoted' => true,
                        ),
                        'key' => array(
                            'value' => $key,
                            'quoted' => true,
                        ),
                        'value' => array(
                            'value' => $value,
                            'quoted' => true,
                        ),
                        'added' => array(
                            'value' => 'NOW()',
                            'quoted' => false,
                        ),
                    ), false, false)
                ) {
                    $accBool = ($accBool && true);
                } else {
                    $accBool = false;
                }
            }

            return $accBool;

        }

        /**
         * Determine if session exists.
         *
         * @internal
         * @static
         * @param string [$sessionId = null]
         * @return bool
         */
        private static function _sessionExists($sessionId = null)
        {
            if (self::_getSession($sessionId)) {
                return true;
            }
            return false;
        }

        /**
         * Determine if session id exists.
         *
         * @internal
         * @static
         * @param string|null [$sessionId = null]
         * @return bool
         */
        private static function _sessionIdExists($sessionId = null)
        {
            if (\Aomebo\Database\Adapter::query(
                'SELECT * FROM `' . self::getTableSessions() . '` '
                . 'WHERE ' . '`session_id` = {session_id} LIMIT 1',
                array(
                    'session_id' => array(
                        'value' => (isset($sessionId) ?
                            $sessionId : self::$_sessionData['session_id']),
                        'quoted' => true,
                    ),
                ))
            ) {
                return true;
            }
            return false;
        }

        /**
         * Session id and client browser agent must match.
         *
         * @internal
         * @static
         * @param string|null [$sessionId = null]
         * @return array|bool
         */
        private static function _getSession($sessionId = null)
        {
            if ($result = \Aomebo\Database\Adapter::query(
                'SELECT * FROM `' . self::getTableSessions() . '` '
                . 'WHERE `session_id` = {session_id} '
                . 'AND `session_http_agent` = {session_http_agent} LIMIT 1',
                array(
                    'session_id' => array(
                        'value' => (isset($sessionId) ? $sessionId : self::$_sessionData['session_id']),
                        'quoted' => true,
                    ),
                    'session_http_agent' => array(
                        'value' => self::$_sessionData['session_http_agent'],
                        'quoted' => true,
                    )
                ))
            ) {
                return $result->fetchAssocAndFree();
            }
            return false;
        }

        /**
         * This method parses COOKIE data and client information.
         *
         * @internal
         * @static
         */
        private static function _loadRootSession()
        {

            // Set default values
            self::$_sendCookie = false;
            self::$_sessionData = array();
            self::flagChangeOfSessionState(false);
            self::$_overrideUpdateSession = self::OVERRIDE_UPDATE_SESSION_NONE;
            self::$_overrideSendCookie = self::OVERRIDE_SEND_COOKIE_NONE;

            self::$_sessionData['session_time_last'] = date('Y-m-d H:i:s', time());
            self::$_sessionData['session_remote_ip'] = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            self::$_sessionData['session_remote_port'] = (isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : '');
            self::$_sessionData['session_http_agent'] = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
            self::$_sessionData['session_http_accept'] = (isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '');
            self::$_sessionData['session_http_accept_language'] = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '');
            self::$_sessionData['session_http_accept_encoding'] = (isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '');
            self::$_sessionData['session_http_connection'] = (isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : '');
            self::$_sessionData['session_request_uri'] = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
            self::$_sessionData['session_request_query_string'] = (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');
            self::$_sessionData['session_request_path_info'] = (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '');

            // Build cookie-key name
            $cookieKey =
                \Aomebo\Configuration::getSetting('session,cookie key')
                . \Aomebo\Configuration::getSetting('session,cookie delimiter')
                . self::COOKIE_KEY_SESSION_ID;

            if (!\Aomebo\Dispatcher\System::isShellRequest()) {

                // Do client have a cookie?
                if (!empty($_COOKIE[$cookieKey])) {

                    // Is this cookie representing a existing session?
                    if ($sessionData = self::_getSession($_COOKIE[$cookieKey])) {

                        self::$_sessionData['session_id'] =
                            $sessionData['session_id'];
                        self::$_sessionData['session_time_start'] =
                            $sessionData['session_time_start'];

                    // Otherwise - start new session with old session id
                    } else {
                        self::_startNewSession();
                    }
                } else {
                    self::_startNewSession();
                }

            }

        }

        /**
         * @internal
         * @static
         */
        private static function _startNewSession()
        {
            self::$_sessionData['session_id'] = self::_getNewSessionId();
            self::$_sessionData['session_time_start'] = date('Y-m-d H:i:s', time());
            self::$_sendCookie = true;
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _getNewSessionId()
        {
            do {
                $sid = self::_generateSessionId();
            } while (self::_sessionIdExists($sid));
            return $sid;
        }

        /**
         * @internal
         * @static
         * @return string
         */
        private static function _generateSessionId()
        {
            return md5(
                uniqid(rand(100, 999))
                    . (isset($_SERVER['REMOTE_PORT']) ?
                    $_SERVER['REMOTE_PORT'] : '')
                    . (isset($_SERVER['HTTP_USER_AGENT']) ?
                    $_SERVER['HTTP_USER_AGENT'] : '')
                    . (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ?
                    $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '')
                    . (isset($_SERVER['HTTP_ACCEPT']) ?
                    $_SERVER['HTTP_ACCEPT'] : '')
            );
        }

        /**
         * Send session cookie as HTTP HEADER.
         *
         * @internal
         * @static
         * @param string $value
         */
        private static function _sendSessionCookie($value)
        {
            $headerData = rawurlencode(
                \Aomebo\Configuration::getSetting('session,cookie key')
                . \Aomebo\Configuration::getSetting('session,cookie delimiter')
                . self::COOKIE_KEY_SESSION_ID) . '=' . rawurlencode($value) . '; ';
            $headerData .= 'Expires='
                         . gmdate('D, d-M-Y H:i:s \\G\\M\\T',
                            mktime(0, 0, 0, (int) date('m') + 1,
                            date('d'), date('Y'))) . '; ';
            $headerData .= 'Path=' . \Aomebo\Dispatcher\System::getBaseUri() . '; ';
            $headerData .= 'Domain=' . $_SERVER['SERVER_NAME'] . '; ';
            $headerData .= 'HttpOnly';
            \Aomebo\Dispatcher\System::setHttpHeaderField(
                'Set-Cookie',
                $headerData);
        }

        /**
         * This method loads all availible session blocks.
         *
         * @internal
         * @static
         */
        private static function _loadSessionBlocks()
        {
            $directory = _SITE_ROOT_ . 'Sessions';

            self::$_blockNameToBlockObject = array();
            self::$_blockNameToBlockData = array();
            self::$_blockNameToSaveBlockData = array();
            self::$_mainBlockName =
                strtolower(\Aomebo\Configuration::getSetting('session,handler'));

            if (is_dir($directory)) {

                $dirs = scandir($directory);
                $blocks = array();

                foreach ($dirs as $dir)
                {

                    $csName = $dir;
                    $cisName = strtolower($dir);
                    $path = $directory . DIRECTORY_SEPARATOR . $csName;

                    if ($dir != '..'
                        && $dir != '.'
                        && is_dir($path)
                    ) {

                        $classPath =
                            $path . DIRECTORY_SEPARATOR . 'Block.php';

                        if (file_exists($classPath)) {

                            try {

                                require_once($classPath);

                                $className = '\\Sessions\\' . $csName . '\\Block';

                                if (class_exists($className, false))
                                {

                                    $blocks[$cisName] = true;

                                    /** @var \Aomebo\Session\Blocks\Base $block  */
                                    $block = new $className();

                                    $blockEnabled = true;

                                    if ($blockEnabled) {

                                        if ($config =
                                            \Aomebo\Interpreter\Engine::getBridgeConfig($cisName)
                                        ) {
                                            if (isset($config['enabled'])
                                                && $config['enabled']
                                            ) {

                                                self::$_blockNameToBlockObject[$cisName] = & $block;
                                                $block->loadEvent();

                                            }
                                        } else {

                                            self::$_blockNameToBlockObject[$cisName] = & $block;
                                            $block->loadEvent();

                                        }
                                    }
                                }

                            } catch (\Exception $e) {}

                        }
                    }
                }
            }
        }

        /**
         * This method inits all session-blocks which are loaded.
         *
         * @internal
         * @static
         */
        private static function _initSessionEvent()
        {

            foreach (self::$_blockNameToBlockObject as
                $blockName => $blockObject
            ) {

                /** @var \Aomebo\Session\Blocks\Base $blockObject */

                $blockData = null;

                // Load session-block data
                if (!empty(self::$_sessionData['session_id'])) {
                    if ($result = \Aomebo\Database\Adapter::query(
                        'SELECT * FROM `' . self::getTableSessionsBlocksData() . '` '
                        . 'WHERE `session_id` = {id} '
                        . 'AND `session_block_name` = {block_name}',
                            array(
                                'id' => array(
                                    'value' => self::$_sessionData['session_id'],
                                    'quoted' => true,
                                ),
                                'block_name' => array(
                                    'value' => $blockName,
                                    'quoted' => true,
                                ),
                            ))
                    ) {

                        $result = $result->fetchAssocAllAndFree();
                        $blockData = array();

                        foreach ($result as $row)
                        {
                            $blockData[$row['session_block_data_key']] =
                                $row['session_block_data_value'];
                        }

                    }
                }

                self::$_blockNameToSaveBlockData[$blockName] = false;
                self::$_blockNameToBlockData[$blockName] = $blockData;

                $blockObject->garbageCollect();
                $blockObject->initSessionEvent($blockData, self::$_sessionData);

            }
        }

        /**
         * Clean up expired sessions from database.
         *
         * @internal
         * @static
         */
        private static function _sessionGarbageCollect()
        {

            $garbageCollectOnPageRequests =
                \Aomebo\Configuration::getSetting(
                    'session,garbage collect on page requests');
            $garbageCollectOnShellRequests =
                \Aomebo\Configuration::getSetting(
                    'session,garbage collect on shell requests');

            if ($garbageCollectOnPageRequests
                && \Aomebo\Dispatcher\System::isPageRequest()
                || $garbageCollectOnShellRequests
                && \Aomebo\Dispatcher\System::isShellRequest()
            ) {

                $expires =
                    \Aomebo\Configuration::getSetting('session,expires');
                $lastCheck =
                    \Aomebo\Application::getApplicationData('last_session_garbage_collect');

                if (!isset($lastCheck)
                    || $lastCheck < time() - $expires
                ) {

                    \Aomebo\Application::setApplicationData(
                        'last_session_garbage_collect',
                        time()
                    );

                    if (\Aomebo\Database\Adapter::query(
                        'DELETE FROM `' . self::getTableSessionsBlocksData() . '` '
                        . 'WHERE `session_id` IN '
                        . '(SELECT `session_id` FROM `' . self::getTableSessions() . '` '
                        . 'WHERE `session_time_last` < NOW() - INTERVAL {expires} SECOND)',
                        array(
                            'expires' => (int) $expires,
                        ), true, false)
                    ) {

                        \Aomebo\Database\Adapter::query(
                            'DELETE FROM `' . self::getTableSessions() . '` '
                            . 'WHERE `session_time_last` < NOW() - INTERVAL {expires} SECOND',
                            array(
                                'expires' => (int) $expires,
                            ), true, false);

                    }

                }

            }

        }

        /**
         * This function checks whether the session-handling tables are existing.
         *
         * @internal
         * @static
         * @return bool
         */
        public static function isInstalled()
        {
            if (\Aomebo\Database\Adapter::tableExists(
                    self::getTableSessions())
                && \Aomebo\Database\Adapter::tableExists(
                    self::getTableSessionsBlocksData())
            ) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * This function installs session-handling tables unrelated to blocks.
         *
         * @internal
         * @static
         * @throws \Exception
         * @return bool
         */
        public static function install()
        {

            $databaseAdapter =
                strtolower(\Aomebo\Configuration::getSetting('database,adapter'));

            /** Aomebo Indexing Engine only supports mysql or mysqli */
            if ($databaseAdapter == 'mysqli'
                || $databaseAdapter == 'mysql'
            ) {

                $storageEngine =
                    strtolower(\Aomebo\Configuration::getSetting('database,storage engine'));

                // Is storage engine myisam or innodb or all?
                if ($storageEngine == 'myisam'
                    || $storageEngine == 'innodb'
                    || $storageEngine == 'all'
                ) {

                    // Is storage-engine innodb or all?
                    if ($storageEngine == 'innodb'
                        || $storageEngine == 'all'
                    ) {

                        // Create sessions table
                        \Aomebo\Database\Adapter::query(
                            'CREATE TABLE IF NOT EXISTS `' . self::getTableSessions() . '`('
                            . '`session_id` VARCHAR(50) NOT NULL,'
                            . '`session_time_start` DATETIME NOT NULL,'
                            . '`session_time_last` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
                            . '`session_remote_ip` VARCHAR(20) NOT NULL,'
                            . '`session_remote_port` VARCHAR(10) NOT NULL,'
                            . '`session_http_agent` VARCHAR(200) NOT NULL,'
                            . '`session_http_accept` VARCHAR(100) NOT NULL,'
                            . '`session_http_accept_language` VARCHAR(100) NOT NULL,'
                            . '`session_http_accept_encoding` VARCHAR(100) NOT NULL,'
                            . '`session_http_connection` VARCHAR(100) NOT NULL,'
                            . '`session_request_uri` VARCHAR(100) NOT NULL,'
                            . '`session_request_query_string` VARCHAR(100) NOT NULL,'
                            . '`session_request_path_info` VARCHAR(100) NOT NULL,'
                            . 'PRIMARY KEY (`session_id`)) ENGINE=InnoDB DEFAULT CHARSET={DATA CHARSET};'
                        );

                        \Aomebo\Database\Adapter::query(
                            'CREATE TABLE IF NOT EXISTS `' . self::getTableSessionsBlocksData() . '` ('
                            . '`session_id` VARCHAR(50) NOT NULL,'
                            . '`session_block_name` VARCHAR(50) NOT NULL,'
                            . '`session_block_data_key` VARCHAR(100) NOT NULL DEFAULT "",'
                            . '`session_block_data_value` LONGTEXT NOT NULL DEFAULT "",'
                            . '`session_block_data_added` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",'
                            . '`session_block_data_edited` TIMESTAMP NOT NULL DEFAULT "0000-00-00 00:00:00" ON UPDATE CURRENT_TIMESTAMP, '
                            . 'PRIMARY KEY (`session_id`, `session_block_name`, `session_block_data_key`), '
                            . 'FOREIGN KEY (`session_id`) REFERENCES '
                            . '`' . self::getTableSessions() . '`(`session_id`) '
                            . 'ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB DEFAULT CHARSET={DATA CHARSET};');

                    } else {

                        // Create sessions table
                        \Aomebo\Database\Adapter::query(
                            'CREATE TABLE IF NOT EXISTS `' . self::getTableSessions() . '`('
                            . '`session_id` VARCHAR(50) NOT NULL,'
                            . '`session_time_start` DATETIME NOT NULL,'
                            . '`session_time_last` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
                            . '`session_remote_ip` VARCHAR(20) NOT NULL,'
                            . '`session_remote_port` VARCHAR(10) NOT NULL,'
                            . '`session_http_agent` VARCHAR(200) NOT NULL,'
                            . '`session_http_accept` VARCHAR(100) NOT NULL,'
                            . '`session_http_accept_language` VARCHAR(100) NOT NULL,'
                            . '`session_http_accept_encoding` VARCHAR(100) NOT NULL,'
                            . '`session_http_connection` VARCHAR(100) NOT NULL,'
                            . '`session_request_uri` VARCHAR(100) NOT NULL,'
                            . '`session_request_query_string` VARCHAR(100) NOT NULL,'
                            . '`session_request_path_info` VARCHAR(100) NOT NULL,'
                            . 'PRIMARY KEY (`session_id`)) ENGINE=MyISAM DEFAULT CHARSET={DATA CHARSET};'
                        );

                        \Aomebo\Database\Adapter::query(
                            'CREATE TABLE IF NOT EXISTS `' . self::getTableSessionsBlocksData() . '` ('
                            . '`session_id` VARCHAR(50) NOT NULL,'
                            . '`session_block_name` VARCHAR(50) NOT NULL,'
                            . '`session_block_data_key` VARCHAR(100) NOT NULL DEFAULT "",'
                            . '`session_block_data_value` LONGTEXT NOT NULL DEFAULT "",'
                            . '`session_block_data_added` DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",'
                            . '`session_block_data_edited` TIMESTAMP NOT NULL DEFAULT "0000-00-00 00:00:00" ON UPDATE CURRENT_TIMESTAMP, '
                            . 'PRIMARY KEY (`session_id`, `session_block_name`, `session_block_data_key`)) '
                            . 'ENGINE=MyISAM DEFAULT CHARSET={DATA CHARSET};'
                        );

                    }
                } else {
                    Throw new \Exception(
                        'Aomebo Session Handler only supports MyISAM or InnoDB as storage engines'
                    );
                }
            } else {
                Throw new \Exception(
                    'Aomebo Session Handler only supports MySQL or MySQLi as database adapter'
                );
            }
        }

    }
}

