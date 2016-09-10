<?php
/**
 *
 */

/**
 *
 */
namespace Modules\Html
{

    /**
     * @method static \Modules\Html\Module getInstance()
     */
    class Module extends \Aomebo\Runtime\Module implements
        \Aomebo\Runtime\Executable,
        \Aomebo\Runtime\ExecutionParameters,
        \Aomebo\Runtime\Dependent
    {

	    /**
	     * @static
	     * @var string
	     */
	    private static $_title;

        /**
         * @return array|bool
         */
        public function getDependencies()
        {
            return array(
                new \Aomebo\Associatives\Dependent('jQuery'));
        }

        /**
         * @return array|bool
         */
        public function getParameters()
        {
	        return array('title', 'body');
        }

        /**
         * @static
         * @param string $title
         */
        public static function setTitle($title)
        {
	        self::$_title = $title;
        }

        /**
         * @static
         * @return string
         */
        public static function getTitle()
        {
	        return self::$_title;
        }

        /**
         * @return bool|mixed|string
         */
        public function execute()
        {
	        if (empty(self::$_title)) {
		        self::$_title = $this->getField('title');
	        }
	        $view = self::_getTwigView();
	        $view->setFile('views/view.twig');
	        $view->attachVariables(array(
		        'title' => self::$_title,
		        'body' => $this->getField('body'),
	        ));
            return $view->parse();
        }

    }
}
