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
namespace Aomebo\Session\Blocks
{

    /**
     * @method static \Aomebo\Session\Blocks\Base getInstance()
     */
    abstract class Base extends \Aomebo\Singleton
    {

        /**
         * This variable will hold the sessions name.
         *
         * @var string
         */
        protected $_name;

        /**
         * This variable will hold the sessions configuration.
         *
         * @var array
         */
        protected $_config;

        /**
         * This array will hold all data related to session.
         *
         * @var array
         */
        protected $_blockData;

        /**
         * This method is triggered when the session-block is found.
         */
        abstract public function loadEvent();

        /**
         * @param array|null [$blockData = null]
         * @param array|null [$sessionData = null]
         */
        abstract public function initSessionEvent(
            $blockData = null, $sessionData = null);

        /**
         * @param string $username
         * @param string $password
         * @param array|null [$options = null]
         * @return bool
         */
        abstract public function loginEvent($username,
            $password, $options = null);

        /**
         * @return bool
         */
        abstract public function logoutEvent();

        /**
         * This method returns whether current session is logged in or not.
         *
         * @param int|null [$userId = null]
         * @param string|null [$userName = null]
         * @return bool
         */
        abstract public function isLoggedIn($userId = null,
            $userName = null);

        /**
         * This method performs session-block garbage collect if any specified.
         */
        abstract public function garbageCollect();

        /**
         * @param array|null [$config = null]
         */
        public function __construct($config = null)
        {
            if (!self::_isConstructed()) {

                parent::__construct();

                $file =
                    $this->getAbsoluteFilename();
                $this->_name =
                    strtolower(basename(dirname($file)));
                $this->_config = (isset($config) ? $config : false);

            }
        }

        /**
         * This method is used to set data related to session.
         *
         * @param array $blockData
         */
        public function setBlockData($blockData)
        {
            $this->_blockData = $blockData;
        }

        /**
         * @return array
         */
        public function getBlockData()
        {
            return $this->_blockData;
        }

        /**
         * This method will return the name of this session block.
         *
         * @return string
         */
        public function getName()
        {
            return $this->_name;
        }

        /**
         * This method will save block data instantly and
         * not wait untill interpret page completely.
         */
        protected function _saveInstantBlockData()
        {
            $session =
                \Aomebo\Session\Handler::getInstance();
            $session->saveInstantSessionBlockData(
                $this, $this->_blockData);
        }

        /**
         * When interpretation is finished, all changes between
         * blockdata and old is saved to spare the database connection.
         */
        protected function _saveBlockData()
        {
            $session =
                \Aomebo\Session\Handler::getInstance();
            $session->saveSessionBlockData(
                $this, $this->_blockData);
        }

        /**
         * This method will generate a new session id.
         */
        protected function _refreshSession()
        {
            $session =
                \Aomebo\Session\Handler::getInstance();
            $session->changeSessionId();
        }

    }
}
