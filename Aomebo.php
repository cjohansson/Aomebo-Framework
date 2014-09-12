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
 * @method static \Aomebo getInstance()
 */
final class Aomebo extends \Aomebo\Singleton
{

    /**
     * @static
     * @return \Aomebo\Application
     */
    public static function Application()
    {
        return
            \Aomebo\Application::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Pointers\Associatives
     */
    public static function Associatives()
    {
        return
            \Aomebo\Pointers\Associatives::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Pointers\Cache
     */
    public static function Cache()
    {
        return
            \Aomebo\Pointers\Cache::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Configuration
     */
    public static function Configuration()
    {
        return
            \Aomebo\Configuration::getInstance();
    }


    /**
     * @static
     * @return \Aomebo\Pointers\Database
     */
    public static function Database()
    {
        return
            \Aomebo\Pointers\Database::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Pointers\Dispatcher
     */
    public static function Dispatcher()
    {
        return
            \Aomebo\Pointers\Dispatcher::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Pointers\Feedback
     */
    public static function Feedback()
    {
        return
            \Aomebo\Pointers\Feedback::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Filesystem
     */
    public static function Filesystem()
    {
        return
            \Aomebo\Filesystem::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Pointers\Indexing
     */
    public static function Indexing()
    {
        return
            \Aomebo\Pointers\Indexing::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Pointers\Internationalization
     */
    public static function Internationalization()
    {
        return
            \Aomebo\Pointers\Internationalization::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Pointers\Interpreter
     */
    public static function Interpreter()
    {
        return
            \Aomebo\Pointers\Interpreter::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Pointers\Presenter
     */
    public static function Presenter()
    {
        return
            \Aomebo\Pointers\Presenter::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Pointers\Session
     */
    public static function Session()
    {
        return
            \Aomebo\Pointers\Session::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Pointers\System
     */
    public static function System()
    {
        return
            \Aomebo\Pointers\System::getInstance();
    }

    /**
     * @static
     * @return \Aomebo\Pointers\Trigger
     */
    public static function Trigger()
    {
        return
            \Aomebo\Pointers\Trigger::getInstance();
    }


}

