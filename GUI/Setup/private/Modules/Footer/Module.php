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
         * @return string
         */
        public function execute()
        {
            $view = self::_getTwigView();
            $view->setFile('views/view.twig');
            $view->attachVariables(array(
                'website' => \Aomebo\Configuration::getSetting('framework,website'),
                'year' => date('Y'),
            ));
            return $view->parse();
        }

    }

}
