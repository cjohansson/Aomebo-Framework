<?php
/**
 *
 */

/**
 *
 */
namespace Aomebo
{

    /**
     *
     */
	class ApplicationTest extends \PHPUnit\Framework\TestCase
    {

	    /**
	     *
	     */
	    public function testSetup()
	    {
		    ob_start();
		    require_once(dirname(__DIR__) . '/Application.php');
		    new \Aomebo\Application(array(
			    \Aomebo\Application::PARAMETER_PUBLIC_INTERNAL_PATH => dirname(__DIR__),
                \Aomebo\Application::PARAMETER_PUBLIC_EXTERNAL_PATH => '/',
			    \Aomebo\Application::PARAMETER_SHOW_SETUP => true,
		    ));
            $test = ob_get_contents();
		    ob_end_clean();

		    $this->assertNotEmpty(
			    $test,
			    'Setup Request was not empty'
		    );
            $this->assertTrue(
                strpos($test, '<h1>Aomebo Framework</h1>') !== false
            );
	    }

	}
}