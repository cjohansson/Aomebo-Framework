<?php
/**
 *
 */

/**
 *
 */
namespace Modules\Footer
{

    /**
     * @method static \Modules\Footer\Module getInstance()
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
            $view->attachVariable('website',
                \Aomebo\Configuration::getSetting('framework,website'));
            $view->attachVariable('year', date('Y'));

            return $view->parse();
        }

    }

}
