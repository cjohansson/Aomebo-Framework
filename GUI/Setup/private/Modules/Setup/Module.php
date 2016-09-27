<?php
/**
 *
 */

/**
 *
 */
namespace Modules\Setup
{

    /**
     * @method static \Modules\Setup\Module getInstance()
     */
    class Module extends \Aomebo\Runtime\Module implements
        \Aomebo\Runtime\Executable,
        \Aomebo\Runtime\Routable,
        \Aomebo\Runtime\Pageable,
        \Aomebo\Runtime\Internationalized
    {

        /**
         * @return array
         */
        public function getRoutes()
        {
            return array(
                new \Aomebo\Dispatcher\Route(
                    null,
                    '/^([\w]+)$/',
                    '%s',
                    array('page')
                ),
                new \Aomebo\Dispatcher\Route(
                    null,
                    '/^([\w]+)\/([\w]+)$/',
                    '%s/%s',
                    array('parameter1', 'parameter2')
                ),
                new \Aomebo\Dispatcher\Route(
                    null,
                    '/^([\w]+)\/([\w]+)\/([\w]+)$/',
                    '%s/%s/%s',
                    array('parameter1', 'parameter2', 'parameter3')
                ),
            );
        }

	    /**
	     * @return string
	     */
	    public function execute()
	    {
		    if (\Aomebo\Dispatcher\System::isHttpPostRequestWithPostData()) {
			    $config = array(
				    'database' => array(
					    'host' => self::_getPostLiterals('database_host'),
					    'database' => self::_getPostLiterals('database_database'),
					    'username' => self::_getPostLiterals('database_username'),
					    'password' => self::_getPostLiterals('database_password'),
					    'type' => self::_getPostLiterals('database_type'),
					    'options' => array(
						    'dsn' => self::_getPostLiterals('database_dsn'),
					    ),
				    )
			    );

			    $data = \Aomebo\Configuration::generatePhpData($config);
			    \Aomebo\Dispatcher\System::setHttpHeaderField(
				    'Content-Disposition',
				    'attachment; filename="configuration.php"'
			    );
			    \Aomebo\Dispatcher\System::setHttpHeaderField(
				    'Content-Type',
				    'text/php'
			    );
			    \Aomebo\Dispatcher\System::outputHttpHeaders();
			    die($data);

		    } else {
			    $submit = array(
				    'database_host' => '',
				    'database_database' => '',
				    'database_username' => '',
				    'database_password' => '',
				    'database_type' => '',
				    'database_dsn' => '',
				    'locale' => '',
				    'database_autoinstall' => '',
				    'database_autouninstall' => '',
				    'database_autoupdate' => '',
			    );
		    }

		    $view = self::_getTwigView();
		    $view->attachVariable('submit', $submit);
		    $view->setFile('views/view.twig');
		    return $view->parse();
	    }

        /**
         * Should return an associative array containing page => page data or boolean false.
         *
         * @return array|bool
         */
        public function getPages()
        {
            return array(
                'setup' => array(
                    array(
                        'key' => 'html',
                        'value' => array(
                            array(
                                'key' => 'title',
                                'value' => 'Another Setup',
                            ),
                            array(
                                'key' => 'body',
                                'value' =>
                                array(
	                                array(
		                                'key' => 'header',
		                                'value' => '',
	                                ),
                                    array(
                                        'key' => 'setup',
                                        'value' => '',
                                    ),
                                    array(
	                                    'key' => 'footer',
	                                    'value' => '',
                                    )
                                ),
                            ),
                        ),
                    ),
                )
            );
        }

        /**
         * Should return an associative array with uri => page or boolean false.
         *
         * @return array|bool
         */
        public function getUriToPages()
        {
            return array(
                'setup' => 'setup',
            );
        }

        /**
         * Should return an associative array with page => uri or boolean false.
         *
         * @return array|bool
         */
        public function getPagesToUri()
        {
            return array(
                'setup' => 'setup',
            );
        }

        /**
         * Should return boolean FALSE or an associative array( $textDomain => $location )
         * @return bool|array
         */
        public function getTextDomains()
        {
            return array(
                'setup' => __DIR__ . '/Locales',
            );
        }

    }

}
