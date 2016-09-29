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
	        $view = self::_getTwigView();
            $view->setFile('views/view.twig');
            $view->attachVariables(array(
	            'title' => \Aomebo\Configuration::getSetting('framework,name'),
	            'version' => \Aomebo\Application::getVersion(),
	            'menu' => Model::getMenu(),
            ));
            return $view->parse();
        }

    }
}
