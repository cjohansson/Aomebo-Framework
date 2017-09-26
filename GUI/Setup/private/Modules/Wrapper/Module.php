<?php
/**
 *
 */

/**
 *
 */
namespace Modules\Wrapper
{

    /**
     * @method static \Modules\Wrapper\Module getInstance()
     */
    class Module extends \Aomebo\Runtime\Module implements
        \Aomebo\Runtime\Executable,
        \Aomebo\Runtime\ExecutionParameters
    {

        /**
         * @return array|bool
         */
        public function getParameters()
        {
            return array('contents');
        }

        /**
         * @return string
         */
        public function execute()
        {
            $view = self::_getTwigView();
            $view->setFile('views/view.twig');
            $view->attachVariables(array(
                'contents' => $this->getField('contents'),
            ));
            return $view->parse();
        }

    }

}
