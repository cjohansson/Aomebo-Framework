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
        \Aomebo\Runtime\Cacheable
    {

        /**
         * @return bool
         */
        public function useCache()
        {
            return (!empty($_GET['cache']));
        }

        /**
         * @return string
         */
        public function getCacheParameters()
        {
            return 'Runtime/Modules/Footer';
        }

        /**
         * @return string
         */
        public function getCacheKey()
        {
            return 'logged-in';
        }

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

            $tests = array();
            
            ini_set('display_errors', 1);
            
            $this->_testTriggers();

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
                    
                } else if ($submit['action'] == 'Export configuration.php') {
                    
                    // TODO: Export configuration here
                    
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

            $view = \Aomebo\Template\Adapters\Twig\Adapter::getInstance();
            $view->setFile('views/view.twig');
            // $view = \Aomebo\Template\Adapters\Smarty\Adapter::getInstance();
            // $view->setFile('views/view.tpl');
            $view->attachVariable('tests', $tests);
            $view->attachVariable('submit', $submit);
            $view->attachVariable('locales',
                \Aomebo\Internationalization\System::getLocalesFromDirectory(
                    dirname(dirname(__DIR__)) . '/Language'));

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
                    __('Successfully set locale to %s. '),
                    'en_US'
                );
                    
            } else {
                $tests .= sprintf(
                    __('Failed to set locale to %s. '),
                    'en_US'
                );
            }

            if (\Aomebo\Internationalization\System::setLocale('sv_SE')) {

                $tests .= sprintf(
                    __('Successfully set locale to %s. '),
                    'sv_SE'
                );

            } else {
                $tests .= sprintf(
                    __('Failed to set locale to %s. '),
                    'sv_SE'
                );
            }

            if (\Aomebo\Internationalization\System::setLocale($locale)) {
                
                $tests .= sprintf(
                    __('Successfully set locale to %s. '),
                    $locale
                );

            } else {
                $tests .= sprintf(
                    __('Failed to set locale to %s. '),
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
                true,
                false)
            )  {

                $databaseTests = sprintf(
                    __('Connected to host `%s`. '
                    . 'Selected database `%s`. '),
                    $host,
                    $database
                );

                $rawSql = 'SELECT * FROM WHERE `user` = {user}';

                $preparedSql = \Aomebo\Database\Adapter::prepare(
                    $rawSql, array('user' => '1 OR 1=1'));

                $rawSql2 = 'SELECT * FROM WHERE `user` = %s';

                $preparedSql2 = \Aomebo\Database\Adapter::preparef($rawSql2, "'1' OR 1=1");
                
                // TODO: Should verify escaping here

                $table = \Modules\Setup\Table::getInstance();
                
                if (!empty($autoInstall)) {
                    if (\Aomebo\Application::autoInstall()) {
                        $databaseTests .= 
                            __('System successfully auto-installed. ');
                    } else {
                        $databaseTests .=
                            __('System failed to auto-install. ');
                    }
                }
                
                if (!empty($autoUninstall)) {
                    if (\Aomebo\Application::autoUninstall()) {
                        $databaseTests .=
                            __('System successfully auto-uninstalled. ');
                    } else {
                        $databaseTests .=
                            __('System failed to auto-uninstall. ');
                    }
                }

                if (!empty($autoUpdate)) {
                    if (\Aomebo\Application::autoUpdate()) {
                        $databaseTests .=
                            __('System successfully auto-updated. ');
                    } else {
                        $databaseTests .=
                            __('System failed to auto-update. ');
                    }
                }

                if ($table->exists()) {

                    $databaseTests .= sprintf(
                        __('Table `%s` exists. '),
                        $table->getName()
                    );

                    if ($table->drop()) {

                        $databaseTests .= sprintf(
                            __('Dropped table `%s`. '),
                            $table->getName()
                        );

                    } else {
                        $databaseTests .=
                            sprintf(
                                __('Failed to drop table `%s`. '),
                                $table->getName()
                            );
                    }

                } else {
                    if ($table->create()) {

                        $databaseTests .= sprintf(
                            __('Table `%s` created. '),
                            $table->getName()
                        );

                        if ($id = $table->add(
                            array(
                                array($table->name, 'Göran Svensson'),
                                array($table->cash, 250),
                            ))
                        ) {

                            $databaseTests .= sprintf(
                                __('Entry added with assigned id %d. '),
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
                                    __('Entry updated with id %d. '),
                                    $id
                                );

                            } else {
                                $databaseTests .= __('Failed to update data. ');
                            }

                            if ($result = $table->select()) {

                                $databaseTests .= sprintf(
                                    __('Entry with id %d selected. Assoc data: "%s". '),
                                    $id,
                                    print_r($result->fetchAssoc(), true)
                                );

                                $result->free();

                            } else {
                                $databaseTests .= __('Failed to select data. ');
                            }

                            if ($result = $table->select()) {

                                $databaseTests .= sprintf(
                                    __('Entry with id %d selected again. Object data: "%s". '),
                                    $id,
                                    print_r($result->fetchObjectAndFree(), true)
                                );

                            } else {
                                $databaseTests .= __('Failed to select data. ');
                            }


                            $table->delete(array(array($table->id, $id)));

                            $databaseTests .= sprintf(
                                __('Entry with id %d deleted. '),
                                $id
                            );

                        } else {

                            $databaseTests .= __('Failed to add data to table. ');

                        }

                        $table->delete();

                        $databaseTests .= __('All entries deleted. ');

                    } else {
                        $databaseTests .= __('Failed to create table. ');
                    }
                }
            } else {
                $databaseTests .= sprintf(
                    __('Failed to connect to host `%s` or failed to select database `%s`. '),
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
            
            $here = true;
            
        }

    }

}
