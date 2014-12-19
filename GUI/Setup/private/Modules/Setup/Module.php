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

            if (\Aomebo\Dispatcher\System::isHttpPostRequestWithPostData()) {

                $submit = array(
                    'database_host' => self::_getPostLiterals('database_host'),
                    'database_database' => self::_getPostLiterals('database_database'),
                    'database_username' => self::_getPostLiterals('database_username'),
                    'database_password' => self::_getPostLiterals('database_password'),
                    'database_type' => self::_getPostLiterals('database_type'),
                    'database_dsn' => self::_getPostLiterals('database_dsn'),
                );

                if (!empty($submit['database_host'])
                    && !empty($submit['database_database'])
                    && !empty($submit['database_username'])
                    && !empty($submit['database_type'])
                ) {
                    if ($dbTests = $this->_testDatabase(
                        $submit['database_host'],
                        $submit['database_database'],
                        $submit['database_username'],
                        $submit['database_password'],
                        $submit['database_type'],
                        $submit['database_dsn'])
                    ) {
                        $tests[] = $dbTests;
                    }
                }

            } else {
                $submit = array(
                    'database_host' => '',
                    'database_database' => '',
                    'database_username' => '',
                    'database_password' => '',
                );
            }

            $view = \Aomebo\Template\Adapters\Smarty\Adapter::getInstance();
            $view->setFile('views/view.tpl');
            $view->attachVariable('tests', $tests);
            $view->attachVariable('submit', $submit);

            return $view->parse();


        }

        /**
         * @param string $host
         * @param string $database
         * @param string $username
         * @param string [$password = '']
         * @param string $type
         * @param string [$dsn = '']
         * @throws \Exception
         * @return string
         */
        private function _testDatabase($host, $database, $username, 
            $password = '', $type, $dsn = '')
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
                $options)
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


                $table = \Modules\Setup\Table::getInstance();

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

                            $databaseTests .= __('Failed to data to table. ');

                        }

                        $table->delete();

                        $databaseTests .= __('All entries deleted. ');

                    } else {
                        $databaseTests .= __('Failed to create table. ');
                    }
                }
            } else {
                $databaseTests .= sprintf(
                    __('Failed to connect to host `%s` or failed to select database `%`. '),
                    $host,
                    $database
                );
            }

            return $databaseTests;

        }

    }

}
