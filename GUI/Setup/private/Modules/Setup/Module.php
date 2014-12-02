<?php
/**
 *
 */

/**
 *
 */
namespace Modules\Setup
{
    use Aomebo\Database\Adapters\Table;

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
                    '/^([\w]+)$/',
                    '%s',
                    array('page')
                ),
                new \Aomebo\Dispatcher\Route(
                    null,
                    '/^([\w]+)\/([\w]+)$/',
                    '%s/%s',
                    array('parameter1', 'parameter2')
                ),
                new \Aomebo\Dispatcher\Route(
                    null,
                    '/^([\w]+)\/([\w]+)\/([\w]+)$/',
                    '%s/%s/%s',
                    array('parameter1', 'parameter2', 'parameter3')
                ),
            );
        }

        /**
         * @return string
         */
        public function execute()
        {

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

            $table = \Modules\Setup\Table::getInstance();

            if ($table->exists()) {
                $table->drop();
            } else {
                if ($table->create()) {
                    if ($id = $table->add(
                        array(
                            array($table->name, 'Göran Svensson'),
                            array($table->cash, 250),
                        ))
                    ) {
                        $table->update(
                            array(array($table->name, 'Göransson')),
                            array(
                                array($table->id, $id),
                                array($table->name, 'Göran Svensson')
                            ),
                            5
                        );

                        if ($result = $table->select()) {
                            $all = $result->fetchObjectAndFree();
                        }

                        $table->delete(array(array($table->id, $id)));
                    }
                   $table->delete();
                }
            }

            $view = \Aomebo\Template\Adapters\Smarty\Adapter::getInstance();
            $view->setFile('views/view.tpl');
            $view->attachVariable('locale', \Aomebo\Internationalization\System::getLocale());
            $view->attachVariable('submit', $submit);
            $view->attachVariable('cache', $abc);
            $view->attachVariable('translated', self::__('Invalid parameters'));
            $return = $view->parse();

            $view2 = new \Aomebo\Template\Adapters\Php\Adapter();
            $view2->setFile('views/view.php');
            $view2->attachVariable('locale', \Aomebo\Internationalization\System::getLocale());
            $view2->attachVariable('submit', $submit);
            $view2->attachVariable('cache', $abc);
            $view2->attachVariable('translated', self::__('Invalid parameters'));
            $return2 = $view2->parse();

            return $return . $return2;


        }


    }

}
