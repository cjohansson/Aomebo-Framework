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
     * @method static \Modules\Header\Model getInstance()
     */
    class Model extends \Aomebo\Runtime\Model
    {

	    /**
	     * @static
	     * @return array
	     */
	    public static function getMenu()
	    {
		    $menu = array(
			    array(
				    'page' => 'setup',
				    'title' => __('Setup', 'setup'),
			    ),
			    array(
				    'page' => 'test',
				    'title' => __('Test', 'setup'),
			    ),
		    );
		    foreach ($menu as &$item)
		    {
			    $item['uri'] = self::_buildUri(null, $item['page']);
			    $item['active'] = \Aomebo\Dispatcher\System::getPage() == $item['page'];
		    }
		    return $menu;
	    }

    }
}
