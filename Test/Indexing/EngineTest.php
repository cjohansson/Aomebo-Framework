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
 * This namespace contains Aomebo-related code and nothing else
 */
namespace Aomebo\Indexing;

require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'PHPUnitBootstrap.php');

/**
 * ModelTest
 *
 * Unit tests for Model Email
 */
class EngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Engine
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Engine;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Engine::getInstance
     */
    public function testGetInstance()
    {
        $object = \Aomebo\Indexing\Engine::getInstance();
        $newObject = new \Aomebo\Indexing\Engine();
        $this->assertEquals($newObject, $object);
    }

    /**
     * @covers Engine->addUri($uri, $row)
     */
    public function testAddUri()
    {
        $iterations = 10;
        for ($i = 0; $i < $iterations; $i++)
        {
            $uri = $this->_generateUniqueUri();
            if (!$get = $this->object->getUri($uri)) {

                $row = $this->_generateRow($uri);
                if ($add = $this->object->addUri($uri, $row)) {

                    if ($get2 = $this->object->getUri($uri)) {

                        unset($get2['added'], $get2['edited']);

                        $this->assertEquals($get2, $row, 'Added row equals loaded for uri: "' . $uri . '", stored: "' . print_r($row, true) . '", loaded: "' . print_r($get2, true) . '"');

                        $row2 = $this->_generateRow($uri);

                        if ($update = $this->object->updateUri($uri, $row2)) {

                            if ($get2 = $this->object->getUri($uri)) {

                                unset($get2['added'], $get2['edited']);

                                $this->assertEquals($get2, $row2, 'Added row equals loaded');

                                if ($remove = $this->object->removeUri($uri)) {

                                    $get3 = $this->object->getUri($uri);
                                    $this->assertFalse($get3, 'Uri is no longer existing');

                                }
                                $this->assertTrue($remove, 'Succesfully deleted uri');

                            }

                        }
                        $this->assertTrue($update, 'Successfully update uri');

                    }
                    $this->assertTrue((isset($get2) && is_array($get2)), 'Uri is found in database');


                }
                $this->assertTrue($add, 'Succesfully added uri');

            }
            $this->assertTrue((isset($get) && $get === false), 'Uri is unique');

        }
    }

    /**
     * Generate random uri
     *
     * @return string
     */
    private function _generateUri()
    {
        $chars = 'abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVWYZ/.-';
        $strlen = strlen($chars);
        $uri = '';
        for ($i = 0; $i < 50; $i++) {
            $rand = rand(0, $strlen);
            $uri .= $chars[$rand];
        }
        return $uri;
    }

    /**
     * Generate a random unique uri
     *
     * @return string
     */
    private function _generateUniqueUri()
    {
        $uri = $this->_generateUri();
        while ($this->object->getUri($uri)) {
            $uri = $this->_generateUri();
        }
        return $uri;
    }

    /**
     * Generate random text
     *
     * @param int [$letters = 30]
     * @return string
     */
    private function _generateText($letters = 30)
    {
        $chars = 'abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVWYZ .,';
        $strlen = strlen($chars);
        $text = '';
        for ($i = 0; $i < $letters; $i++) {
            $rand = rand(0, $strlen);
            $text .= $chars[$rand];
        }
        return $text;
    }


    /**
     * Generate random row
     *
     * @param string [$uri = '']
     * @return array
     */
    private function _generateRow($uri = '')
    {
        $content = $this->_generateText(200);
        $contentMd5 = md5($content);
        $title = $this->_generateText(30);
        $description = $this->_generateText(55);
        $keywords = $this->_generateText(10);
        return array(
            'uri' => (!empty($uri) ? $uri : $this->_generateUri()),
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'content_md5' => $contentMd5,
            'content_last_modified' => '0000-00-00 00:00:00',
            'content_modification_duration' => '0',
            'content_modification_duration_norm' => '0',
            'content_modification_number' => '0',
        );
    }

}
