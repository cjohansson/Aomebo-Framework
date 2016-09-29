<?php
/**
 *
 */

/**
 *
 */
namespace Modules\Test
{

    /**
     * @method static \Modules\Test\Module getInstance()
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
		    if (\Aomebo\Response\Handler::isResponse('ajax')) {
			    return $this->_ajax();
		    } else {
			    \Modules\Html\Module::setTitle(__('Test', 'test'));
			    return $this->_test();
		    }
	    }

	    /**
	     * @internal
	     * @return string
	     */
	    private function _ajax()
	    {
		    $view = self::_getTwigView();
		    $view->setFile('views/ajax.twig');
		    return $view->parse();
	    }

        /**
         * @internal
         * @return string
         */
        private function _test()
        {
		    $tests = array();
		    $this->_testTriggers();

		    $command = escapeshellarg(_SYSTEM_ROOT_ . 'test.sh');
		    $tests[] = shell_exec($command);

		    if (\Aomebo\Dispatcher\System::isHttpPostRequestWithPostData()) {

			    $submit = array(
				    'database_host' => self::_getPostLiterals('database_host'),
				    'database_database' => self::_getPostLiterals('database_database'),
				    'database_username' => self::_getPostLiterals('database_username'),
				    'database_password' => self::_getPostLiterals('database_password'),
				    'database_type' => self::_getPostLiterals('database_type'),
				    'database_dsn' => self::_getPostLiterals('database_dsn'),
				    'action' => self::_getPostLiterals('action'),
				    'locale' => self::_getPostLiterals('localization_locale'),
				    'database_autoinstall' => self::_getPostBoolean('database_autoinstall'),
				    'database_autouninstall' => self::_getPostBoolean('database_autouninstall'),
				    'database_autoupdate' => self::_getPostBoolean('database_autoupdate'),
			    );

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

			    if ($submit['action'] == 'Test') {

				    // Use locale?
				    if (!empty($submit['locale'])) {
					    $tests[] = $this->_testLocale($submit['locale']);
				    }

				    if (!empty($submit['database_host'])
				        && !empty($submit['database_username'])
				        && !empty($submit['database_type'])
				    ) {
					    if ($dbTests = $this->_testDatabase(
						    $submit['database_host'],
						    $submit['database_database'],
						    $submit['database_username'],
						    $submit['database_password'],
						    $submit['database_type'],
						    $submit['database_dsn'],
						    $submit['database_autoinstall'],
						    $submit['database_autouninstall'],
						    $submit['database_autoupdate'])
					    ) {
						    $tests[] = $dbTests;
					    }
				    }

			    }

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

		    $ajax = $this->_ajax();

		    $view = self::_getTwigView();
		    $view->attachVariable('tests', $tests);
		    $view->attachVariable('submit', $submit);
		    $view->attachVariable('title', \Aomebo\Configuration::getSetting('framework,name'));
		    $view->attachVariable('version', \Aomebo\Configuration::getSetting('framework,version'));
		    $view->attachVariable('locales',
		                          \Aomebo\Internationalization\System::getLocalesFromDirectory(
			                          __DIR__ . '/Locales'));
		    $view->setFile('views/view.twig');
		    $view->attachVariable('rewriteEnabled', \Aomebo\Dispatcher\System::isRewriteEnabled());
		    $view->attachVariable('ajax', $ajax);

		    return $view->parse();
	    }

        /**
         * @internal
         * @param string $locale
         * @return string
         */
        private function _testLocale($locale)
        {

            $tests = '';

            \Aomebo\Internationalization\System::init();

            \Aomebo\Internationalization\System::addTextDomain(
                'site',
                dirname(dirname(__DIR__)) . '/Language'
            );

            if (\Aomebo\Internationalization\System::setLocale('en_US')) {

                $tests .= sprintf(
                    'Gettext: ' . __('Successfully set locale to %s. ', 'site'),
                    'en_US'
                );
                $tests .= sprintf(
                    'PHP: ' . __('Successfully set locale to %s. ', 'test'),
                    'en_US'
                );

            } else {

                $tests .= sprintf(
                    'Gettext: ' . __('Failed to set locale to %s. ', 'site'),
                    'en_US'
                );
                $tests .= sprintf(
                    'PHP: ' . __('Failed to set locale to %s. ', 'test'),
                    'en_US'
                );

            }

            if (\Aomebo\Internationalization\System::setLocale('sv_SE')) {

                $tests .= sprintf(
                    'Gettext: ' . __('Successfully set locale to %s. ', 'site'),
                    'sv_SE'
                );
                $tests .= sprintf(
                    'PHP: ' . __('Successfully set locale to %s. ', 'test'),
                    'sv_SE'
                );

            } else {

                $tests .= sprintf(
                    'Gettext: ' . __('Failed to set locale to %s. ', 'site'),
                    'sv_SE'
                );
                $tests .= sprintf(
                    'PHP: ' . __('Failed to set locale to %s. ', 'test'),
                    'sv_SE'
                );

            }

            if (\Aomebo\Internationalization\System::setLocale($locale)) {

                $tests .= sprintf(
                    'Gettext: ' . __('Successfully set locale to %s. ', 'site'),
                    $locale
                );
                $tests .= sprintf(
                    'PHP: ' . __('Successfully set locale to %s. ', 'test'),
                    $locale
                );

            } else {

                $tests .= sprintf(
                    'Gettext: ' . __('Failed to set locale to %s. ', 'site'),
                    $locale
                );
                $tests .= sprintf(
                    'PHP: ' . __('Failed to set locale to %s. ', 'test'),
                    $locale
                );

            }

            return $tests;

        }

        /**
         * @param string $host
         * @param string $database
         * @param string $username
         * @param string [$password = '']
         * @param string $type
         * @param string [$dsn = '']
         * @param bool [$autoInstall = false]
         * @param bool [$autoUninstall = false]
         * @param bool [$autoUpdate = false]
         * @throws \Exception
         * @return string
         */
        private function _testDatabase($host, $database, $username,
            $password = '', $type, $dsn = '', $autoInstall = false,
            $autoUninstall = false, $autoUpdate = false)
        {

            $databaseTests = '';

            \Aomebo\Configuration::saveSetting('database,adapter', $type);

            $options = array(
                'dsn' => $dsn,
            );

            if (\Aomebo\Database\Adapter::connect(
                $host,
                $username,
                $password,
                $database,
                $options,
                true)
            )  {
                $databaseTests = '';
                $databaseTests .= sprintf(
	                __('Connected to host `%s`. ', 'test'),
                    $host
                );

                $selectedDatabase = \Aomebo\Database\Adapter::getSelectedDatabase();
                if ($selectedDatabase == $database) {
	                $databaseTests .= sprintf(
		                __('Selected correct database `%s`. ', 'test'),
		                $selectedDatabase
	                );
                } else {
	                $databaseTests .= sprintf(
		                __('ERROR. Failed to select database `%s`, instead selected to `%s`. ', 'test'),
		                $database,
		                $selectedDatabase
	                );
                }

                if (\Aomebo\Database\Adapter::lostConnection()) {
                    $databaseTests .= __('ERROR. Returned lost connection. ', 'test');
                } else {
                    $databaseTests .= __('Have not lost connection. ', 'test');
                }

                /** @see http://php.net/manual/en/function.mysql-real-escape-string.php */

                $rawSql = 'SELECT * FROM users WHERE user={user} AND password={password}';

                $preparedSql = \Aomebo\Database\Adapter::prepare(
                    $rawSql, array(
                        'user' => array(
                            'value' => 'aidan',
                            'quoted' => true,
                        ),
                        'password' => array(
                            'value' => "' OR ''='",
                            'quoted' => true,
                        )
                    ));

                $rawSql2 = 'SELECT * FROM users WHERE user="%s" AND password="%s"';

                $preparedSql2 = \Aomebo\Database\Adapter::preparef(
                    $rawSql2,
                    'aidan',
                    "' OR ''='"
                );

                if ($preparedSql == "SELECT * FROM users WHERE user=\"aidan\" AND password=\"\' OR \'\'=\'\"") {
                    $databaseTests .= __('Escaping of values for SQL escaping method 1 was valid. ', 'test');
                } else {
                    $databaseTests .= __('ERROR: Escaping of values for SQL escaping method 1 was invalid. ', 'test');
                }

                if ($preparedSql2 == "SELECT * FROM users WHERE user=\"aidan\" AND password=\"\' OR \'\'=\'\"") {
                    $databaseTests .= __('Escaping of values for SQL escaping method 2 was valid. ', 'test');
                } else {
                    $databaseTests .= __('ERROR: Escaping of values for SQL escaping method 2 was invalid. ', 'test');
                }

                if ($preparedSql == $preparedSql2) {
                    $databaseTests .= sprintf(__('SQL escaping methods produced identical escaped SQL: "%s". ', 'test'), $preparedSql);
                } else {
                    $databaseTests .= sprintf(__('ERROR: SQL escaping methods produced different escaped SQL "%s" and "%s". ', 'test'), $preparedSql, $preparedSql2);
                }

                $table = \Modules\Setup\Table::getInstance();

                if (!empty($autoInstall)) {
                    if (\Aomebo\Application::autoInstall()) {
                        $databaseTests .=
                            __('System successfully auto-installed. ', 'test');
                    } else {
                        $databaseTests .=
                            __('System failed to auto-install. ', 'test');
                    }
                }

                if (!empty($autoUninstall)) {
                    if (\Aomebo\Application::autoUninstall()) {
                        $databaseTests .=
                            __('System successfully auto-uninstalled. ', 'test');
                    } else {
                        $databaseTests .=
                            __('System failed to auto-uninstall. ', 'test');
                    }
                }

                if (!empty($autoUpdate)) {
                    if (\Aomebo\Application::autoUpdate()) {
                        $databaseTests .=
                            __('System successfully auto-updated. ', 'test');
                    } else {
                        $databaseTests .=
                            __('System failed to auto-update. ', 'test');
                    }
                }

                if ($table->exists()) {

	                $databaseTests .= sprintf(
                        __('Table `%s` exists. ', 'test'),
                        $table->getName()
                    );
                    if ($fields = $table->getTableColumns()) {
                        $databaseTests .= sprintf(
                            __('Found table fields `%s`. ', 'test'),
                            print_r($fields, true)
                        );
                    } else {
                        $databaseTests .=
                            __('Found no table fields. ', 'test');
                    }

                    if ($table->hasTableColumn('cash')) {
                        $databaseTests .= sprintf(
                            __('Table column "%s" exists. ', 'test'),
                            'cash'
                        );
                    } else {
                        $databaseTests .= sprintf(
                            __('Table column "%s" does not exist. ', 'test'),
                            'cash'
                        );
                    }

                    if ($table->hasTableColumn('casher')) {
                        $databaseTests .= sprintf(
                            __('Table column "%s" exists. ', 'test'),
                            'casher'
                        );
                    } else {
                        $databaseTests .= sprintf(
                            __('Table column "%s" does not exist. ', 'test'),
                            'casher'
                        );
                    }

                    if ($table->drop()) {
                        $databaseTests .= sprintf(
                            __('Dropped table `%s`. ', 'test'),
                            $table->getName()
                        );
                    } else {
                        $databaseTests .=
                            sprintf(
                                __('Failed to drop table `%s`. ', 'test'),
                                $table->getName()
                            );
                    }

                } else {

	                if ($table->create()) {

                        $databaseTests .= sprintf(
                            __('Table `%s` created. ', 'test'),
                            $table->getName()
                        );

                        if ($fields = $table->getTableColumns()) {
                            $databaseTests .= sprintf(
                                __('Found table fields `%s`. ', 'test'),
                                print_r($fields, true)
                            );
                        } else {
                            $databaseTests .=
                                __('Found no table fields. ', 'test');
                        }

                        if ($table->hasTableColumn('cash')) {
                            $databaseTests .= sprintf(
                                __('Table column "%s" exists. ', 'test'),
                                'cash'
                            );
                        } else {
                            $databaseTests .= sprintf(
                                __('Table column "%s" does not exist. ', 'test'),
                                'cash'
                            );
                        }

                        if ($table->hasTableColumn('casher')) {
                            $databaseTests .= sprintf(
                                __('Table column "%s" exists. ', 'test'),
                                'casher'
                            );
                        } else {
                            $databaseTests .= sprintf(
                                __('Table column "%s" does not exist. ', 'test'),
                                'casher'
                            );
                        }

                        if ($id = $table->add(
                            array(
                                array($table->name, 'Göran Svensson'),
                                array($table->cash, 250),
                            ))
                        ) {

                            $databaseTests .= sprintf(
                                __('Entry added with assigned id %d. ', 'test'),
                                $id
                            );

                            if ($table->update(
                                array(array($table->name, 'Göransson')),
                                array(
                                    array($table->id, $id),
                                    array($table->name, 'Göran Svensson')
                                ),
                                5)
                            ) {
                                $databaseTests .= sprintf(
                                    __('Entry updated with id %d. ', 'test'),
                                    $id
                                );
                            } else {
                                $databaseTests .= __('Failed to update data. ', 'test');
                            }

                            if ($result = $table->select()) {
                                $databaseTests .= sprintf(
                                    __('Entry with id %d selected. Assoc data: "%s". ', 'test'),
                                    $id,
                                    print_r($result->fetchAssoc(), true)
                                );
                                $result->free();
                            } else {
                                $databaseTests .= __('Failed to select data. ', 'test');
                            }

                            if ($result = $table->select()) {
                                $databaseTests .= sprintf(
                                    __('Entry with id %d selected again. Object data: "%s". ', 'test'),
                                    $id,
                                    print_r($result->fetchObjectAndFree(), true)
                                );
                            } else {
                                $databaseTests .= __('Failed to select data. ', 'test');
                            }

                            $table->delete(array(array($table->id, $id)));
                            $databaseTests .= sprintf(
                                __('Entry with id %d deleted. ', 'test'),
                                $id
                            );

                        } else {
                            $databaseTests .= __('Failed to add data to table. ', 'test');
                        }

                        $table->delete();
                        $databaseTests .= __('All entries deleted. ', 'test');

                    } else {
                        $databaseTests .= __('Failed to create table. ', 'test');
                    }
                }

                if (\Aomebo\Database\Adapter::disconnect()) {
                    $databaseTests .= __('Successfully disconnected from host. ', 'test');
                } else {
                    $databaseTests .= __('ERROR. Failed to disconnect from host. ', 'test');
                }

                if (\Aomebo\Database\Adapter::reconnect()) {
                    $databaseTests .= __('Successfully reconnected to host. ', 'test');
                } else {
                    $databaseTests .= __('ERROR. Failed to reconnect from host. ', 'test');
                }

                if (\Aomebo\Database\Adapter::lostConnection()) {
                    $databaseTests .=
                                   __('ERROR. Returned lost connection. ', 'test');
                } else {
                    $databaseTests .=
                                   __('Have not lost connection. ', 'test');
                }

                if ($table->create()) {
                    $databaseTests .= sprintf(
                        __('Table `%s` created again. ', 'test'),
                        $table->getName()
                    );
                } else {
                    $databaseTests .= sprintf(
                        __('ERROR. Failed to create table `%s` again. ', 'test'),
                        $table->getName()
                    );
                }

                if ($table->drop()) {
                    $databaseTests .= sprintf(
                        __('Dropped table `%s` again. ', 'test'),
                        $table->getName()
                    );
                } else {
                    $databaseTests .= sprintf(
	                    __('Failed to drop table `%s` again. ', 'test'),
	                    $table->getName()
                    );
                }

            } else {
                $databaseTests .= sprintf(
                    __('Failed to connect to host `%s` or failed to select database `%s`. ', 'test'),
                    $host,
                    $database
                );
            }

            return $databaseTests;

        }

        /**
         *
         */
        private function _testTriggers()
        {
            \Aomebo\Trigger\System::addTrigger(
                'random',
                array( & $this, 'execute'),
                10
            );
            \Aomebo\Trigger\System::addTrigger(
                'random',
                array( & $this, 'useCache'),
                11
            );
            $triggers = \Aomebo\Trigger\System::getTriggers('random');
            // TODO: Implement this
        }

        /**
         * Should return an associative array containing page => page data or boolean false.
         *
         * @return array|bool
         */
        public function getPages()
        {
            return array(
                'test' => array(
                    array(
                        'key' => 'html',
                        'value' => array(
                            array(
                                'key' => 'title',
                                'value' => 'Unit Tests',
                            ),
                            array(
                                'key' => 'body',
                                'value' => array(
	                                array(
		                                'key' => 'wrapper',
		                                'value' => array(
			                                array(
				                                'key' => 'header',
				                                'value' => '',
			                                ),
			                                array(
				                                'key' => 'test',
				                                'value' => '',
			                                ),
			                                array(
				                                'key' => 'footer',
				                                'value' => '',
			                                ),
		                                ),
	                                ),
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
                'test' => 'test',
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
                'test' => 'test',
            );
        }

        /**
         * Should return boolean FALSE or an associative array( $textDomain => $location )
         * @return bool|array
         */
        public function getTextDomains()
        {
            return array(
                'test' => __DIR__ . '/Locales',
            );
        }

    }

}
