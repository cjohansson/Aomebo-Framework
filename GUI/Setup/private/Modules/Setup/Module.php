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
         * @return null|string
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
                    '/.*/',
                    '%s',
                    array('random'),
                    'test'),
            );
        }

        /**
         * @return bool|mixed|string
         */
        public function execute()
        {

/*
            $view = \Aomebo\Template\Adapters\Php\Adapter::getInstance();
            $view->setFile('views/view.php');
            return $view->parse();
*/

            $view = \Aomebo\Template\Adapters\Smarty\Adapter::getInstance();
            $view->setFile('views/view.tpl');
            return $view->parse();

        }


    }

}
