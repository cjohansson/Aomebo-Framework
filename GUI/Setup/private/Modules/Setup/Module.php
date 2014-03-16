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
                    '/.*/',
                    '%s',
                    array('random'),
                    'test'),
            );
        }

        /**
         * @return string
         */
        public function execute()
        {

            $view = \Aomebo\Template\Adapters\Smarty\Adapter::getInstance();
            $view->setFile('views/view.tpl');

            $submit = array();
            if ($siteSettings = self::$_aomebo->Configuration()->getSetting('site')) {
                $submit['siteTitle'] = $siteSettings['title'];
                $submit['siteTitleDelimiter'] = $siteSettings['title delimiter'];
                $submit['siteTitleDirection'] = $siteSettings['title direction'];
                $submit['siteSlogan'] = $siteSettings['slogan'];
            }

            if ($pathsSettings = self::$_aomebo->Configuration()->getSetting('paths')) {
                $submit['pathsDefaultFileMod'] = $pathsSettings['default file mod'];
            }

            if (self::$_aomebo->Cache()->System()->cacheExists(
                'abcd',
                '123',
                \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_DATABASE)
            ) {

                $abc = self::$_aomebo->Cache()->System()->loadCache(
                    'abcd',
                    '123',
                    \Aomebo\Cache\System::FORMAT_RAW,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_DATABASE
                );

                $abc .= ' (from database)';

            } else {

                $abc = 'random';

                self::$_aomebo->Cache()->System()->saveCache(
                    'abcd',
                    '123',
                    $abc,
                    \Aomebo\Cache\System::FORMAT_RAW,
                    \Aomebo\Cache\System::CACHE_STORAGE_LOCATION_DATABASE
                );

            }

            $view->attachVariable('submit', $submit);
            $view->attachVariable('cache', $abc);

            return $view->parse();

        }


    }

}
