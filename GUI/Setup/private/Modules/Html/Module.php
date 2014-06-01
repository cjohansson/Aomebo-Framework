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
         * @var string
         */
        const DEFAULT_DOCTYPE = 'xhtml 1.0 transitional';

        /**
         * @var bool
         */
        private $_isXmlDoctype;

        /**
         * @static
         * @var array
         */
        private static $_pageData = array();

        /**
         * @return array|bool
         */
        public function getDependencies()
        {
            return array(
                new \Aomebo\Associatives\Dependent('jQuery'),
                new \Aomebo\Associatives\Dependent('ImagesLoaded')
            );
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
        public static function setPageTitle($title)
        {
            self::$_pageData['title'] = $title;
        }

        /**
         * @static
         * @return string
         */
        public static function getPageTitle()
        {
            return (!empty(self::$_pageData['title']) ?
                self::$_pageData['title'] : '');
        }

        /**
         * @static
         * @param bool $isSubtitle
         */
        public static function setTitleIsSubTitle($isSubtitle)
        {
            self::$_pageData['title_is_subtitle'] =
                (!empty($isSubtitle));
        }

        /**
         * @static
         * @return bool
         */
        public static function getTitleIsSubTitle()
        {
            return (!empty(self::$_pageData['title_is_subtitle']));
        }

        /**
         * @static
         * @param string $slogan
         */
        public static function setPageSlogan($slogan)
        {
            self::$_pageData['slogan'] = $slogan;
        }

        /**
         * @static
         * @return string
         */
        public static function getPageSlogan()
        {
            return (!empty(self::$_pageData['slogan']) ? self::$_pageData['slogan'] : '');
        }

        /**
         * @return bool|mixed|string
         */
        public function execute()
        {

            $interpreter =
                \Aomebo\Interpreter\Engine::getInstance();
            $dispatcher =
                \Aomebo\Dispatcher\System::getInstance();

            $this->_isXmlDoctype = array(
                'html5' => false,
                'xhtml 1.1' => true,
                'xhtml 1.0 strict' => true,
                'xhtml 1.0 transitional' => true,
                'xhtml 1.0 frameset' => true,
                'html 4.01 strict' => false,
                'html 4.01 transitional' => false,
                'html 4.01 frameset' => false,
            );

            $variables = array();
            $variables['current_page'] = $dispatcher::getPage();
            $variables['doctype'] =
                $this->_getDoctype();
            $variables['doctype_xml_based'] =
                $this->_isXmlBasedDoctype(
                    $variables['doctype']);
            $variables['html_lang'] =
                \Aomebo\Configuration::getSetting('output,language');
            $variables['shortcut_icon'] =
                $this->_getShortcutIcon();

            if (!self::getPageTitle()) {
                self::setPageTitle($this->getField('title'));
            }

            if (!isset(self::$_pageData['title_is_subtitle'])) {
                self::setTitleIsSubTitle(true);
            }

            if (!isset(self::$_pageData['slogan'])) {
                self::setPageSlogan(\Aomebo\Configuration::getSetting('site,slogan'));
            }

            self::$_pageData['meta_title'] = '';
            self::$_pageData['meta_description'] =
                \Aomebo\Configuration::getSetting('site,description');
            self::$_pageData['meta_keywords'] =
                \Aomebo\Configuration::getSetting('site,keywords');
            self::$_pageData['canonical_uri'] = '';

            $variables['public_uri'] =
                _PUBLIC_EXTERNAL_ROOT_;
            $associativesMode =
                \Aomebo\Configuration::getSetting('settings,associatives mode');
            $variables['ajax_uri'] =
                $dispatcher->getAjaxUri();
            $variables['script_dependencies_uri'] =
                _PUBLIC_EXTERNAL_ROOT_ . $associativesMode . '.js?ds=';
            $variables['style_dependencies_uri'] =
                _PUBLIC_EXTERNAL_ROOT_ . $associativesMode . '.css?ds=';
            $variables['script_associatives_uri'] =
                _PUBLIC_EXTERNAL_ROOT_ . $associativesMode . '.js?fs=';
            $variables['style_associatives_uri'] =
                _PUBLIC_EXTERNAL_ROOT_ . $associativesMode . '.css?fs=';
            $variables['site_uri'] =
                \Aomebo\Configuration::getSetting('site,server name');
            $variables['site_title'] =
                \Aomebo\Configuration::getSetting('site,title');
            $variables['site_description'] =
                \Aomebo\Configuration::getSetting('site,description');
            $variables['page_data'] =
                self::$_pageData;
            $variables['meta'] =
                $this->_getMeta();
            $variables['title'] =
                $this->_getTitle();
            $variables['body'] =
                $this->getField('body');
            $variables['logging_out'] = ($dispatcher::getPage() == 'log_out');
            $variables['is_admin'] = false;
            $variables['is_seo'] = false;
            $variables['is_moderator'] = false;

            // Disallow indexing of Xdebug sessions
            if (isset($_GET['XDEBUG_SESSION_START'])
                || isset($_GET['XDEBUG_SESSION_STOP'])
            ) {
                \Aomebo\Indexing\Engine::disallowIndexing();
            }

            $isIphone =
                (isset($_SERVER['HTTP_USER_AGENT']) && (stripos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') !== false) ? true : false);
            $isIpad =
                (isset($_SERVER['HTTP_USER_AGENT']) && (stripos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') !== false) ? true : false);
            $variables['is_ios'] = ($isIphone || $isIpad);

            $view = \Aomebo\Template\Adapters\Smarty\Adapter::getInstance();
            $view->setFile('views/view.tpl');

            $view->attachVariables($variables);
            $tmp = $view->parse();
            $interpreter->setInsertPoint('meta',
                (stripos($tmp, '<title') - 1));
            $interpreter->setInsertPoint('style',
                (stripos($tmp, '</title>') + 9));
            $interpreter->setInsertPoint('script',
                (stripos($tmp, '</title>') + 9));
            return $tmp;

        }

        /**
         * @return string
         */
        private function _getMeta()
        {
            $metas = array();
            if (\Aomebo\Configuration::getSetting('site,show generator')) {
                $metas[] = '<meta name="generator" content="'
                      . \Aomebo\Configuration::getSetting('framework,name') . ' '
                      . \Aomebo\Configuration::getSetting('framework,version') .'" />';
            }
            if (\Aomebo\Configuration::getSetting('output,character set')) {
                $metas[] = '<meta http-equiv="Content-type" content="text/html; charset='
                      . \Aomebo\Configuration::getSetting('output,character set') . '" />';
            }
            if (!empty(self::$_pageData['meta_description'])) {
                $metas[] = '<meta name="description" content="'
                      . self::$_pageData['meta_description'] . '" />';
            }
            if (!empty(self::$_pageData['meta_keywords'])) {
                $metas[] = '<meta name="keywords" content="'
                      . self::$_pageData['meta_keywords']
                      . '" />';
            }
            if (!empty(self::$_pageData['meta_title'])) {
                $metas[] = '<meta name="title" content="'
                      . self::$_pageData['meta_title']
                      . '" />';
            }
            return $metas;
        }

        /**
         * @return string
         */
        private function _getTitle()
        {
            if (!empty(self::$_pageData['title_is_subtitle'])) {
                if (\Aomebo\Configuration::getSetting('site,title direction')
                    == 'append'
                ) {
                    $title = \Aomebo\Configuration::getSetting('site,title')
                           . \Aomebo\Configuration::getSetting('site,title delimiter')
                           . self::getPageSlogan()
                           . \Aomebo\Configuration::getSetting('site,title delimiter')
                           . self::getPageTitle();
                } else {
                    $title = self::getPageTitle()
                        . \Aomebo\Configuration::getSetting('site,title delimiter')
                        . self::getPageSlogan()
                        . \Aomebo\Configuration::getSetting('site,title delimiter')
                        . \Aomebo\Configuration::getSetting('site,title');
                }
            } else {
                $title = self::getPageTitle();
            }
            return $title;
        }

        /**
         * This method gets doctype tag.
         *
         * @return string
         */
        private function _getDoctype()
        {
            $doctype =
                strtolower(\Aomebo\Configuration::getSetting('output,doctype'));
            if (!isset($this->_isXmlDoctype[$doctype])) {
                $doctype = self::DEFAULT_DOCTYPE;
            }
            return $doctype;
        }

        /**
         * This method checks whether doctype is xml based or not.
         *
         * @param string $doctype
         * @return bool
         */
        private function _isXmlBasedDoctype($doctype)
        {
            if (isset($this->_isXmlDoctype[$doctype])
                && $this->_isXmlDoctype[$doctype]
            ) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * This method retreives shortcut icon.
         *
         * @return string
         */
        private function _getShortcutIcon()
        {
            $icon =
                \Aomebo\Configuration::getSetting('site,shortcut icon');
            $suffixToMime = array(
                '.png' => 'image/png',
                '.jpg' => 'image/jpeg',
                '.gif' => 'image/gif',
                '.ico' => 'image/vnd.microsoft.icon'
            );
            $suffix = substr(strtolower($icon), strripos($icon, '.'));
            if (!empty($icon)
                && isset($suffixToMime[$suffix])
            ) {
                $mime = $suffixToMime[$suffix];
                return '<link rel="shortcut icon" href="'
                      . _PUBLIC_EXTERNAL_ROOT_ . \Aomebo\Configuration::getSetting('paths,resources dir')
                      . DIRECTORY_SEPARATOR . $icon . '" type="' . $mime . '" />';
            } else {
                return '';
            }
        }

    }

}
