<?php
/**
 * Module
 */

/**
 *
 */
namespace Modules\Footer
{

    /**
     * @method static \Modules\Footer\Module getInstance()
     */
    class Module extends \Aomebo\Runtime implements \Aomebo\Runtime\Executable
    {

        /**
         * @return bool|mixed|string
         */
        public function execute()
        {
            $view = \Aomebo\Template\Adapters\Smarty\Adapter::getInstance();
            $view->setFile('views/view.tpl');
            return $view->parse();
        }


    }

}
