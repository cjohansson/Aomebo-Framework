<?php
/**
 *
 */

/**
 *
 */
namespace Modules\Header
{

    /**
     * @method static \Modules\Header\Module getInstance()
     */
    class Module extends \Aomebo\Runtime\Module implements
        \Aomebo\Runtime\Executable
    {

        /**
         * @return bool|mixed|string
         */
        public function execute()
        {
            $view = \Aomebo\Template\Adapters\Smarty\Adapter::getInstance();
            $view->setFile('views/view.tpl');
            $view->attachVariable('title',
                \Aomebo\Configuration::getSetting('framework,name'));
            $view->attachVariable('version',
                \Aomebo\Configuration::getSetting('framework,version'));
            return $view->parse();
        }

    }

}
