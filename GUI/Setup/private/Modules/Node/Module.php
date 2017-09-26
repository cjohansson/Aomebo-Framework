<?php
/**
 *
 */

/**
 *
 */
namespace Modules\Node
{

    /**
     * @method static \Modules\Node\Module getInstance()
     */
    class Module extends \Aomebo\Runtime\Module implements
        \Aomebo\Runtime\Executable,
        \Aomebo\Runtime\Internationalized,
        \Aomebo\Runtime\Pageable,
        \Aomebo\Runtime\ExecutionParameters
    {

        /**
         * Should return an associative array containing page => page data or boolean false.
         *
         * @return array|bool
         */
        public function getPages()
        {
            return array(
                'file_not_found' => array(
                    array(
                        'key' => 'html',
                        'value' => array(
                            array(
                                'key' => 'title',
                                'value' => 'File Not Found',
                            ),
                            array(
                                'key' => 'body',
                                'value' => array(
                                    array(
                                        'key' => 'wrapper',
                                        'value' => array(
                                            array(
                                                'key' => 'header',
                                                'value' => '',
                                            ),
                                            array(
                                                'key' => 'node',
                                                'value' => 'file_not_found',
                                            ),
                                            array(
                                                'key' => 'footer',
                                                'value' => '',
                                            )
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            );
        }

        /**
         * Should return an associative array with uri => page or boolean false.
         *
         * @return array|bool
         */
        public function getUriToPages()
        {
            return array(
                'file-not-found' => 'file_not_found',
            );
        }

        /**
         * Should return an associative array with page => uri or boolean false.
         *
         * @return array|bool
         */
        public function getPagesToUri()
        {
            return array(
                'file_not_found' => 'file-not-found',
            );
        }


        /**
         * Should return boolean FALSE or an associative array( $textDomain => $location )
         * @return bool|array
         */
        public function getTextDomains()
        {
            return array(
                'node' => __DIR__ . '/Locales',
            );
        }

        /**
         * @return array|bool
         */
        public function getParameters()
        {
            return array('template');
        }

        /**
         * @return string|void
         */
        public function execute()
        {
            $templatePath = dirname(__FILE__) . '/views/' . $this->getField('template') . '.twig';
            if (file_exists($templatePath)) {
                $view = self::_getTwigView();
                $view->setFile('views/' . $this->getField('template') . '.twig');
                return $view->parse();
            }
        }

    }

}